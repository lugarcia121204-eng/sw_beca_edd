<?php
// check_solicitud_v2.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latestSolicitud = \App\Models\Solicitud::latest()->first();

if ($latestSolicitud) {
    echo "Latest Solicitud ID: " . $latestSolicitud->id . "\n";
    echo "Created At: " . $latestSolicitud->created_at . "\n";
    echo "Convocatoria ID: " . ($latestSolicitud->convocatoria_id ?? 'NULL') . "\n";
    
    if ($latestSolicitud->convocatoria_id) {
        $convocatoria = \App\Models\Convocatoria::find($latestSolicitud->convocatoria_id);
        echo "Convocatoria Name: " . ($convocatoria ? $convocatoria->nombre : 'NOT FOUND') . "\n";
    }
} else {
    echo "No solicitudes found.\n";
}
