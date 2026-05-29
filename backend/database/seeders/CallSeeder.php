<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Call;

class CallSeeder extends Seeder
{
    public function run(): void
    {
        Call::insert([
            [
                'caller_id'   => 2,
                'receiver_id' => 3,
                'type'        => 'audio',
                'status'      => 'ended',
                'started_at'  => now()->subDays(2)->setTime(14, 0),
                'ended_at'    => now()->subDays(2)->setTime(14, 8),
                'created_at'  => now()->subDays(2),
            ],
            [
                'caller_id'   => 3,
                'receiver_id' => 4,
                'type'        => 'video',
                'status'      => 'ended',
                'started_at'  => now()->subDays(1)->setTime(20, 0),
                'ended_at'    => now()->subDays(1)->setTime(20, 25),
                'created_at'  => now()->subDays(1),
            ],
            [
                'caller_id'   => 5,
                'receiver_id' => 2,
                'type'        => 'audio',
                'status'      => 'declined',
                'started_at'  => null,
                'ended_at'    => null,
                'created_at'  => now()->subHours(3),
            ],
        ]);
    }
}