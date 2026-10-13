<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'catalog_documents';

    protected $fillable = [
        'name',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'active',
        'via'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relationship with Announcements (many to many)
     */
    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class, 'announcement_document', 'catalog_document_id', 'announcement_id')
            ->withPivot('is_mandatory', 'via')
            ->withTimestamps();
    }

    /**
     * Scope for active documents
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for ordering by name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
    public function scopeOrdenado($query)
    {
        return $query->orderBy('name', 'asc');
    }
}
