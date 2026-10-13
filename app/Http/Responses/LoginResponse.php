<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): Response
    {
        $user = Auth::user();
        
        Log::info('🔐 LoginResponse::toResponse - Usuario: ' . $user->email);
        Log::info('📧 Email verificado: ' . ($user->email_verified_at ? 'SI' : 'NO'));
        
        // Si el usuario no ha verificado su email, redirigir a verificación
        if (is_null($user->email_verified_at)) {
            Log::info('⚠️ Usuario no verificado, iniciando proceso de redirección');
            
            // Guardar email antes de hacer logout
            $userEmail = $user->email;
            $userId = $user->id;
            
            Auth::logout();
            
            // Recargar el usuario después del logout
            $user = \App\Models\User::find($userId);
            
            // Generar nuevo código si no existe o ha expirado
            if (!$user->email_verification_code || $user->email_verification_code_expires_at < now()) {
                $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                Log::info('🔐 Nuevo código generado: ' . $verificationCode);
                
                $user->update([
                    'email_verification_code' => $verificationCode,
                    'email_verification_code_expires_at' => now()->addHours(24),
                ]);
                
                // Reenviar correo de verificación
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)
                        ->queue(new \App\Mail\VerificationCode($user, $verificationCode));
                    Log::info('📧 Correo de verificación enviado a: ' . $user->email);
                    $message = 'Por favor verifica tu correo electrónico. Te hemos enviado un código de verificación.';
                } catch (\Exception $e) {
                    Log::error('❌ Error al enviar correo de verificación en login: ' . $e->getMessage());
                    $message = 'Por favor verifica tu correo electrónico. Revisa tu bandeja de entrada.';
                }
            } else {
                Log::info('📧 Ya existe código válido para: ' . $user->email);
                $message = 'Por favor verifica tu correo electrónico. Ya tienes un código pendiente.';
            }
            
            Log::info('🔄 Redirigiendo a verification.notice');
            
            return redirect()->route('verification.notice')
                ->with('email', $userEmail)
                ->with('status', $message);
        }
        
        // Obtener el rol primario del usuario
        $role = $user->getPrimaryRole();
        Log::info('👤 Usuario verificado, redirigiendo a dashboard de: ' . $role);
        
        // Redirigir según el rol
        $redirectRoute = match ($role) {
            'Super Admin' => 'superadmin.dashboard',
            'Admin' => 'admin.dashboard',
            'Evaluador' => 'evaluator.dashboard',
            'Docente' => 'teacher.dashboard',
            default => 'dashboard.index',
        };
        
        // Usar redirect() directamente en lugar de intended() para evitar 
        // problemas de sincronización de roles/permisos en la sesión
        return redirect()->route($redirectRoute);
    }
}
