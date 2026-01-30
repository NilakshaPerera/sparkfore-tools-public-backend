<?php

namespace App\Infrastructure\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        if(env('APP_ENV') != 'local') {
            URL::forceScheme('https');
        }

        Factory::guessFactoryNamesUsing(function ($name) {
            return (string) '\Database\Factories\\'.
                (class_basename($name)).
                'Factory';
        });
    }
}
