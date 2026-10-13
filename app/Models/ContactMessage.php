<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'institution_name', 'message', 'read_at'];
    protected function casts(): array { return ['read_at' => 'datetime']; }
}
