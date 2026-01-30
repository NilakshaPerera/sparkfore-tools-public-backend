<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstallationTargetTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('installation_target_types')->updateOrInsert(
            ['key'=>'production'], ['name'=>'Production']
        );
        DB::table('installation_target_types')->updateOrInsert(
            ['key'=>'staging'], ['name'=>'Staging']
        );
        DB::table('installation_target_types')->updateOrInsert(
            ['key'=>'development'], ['name'=>'Development']
        );
    }
}
