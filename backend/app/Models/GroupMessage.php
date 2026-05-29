<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    protected $fillable = [
        'group_id', 'sender_id', 'message',
        'type', 'file_url', 'is_seen',
    ];

    protected $casts = ['is_seen' => 'boolean'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}