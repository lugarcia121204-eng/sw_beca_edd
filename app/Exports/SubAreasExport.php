<?php

namespace App\Exports;

use App\Models\SubArea;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SubAreasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return SubArea::with('priorityArea')->get();
    }

    public function map($subArea): array
    {
        return [
            $subArea->id,
            $subArea->name,
            $subArea->priorityArea ? $subArea->priorityArea->name : '',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Área Prioritaria',
        ];
    }
}
