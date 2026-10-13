<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriorityArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function subAreas()
    {
        return $this->hasMany(SubArea::class);
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

        return $query->where('priority_areas.name', 'LIKE', "%{$search}%");
    }

    public function scopeOrdenado($query, $sortField = 'id', $sortDirection = 'asc')
    {
        return $query->orderBy($sortField, $sortDirection);
    }
}
