<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypingStatus extends Model
{
    public $timestamps = false;
    protected $fillable = ['conversation_id', 'user_id', 'is_typing'];
    protected $casts = ['is_typing' => 'boolean'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}