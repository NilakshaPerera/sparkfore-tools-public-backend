<?php

namespace Database\Seeders;

use App\Domain\Models\Module;
use Illuminate\Database\Seeder;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            ['name' => 'Dashboard'], // 1
            ['name' => 'Products'], // 2
            ['name' => 'Customers'], // 3
            ['name' => 'Users'], // 4
            ['name' => 'Resellers'], // 5
            ['name' => 'Installations'], // 6
            ['name' => 'Logs'], // 7
            ['name' => 'Documentation'], // 8
            ['name' => 'Invoicing'], // 9
            ['name' => 'Access Control'], // 10
        ];
        foreach ($modules as $item) {
            Module::updateOrCreate(
                ['name' => $item['name']]
            );
        }
    }
}
