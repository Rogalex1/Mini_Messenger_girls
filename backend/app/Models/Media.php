<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'file_name', 'file_url', 'mime_type', 'size'];
    protected $casts = ['created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}