<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            [
                'id'         => 1,
                'name'       => 'admin',
                'label'      => 'Administrateur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 2,
                'name'       => 'user',
                'label'      => 'Utilisateur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}