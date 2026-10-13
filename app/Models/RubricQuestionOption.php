<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricQuestionOption extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['rubric_question_id', 'text', 'score'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(RubricQuestion::class, 'rubric_question_id');
    }
}
