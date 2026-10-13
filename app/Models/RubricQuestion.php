<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubricQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['rubric_id', 'text'];

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(RubricQuestionOption::class);
    }
}
