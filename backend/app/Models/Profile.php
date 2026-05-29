<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name',
        'profile_photo', 'cover_photo', 'bio',
        'gender', 'birth_date', 'country', 'city',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Nom complet
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // Âge calculé automatiquement
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }
}