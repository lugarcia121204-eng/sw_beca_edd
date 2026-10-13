<?php
// check_solicitud.php
require __DIR__.'/bootstrap/app.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latestSolicitud = \App\Models\Solicitud::latest()->first();

if ($latestSolicitud) {
    echo "Latest Solicitud ID: " . $latestSolicitud->id . "\n";
    echo "User ID: " . $latestSolicitud->user_id . "\n";
    echo "Convocatoria ID: " . ($latestSolicitud->convocatoria_id ?? 'NULL') . "\n";
    
    if ($latestSolicitud->convocatoria_id) {
        $convocatoria = \App\Models\Convocatoria::find($latestSolicitud->convocatoria_id);
        if ($convocatoria) {
            echo "Convocatoria Found: " . $convocatoria->nombre . "\n";
        } else {
            echo "Convocatoria NOT FOUND in DB.\n";
        }
    }
} else {
    echo "No solicitudes found.\n";
}
