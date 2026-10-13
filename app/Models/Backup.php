<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'file_path',
        'file_size',
        'status',
        'type',
        'backup_mode',
        'is_encrypted',
        'created_by',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Human-readable file size.
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '—';
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Frequency label in Spanish for scheduled backups.
     */
    public static function frequencyLabel(string $freq): string
    {
        return match ($freq) {
            'daily'   => 'Diario',
            'weekly'  => 'Semanal',
            'monthly' => 'Mensual',
            default   => ucfirst($freq),
        };
    }
}
