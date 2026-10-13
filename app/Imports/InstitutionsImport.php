<?php

namespace App\Imports;

use App\Models\Institution;
use App\Models\State;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InstitutionsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Las columnas del Excel son: 'nombre_de_la_institucion', 'ciudad', 'estado'
        $stateId = null;
        if (!empty($row['estado'])) {
            $state = State::where('name', 'LIKE', '%' . trim($row['estado']) . '%')->first();
            $stateId = $state ? $state->id : null;
        }

        return new Institution([
            'name'       => $row['nombre_de_la_institucion'] ?? $row['nombre'], 
            'state_id'   => $stateId ?? 1,
        ]);
    }
}
