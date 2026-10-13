<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriorityArea;

class PriorityAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['id' => 1, 'name' => 'Física, Matemáticas y Ciencias de la Tierra'],
            ['id' => 2, 'name' => 'Biología y Química'],
            ['id' => 3, 'name' => 'Medicina y Salud'],
            ['id' => 4, 'name' => 'Biotecnología y Ciencias Agropecuarias'],
            ['id' => 5, 'name' => 'Tecnología 4.0'],
            ['id' => 6, 'name' => 'Ingeniería e Industria'],
            ['id' => 7, 'name' => 'Humanidades, Ciencias Sociales y Administrativas'],
            ['id' => 8, 'name' => 'Interdisciplinaria (Proyectos sobre litio, Ferroviarios, Semiconductores, electromovilidad, entre otros)'],
        ];

        foreach ($areas as $area) {
            PriorityArea::create($area);
        }
    }
}
