<?php

namespace App\Imports;

use App\Models\PriorityArea;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PriorityAreasImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // La columna del Excel es: 'nombre_del_area_prioritaria'
        $nombre = $row['nombre_del_area_prioritaria'] ?? $row['nombre'] ?? $row['name'];
        
        // Check for duplicates
        if (PriorityArea::where('name', $nombre)->exists()) {
            return null;
        }

        return new PriorityArea([
            'name' => $nombre,
            'description' => $row['descripcion'] ?? $row['description'] ?? null,
        ]);
    }
}
