<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;
use App\Models\Calendar;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tablas para evitar duplicados
        Announcement::query()->delete();

        $years = [2022, 2023, 2024, 2025];
        $names = [
            2022 => 'Convocatoria de Apoyo a la Investigación para Docentes 2022',
            2023 => 'Incentivo al Desempeño Académico del Personal Docente 2023',
            2024 => 'Capacitación en Nuevas Tecnologías Educativas 2024',
            2025 => 'Beca de Profesionalización Docente Nivel Maestría 2025',
        ];
        $descriptions = [
            2022 => 'Esta convocatoria estuvo dirigida a docentes investigadores interesados en obtener financiamiento para proyectos de impacto regional y nacional durante el ciclo 2022.',
            2023 => 'Programa diseñado para reconocer y premiar la excelencia en la labor docente, considerando la evaluación de los alumnos y la participación en cuerpos académicos.',
            2024 => 'Iniciativa para fortalecer las competencias digitales de los docentes, facilitando el acceso a herramientas de vanguardia para la enseñanza híbrida y virtual.',
            2025 => 'Apoyo económico destinado a docentes que iniciaron estudios de posgrado en áreas prioritarias para fortalecer la calidad académica de nuestra institución.',
        ];

        foreach ($years as $year) {
            $announcement = Announcement::create([
                'name' => $names[$year],
                'description' => $descriptions[$year],
                'status' => 'cerrada',
                'created_at' => Carbon::create($year, 1, 1),
                'updated_at' => Carbon::create($year, 1, 1),
            ]);

            Calendar::create([
                'announcement_id' => $announcement->id,
                'publication_start' => Carbon::create($year, 7, 1),
                'registration_start' => Carbon::create($year, 7, 5),
                'registration_end' => Carbon::create($year, 7, 25),
                'evaluation_start' => Carbon::create($year, 8, 1),
                'evaluation_end' => Carbon::create($year, 8, 15),
                'results_start' => Carbon::create($year, 8, 20),
                'results_end' => Carbon::create($year, 8, 30),
            ]);
        }

        // 5. Convocatoria Pendiente (2026)
        $pendingDate = Carbon::create(2026, 2, 22); // Hoy según metadata

        $pending = Announcement::create([
            'name' => 'Fomento a la Innovación Educativa y Tecnológica 2026',
            'description' => 'Convocatoria abierta para el personal docente que desee implementar metodologías activas y uso de inteligencia artificial en sus programas de estudio durante el presente año.',
            'status' => 'pendiente',
        ]);

        Calendar::create([
            'announcement_id' => $pending->id,
            'publication_start' => $pendingDate->copy()->subDays(10), // Feb 12
            'registration_start' => $pendingDate->copy()->addDays(5), // Feb 27
            'registration_end' => $pendingDate->copy()->addDays(20), // Mar 14
            'evaluation_start' => $pendingDate->copy()->addDays(25), // Mar 19
            'evaluation_end' => $pendingDate->copy()->addDays(35), // Mar 29
            'results_start' => $pendingDate->copy()->addDays(40), // Apr 03
            'results_end' => $pendingDate->copy()->addDays(45), // Apr 08
        ]);
    }
}
