<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conversation;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $conversations = [
            // alice ↔ bob
            [
                'user_one'        => 2,
                'user_two'        => 3,
                'last_message_id' => null,
                'created_at'      => now()->subDays(9),
                'updated_at'      => now()->subDays(9),
            ],
            // alice ↔ clara
            [
                'user_one'        => 2,
                'user_two'        => 4,
                'last_message_id' => null,
                'created_at'      => now()->subDays(7),
                'updated_at'      => now()->subDays(7),
            ],
            // bob ↔ clara
            [
                'user_one'        => 3,
                'user_two'        => 4,
                'last_message_id' => null,
                'created_at'      => now()->subDays(5),
                'updated_at'      => now()->subDays(5),
            ],
        ];

        Conversation::insert($conversations);
    }
}