<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class SignatureService
{
    private $cerPath;
    private $keyPath;
    private $password;

    public function __construct()
    {
        // Configurable from environment for safer deployments.
        $this->cerPath = storage_path('app/' . env('SIGNATURE_CER_PATH', 'firma/VIICyT.cer'));
        $this->keyPath = storage_path('app/' . env('SIGNATURE_KEY_PATH', 'firma/VIICyT.key'));
        $this->password = (string) env('SIGNATURE_KEY_PASSWORD', '');
    }

    /**
     * Genera el Sello Digital (Firma) a partir de una cadena original.
     *
     * @param string $cadenaOriginal
     * @return string|null
     * @throws Exception
     */
    public function sign(string $cadenaOriginal): ?string
    {
        if (!file_exists($this->keyPath)) {
            throw new Exception("No se encontró el archivo de llave privada (.key)");
        }

        // Leer el archivo .key
        $privateKeyContent = file_get_contents($this->keyPath);
        
        // El archivo .key suele estar en formato DER, necesitamos convertirlo o usarlo directamente si OpenSSL lo soporta
        // Intentar cargar la llave privada
        $privateKey = @openssl_pkey_get_private($privateKeyContent, $this->password);

        if (!$privateKey) {
            // Attempt DER -> PEM wrapping for PKCS#8 keys when raw load fails.
            $privateKey = $this->convertDerToPem($privateKeyContent);
        }

        if (!$privateKey) {
            $opensslError = $this->drainOpenSslErrors();
            Log::error('No se pudo cargar la llave privada de firma', [
                'key_path' => $this->keyPath,
                'openssl_error' => $opensslError,
            ]);
            throw new Exception("No se pudo cargar la llave privada. Verifique contraseña, formato .key y extensión OpenSSL.");
        }

        $signature = '';
        // Firmar usando algoritmos estándares (SHA256)
        $result = @openssl_sign($cadenaOriginal, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        
        openssl_free_key($privateKey);

        if ($result) {
            return base64_encode($signature);
        }

        Log::error('No se pudo generar la firma con OpenSSL', [
            'openssl_error' => $this->drainOpenSslErrors(),
        ]);

        return null;
    }

    /**
     * Obtiene el número de serie del certificado .cer
     *
     * @return string|null
     */
    public function getCertificateNumber(): ?string
    {
        if (!file_exists($this->cerPath)) {
            return null;
        }

        $cerContent = file_get_contents($this->cerPath);
        
        // Convertir CER (DER) a PEM para lectura
        $pem = $this->derToPem($cerContent);
        $cert = @openssl_x509_read($pem);
        if ($cert) {
            $certData = openssl_x509_parse($cert);
            if (isset($certData['serialNumber'])) {
                return $certData['serialNumber'];
            }
            if (isset($certData['serialNumberHex'])) {
                return $certData['serialNumberHex'];
            }
        }

        // Fallback manual para certificados tipo SAT/México u otros
        // Buscar secuencias de 20 dígitos que suelen ser el número de serie
        if (preg_match('/([0-9]{20})/', $cerContent, $matches)) {
            return $matches[1];
        }
        
        // Si no hay 20 dígitos, buscar cualquier secuencia larga de números que parezca un serial (15-25 dígitos)
        if (preg_match('/([0-9]{15,25})/', $cerContent, $matches)) {
            return $matches[1];
        }

        return "NO_DISPONIBLE";
    }

    /**
     * Convierte un certificado DER a formato PEM.
     */
    private function derToPem(string $derContent): string
    {
        return "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($derContent), 64, "\n") . "-----END CERTIFICATE-----\n";
    }

    /**
     * Convierte una llave privada DER a formato PEM (PKCS#8).
     */
    private function convertDerToPem(string $derContent)
    {
        if (str_contains($derContent, 'BEGIN')) {
            return @openssl_pkey_get_private($derContent, $this->password);
        }

        $pkcs8Pem = "-----BEGIN PRIVATE KEY-----\n"
            . chunk_split(base64_encode($derContent), 64, "\n")
            . "-----END PRIVATE KEY-----\n";
        $key = @openssl_pkey_get_private($pkcs8Pem, $this->password);
        if ($key) {
            return $key;
        }

        $encPkcs8Pem = "-----BEGIN ENCRYPTED PRIVATE KEY-----\n"
            . chunk_split(base64_encode($derContent), 64, "\n")
            . "-----END ENCRYPTED PRIVATE KEY-----\n";

        return @openssl_pkey_get_private($encPkcs8Pem, $this->password);
    }

    private function drainOpenSslErrors(): string
    {
        $errors = [];
        while ($msg = openssl_error_string()) {
            $errors[] = $msg;
        }

        return empty($errors) ? 'sin detalle de OpenSSL' : implode(' | ', $errors);
    }
}
