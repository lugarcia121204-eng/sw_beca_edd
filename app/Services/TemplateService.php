<?php

namespace App\Services;

use App\Models\Template;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TemplateService
{
    /**
     * Store a new template in storage and database.
     */
    public function createTemplate(array $data, \Illuminate\Http\UploadedFile $file): Template
    {
        return DB::transaction(function () use ($data, $file) {
            $path = $file->store('templates', 'public');
            $originalName = $file->getClientOriginalName();

            $defaultContentData = null;

            if ($data['type'] === 'recognition') {
                // NOTE: this body_text is shown on EVALUATOR recognitions only.
                // Teacher (docente) recognitions use a fixed text defined in PdfGenerationService.
                $defaultContentData = [
                    'body_text'      => "Por su destacada participación como miembro de la Comisión de Evaluación\nLocal y Nacional al Programa de Estímulo al Desempeño del Personal\nDocente para los Institutos Federales y Centros",
                    'director_name'  => "RAMÓN JIMÉNEZ LÓPEZ",
                    'director_title' => "DIRECTOR GENERAL"
                ];
            } else if ($data['type'] === 'acceptance') {
                $defaultContentData = [
                    'body_text' => "De conformidad a su solicitud de la convocatoria de **\"PROGRAMA DE ESTIMULO AL DESEMPEÑO DEL PERSONAL DOCENTE [AÑO_ACTUAL] PARA INSTITUTOS FEDERALES Y CENTROS\"**, me complace informarle que el Comité de Evaluación del Tecnológico Nacional de México (TecNM) y de conformidad con los Lineamientos del Programa de Estímulos al Desempeño del Personal Docente para los Institutos Federales Tecnológicos y Centros, ha dictaminado que su solicitud de Estimulo al Desempeño del Personal Docente fue **\"APROBADA\"** con un nivel asignado de **[NIVEL]**, la cual tendrá una vigencia de **1 (un) año**, del periodo de **[FECHA_INICIO]** a **[FECHA_FIN]**.\n\nEn consecuencia, el TecNM acredita el nivel alcanzado y se invita a mantener y seguir desarrollando sus habilidades en \"Docencia\", \"Producción Académica\", \"Dirección Individualizada\" y \"Gestión Académica\", por lo que al termino de su vigencia será evaluado nuevamente o cuando le sea requerido por esta Dirección Académica de Investigación e Innovación con el propósito de valorar los avances en su desarrollo.\n\n\nSin otro particular, aprovecho la ocasión para enviarle un cordial saludo y felicitaciones.",
                    'director_name' => "RAMÓN JIMÉNEZ LÓPEZ",
                    'director_title' => "DIRECTOR GENERAL DEL TecNM"
                ];
            }

            return Template::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'file_path' => $path,
                'file_name' => $originalName,
                'content_data' => $defaultContentData,
                'is_active' => false, // Default inactive
            ]);
        });
    }

    /**
     * Toggle the active status of a template, ensuring only one is active per type.
     */
    public function toggleActiveStatus(Template $template): bool
    {
        return DB::transaction(function () use ($template) {
            if (!$template->is_active) {
                // Deactivate all others of the same type
                Template::where('type', $template->type)
                    ->where('id', '!=', $template->id)
                    ->update(['is_active' => false]);
                
                $template->is_active = true;
                $template->save();
                return true; // Now active
            } else {
                // Deactivating
                $template->is_active = false;
                $template->save();
                return false; // Now inactive
            }
        });
    }

    /**
     * Delete a template from storage and database.
     */
    public function deleteTemplate(Template $template): void
    {
        DB::transaction(function () use ($template) {
            if (Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }
            
            $template->delete();
        });
    }
}
