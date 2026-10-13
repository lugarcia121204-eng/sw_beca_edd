<?php

namespace App\Exports;

use App\Models\PriorityArea;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PriorityAreasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return PriorityArea::all();
    }

    public function map($area): array
    {
        return [
            $area->id,
            $area->name,
            $area->description, // If exists
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Descripción',
        ];
    }
}
