<?php

namespace Database\Seeders;

use App\Domain\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $accountTypes = [
            ['name' => 'customer'],
            ['name' => 'reseller']
        ];

        foreach ($accountTypes as $type) {
            AccountType::updateOrCreate(
                ['name' => $type['name']]
            );
        }
    }
}
