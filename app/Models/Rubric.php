<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubric extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(RubricQuestion::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
