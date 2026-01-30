<?php

use App\Domain\Models\Permission;
use App\Domain\Models\Module;
use App\Domain\Models\Product;
use App\Domain\Models\RoleHasPermission;
use Illuminate\Support\Facades\Auth;

function getRolePermissions($roleId)
{

    $roleId = Auth::user() ? Auth::user()->role_id : $roleId;
    $permissions = [];

    if ($roleId) {

        $rolePermissions = RoleHasPermission::where('role_id', $roleId)->get();

        foreach ($rolePermissions as $perm) {

            $permission = [];
            $permission['name'] = $perm->permission->name;
            $permission['codename'] = $perm->permission->codename;
            $permission['action'] = $perm->permission->action;
            $permission['module'] = $perm->permission->module->name;
            array_push($permissions, $permission);

        }
    }

    return $permissions;
}

function createCodeName($module, $name, $action)
{

    $module = Module::where("id", $module)->first();
    $code = trimEnds($module->name) . "_" . trimEnds($name) . '_' . $action;
    $code = strtoupper($code);
    $code = str_replace(' ', '_', $code);

    return $code;
}

/**
 * Get the customer ID of the authenticated user if they are not an admin.
 *
 * This function checks the role of the authenticated user. If the user's role
 * is not 'admin', it returns the user's customer ID. Otherwise, it returns null.
 *
 * @return int|null The customer ID of the authenticated user if they are not an admin, or null if they are an admin.
 */
function getNonAdminCustomerId(): ?int {
    if (auth()->user()->role->name != 'admin') {
        return auth()->user()->customer_id;
    }
    return null;
}


function trimEnds($string)
{
    return ltrim(rtrim($string));
}

function test($data)
{
    return "HEllo";
}


function can($codename)
{
    $currentUser = Auth::user();
    if (!$currentUser || !$currentUser->role) {
        return false;
    }
    $roleId = $currentUser->role->id;
    $permission = Permission::where('codename', '=', $codename)->first();

    if (
        $permission &&
        RoleHasPermission::where('role_id', $roleId)
            ->where('permission_id', $permission->id)
            ->first()
    ) {
        return true;
    }
    return false;
}

if (! function_exists('localizedUrl')) {
    function localizedUrl($locale = null)
    {
        // If no locale is provided, determine the fallback locale
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        // Define the base URL
        $baseUrl = rtrim(url('/'), '/');

        // Get the current URL segments
        $segments = request()->segments();

        // Check if the first segment is a locale
        if (!empty($segments) && in_array($segments[0], array_keys(config('languages')))) {
            // Remove the existing locale segment
            array_shift($segments);
        }

        // Add the new locale segment
        array_unshift($segments, $locale);

        // Rebuild the URL with the new locale segment
        $localizedUrl = $baseUrl . '/' . implode('/', $segments);

        // Handle the case where the URL is only the base URL
        if ($localizedUrl === $baseUrl . '/' . $locale) {
            return $baseUrl . '/' . $locale;
        }

        return $localizedUrl;
    }
}


function getProductNameSpace(Product $product)
{
    switch ($product->pipeline_name) {
        case 'sparkfore':
            return 'sparkfore';
        case config('sparkfore.free_installation.package_pipeline_name'):
            return 'shared';
        default:
            return $product->pipeline_name;
    }
}



