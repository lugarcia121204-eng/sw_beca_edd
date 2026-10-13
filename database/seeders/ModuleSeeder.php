<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Inicio',
                'description' => 'Módulo de inicio y dashboard principal',
                'key' => 'dashboard',
            ],
            [
                'name' => 'Solicitudes',
                'description' => 'Gestión individual de solicitudes de becas y estímulos',
                'key' => 'applications', // Was adminsoli
            ],
            [
                'name' => 'Control de solicitudes',
                'description' => 'Resumen y control de solicitudes por institución',
                'key' => 'request_control', // Was controlsoli
            ],
            [
                'name' => 'Convocatorias',
                'description' => 'Administración de convocatorias activas e historial',
                'key' => 'announcements', // Was convo
            ],
            [
                'name' => 'Modulo de Seguridad',
                'description' => 'Gestión de usuarios, roles, permisos y módulos del sistema',
                'key' => 'security', // Was seg
            ],
            [
                'name' => 'Catálogo',
                'description' => 'Módulo de catálogo',
                'key' => 'catalog', // Was cat
            ],
            [
                'name' => 'Reconocimientos',
                'description' => 'Gestión de reconocimientos para evaluadores',
                'key' => 'recognitions', // Was reconocimiento
            ],
            [
                'name' => 'Evaluaciones',
                'description' => 'Módulo de evaluaciones y su historial',
                'key' => 'evaluations', // Was evaluaciones (check match)
            ],
            [
                'name' => 'Gestión de Respaldos',
                'description' => 'Módulo de respaldo y restauración de la base de datos',
                'key' => 'backup',
            ],

        ];

        foreach ($modules as $module) {
            Module::firstOrCreate(
                ['key' => $module['key']],
                [
                    'name' => $module['name'],
                    'description' => $module['description'],
                ]
            );
        }
    }
}