<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::group([
    'prefix' => '{locale?}', 'where' => ['locale' => 'en|sv'],
    'middleware' => 'localization'
], function () {

    Route::get('/', [\App\Application\Controllers\Public\PublicController::class, 'viewStart'])->name("home");
    Route::get('/start', [\App\Application\Controllers\Public\PublicController::class, 'viewStart'])->name('public.start');
    Route::get('/pricing', [\App\Application\Controllers\Public\PublicController::class, 'viewPricing'])->name('public.pricing');
    Route::post('/contact-us', [\App\Application\Controllers\Public\PublicController::class, 'submitContactUs'])->name('public.contact.submit');

    Route::get('/instance/create', [\App\Application\Controllers\Public\NewInstallationController::class, 'viewPublicCreateInstallation'])->name('public.create-installation.view');
    Route::post('/instance/create', [\App\Application\Controllers\Public\NewInstallationController::class, 'publicCreateInstallation'])->name('public.create-installation');
    Route::get('/instance/in-progress', [\App\Application\Controllers\Public\NewInstallationController::class, 'viewPublicCreateInstallationInProgress']);
});

Route::fallback(function () {
    return redirect(localizedUrl('en'));
});
