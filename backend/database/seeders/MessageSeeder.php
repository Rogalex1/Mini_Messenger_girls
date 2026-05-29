<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\Conversation;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            // Conversation 1 — alice ↔ bob
            [
                'conversation_id' => 1,
                'sender_id'       => 2,
                'receiver_id'     => 3,
                'message'         => 'Salut Bob ! Comment tu vas ?',
                'type'            => 'text',
                'is_seen'         => true,
                'seen_at'         => now()->subDays(8),
                'created_at'      => now()->subDays(9),
                'updated_at'      => now()->subDays(9),
            ],
            [
                'conversation_id' => 1,
                'sender_id'       => 3,
                'receiver_id'     => 2,
                'message'         => 'Ça va super ! Et toi Alice ?',
                'type'            => 'text',
                'is_seen'         => true,
                'seen_at'         => now()->subDays(8),
                'created_at'      => now()->subDays(8),
                'updated_at'      => now()->subDays(8),
            ],
            [
                'conversation_id' => 1,
                'sender_id'       => 2,
                'receiver_id'     => 3,
                'message'         => 'Très bien merci ! Tu es dispo ce soir ?',
                'type'            => 'text',
                'is_seen'         => false,
                'created_at'      => now()->subHours(2),
                'updated_at'      => now()->subHours(2),
            ],
            // Conversation 2 — alice ↔ clara
            [
                'conversation_id' => 2,
                'sender_id'       => 4,
                'receiver_id'     => 2,
                'message'         => 'Alice t\'as vu mon nouveau design ?',
                'type'            => 'text',
                'is_seen'         => true,
                'seen_at'         => now()->subDays(6),
                'created_at'      => now()->subDays(7),
                'updated_at'      => now()->subDays(7),
            ],
            [
                'conversation_id' => 2,
                'sender_id'       => 2,
                'receiver_id'     => 4,
                'message'         => 'Oui ! C\'est magnifique 🔥',
                'type'            => 'text',
                'is_seen'         => true,
                'seen_at'         => now()->subDays(6),
                'created_at'      => now()->subDays(6),
                'updated_at'      => now()->subDays(6),
            ],
            // Conversation 3 — bob ↔ clara
            [
                'conversation_id' => 3,
                'sender_id'       => 3,
                'receiver_id'     => 4,
                'message'         => 'Clara, tu peux m\'aider sur le projet ?',
                'type'            => 'text',
                'is_seen'         => false,
                'created_at'      => now()->subHours(1),
                'updated_at'      => now()->subHours(1),
            ],
        ];

        foreach ($messages as $data) {
            Message::create($data);
        }

        // Mettre à jour last_message_id dans chaque conversation
        Conversation::find(1)->update(['last_message_id' => 3]);
        Conversation::find(2)->update(['last_message_id' => 5]);
        Conversation::find(3)->update(['last_message_id' => 6]);
    }
}