<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedUser extends Model
{
    public $timestamps = false;
    protected $fillable = ['blocker_id', 'blocked_user_id'];
    protected $casts = ['created_at' => 'datetime'];

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }
    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}