<?php

namespace database\seeders;

use App\Domain\Models\HostingType;
use Illuminate\Database\Seeder;

class HostingTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id' => 1, 'key' => 'cloud', 'name' => 'Cloud'],
            ['id' => 2, 'key' => 'on-prem', 'name' => 'On Prem']
        ];

        foreach ($data as $item) {
            HostingType::updateOrCreate(
                ['id' => $item['id']],
                ['key' => $item['key'], 'name' => $item['name']]
            );
        }
    }
}
