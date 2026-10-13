<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'priority_area_id'];

    public function priorityArea()
    {
        return $this->belongsTo(PriorityArea::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeBuscarGlobal($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('sub_areas.name', 'LIKE', "%{$search}%")
                ->orWhereHas('priorityArea', function ($qArea) use ($search) {
                    $qArea->where('priority_areas.name', 'LIKE', "%{$search}%");
                });
        });
    }

    public function scopeOrdenado($query, $sortField = 'id', $sortDirection = 'asc')
    {
        return $query->orderBy($sortField, $sortDirection);
    }
}
