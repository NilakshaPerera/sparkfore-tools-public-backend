<?php

namespace Database\Seeders;

use App\Domain\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
/*
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
*/

        // create , read , update , delete , soft_delete
        $perms = [
            //Dashboard
            ['module_id' => 1, 'name' => 'Dashboard', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 1, 'name' => 'Dashboard', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 1, 'name' => 'Dashboard', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 1, 'name' => 'Dashboard', 'codename' => "", 'description' => "", 'action' => "delete"],
            ['module_id' => 1, 'name' => 'Admin Analytics', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 1, 'name' => 'Analytics', 'codename' => "", 'description' => "", 'action' => "read"],
            // ['module_id' => 1, 'name' => 'Dashboard', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Products
            ['module_id' => 2, 'name' => 'Products', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 2, 'name' => 'Products', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 2, 'name' => 'Products', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 2, 'name' => 'Products', 'codename' => "", 'description' => "", 'action' => "delete"],
            ['module_id' => 2, 'name' => 'Product Self', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 2, 'name' => 'Product Self', 'codename' => "", 'description' => "", 'action' => "create"],
            // ['module_id' => 2, 'name' => 'Products', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Products - Software
            ['module_id' => 2, 'name' => 'Products Software', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 2, 'name' => 'Products Software', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 2, 'name' => 'Products Software', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 2, 'name' => 'Products Software', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 2, 'name' => 'Products Software', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Products - Plugins
            ['module_id' => 2, 'name' => 'Products Plugins', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 2, 'name' => 'Products Plugins', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 2, 'name' => 'Products Plugins', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 2, 'name' => 'Products Plugins', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 2, 'name' => 'Products Plugins', 'codename' => "", 'description' => "", 'action' => "soft_delete"],


            //Products - Hosting
            ['module_id' => 2, 'name' => 'Products Hosting', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 2, 'name' => 'Products Hosting', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 2, 'name' => 'Products Hosting', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 2, 'name' => 'Products Hosting', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 2, 'name' => 'Products Hosting', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Customers
            ['module_id' => 3, 'name' => 'Customers', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 3, 'name' => 'Customers', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 3, 'name' => 'Customers', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 3, 'name' => 'Customers', 'codename' => "", 'description' => "", 'action' => "delete"],
            ['module_id' => 3, 'name' => 'Customer Self', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 3, 'name' => 'Customer Self', 'codename' => "", 'description' => "", 'action' => "update"],
            // ['module_id' => 3, 'name' => 'Customers', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Users
            ['module_id' => 4, 'name' => 'Users', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 4, 'name' => 'Users', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 4, 'name' => 'Users', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 4, 'name' => 'Users', 'codename' => "", 'description' => "", 'action' => "delete"],
            ['module_id' => 4, 'name' => 'Profile', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 4, 'name' => 'Profile', 'codename' => "", 'description' => "", 'action' => "update"],
            // ['module_id' => 4, 'name' => 'Users', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Resellers
            ['module_id' => 5, 'name' => 'Resellers', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 5, 'name' => 'Resellers', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 5, 'name' => 'Resellers', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 5, 'name' => 'Resellers', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 5, 'name' => 'Resellers', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Installations
            ['module_id' => 6, 'name' => 'Installations', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 6, 'name' => 'Installations', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 6, 'name' => 'Installations', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 6, 'name' => 'Installations', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 6, 'name' => 'Installations', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Logs
            ['module_id' => 7, 'name' => 'Logs', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 7, 'name' => 'Logs', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 7, 'name' => 'Logs', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 7, 'name' => 'Logs', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 7, 'name' => 'Logs', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Documentation
            ['module_id' => 8, 'name' => 'Documentation', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 8, 'name' => 'Documentation', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 8, 'name' => 'Documentation', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 8, 'name' => 'Documentation', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 8, 'name' => 'Documentation', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Invoicing
            ['module_id' => 9, 'name' => 'Invoicing', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 9, 'name' => 'Invoicing', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 9, 'name' => 'Invoicing', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 9, 'name' => 'Invoicing', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 9, 'name' => 'Invoicing', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Access Control Modules
            ['module_id' => 10, 'name' => 'Access Control Modules', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 10, 'name' => 'Access Control Modules', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 10, 'name' => 'Access Control Modules', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 10, 'name' => 'Access Control Modules', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 10, 'name' => 'Access Control Modules', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            //Access Control Permissions
            ['module_id' => 10, 'name' => 'Access Control Permissions', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 10, 'name' => 'Access Control Permissions', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 10, 'name' => 'Access Control Permissions', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 10, 'name' => 'Access Control Permissions', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 10, 'name' => 'Access Control Permissions', 'codename' => "", 'description' => "", 'action' => "soft_delete"],

            // Access Control Role Permissions
            ['module_id' => 10, 'name' => 'Access Control_Role Permissions', 'codename' => "", 'description' => "", 'action' => "create"],
            ['module_id' => 10, 'name' => 'Access Control Role Permissions', 'codename' => "", 'description' => "", 'action' => "read"],
            ['module_id' => 10, 'name' => 'Access Control Role Permissions', 'codename' => "", 'description' => "", 'action' => "update"],
            ['module_id' => 10, 'name' => 'Access Control Role Permissions', 'codename' => "", 'description' => "", 'action' => "delete"],
            // ['module_id' => 10, 'name' => 'Access Control Role Permissions', 'codename' => "", 'description' => "", 'action' => "soft_delete"],


        ];

        foreach ($perms as $k => $item) {

            $perms[$k]['codename'] = createCodeName($item['module_id'], $item['name'], $item['action'] );
            $perms[$k]['description'] = $item['name'] . " " . $item['action'];

        }

        foreach ($perms as $item) {
            Permission::updateOrCreate(
                ['codename' => $item['codename']],
                ['module_id' => $item['module_id'], 'name' => $item['name'], 'action' => $item['action'], 'description' => $item['description']]
            );
        }
    }
}
