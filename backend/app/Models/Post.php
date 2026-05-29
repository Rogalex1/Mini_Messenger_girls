<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'content', 'media_url', 'type', 'likes_count', 'comments_count'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }
}