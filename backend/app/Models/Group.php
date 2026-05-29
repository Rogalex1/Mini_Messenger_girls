<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'photo', 'description', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')
                    ->withPivot('role', 'joined_at');
    }
    public function admins()
    {
        return $this->members()->wherePivot('role', 'admin');
    }
    public function messages()
    {
        return $this->hasMany(GroupMessage::class);
    }
}