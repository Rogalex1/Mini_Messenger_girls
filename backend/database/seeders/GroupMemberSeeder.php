<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GroupMember;

class GroupMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            // Groupe 1 — Équipe Dev
            ['group_id' => 1, 'user_id' => 1, 'role' => 'admin',  'joined_at' => now()->subDays(6)],
            ['group_id' => 1, 'user_id' => 2, 'role' => 'member', 'joined_at' => now()->subDays(6)],
            ['group_id' => 1, 'user_id' => 3, 'role' => 'member', 'joined_at' => now()->subDays(5)],
            ['group_id' => 1, 'user_id' => 4, 'role' => 'member', 'joined_at' => now()->subDays(5)],

            // Groupe 2 — Amis Cotonou
            ['group_id' => 2, 'user_id' => 2, 'role' => 'admin',  'joined_at' => now()->subDays(4)],
            ['group_id' => 2, 'user_id' => 3, 'role' => 'member', 'joined_at' => now()->subDays(4)],
            ['group_id' => 2, 'user_id' => 4, 'role' => 'member', 'joined_at' => now()->subDays(3)],
            ['group_id' => 2, 'user_id' => 5, 'role' => 'member', 'joined_at' => now()->subDays(3)],
        ];

        GroupMember::insert($members);
    }
}