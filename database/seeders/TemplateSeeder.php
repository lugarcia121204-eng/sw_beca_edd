<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'type' => 'recognition',
                'name' => 'Plantilla Reconocimiento',
                'description' => 'Plantilla base para generar reconocimientos.',
                'source_file' => database_path('seeders/templates/reconocimiento.pdf'),
                'target_file' => 'templates/template_reconocimiento.pdf',
                'content_data' => [
                    'body_text' => "Por su destacada participación como miembro de la Comisión de Evaluación\nLocal y Nacional al Programa de Estímulo al Desempeño del Personal\nDocente para los Institutos Federales y Centros",
                    'director_name' => 'RAMÓN JIMÉNEZ LÓPEZ',
                    'director_title' => 'DIRECTOR GENERAL',
                ],
            ],
            [
                'type' => 'acceptance',
                'name' => 'Plantilla Carta de Aceptacion',
                'description' => 'Plantilla base para generar cartas de aceptacion.',
                'source_file' => database_path('seeders/templates/carta_aceptacion.pdf'),
                'target_file' => 'templates/template_carta_aceptacion.pdf',
                'content_data' => [
                    'body_text' => "De conformidad a su solicitud de la convocatoria de **\"PROGRAMA DE ESTIMULO AL DESEMPEÑO DEL PERSONAL DOCENTE [AÑO_ACTUAL] PARA INSTITUTOS FEDERALES Y CENTROS\"**, me complace informarle que el Comité de Evaluación del Tecnológico Nacional de México (TecNM) y de conformidad con los Lineamientos del Programa de Estímulos al Desempeño del Personal Docente para los Institutos Federales Tecnológicos y Centros, ha dictaminado que su solicitud de Estimulo al Desempeño del Personal Docente fue **\"APROBADA\"** con un nivel asignado de **[NIVEL]**, la cual tendrá una vigencia de **1 (un) año**, del periodo de **[FECHA_INICIO]** a **[FECHA_FIN]**.\n\nEn consecuencia, el TecNM acredita el nivel alcanzado y se invita a mantener y seguir desarrollando sus habilidades en \"Docencia\", \"Producción Académica\", \"Dirección Individualizada\" y \"Gestión Académica\", por lo que al termino de su vigencia será evaluado nuevamente o cuando le sea requerido por esta Dirección Académica de Investigación e Innovación con el propósito de valorar los avances en su desarrollo.\n\n\nSin otro particular, aprovecho la ocasión para enviarle un cordial saludo y felicitaciones.",
                    'director_name' => 'RAMÓN JIMÉNEZ LÓPEZ',
                    'director_title' => 'DIRECTOR GENERAL DEL TecNM',
                ],
            ],
        ];

        foreach ($templates as $templateConfig) {
            if (!file_exists($templateConfig['source_file'])) {
                if ($this->command) {
                    $this->command->warn("No se encontro el archivo de plantilla: {$templateConfig['source_file']}");
                }
                continue;
            }

            Storage::disk('public')->put(
                $templateConfig['target_file'],
                file_get_contents($templateConfig['source_file'])
            );

            Template::where('type', $templateConfig['type'])
                ->where('name', '!=', $templateConfig['name'])
                ->update(['is_active' => false]);

            Template::updateOrCreate(
                [
                    'type' => $templateConfig['type'],
                    'name' => $templateConfig['name'],
                ],
                [
                    'description' => $templateConfig['description'],
                    'file_path' => $templateConfig['target_file'],
                    'file_name' => basename($templateConfig['source_file']),
                    'is_active' => true,
                    'content_data' => $templateConfig['content_data'],
                ]
            );

            if ($this->command) {
                $this->command->info("Plantilla sembrada: {$templateConfig['name']}");
            }
        }
    }
}
