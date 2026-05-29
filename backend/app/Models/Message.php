<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id', 'sender_id', 'receiver_id',
        'message', 'type', 'file_url',
        'is_seen', 'seen_at', 'is_single_view',
    ];

    protected $casts = [
        'is_seen'       => 'boolean',
        'is_single_view'=> 'boolean',
        'seen_at'       => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }
}