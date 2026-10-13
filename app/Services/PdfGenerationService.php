<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Template;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Carbon\Carbon;
use Exception;
use App\Models\Recognition;
use App\Models\User;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class PdfGenerationService
{
    protected $signatureService;

    public function __construct(SignatureService $signatureService)
    {
        $this->signatureService = $signatureService;
    }

    public function freezeAcceptanceLetter(Application $application)
    {
        $user = $application->user;
        $template = $application->template ?? Template::active()->type('acceptance')->first();

        if ($template && !$application->template_id) {
            $application->template_id = $template->id;
            $application->save();
        }

        if (!$template) return;

        $snapshot = is_string($application->snapshot_data) ? json_decode($application->snapshot_data, true) : $application->snapshot_data;
        if (!$snapshot) {
            $contentData = $template->content_data ?? [];
            $rawBodyText = $contentData['body_text'] ?? "De conformidad a su solicitud de la convocatoria de “PROGRAMA DE ESTIMULO AL DESEMPEÑO DEL PERSONAL DOCENTE [AÑO_ACTUAL] PARA INSTITUTOS FEDERALES Y CENTROS”, me complace informarle que el Comité de Evaluación del Tecnológico Nacional de México (TecNM) y de conformidad con los Lineamientos del Programa de Estímulos al Desempeño del Personal Docente para los Institutos Federales Tecnológicos y Centros, ha dictaminado que su solicitud de Estimulo al Desempeño del Personal Docente fue “[ESTADO]” con un nivel asignado de [NIVEL], la cual tendrá una vigencia de 1 (un) año, del periodo de [FECHA_INICIO] a [FECHA_FIN].\n\nEn consecuencia, el TecNM acredita el nivel alcanzado y se invita a mantener y seguir desarrollando sus habilidades en “Docencia”, “Producción Académica”, “Dirección Individualizada” y “Gestión Académica”, por lo que al termino de su vigencia será evaluado nuevamente o cuando le sea requerido por esta Dirección Académica de Investigación e Innovación con el propósito de valorar los avances en su desarrollo.\n\nSin otro particular, aprovecho la ocasión para enviarle un cordial saludo y felicitaciones.";

            $announcementName = $application->announcement->name ?? 'XXXXX';
            $statusText = $application->status === 'approved' ? 'APROBADA' : 'XXXXX';
            $levelText = 'VI';
            $vigenciaStart = Carbon::now()->isoFormat('DD [de] MMMM [de] YYYY');
            $vigenciaEnd = Carbon::now()->addYear()->isoFormat('DD [de] MMMM [de] YYYY');
            $currentYear = Carbon::now()->year;

            $resolvedBodyText = str_replace(
                ['[CONVOCATORIA]', '[ESTADO]', '[NIVEL]', '[FECHA_INICIO]', '[FECHA_FIN]', '[AÑO_ACTUAL]'],
                [$announcementName, $statusText, $levelText, $vigenciaStart, $vigenciaEnd, $currentYear],
                $rawBodyText
            );

            $snapshot = [
                'date_text' => "CIUDAD DE MÉXICO, A " . mb_strtoupper(Carbon::now()->timezone('America/Mexico_City')->isoFormat('DD [DE] MMMM [DE] YYYY'), 'UTF-8'),
                'director_name' => $contentData['director_name'] ?? 'Ramón Jiménez López',
                'director_title' => $contentData['director_title'] ?? 'Director General DEL TecNM',
                'body_text' => $resolvedBodyText,
            ];
            $application->snapshot_data = json_encode($snapshot);
            $application->save();
        }

        if (!$application->identifier || !$application->digital_seal) {
            if (!$application->identifier) {
                $year = date('Y');
                $uniqueSuffix = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 5)), 0, 6);
                $application->identifier = "ACE-{$year}-ACE-{$application->id}-{$uniqueSuffix}";
            }
            if (!$application->digital_seal) {
                $originalString = "||{$application->id}|{$user->id}|" . Carbon::now()->toIso8601String() . "||";
                $application->digital_seal = $this->signatureService->sign($originalString);
            }
            $application->save();
        }
    }

    public function freezeEvaluatorRecognition(Recognition $recognition, User $user)
    {
        $template = $recognition->template ?? Template::active()->type('recognition')->first();

        if ($template && !$recognition->template_id) {
            $recognition->template_id = $template->id;
            $recognition->save();
        }

        if (!$template) return;

        $snapshot = is_string($recognition->snapshot_data) ? json_decode($recognition->snapshot_data, true) : $recognition->snapshot_data;
        if (!$snapshot) {
            $contentData = $template->content_data ?? [];
            $announcementName = $recognition->announcement ? $recognition->announcement->name : 'CONVOCATORIA GENERAL';
            $defaultBody = $contentData['body_text'] ?? "Por su destacada participación como miembro de la Comisión de Evaluación\nLocal y Nacional al Programa de Estímulo al Desempeño del Personal\nDocente para los Institutos Federales y Centros";
            $resolvedBody = str_replace('[CONVOCATORIA]', $announcementName, $defaultBody);
            
            $snapshot = [
                'date_text' => "CIUDAD DE MÉXICO, A " . mb_strtoupper(Carbon::now()->timezone('America/Mexico_City')->isoFormat('DD [DE] MMMM [DE] YYYY'), 'UTF-8'),
                'director_name' => $contentData['director_name'] ?? 'Ramón Jiménez López',
                'director_title' => $contentData['director_title'] ?? 'Director General',
                'body_text' => $resolvedBody,
            ];
            $recognition->snapshot_data = json_encode($snapshot);
            $recognition->save();
        }

        if (!$recognition->identifier || !$recognition->digital_seal) {
            if (!$recognition->identifier) {
                $year = date('Y');
                $uniqueSuffix = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 5)), 0, 6);
                $recognition->identifier = "ACE-{$year}-EVAL-{$recognition->id}-{$uniqueSuffix}";
            }
            if (!$recognition->digital_seal) {
                $sentAtIso = Carbon::parse($recognition->sent_at ?? now())->toIso8601String();
                $originalString = "||REC-{$recognition->id}|{$user->id}|{$sentAtIso}||";
                $recognition->digital_seal = $this->signatureService->sign($originalString);
            }
            $recognition->save();
        }
    }

    public function freezeTeacherRecognition(Recognition $recognition, User $user)
    {
        $template = $recognition->template ?? Template::active()->type('recognition')->first();

        if ($template && !$recognition->template_id) {
            $recognition->template_id = $template->id;
            $recognition->save();
        }

        if (!$template) return;

        $snapshot = is_string($recognition->snapshot_data) ? json_decode($recognition->snapshot_data, true) : $recognition->snapshot_data;
        if (!$snapshot) {
            $contentData = $template->content_data ?? [];
            $teacherBodyText = "Por su destacada participación como postulante en el Programa de Estímulo"
                . "\nal Desempeño del Personal Docente para los Institutos Federales y Centros.";

            $snapshot = [
                'date_text' => "CIUDAD DE MÉXICO, A " . mb_strtoupper(Carbon::now()->timezone('America/Mexico_City')->isoFormat('DD [DE] MMMM [DE] YYYY'), 'UTF-8'),
                'director_name' => $contentData['director_name'] ?? 'Ramón Jiménez López',
                'director_title' => $contentData['director_title'] ?? 'Director General',
                'body_text' => $teacherBodyText,
            ];
            $recognition->snapshot_data = $snapshot; // Auto json cast if array
            $recognition->save();
        }

        if (!$recognition->identifier || !$recognition->digital_seal) {
            if (!$recognition->identifier) {
                $year = date('Y');
                $uniqueSuffix = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 5)), 0, 6);
                $recognition->identifier = "ACE-{$year}-DOC-{$recognition->id}-{$uniqueSuffix}";
            }
            if (!$recognition->digital_seal) {
                $sentAtIso = Carbon::parse($recognition->sent_at ?? now())->toIso8601String();
                $originalString = "||DOC-{$recognition->id}|{$user->id}|{$sentAtIso}||";
                $recognition->digital_seal = $this->signatureService->sign($originalString);
            }
            $recognition->save();
        }
    }
    /**
     * Genera una carta de aceptación en formato PDF inyectando texto en una plantilla.
     *
     * @param Application $application El modelo de la aplicación aprobada con relaciones cargadas.
     * @return \Illuminate\Http\Response
     * @throws Exception Si no hay plantilla activa.
     */
    public function generateAcceptanceLetter(Application $application)
    {
        $user = $application->user;

        $this->freezeAcceptanceLetter($application);

        $template = $application->template;
        $templatePath = $template ? Storage::disk('public')->path($template->file_path) : null;

        if (!$templatePath || !file_exists($templatePath)) {
            throw new Exception("No hay una plantilla de carta de aceptación activa.");
        }

        // Initialize FPDI
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplId);
        $w = $size['width'];
        $h = $size['height'];

        $pdf->AddPage($size['orientation'], [$w, $h]);
        $pdf->useTemplate($tplId, 0, 0, $w, $h);

        $snapshot = is_string($application->snapshot_data) ? json_decode($application->snapshot_data, true) : $application->snapshot_data;
        if (!$snapshot) {
            throw new Exception("Datos de congelación no disponibles.");
        }

        // =====================================================================
        // Coordinates - calibrated empirically to match actual template layout.
        // The WYSIWYG CSS percentages are relative to the rendered canvas, not
        // the PDF mm page, so we adjust slightly here.
        // Date:        top~22%  right:7%  (ABOVE ASUNTO block)
        // Name:        top~28.5% left:11.5% (ABOVE baked DOCENTE DEL)
        // Institution: top~31%  left:24%   (ON the baked DOCENTE DEL line)
        // Body:        top~38%  left:10.5% (BELOW baked PRESENTE)
        // Director:    top~76%  left:9%
        // Dir. Title:  top~78%  left:9%
        // =====================================================================

        $pdf->SetTextColor(0, 0, 0);

        // Date (top-right, above ASUNTO)
        $pdf->SetFont('Arial', '', 10);
        $dateWidth = $w * 0.50;
        $dateX = $w - $dateWidth - ($w * 0.065); // right:7%
        $pdf->SetXY($dateX, $h * 0.22);
        $pdf->Cell($dateWidth, 6, iconv('UTF-8', 'ISO-8859-1', $snapshot['date_text']), 0, 1, 'R');

        // Asunto removed, already baked into PDF.

        // Name (above the baked DOCENTE DEL line)
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($w * 0.111, $h * 0.310);
        $userNameString = $user->name ? mb_strtoupper($user->name, 'UTF-8') : 'XXXXXXXXXX XXXXXXXXXX XXXXXXXXXX';
        $pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1', $userNameString), 0, 1, 'L');

        // Institution (on the baked DOCENTE DEL line, after the baked text)
        $institutionName = $user->institution ? mb_strtoupper($user->institution->name, 'UTF-8') : 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($w * 0.217, $h * 0.327);
        $pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1', $institutionName), 0, 1, 'L');

        // Textos directos del snapshot
        $bodyText = $snapshot['body_text'];
        $directorName = $snapshot['director_name'];
        $directorTitle = $snapshot['director_title'];

        // Print Body Text (Parse **bold** markdown to specific font weights)
        $bodyLeftMargin = $w * 0.111;
        $bodyRightMargin = $w * 0.065;
        $bodyWidth = $w - $bodyLeftMargin - $bodyRightMargin;
        $lineHeight = 5;

        $pdf->SetLeftMargin($bodyLeftMargin);
        $pdf->SetRightMargin($bodyRightMargin);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY($bodyLeftMargin, $h * 0.37);

        $paragraphs = explode("\n", $bodyText);

        foreach ($paragraphs as $p) {
            if (trim($p) === '') {
                $pdf->Ln(4);
                continue;
            }

            if (strpos($p, '**') === false) {
                // No bold: use MultiCell with justification
                $pdf->SetFont('Arial', '', 9);
                $safeParagraph = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"], ['"', '"', "'", "'"], $p);
                $encodedParagraph = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $safeParagraph);
                $pdf->MultiCell($bodyWidth, $lineHeight, $encodedParagraph !== false ? $encodedParagraph : '', 0, 'J');
            } else {
                // Has bold parts: use Write() respecting set margins
                $parts = explode('**', $p);
                $isBold = false;
                foreach ($parts as $part) {
                    $pdf->SetFont('Arial', $isBold ? 'B' : '', 9);
                    $safePart = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"], ['"', '"', "'", "'"], $part);
                    $encodedPart = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $safePart);
                    $pdf->Write($lineHeight, $encodedPart !== false ? $encodedPart : '');
                    $isBold = !$isBold;
                }
                $pdf->Ln($lineHeight);
            }
        }

        // Reset margins to default
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);

        // Director Box (in signature area before footer logos)
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($w * 0.111, $h * 0.73); // ~76% from top, left:9%
        $pdf->Cell(160, 5, iconv('UTF-8', 'ISO-8859-1', mb_strtoupper($directorName, 'UTF-8')), 0, 1, 'L');
        $pdf->SetXY($w * 0.111, $h * 0.745); // ~78% from top
        $pdf->Cell(160, 5, iconv('UTF-8', 'ISO-8859-1', mb_strtoupper($directorTitle, 'UTF-8')), 0, 1, 'L');

        // C.c.p. Line is baked into PDF.

        // Add Legal Signature QR Page (Second Page) - uses same page size as template
        $this->addLegalSignaturePage($pdf, 'Carta de Aceptación', $user->id, $application->id, $size);

        // Output PDF
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Carta_Aceptacion.pdf"');
    }

    /**
     * Adds an inline digital signature text block.
     */
    private function addSignatureBlock($pdf, $originalString, $x, $y)
    {
        try {
            $digitalSeal = $this->signatureService->sign($originalString) ?? 'Firma Electrónica No Disponible';
            $pdf->SetXY($x, $y);
            $pdf->SetFont('Courier', '', 5);
            $pdf->SetTextColor(160, 160, 160);
            $pdf->MultiCell(165, 2.5, iconv('UTF-8', 'ISO-8859-1', "Sello Digital:\n") . $digitalSeal, 0, 'L');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error generating inline signature: " . $e->getMessage());
        }
    }

    /**
     * Genera un reconocimiento en PDF con o sin plantilla.
     *
     * @param object $recognition
     * @param object $user
     * @return \Illuminate\Http\Response
     */
    public function generateRecognitionPdf($recognition, $user)
    {
        $this->freezeEvaluatorRecognition($recognition, $user);

        $template = $recognition->template;
        $templatePath = $template ? Storage::disk('public')->path($template->file_path) : null;

        if (!$templatePath || !file_exists($templatePath)) {
            throw new Exception("El reconocimiento aún no se encuentra disponible.");
        }

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($templatePath);
        
        $tplId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplId);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

        $snapshot = is_string($recognition->snapshot_data) ? json_decode($recognition->snapshot_data, true) : $recognition->snapshot_data;

        // Format date and location strings
        $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);

        // 1. Participant Name (Centered)
        $pdf->SetFont('Arial', 'B', 24); 
        $pdf->SetTextColor(80, 80, 80); 
        $pdf->SetXY(0, 121);
        $pdf->Cell($size['width'], 10, iconv('UTF-8', 'ISO-8859-1', mb_strtoupper($user->name)), 0, 1, 'C');

        // 2. Participating Activity (Centered)
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY(20, 140);
        $pdf->MultiCell($size['width'] - 40, 7, iconv('UTF-8', 'ISO-8859-1', $snapshot['body_text']), 0, 'C');

        // 3. Signer Information
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY(0, 211);
        $pdf->Cell($size['width'], 6, iconv('UTF-8', 'ISO-8859-1', $snapshot['director_name']), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(0, 217);
        $pdf->Cell($size['width'], 6, iconv('UTF-8', 'ISO-8859-1', $snapshot['director_title']), 0, 1, 'C');

        // 4. City, State, Month, Year
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(194, 155, 34);
        $pdf->SetXY(0, 225);
        $pdf->Cell($size['width'], 6, iconv('UTF-8', 'ISO-8859-1', $snapshot['date_text']), 0, 1, 'C');

        // Second Page setup for legal QR and Seal
        $this->addLegalSignaturePage($pdf, "Reconocimiento Evaluador", $user->id, $recognition->id, $size);

        // Output PDF
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Reconocimiento.pdf"');
    }

    /**
     * Genera un certificado de reconocimiento para Docentes por su participación.
     *
     * @param Recognition $recognition
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     * @throws Exception Si falla la carga de la plantilla.
     */
    public function generateTeacherRecognitionPdf(Recognition $recognition, $user)
    {
        $this->freezeTeacherRecognition($recognition, $user);

        $template = $recognition->template;
        if (!$template || !Storage::disk('public')->exists($template->file_path)) {
            throw new Exception("El reconocimiento aún no se encuentra disponible.");
        }

        $templatePath = Storage::disk('public')->path($template->file_path);
        
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        try {
            $pdf->setSourceFile($templatePath);
            $tplId = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($tplId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);
        } catch (\Exception $e) {
            throw new Exception("Error al procesar la plantilla PDF: " . $e->getMessage());
        }

        $snapshot = is_string($recognition->snapshot_data) ? json_decode($recognition->snapshot_data, true) : $recognition->snapshot_data;

        // 1. Teacher Name (Centered)
        $pdf->SetFont('Arial', 'B', 24);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY(0, 118);
        $pdf->Cell($size['width'], 10, iconv('UTF-8', 'ISO-8859-1', mb_strtoupper($user->name)), 0, 1, 'C');

        // 2. Announcement (Centered)
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY(20, 133);
        $pdf->MultiCell($size['width'] - 40, 7, iconv('UTF-8', 'ISO-8859-1', $snapshot['body_text']), 0, 'C');

        // 3. Signer Information
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetXY(0, 212);
        $pdf->Cell($size['width'], 6, iconv('UTF-8', 'ISO-8859-1', $snapshot['director_name']), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(0, 218);
        $pdf->Cell($size['width'], 6, iconv('UTF-8', 'ISO-8859-1', $snapshot['director_title']), 0, 1, 'C');

        // 4. Date (Bottom Center)
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(194, 155, 34);
        $pdf->SetXY(0, 226);
        $pdf->Cell($size['width'], 6, iconv('UTF-8', 'ISO-8859-1', $snapshot['date_text']), 0, 1, 'C');

        // Append Legal Page
        $this->addLegalSignaturePage($pdf, "Reconocimiento Postulante", $user->id, $recognition->id, $size);

        // Output PDF
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Reconocimiento_Docente.pdf"');
    }

    /**
     * Creates a secondary page containing the legal QR code and the digital signature seal.
     */
    private function addLegalSignaturePage($pdf, $documentType, $userId, $recognitionId, $size = null)
    {
        // Add a blank page with the exact same dimensions as the template, or fallback to standard A4 Portrait
        if ($size && isset($size['orientation'])) {
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        } else {
            $pdf->AddPage('P'); // Fallback Portrait A4
        }
        
        try {
            // Determine which model to use based on document type
            $isAcceptanceLetter = $documentType === 'Carta de Aceptación';
            
            if ($isAcceptanceLetter) {
                $document = \App\Models\Application::find($recognitionId);
            } else {
                $document = \App\Models\Recognition::find($recognitionId);
            }
            
            // Should always have identifier and seal by now because of freeze routines
            $uniqueIdentifier = $document->identifier ?? 'SIN-FOLIO';
            $digitalSeal = $document->digital_seal ?? 'FIRMA ELECTRÓNICA NO DISPONIBLE';

            $user = \App\Models\User::find($userId);

            // Generate QR Code image (Holding just the Lookup URL)
            $appUrl = config('app.url') ?? 'http://becaslaravel_ad.test:8080';
            $validationUrl = rtrim($appUrl, '/') . "/verify-recognition/" . urlencode($uniqueIdentifier);
            
            $qrData = $validationUrl;
            
            $qrCode = new QrCode(
                data: $qrData,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 250,
                margin: 2
            );

            $writer = new PngWriter();
            $qrResult = $writer->write($qrCode);

            // Save temporarily to disk to inject into FPDF
            $tempQR = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            $qrResult->saveToFile($tempQR);

            // Draw a black outline box behind the QR to resemble the prototype
            $pdf->SetDrawColor(0, 0, 0); // Black
            $pdf->SetLineWidth(0.5);
            $pdf->Rect(14.5, 14.5, 31, 31); // Slightly larger than the 30x30 image

            // Left side block: QR Code
            $pdf->Image($tempQR, 15, 15, 30, 30, 'PNG');
            unlink($tempQR); // Cleanup

            // Right side block: Texts
            $startX = 50;
            $startY = 15;

            // Folio (Identificador Único)
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetXY($startX, $startY);
            $pdf->Cell(10, 4, iconv('UTF-8', 'ISO-8859-1', 'Folio:'), 0, 0, 'L');

            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 4, iconv('UTF-8', 'ISO-8859-1', $uniqueIdentifier), 0, 1, 'L');

            // Move Y down slightly for Sello
            $startY = $pdf->GetY() + 2;

            // Sello Digital Header
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetXY($startX, $startY);
            $pdf->Cell(0, 4, iconv('UTF-8', 'ISO-8859-1', 'Sello Digital de Verificación:'), 0, 1, 'L');

            // Sello Digital Base64 string - Lighter smaller text
            $pdf->SetFont('Courier', '', 5);
            $pdf->SetTextColor(160, 160, 160);
            $pdf->SetXY($startX, $startY + 5);
            $pdf->MultiCell(130, 2.5, $digitalSeal, 0, 'J');

            // Leyenda Legal - Adjusted positioning relative to seal
            $currentY = $pdf->GetY() + 8;
            $legalNotice = "La firma electrónica que sustituye a la firma autógrafa del firmante, garantiza la integridad de la constancia y producirá los mismos efectos que las leyes otorgan a los documentos con firma autógrafa.";
            
            $pdf->SetFont('Arial', '', 5);
            $pdf->SetTextColor(160, 160, 160); // Softer color for legal notice
            $pdf->SetXY($startX, $currentY);
            $pdf->MultiCell(130, 2.5, iconv('UTF-8', 'ISO-8859-1', $legalNotice), 0, 'J');

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error generating QR or Signature: " . $e->getMessage());
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetXY(20, 30);
            $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', '[Error general al procesar la firma electrónica y QR]'), 0, 1, 'L');
        }
    }
}
