<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'media_url', 'type', 'caption', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime', 'created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function views()
    {
        return $this->hasMany(StatusView::class);
    }

    // Scope : statuts non expirés
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}