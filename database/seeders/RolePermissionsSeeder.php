<?php

namespace Database\Seeders;

use App\Domain\Models\Permission;
use App\Domain\Models\RoleHasPermission;
use Illuminate\Database\Seeder;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $adminPerms = Permission::all();
        $this->checkAndAddPermByModel($adminPerms, 1);

        $customerPerms = [
            "USERS_PROFILE_READ",
            "USERS_PROFILE_UPDATE",

            "DASHBOARD_DASHBOARD_READ",
            "DASHBOARD_ANALYTICS_READ",

            "INSTALLATIONS_INSTALLATIONS_READ",

            "PRODUCTS_PRODUCTS_CREATE",
            "PRODUCTS_PRODUCTS_READ",
            "PRODUCTS_PRODUCTS_UPDATE",

            "PRODUCTS_PRODUCTS_PLUGINS_READ",
            "PRODUCTS_PRODUCTS_PLUGINS_CREATE",
            "PRODUCTS_PRODUCTS_PLUGINS_UPDATE",

            "CUSTOMERS_CUSTOMER_SELF_READ",
            "CUSTOMERS_CUSTOMER_SELF_UPDATE",

            "INSTALLATIONS_INSTALLATIONS_CREATE",
            "INSTALLATIONS_INSTALLATIONS_UPDATE",
            "INSTALLATIONS_INSTALLATIONS_READ"
        ];
        $this->checkAndAddPermByCodeName($customerPerms, 2);


    }

    private function checkAndAddPermByModel($perms, $role)
    {
        foreach ($perms as $perm) {
            RoleHasPermission::updateOrCreate(
                ['role_id' => $role, 'permission_id' => $perm->id],
                ['status' => 'allowed']
            );

        }
    }
    private function checkAndAddPermByCodeName($perms, $role)
    {
        foreach ($perms as $perm) {
            $permModel = Permission::where('codename', $perm)->first();
            if ($permModel) {
                RoleHasPermission::updateOrCreate(
                    ['role_id' => $role, 'permission_id' => $permModel->id],
                    ['status' => 'allowed']
                );
            }

        }
    }
}
