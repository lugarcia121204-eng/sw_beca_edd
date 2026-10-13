<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubArea;

class SubAreaSeeder extends Seeder
{
    public function run(): void
    {
        $subAreas = [
            ['id' => 1, 'name' => 'Astrofísica', 'priority_area_id' => 1],
            ['id' => 2, 'name' => 'Astronomía', 'priority_area_id' => 1],
            ['id' => 3, 'name' => 'Ciencias de la Tierra y del espacio', 'priority_area_id' => 1],
            ['id' => 4, 'name' => 'Física', 'priority_area_id' => 1],
            ['id' => 5, 'name' => 'Lógica', 'priority_area_id' => 1],
            ['id' => 6, 'name' => 'Matemáticas', 'priority_area_id' => 1],
            ['id' => 7, 'name' => 'Prospectiva', 'priority_area_id' => 1],
            ['id' => 8, 'name' => 'Ciencias del mar', 'priority_area_id' => 1],
            ['id' => 9, 'name' => 'Ciencias de la vida', 'priority_area_id' => 2],
            ['id' => 10, 'name' => 'Prospectiva', 'priority_area_id' => 2],
            ['id' => 11, 'name' => 'Ciencias biomédicas', 'priority_area_id' => 2],
            ['id' => 12, 'name' => 'Biología', 'priority_area_id' => 2],
            ['id' => 13, 'name' => 'Química', 'priority_area_id' => 2],
            ['id' => 14, 'name' => 'Ciencias Ambientales', 'priority_area_id' => 2],
            ['id' => 15, 'name' => 'Ciencias médicas', 'priority_area_id' => 3],
            ['id' => 16, 'name' => 'Medicina', 'priority_area_id' => 3],
            ['id' => 17, 'name' => 'Enfermería', 'priority_area_id' => 3],
            ['id' => 18, 'name' => 'Ingeniería biomédica', 'priority_area_id' => 3],
            ['id' => 19, 'name' => 'Especialidad médica', 'priority_area_id' => 3],
            ['id' => 20, 'name' => 'Odontología', 'priority_area_id' => 3],
            ['id' => 21, 'name' => 'Investigación médica', 'priority_area_id' => 3],
            ['id' => 22, 'name' => 'Biotecnología', 'priority_area_id' => 4],
            ['id' => 23, 'name' => 'Ciencias agrarias', 'priority_area_id' => 4],
            ['id' => 24, 'name' => 'Biotecnología agrícola', 'priority_area_id' => 4],
            ['id' => 25, 'name' => 'Salud y Producción Animal', 'priority_area_id' => 4],
            ['id' => 26, 'name' => 'Pesca', 'priority_area_id' => 4],
            ['id' => 27, 'name' => 'Robótica', 'priority_area_id' => 5],
            ['id' => 28, 'name' => 'Simulaciones', 'priority_area_id' => 5],
            ['id' => 29, 'name' => 'Materiales avanzados', 'priority_area_id' => 5],
            ['id' => 30, 'name' => 'Realidad virtual', 'priority_area_id' => 5],
            ['id' => 31, 'name' => 'Realidad aumentada', 'priority_area_id' => 5],
            ['id' => 32, 'name' => 'Big Data', 'priority_area_id' => 5],
            ['id' => 33, 'name' => 'Inteligencia Artificial', 'priority_area_id' => 5],
            ['id' => 34, 'name' => 'Software como servicio', 'priority_area_id' => 5],
            ['id' => 35, 'name' => 'Manufactura aditiva', 'priority_area_id' => 5],
            ['id' => 36, 'name' => 'Ciencias tecnológicas', 'priority_area_id' => 6],
            ['id' => 37, 'name' => 'Ingeniería', 'priority_area_id' => 6],
            ['id' => 38, 'name' => 'Prospectiva', 'priority_area_id' => 6],
            ['id' => 39, 'name' => 'Ciencia política', 'priority_area_id' => 7],
            ['id' => 40, 'name' => 'Ciencias de la educación', 'priority_area_id' => 7],
            ['id' => 41, 'name' => 'Ciencias económicas', 'priority_area_id' => 7],
            ['id' => 42, 'name' => 'Ciencias jurídicas y derecho', 'priority_area_id' => 7],
            ['id' => 43, 'name' => 'Demografía', 'priority_area_id' => 7],
            ['id' => 44, 'name' => 'Formación docente', 'priority_area_id' => 7],
            ['id' => 45, 'name' => 'Geografía', 'priority_area_id' => 7],
            ['id' => 46, 'name' => 'Historia', 'priority_area_id' => 7],
            ['id' => 47, 'name' => 'Sociología', 'priority_area_id' => 7],
            ['id' => 48, 'name' => 'Prospectiva', 'priority_area_id' => 7],
            ['id' => 49, 'name' => 'Administración y negocios', 'priority_area_id' => 7],
            ['id' => 50, 'name' => 'Medios de comunicación y comunicaciones', 'priority_area_id' => 7],
            ['id' => 51, 'name' => 'Comunicación Científica', 'priority_area_id' => 7],
            ['id' => 52, 'name' => 'Gestión', 'priority_area_id' => 7],
            ['id' => 53, 'name' => 'Estudios de Género', 'priority_area_id' => 7],
            ['id' => 54, 'name' => 'Evaluación de reservas de litio', 'priority_area_id' => 8],
            ['id' => 55, 'name' => 'Innovación en la explotación y transformación del litio', 'priority_area_id' => 8],
            ['id' => 56, 'name' => 'Almacenamiento de energía', 'priority_area_id' => 8],
            ['id' => 57, 'name' => 'Aplicaciones', 'priority_area_id' => 8],
            ['id' => 58, 'name' => 'Comercialización, logística y transporte del litio', 'priority_area_id' => 8],
        ];

        foreach ($subAreas as $subArea) {
            SubArea::create($subArea);
        }
    }
}
