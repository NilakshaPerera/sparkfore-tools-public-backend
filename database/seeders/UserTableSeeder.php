<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user and the client
        return;
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@sparkfore.com'],
            ['f_name' => 'admin',
            'l_name' => 'admin',
            'password' => Hash::make('8pIT9YeYpzoOjgT'),
            'role_id' => 1,
            'lang_id' => 1,
            'account_type_id' => 1,
            'trial' => false,
        ]);

        DB::table('oauth_clients')->updateOrInsert(
            ['id' => 10000],
            ['name' => 'FRONTEND',
            'secret' => 'jaR1nxPJcachu9ieL5zZK2X9hbX5AfDg52StnrDR',
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => 0
        ]);

        DB::table('oauth_clients')->updateOrInsert(
            ['id' => 10001],
            ['name' => 'WEBHOOK',
            'secret' => 'ptBuoyiZsTIxhsNjTWHuwwQhiGNWUNhBQNALvxGd',
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => 0
        ]);

        DB::table('oauth_clients')->updateOrInsert(
            ['name' => 'SPARKFORE-OPEN-AI'],
            ['secret' => 'XxGfVtSzIuLqnbRl5G7ZJKNkjudwlR9jQW4q8Ukxx6Aa5YbT0V',
            'id' => 10002,
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => 0
        ]);
    }
}
