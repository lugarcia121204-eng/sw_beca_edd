<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class WorkDocument extends Model
{
    use SoftDeletes;
    protected $fillable = ['source_key', 'title', 'description', 'category', 'file_path', 'file_name', 'file_size', 'published'];
    protected $casts = ['published' => 'boolean'];
    public function publicData(): array
    {
        return ['id' => 'work-'.$this->id, 'title' => $this->title, 'description' => $this->description, 'category' => $this->category,
            'format' => strtoupper(pathinfo($this->file_name, PATHINFO_EXTENSION)), 'file_size' => $this->file_size,
            'download_url' => route('work-documents.download', $this),
            'preview_url' => strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)) === 'pdf' ? route('work-documents.preview', $this) : null];
    }
}
