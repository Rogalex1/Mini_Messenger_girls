<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_one', 'user_two', 'last_message_id'];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one');
    }
    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two');
    }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }
    public function typingStatus()
    {
        return $this->hasMany(TypingStatus::class);
    }

    // Retourne l'autre participant
    public function getOtherUser(int $myId): User
    {
        return $this->user_one === $myId ? $this->userTwo : $this->userOne;
    }
}