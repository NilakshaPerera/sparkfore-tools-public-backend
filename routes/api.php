<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:api')->get('/user', [\App\Application\Controllers\UserController::class, 'getAuthUser']);

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::middleware(['permission:USERS_PROFILE_READ'])->get('/profile', [\App\Application\Controllers\UserController::class, 'authUser']);

    Route::prefix('accesscontrol')->group(function () {
        Route::post('updaterolepermission', [\App\Application\Controllers\AccessControlController::class, 'updateRolePermission']);
        Route::post('getrolepermissions', [\App\Application\Controllers\AccessControlController::class, 'getRolePermissions']);
        Route::post('setrolepermissions', [\App\Application\Controllers\AccessControlController::class, 'setRolePermissions']);

        Route::get('readpermission', [\App\Application\Controllers\AccessControlController::class, 'readPermission']);
        Route::post('createpermission', [\App\Application\Controllers\AccessControlController::class, 'createPermission']);
        Route::post('updatepermission/{permission}', [\App\Application\Controllers\AccessControlController::class, 'updatePermission']);

        Route::get('readmodule', [\App\Application\Controllers\AccessControlController::class, 'readModule']);
        Route::post('createmodule', [\App\Application\Controllers\AccessControlController::class, 'createModule']);
        Route::post('updatemodule/{module}', [\App\Application\Controllers\AccessControlController::class, 'updateModule']);

        Route::get('readrole', [\App\Application\Controllers\AccessControlController::class, 'readRole']);

    });

    Route::prefix('log')->group(function () {
        Route::get('list', [\App\Application\Controllers\LogController::class, 'listLogs']);
    });

    Route::prefix('remote-job')->group(function () {
        Route::get('product-build/{product}', [\App\Application\Controllers\RemoteAdminController::class, 'productBuildLogs']);
        Route::get('{remoteJob}/{fileType}/file-download', [\App\Application\Controllers\RemoteAdminController::class, 'downloadRemoteJobFile']);
    });

    Route::prefix('customer')->group(function () {
        Route::middleware(["permission:CUSTOMERS_CUSTOMERS_CREATE"])->post('store', [\App\Application\Controllers\CustomerController::class, 'storeCustomer']);
        Route::middleware(["permission:CUSTOMERS_CUSTOMERS_READ"])->get('list', [\App\Application\Controllers\CustomerController::class, 'listCustomers']);
        Route::middleware(["permission:CUSTOMERS_CUSTOMERS_UPDATE"])->post('update/{id}', [\App\Application\Controllers\CustomerController::class, 'updateCustomer']);
        Route::middleware(["permission:CUSTOMERS_CUSTOMERS_UPDATE"])->get('edit/{id}', [\App\Application\Controllers\CustomerController::class, 'edit']);

        Route::prefix('product')->group(function () {
            Route::middleware(["permission:CUSTOMERS_CUSTOMER_SELF_READ,CUSTOMERS_CUSTOMERS_READ"])->get('list', [\App\Application\Controllers\CustomerController::class, 'listCustomerProducts']);
            Route::get('form_create', [\App\Application\Controllers\ProductController::class, 'getCustomerProductFormCreate']);
            Route::get('form_edit', [\App\Application\Controllers\ProductController::class, 'getCustomerProduct']);
            Route::post('edit', [\App\Application\Controllers\ProductController::class, 'editCustomerProduct']);
        });
    });

    Route::prefix('product')->group(function () {
        Route::middleware(["permission:PRODUCTS_PRODUCTS_CREATE"])->post('store', [\App\Application\Controllers\ProductController::class, 'storeProduct']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_READ"])->get('list', [\App\Application\Controllers\ProductController::class, 'listProducts']);
        Route::get('availability', [\App\Application\Controllers\ProductController::class, 'isProductNameAvailable']);
        Route::get('test', [\App\Application\Controllers\ProductController::class, 'test']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_CREATE"])->get('form_create', [\App\Application\Controllers\ProductController::class, 'getFormCreate']);
        Route::post('{product}/build', [\App\Application\Controllers\ProductController::class, 'triggerBuildPipelineRequest']);
        Route::post('{product}/sync-plugins', [\App\Application\Controllers\ProductController::class, 'syncPluginsFromGit']);

        Route::middleware(["permission:PRODUCTS_PRODUCTS_CREATE"])->delete('{product}', [\App\Application\Controllers\ProductController::class, 'deleteProductModule']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_READ"])->get('change-history/{product}', [\App\Application\Controllers\ProductController::class, 'getChangeHistory']);

        Route::prefix( 'setting')->group(function () {
            Route::middleware(["permission:PRODUCTS_PRODUCTS_READ,PRODUCTS_PRODUCTS_UPDATE"])->get('form_create/{env}/{id}', [\App\Application\Controllers\ProductController::class, 'getFormSettingsCreate']);
            Route::middleware(["permission:PRODUCTS_PRODUCTS_READ,PRODUCTS_PRODUCTS_UPDATE"])->get('form_data/{env}/{id}', [\App\Application\Controllers\ProductController::class, 'getFormSettingsData']);
            Route::middleware(["permission:PRODUCTS_PRODUCTS_READ,PRODUCTS_PRODUCTS_UPDATE"])->get('form-data/plugins', [\App\Application\Controllers\ProductController::class, 'getFormSettingsPluginsData']);
            Route::middleware(["permission:PRODUCTS_PRODUCTS_UPDATE"])->post('update/{id}/plugins', [\App\Application\Controllers\ProductController::class, 'updateProductEnvironmentPlugins']);
            Route::middleware(["permission:PRODUCTS_PRODUCTS_UPDATE"])->post('update/{id}', [\App\Application\Controllers\ProductController::class, 'updateProduct']);
        });
    });

    Route::prefix('hosting')->group(function () {
        Route::get('list', [\App\Application\Controllers\HostingController::class, 'listHosting']);
        Route::get('form_create', [\App\Application\Controllers\HostingController::class, 'getFormCreate']);
        Route::post('store', [\App\Application\Controllers\HostingController::class, 'storeHosting']);
        Route::post('update/{id}', [\App\Application\Controllers\HostingController::class, 'updateHosting']);
        Route::get('edit/{id}', [\App\Application\Controllers\HostingController::class, 'edit']);
    });

    Route::prefix('software')->group(function () {
        Route::get('list', [\App\Application\Controllers\SoftwareController::class, 'listSoftware']);
        Route::get('form_create', [\App\Application\Controllers\SoftwareController::class, 'getFormCreate']);
        Route::get('versions', [\App\Application\Controllers\SoftwareController::class, 'getSoftwareVersions']);
        Route::post('store', [\App\Application\Controllers\SoftwareController::class, 'storeSoftware']);
        Route::post('update/{id}', [\App\Application\Controllers\SoftwareController::class, 'updateSoftware']);
        Route::get('edit/{id}', [\App\Application\Controllers\SoftwareController::class, 'edit']);
        Route::get('sync', [\App\Application\Controllers\SoftwareController::class, 'syncSoftwares']);
    });

    Route::prefix('installation')->group(function () {
        Route::get('form_create', [\App\Application\Controllers\InstallationController::class, 'getFormCreate']);
        Route::post('store', [\App\Application\Controllers\InstallationController::class, 'storeInstallation']);
        Route::get('list', [\App\Application\Controllers\InstallationController::class, 'listInstallations']);
        Route::get('list/status/{status}', [\App\Application\Controllers\InstallationController::class, 'listInstallationsByStatus']);
        Route::get('validate', [\App\Application\Controllers\InstallationController::class, 'validateDomain']);
        Route::delete('delete', [\App\Application\Controllers\InstallationController::class, 'deleteInstallations']);
        Route::middleware(["permission:INSTALLATIONS_INSTALLATIONS_READ"])->get('{id}', [\App\Application\Controllers\InstallationController::class, 'getInstallation']);
        Route::get('manage/{id}', [\App\Application\Controllers\InstallationController::class, 'getInstallationForManage']);
        Route::middleware(["permission:INSTALLATIONS_INSTALLATIONS_UPDATE"])->put('{id}', [\App\Application\Controllers\InstallationController::class, 'editInstallation']);
        Route::post('{id}', [\App\Application\Controllers\InstallationController::class, 'buildInstallation']);
    });

    Route::prefix('plugin')->group(function () {
        Route::middleware(["permission:PRODUCTS_PRODUCTS_PLUGINS_READ"])->get('list', [\App\Application\Controllers\PluginController::class, 'listPlugins']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_PLUGINS_CREATE"])->get('form_create', [\App\Application\Controllers\PluginController::class, 'getFormCreate']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_PLUGINS_CREATE"])->post('store', [\App\Application\Controllers\PluginController::class, 'storePlugin']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_PLUGINS_UPDATE"])->get('edit/{id}', [\App\Application\Controllers\PluginController::class, 'edit']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_PLUGINS_CREATE,PRODUCTS_PRODUCTS_PLUGINS_UPDATE"])->get('get_git_plugin_name', [\App\Application\Controllers\PluginController::class, 'getGitPluginName']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_PLUGINS_UPDATE"])->post('update/{id}', [\App\Application\Controllers\PluginController::class, 'updatePlugin']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_PLUGINS_CREATE,PRODUCTS_PRODUCTS_PLUGINS_UPDATE"])->get('get_software_plugins', [\App\Application\Controllers\PluginController::class, 'getSoftwarePlugins']);
        Route::middleware(["permission:PRODUCTS_PRODUCTS_PLUGINS_UPDATE"])->put('sync/{plugin}', [\App\Application\Controllers\PluginController::class, 'syncPlugin']);
        Route::get('{id}/versions', [\App\Application\Controllers\PluginController::class, 'getPluginVersions']);
    });

    Route::prefix('user')->group(function () {
        Route::get('readaccounttypes', [\App\Application\Controllers\UserController::class, 'readAccountTypes']);
        Route::get('readcompanies', [\App\Application\Controllers\UserController::class, 'readCompanies']);
        Route::middleware(["permission:USERS_USERS_READ"])->get('readuser', [\App\Application\Controllers\UserController::class, 'readUser']);
        Route::middleware(["permission:USERS_USERS_CREATE"])->post('createuser', [\App\Application\Controllers\UserController::class, 'createUser']);
        Route::middleware(["permission:USERS_USERS_UPDATE"])->post('updateuser/{user}', [\App\Application\Controllers\UserController::class, 'updateUser'])->middleware('throttle:10,1'); // added throttling to stop password guessing from brute force attack
        Route::middleware(['permission:USERS_PROFILE_UPDATE'])->post('profile', [\App\Application\Controllers\UserController::class, 'updateProfile'])->middleware('throttle:10,1'); // added throttling to stop password guessing from brute force attack
    });

    Route::prefix('remote')->group(function () {
        Route::post('restart', [\App\Application\Controllers\RemoteAdminController::class, 'restartServer']);
    });

    // Test Functions
    Route::prefix('test')->group(function () {
        Route::get('sync_installation', [\App\Application\Controllers\TestController::class, 'testInstallationSync']);
        Route::get('soketi', [\App\Application\Controllers\TestController::class, 'testPusher']);
        Route::get('logs', [\App\Application\Controllers\TestController::class, 'testLogs']);
    });

});



// Route::middleware(['client'])->prefix('v1')->group(function () {
Route::prefix('v1')->group(function () {
    Route::prefix('plugin')->group(function () {
        Route::get('by-git-url/{gitUrl}', [\App\Application\Controllers\PluginController::class,'getPluginByURL']);
    });
    Route::prefix('webhook')->group(function () {
        Route::post('ansible/callback', [\App\Application\Controllers\AlertProcessorController::class, 'ansibleCallback'])
        ->middleware('throttle:50,1')->name('webhook.ansible.callback');
        Route::post('open-ai/release-note/complete', [\App\Application\Controllers\AlertProcessorController::class, 'openAICallback'])->middleware('throttle:50,1');
    });
});
