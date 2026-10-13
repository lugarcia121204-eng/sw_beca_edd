<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Recognition;
use App\Models\Application;

class VerificationController extends Controller
{
    public function verify($identifier)
    {
        // First look in recognitions (DOC / EVAL prefix)
        $recognition = Recognition::validByIdentifier($identifier)->with('announcement')->first();

        if ($recognition) {
            $announcementName = $recognition->convocatoria_nombre
                ?? ($recognition->announcement ? $recognition->announcement->name : 'CONVOCATORIA GENERAL');

            $snapshot = is_string($recognition->snapshot_data) ? json_decode($recognition->snapshot_data, true) : $recognition->snapshot_data;
            $directorName = $snapshot['director_name'] ?? 'Víctor Vázquez López';
            $directorTitle = $snapshot['director_title'] ?? 'Director General';

            return Inertia::render('Public/VerifyRecognition', [
                'identifier'       => $recognition->identifier,
                'participant'      => $recognition->user ? mb_strtoupper($recognition->user->name) : 'PARTICIPANTE DESCONOCIDO',
                'announcementName' => mb_strtoupper($announcementName),
                'digitalSeal'      => $recognition->digital_seal,
                'directorName'     => mb_strtoupper($directorName),
                'directorTitle'    => $directorTitle,
            ]);
        }

        // Fall back to applications (ACE prefix — acceptance letters)
        $application = Application::validByIdentifier($identifier)->first();

        if ($application) {
            $announcementName = $application->announcement ? $application->announcement->name : 'CONVOCATORIA GENERAL';

            $snapshot = is_string($application->snapshot_data) ? json_decode($application->snapshot_data, true) : $application->snapshot_data;
            $directorName = $snapshot['director_name'] ?? 'Víctor Vázquez López';
            $directorTitle = $snapshot['director_title'] ?? 'Director General';

            return Inertia::render('Public/VerifyRecognition', [
                'identifier'       => $application->identifier,
                'participant'      => $application->user ? mb_strtoupper($application->user->name) : 'PARTICIPANTE DESCONOCIDO',
                'announcementName' => mb_strtoupper($announcementName),
                'digitalSeal'      => $application->digital_seal,
                'directorName'     => mb_strtoupper($directorName),
                'directorTitle'    => $directorTitle,
            ]);
        }

        abort(404, 'Reconocimiento no encontrado o identificador inválido.');
    }
}
