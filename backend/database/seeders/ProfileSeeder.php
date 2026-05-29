<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'user_id'       => 1,
                'first_name'    => 'Super',
                'last_name'     => 'Admin',
                'bio'           => 'Administrateur de GlowChat.',
                'gender'        => 'male',
                'birth_date'    => '1990-01-01',
                'country'       => 'Bénin',
                'city'          => 'Cotonou',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'user_id'       => 2,
                'first_name'    => 'Alice',
                'last_name'     => 'Dupont',
                'bio'           => 'Passionnée de musique et de voyages 🌍',
                'gender'        => 'female',
                'birth_date'    => '1998-05-12',
                'country'       => 'Bénin',
                'city'          => 'Cotonou',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'user_id'       => 3,
                'first_name'    => 'Bob',
                'last_name'     => 'Martin',
                'bio'           => 'Développeur web, fan de foot ⚽',
                'gender'        => 'male',
                'birth_date'    => '1995-08-23',
                'country'       => 'Bénin',
                'city'          => 'Porto-Novo',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'user_id'       => 4,
                'first_name'    => 'Clara',
                'last_name'     => 'Hounkpè',
                'bio'           => 'Designer UI/UX, créative avant tout 🎨',
                'gender'        => 'female',
                'birth_date'    => '2000-03-17',
                'country'       => 'Bénin',
                'city'          => 'Abomey-Calavi',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'user_id'       => 5,
                'first_name'    => 'David',
                'last_name'     => 'Kossou',
                'bio'           => 'Entrepreneur, toujours en mouvement 🚀',
                'gender'        => 'male',
                'birth_date'    => '1993-11-30',
                'country'       => 'Bénin',
                'city'          => 'Parakou',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ];

        Profile::insert($profiles);
    }
}