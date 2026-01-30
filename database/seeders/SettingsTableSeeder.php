<?php

namespace database\seeders;

use App\Domain\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'product_maintenance'],
            ['namespace' => 'product_maintenance', 'name' => 'Product Maintenance Cost', 'value' => 100]
        );
    }
}
