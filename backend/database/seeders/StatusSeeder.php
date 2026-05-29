<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;
use App\Models\StatusView;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        // Statuts (expirent dans 24h)
        Status::insert([
            [
                'user_id'    => 2,
                'type'       => 'text',
                'caption'    => 'Bonne journée à tous ! ☀️',
                'expires_at' => now()->addHours(20),
                'created_at' => now()->subHours(4),
            ],
            [
                'user_id'    => 3,
                'type'       => 'text',
                'caption'    => 'En train de coder... ☕',
                'expires_at' => now()->addHours(18),
                'created_at' => now()->subHours(6),
            ],
            [
                'user_id'    => 4,
                'type'       => 'text',
                'caption'    => 'Nouveau design dispo ! 🎨',
                'expires_at' => now()->addHours(22),
                'created_at' => now()->subHours(2),
            ],
        ]);

        // Vues des statuts
        StatusView::insert([
            ['status_id' => 1, 'viewer_id' => 3, 'viewed_at' => now()->subHours(3)],
            ['status_id' => 1, 'viewer_id' => 4, 'viewed_at' => now()->subHours(2)],
            ['status_id' => 2, 'viewer_id' => 2, 'viewed_at' => now()->subHours(5)],
            ['status_id' => 3, 'viewer_id' => 2, 'viewed_at' => now()->subHours(1)],
            ['status_id' => 3, 'viewer_id' => 3, 'viewed_at' => now()->subMinutes(30)],
        ]);
    }
}