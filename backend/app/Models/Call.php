<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    public $timestamps = false;
    protected $fillable = ['caller_id', 'receiver_id', 'type', 'status', 'started_at', 'ended_at'];
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'created_at' => 'datetime',
    ];

    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Durée de l'appel en secondes
    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->ended_at) {
            return $this->started_at->diffInSeconds($this->ended_at);
        }
        return null;
    }
}