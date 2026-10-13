<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'E3519', 'name' => 'Docente de asignatura, nivel A, educación superior'],
            ['code' => 'E3521', 'name' => 'Docente de asignatura, nivel B, educación superior'],
            ['code' => 'E3505', 'name' => 'Técnico docente de asignatura, nivel A, superior'],
            ['code' => 'E3507', 'name' => 'Técnico docente de asignatura, nivel B, superior'],
            ['code' => 'E3509', 'name' => 'Técnico docente de asignatura, nivel C, superior'],
            ['code' => 'E3801', 'name' => 'Profesor de carrera asistente A'],
            ['code' => 'E3803', 'name' => 'Profesor de carrera asistente B'],
            ['code' => 'E3805', 'name' => 'Profesor de carrera asistente C'],
            ['code' => 'E3807', 'name' => 'Profesor de carrera asociado A'],
            ['code' => 'E3809', 'name' => 'Profesor de carrera asociado B'],
            ['code' => 'E3811', 'name' => 'Profesor de carrera asociado C'],
            ['code' => 'E3813', 'name' => 'Profesor de carrera titular A'],
            ['code' => 'E3815', 'name' => 'Profesor de carrera titular B'],
            ['code' => 'E3817', 'name' => 'Profesor de carrera titular C'],
        ];

        foreach ($types as $type) {
            \App\Models\PositionType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
