<?php

namespace database\seeders;

use App\Domain\Models\RemoteJobType;
use Illuminate\Database\Seeder;

class RemoteJobTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'key' => 'restart',
                'name' => 'Restart Installation Server'
            ],
            [
                'key' => 'host',
                'name' => 'Product Host Server'
            ],
            [
                'key' => 'create_pipeline',
                'name' => 'Create Pipeline'
            ],
            [
                'key' => 'build_pipeline',
                'name' => 'Build Pipeline'
            ],
            [
                'key' => 'create_customer',
                'name' => 'Create Customer'
            ],
            [
                'key' => 'rename_customer',
                'name' => 'Rename Customer'
            ],
            [
                'key' => 'delete_pipeline',
                'name' => 'Delete pipeline'
            ],
            [
                'key' => 'public_installation',
                'name' => 'Public installation'
            ],
            [
                'key' => 'change_disk_size',
                'name' => 'Change Disk Size'
            ],
            [
                'key' => 'standard_installation',
                'name' => 'Standard installation'
            ],
            [
                'key' => 'installation_delete',
                'name' => 'Delete Installation'
            ]
        ];
        foreach ($data as $item) {
            RemoteJobType::updateOrCreate(
                ['key' => $item['key']],
                ['name' => $item['name']]
            );
        }
    }
}
