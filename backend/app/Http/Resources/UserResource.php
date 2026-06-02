<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'username'   => $this->username,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'role'       => $this->role?->name,
            'is_online'  => $this->is_online,
            'last_seen'  => $this->last_seen?->diffForHumans(),
            'profile'    => [
                'first_name'    => $this->profile?->first_name,
                'last_name'     => $this->profile?->last_name,
                'avatar'        => $this->profile?->profile_photo
                                    ? asset('storage/' . $this->profile->profile_photo)
                                    : null,
                'bio'           => $this->profile?->bio,
                'gender'        => $this->profile?->gender,
                'country'       => $this->profile?->country,
                'city'          => $this->profile?->city,
            ],
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}