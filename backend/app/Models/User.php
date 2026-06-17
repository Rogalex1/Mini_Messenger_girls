<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'username', 'email', 'phone', 'password',
        'is_online', 'last_seen',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_code', 'two_factor_expires_at',
    ];

    protected $casts = [
        'is_online'         => 'boolean',
        'last_seen'         => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function conversations()
    {
        return Conversation::where('user_one', $this->id)
                           ->orWhere('user_two', $this->id);
    }

    public function sentRequests()
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    public function receivedRequests()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id')
                    ->withPivot('role', 'joined_at');
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function viewedStatuses()
    {
        return $this->belongsToMany(Status::class, 'status_views', 'viewer_id', 'status_id')
                    ->withPivot('viewed_at');
    }

    public function hasUnviewedStatuses()
    {
        return $this->statuses()
                    ->active()
                    ->whereDoesntHave('views', function ($query) {
                        $query->where('viewer_id', auth()->id());
                    })
                    ->exists();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function blockedUsers()
    {
        return $this->hasMany(BlockedUser::class, 'blocker_id');
    }

    public function calls()
    {
        return $this->hasMany(Call::class, 'caller_id');
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function friends()
    {
        $friendIds = \App\Models\FriendRequest::where(function ($query) {
            $query->where('sender_id', $this->id)
                  ->orWhere('receiver_id', $this->id);
        })->where('status', 'accepted')
        ->get()
        ->map(function ($req) {
            return $req->sender_id === $this->id ? $req->receiver_id : $req->sender_id;
        })
        ->toArray();

        return \App\Models\User::whereIn('id', $friendIds);
    }

    // ─── Helpers ─────────────────────────────────────

    // Accès direct au nom complet sans charger le profil manuellement
    public function getFullNameAttribute(): string
    {
        return trim($this->profile?->first_name . ' ' . $this->profile?->last_name)
               ?: $this->username;
    }

    // Accès direct à la photo de profil
    public function getAvatarAttribute(): ?string
    {
        return $this->profile?->profile_photo;
    }
}