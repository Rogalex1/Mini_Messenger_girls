<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::insert([
            [
                'user_id'        => 2,
                'content'        => 'Super journée aujourd\'hui à Cotonou 🌞 La vie est belle !',
                'type'           => 'text',
                'likes_count'    => 3,
                'comments_count' => 2,
                'created_at'     => now()->subDays(2),
                'updated_at'     => now()->subDays(2),
            ],
            [
                'user_id'        => 3,
                'content'        => 'Je viens de finir un nouveau projet Laravel. WebSockets c\'est incroyable 🔥',
                'type'           => 'text',
                'likes_count'    => 4,
                'comments_count' => 1,
                'created_at'     => now()->subDays(1),
                'updated_at'     => now()->subDays(1),
            ],
            [
                'user_id'        => 4,
                'content'        => 'Mon dernier design UI est enfin live 🎨✨',
                'type'           => 'text',
                'likes_count'    => 5,
                'comments_count' => 3,
                'created_at'     => now()->subHours(5),
                'updated_at'     => now()->subHours(5),
            ],
        ]);

        PostComment::insert([
            ['post_id' => 1, 'user_id' => 3, 'comment' => 'Trop bien ! 🔥',         'created_at' => now()->subDays(1)],
            ['post_id' => 1, 'user_id' => 4, 'comment' => 'Profite bien !',           'created_at' => now()->subDays(1)],
            ['post_id' => 2, 'user_id' => 2, 'comment' => 'Bravo Bob !',              'created_at' => now()->subHours(20)],
            ['post_id' => 3, 'user_id' => 2, 'comment' => 'Magnifique comme d\'hab', 'created_at' => now()->subHours(4)],
            ['post_id' => 3, 'user_id' => 3, 'comment' => 'C\'est clean 👌',         'created_at' => now()->subHours(3)],
            ['post_id' => 3, 'user_id' => 5, 'comment' => 'Impressionnant !',         'created_at' => now()->subHours(2)],
        ]);

        PostLike::insert([
            ['post_id' => 1, 'user_id' => 3, 'created_at' => now()->subDays(1)],
            ['post_id' => 1, 'user_id' => 4, 'created_at' => now()->subDays(1)],
            ['post_id' => 1, 'user_id' => 5, 'created_at' => now()->subDays(1)],
            ['post_id' => 2, 'user_id' => 2, 'created_at' => now()->subHours(22)],
            ['post_id' => 2, 'user_id' => 4, 'created_at' => now()->subHours(20)],
            ['post_id' => 2, 'user_id' => 5, 'created_at' => now()->subHours(18)],
            ['post_id' => 2, 'user_id' => 1, 'created_at' => now()->subHours(15)],
            ['post_id' => 3, 'user_id' => 2, 'created_at' => now()->subHours(4)],
            ['post_id' => 3, 'user_id' => 3, 'created_at' => now()->subHours(3)],
            ['post_id' => 3, 'user_id' => 5, 'created_at' => now()->subHours(2)],
            ['post_id' => 3, 'user_id' => 1, 'created_at' => now()->subHours(1)],
        ]);
    }
}