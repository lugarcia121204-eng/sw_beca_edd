<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recognition extends Model
{
    use HasFactory;

    protected $table = 'recognitions';

    protected $fillable = [
        'user_id',
        'announcement_id',
        'type',
        'active',
        'sent_at',
        'identifier',
        'digital_seal',
        'template_id',
        'snapshot_data',
    ];

    protected $casts = [
        'active'        => 'boolean',
        'sent_at'       => 'datetime',
        'snapshot_data' => 'array',   // Auto JSON decode/encode
    ];

    // Relaciones

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Scope a query to find a recognition by its unique identifier.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $identifier
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValidByIdentifier($query, $identifier)
    {
        return $query->where('identifier', $identifier)->with('user');
    }
}
