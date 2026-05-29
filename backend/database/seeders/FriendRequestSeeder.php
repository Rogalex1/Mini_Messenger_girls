<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FriendRequest;

class FriendRequestSeeder extends Seeder
{
    public function run(): void
    {
        $requests = [
            // alice → bob : accepté
            [
                'sender_id'   => 2,
                'receiver_id' => 3,
                'status'      => 'accepted',
                'created_at'  => now()->subDays(10),
                'updated_at'  => now()->subDays(9),
            ],
            // alice → clara : accepté
            [
                'sender_id'   => 2,
                'receiver_id' => 4,
                'status'      => 'accepted',
                'created_at'  => now()->subDays(8),
                'updated_at'  => now()->subDays(7),
            ],
            // david → alice : en attente
            [
                'sender_id'   => 5,
                'receiver_id' => 2,
                'status'      => 'pending',
                'created_at'  => now()->subDays(1),
                'updated_at'  => now()->subDays(1),
            ],
            // bob → david : refusé
            [
                'sender_id'   => 3,
                'receiver_id' => 5,
                'status'      => 'refused',
                'created_at'  => now()->subDays(5),
                'updated_at'  => now()->subDays(4),
            ],
            // clara → david : bloqué
            [
                'sender_id'   => 4,
                'receiver_id' => 5,
                'status'      => 'blocked',
                'created_at'  => now()->subDays(3),
                'updated_at'  => now()->subDays(2),
            ],
        ];

        FriendRequest::insert($requests);
    }
}