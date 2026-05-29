<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        Notification::insert([
            [
                'user_id'    => 2,
                'type'       => 'friend_request',
                'title'      => 'Nouvelle demande d\'ami',
                'content'    => 'David vous a envoyé une demande d\'ami.',
                'is_read'    => false,
                'created_at' => now()->subHours(2),
            ],
            [
                'user_id'    => 2,
                'type'       => 'message',
                'title'      => 'Nouveau message',
                'content'    => 'Bob vous a envoyé un message.',
                'is_read'    => false,
                'created_at' => now()->subHours(1),
            ],
            [
                'user_id'    => 3,
                'type'       => 'like',
                'title'      => 'Quelqu\'un a aimé votre post',
                'content'    => 'Alice a aimé votre publication.',
                'is_read'    => true,
                'created_at' => now()->subDays(1),
            ],
            [
                'user_id'    => 4,
                'type'       => 'comment',
                'title'      => 'Nouveau commentaire',
                'content'    => 'Bob a commenté votre publication.',
                'is_read'    => false,
                'created_at' => now()->subHours(3),
            ],
            [
                'user_id'    => 1,
                'type'       => 'system',
                'title'      => 'Bienvenue sur GlowChat',
                'content'    => 'La plateforme est opérationnelle. 🚀',
                'is_read'    => true,
                'created_at' => now()->subDays(6),
            ],
        ]);
    }
}