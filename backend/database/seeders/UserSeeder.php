<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'role_id'    => 1,
                'username'   => 'admin',
                'email'      => 'admin@glowchat.com',
                'phone'      => '+22960000001',
                'password'   => Hash::make('password'),
                'is_online'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id'    => 2,
                'username'   => 'alice',
                'email'      => 'alice@glowchat.com',
                'phone'      => '+22960000002',
                'password'   => Hash::make('password'),
                'is_online'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id'    => 2,
                'username'   => 'bob',
                'email'      => 'bob@glowchat.com',
                'phone'      => '+22960000003',
                'password'   => Hash::make('password'),
                'is_online'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id'    => 2,
                'username'   => 'clara',
                'email'      => 'clara@glowchat.com',
                'phone'      => '+22960000004',
                'password'   => Hash::make('password'),
                'is_online'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id'    => 2,
                'username'   => 'david',
                'email'      => 'david@glowchat.com',
                'phone'      => '+22960000005',
                'password'   => Hash::make('password'),
                'is_online'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        User::insert($users);
    }
}