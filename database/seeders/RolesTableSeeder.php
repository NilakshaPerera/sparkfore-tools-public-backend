<?php

namespace Database\Seeders;

use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin'],
            ['name' => 'customer'],
            ['name' => 'support']
        ];
        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']]
            );
        }
    }
}
