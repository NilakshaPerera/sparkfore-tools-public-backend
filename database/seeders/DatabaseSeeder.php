<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesTableSeeder::class,
            AccountTypesTableSeeder::class,
            UserTableSeeder::class,
            HostingBasePackageSeeder::class,
            HostingTypesTableSeeder::class,
            HostingsTableSeeder::class,
            SoftwareTableSeeder::class,
            CustomerTableSeeder::class,
            MaintainerTableSeeder::class,
            PluginTableSeeder::class,
            ProductPackageSeeder::class,
            InstallationTargetTypeTableSeeder::class,
            InstallationTableSeeder::class,
            SettingsTableSeeder::class,
            ModulesSeeder::class,
            PermissionsSeeder::class,
            RolePermissionsSeeder::class,
            RemoteJobTypesTableSeeder::class,
            PluginTypesSeeder::class,
        ]);
    }
}
