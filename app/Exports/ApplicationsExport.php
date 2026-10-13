<?php

namespace App\Exports;

use App\Models\Application;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ApplicationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $search;
    protected $institutionId;
    protected $stateId;
    protected $status;

    public function __construct($search = null, $institutionId = null, $stateId = null, $status = null)
    {
        $this->search      = $search;
        $this->institutionId = $institutionId;
        $this->stateId     = $stateId;
        $this->status      = $status;
    }

    public function collection()
    {
        $query = Application::query()
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->join('institutions', 'users.institution_id', '=', 'institutions.id')
            ->join('states', 'institutions.state_id', '=', 'states.id')
            ->leftJoin('announcements', 'applications.announcement_id', '=', 'announcements.id')
            ->leftJoin('position_types', 'applications.position_type_id', '=', 'position_types.id')
            ->select(
                'applications.id',
                'users.name as teacher_name',
                'users.email as teacher_email',
                'institutions.name as institution_name',
                'states.name as state_name',
                'announcements.name as announcement_name',
                'position_types.name as position_type',
                'applications.status',
                'applications.created_at'
            );

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('institutions.name', 'like', "%{$this->search}%")
                  ->orWhere('states.name', 'like', "%{$this->search}%");
            });
        }

        if ($this->institutionId) {
            $query->where('institutions.id', $this->institutionId);
        }

        if ($this->stateId) {
            $query->where('states.id', $this->stateId);
        }

        if ($this->status && in_array($this->status, ['approved', 'rejected'])) {
            $query->where('applications.status', $this->status);
        } else {
            $query->whereIn('applications.status', ['approved', 'rejected']);
        }

        return $query->orderBy('applications.created_at', 'desc')->get();
    }

    public function map($row): array
    {
        $statusMap = [
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada',
            'expired'  => 'Expirada',
            'pending'  => 'Pendiente',
        ];

        return [
            $row->id,
            $row->teacher_name,
            $row->teacher_email,
            $row->institution_name,
            $row->state_name,
            $row->announcement_name ?? 'N/A',
            $row->position_type ?? 'N/A',
            $statusMap[$row->status] ?? $row->status,
            \Carbon\Carbon::parse($row->created_at)->format('d/m/Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Docente',
            'Correo',
            'Institución',
            'Estado',
            'Convocatoria',
            'Tipo de Plaza',
            'Estatus',
            'Fecha de Solicitud',
        ];
    }

    public function title(): string
    {
        return 'Solicitudes';
    }
}
