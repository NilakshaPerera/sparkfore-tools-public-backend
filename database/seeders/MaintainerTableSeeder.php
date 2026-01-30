<?php

namespace database\seeders;

use App\Domain\Models\PipelineMaintainer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MaintainerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Customer', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Sparkfore', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        foreach ($data as $item) {
            PipelineMaintainer::updateOrCreate(
                ['name' => $item['name']],
                ['created_at' => $item['created_at'], 'updated_at' => $item['updated_at']]
            );
        }
    }
}
