<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'name'        => 'Équipe Dev',
                'description' => 'Groupe des développeurs du projet GlowChat',
                'created_by'  => 1,
                'created_at'  => now()->subDays(6),
                'updated_at'  => now()->subDays(6),
            ],
            [
                'name'        => 'Amis Cotonou',
                'description' => 'Le groupe des amis de Cotonou 🇧🇯',
                'created_by'  => 2,
                'created_at'  => now()->subDays(4),
                'updated_at'  => now()->subDays(4),
            ],
        ];

        Group::insert($groups);
    }
}