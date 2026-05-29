<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,         // 1. rôles en premier
            UserSeeder::class,         // 2. users (dépend de roles)
            ProfileSeeder::class,      // 3. profils (dépend de users)
            FriendRequestSeeder::class,// 4. demandes d'amis
            ConversationSeeder::class, // 5. conversations privées
            MessageSeeder::class,      // 6. messages (met à jour last_message_id)
            GroupSeeder::class,        // 7. groupes
            GroupMemberSeeder::class,  // 8. membres des groupes
            GroupMessageSeeder::class, // 9. messages de groupe
            StatusSeeder::class,       // 10. statuts + vues
            PostSeeder::class,         // 11. posts + commentaires + likes
            NotificationSeeder::class, // 12. notifications
            CallSeeder::class,         // 13. appels
        ]);
    }
}