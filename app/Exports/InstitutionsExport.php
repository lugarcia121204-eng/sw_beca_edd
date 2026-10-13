<?php

namespace App\Exports;

use App\Models\Institution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InstitutionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Institution::with('state')->get();
    }

    public function map($institution): array
    {
        return [
            $institution->id,
            $institution->name,
            $institution->state ? $institution->state->name : '',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Estado',
        ];
    }
}
