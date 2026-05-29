<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GroupMessage;

class GroupMessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            // Groupe 1
            [
                'group_id'   => 1,
                'sender_id'  => 1,
                'message'    => 'Bienvenue dans le groupe Équipe Dev ! 🚀',
                'type'       => 'text',
                'is_seen'    => true,
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(6),
            ],
            [
                'group_id'   => 1,
                'sender_id'  => 3,
                'message'    => 'Merci ! On commence quand les migrations ?',
                'type'       => 'text',
                'is_seen'    => true,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'group_id'   => 1,
                'sender_id'  => 4,
                'message'    => 'J\'ai fini les maquettes, je les partage bientôt 🎨',
                'type'       => 'text',
                'is_seen'    => false,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            // Groupe 2
            [
                'group_id'   => 2,
                'sender_id'  => 2,
                'message'    => 'Yo tout le monde ! On se retrouve ce weekend ? 🎉',
                'type'       => 'text',
                'is_seen'    => true,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'group_id'   => 2,
                'sender_id'  => 5,
                'message'    => 'Je suis partant ! Où et à quelle heure ?',
                'type'       => 'text',
                'is_seen'    => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'group_id'   => 2,
                'sender_id'  => 3,
                'message'    => 'Moi aussi ! Cotonou centre ?',
                'type'       => 'text',
                'is_seen'    => false,
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
        ];

        GroupMessage::insert($messages);
    }
}