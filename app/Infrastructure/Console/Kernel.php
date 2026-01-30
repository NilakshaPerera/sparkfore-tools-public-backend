<?php

namespace App\Infrastructure\Console;

use App\Domain\Jobs\SetProductBuildStatusJob;
use Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {

        Log::info("************* Scheduler Stopped *************");
        return;
        
        Log::info("************* Scheduler start *************");
        try {
            // Grouping daily commands together
            $schedule->call(function () {
                Artisan::call('db:seed', [
                    '--class' => 'SoftwareTableSeeder',
                    '--force' => true,
                ]);

                Artisan::call('db:seed', [
                    '--class' => 'PluginTableSeeder',
                    '--force' => true,
                ]);

                Artisan::call('app:sync-product-packages');

            })->daily();

            // Grouping and setting hourly commands
            $schedule->call(function () {
                SetProductBuildStatusJob::dispatch()->onQueue('pluginsSync');
            })->hourly();

            // Run the build sync job every minute
            $schedule->exec('php artisan app:run-scheduled-builds')->everyMinute();
        } catch (\Throwable $e) {
            Log::error("Error in schedule kernal: " . $e->getMessage() . " trace: " . $e->getTraceAsString());
        }
        Log::info("************* Scheduler end *************");
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
