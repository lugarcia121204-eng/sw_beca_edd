<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'file_path',
        'file_name',
        'is_active',
        'content_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'content_data' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
