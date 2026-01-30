<?php

namespace App\Infrastructure\Console\Commands;

use App\Domain\Services\Product\ProductServiceInterface;
use Illuminate\Console\Command;
use Log;

class RunSchedulerBuilds extends Command
{

    protected $productServiceInterface;

    public function __construct(ProductServiceInterface $productServiceInterface)
    {
        parent::__construct();
        $this->productServiceInterface = $productServiceInterface;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-scheduled-builds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the products dev, staging and prod build cron and trigger the re-build';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Starting run schedule builds command");
        $this->productServiceInterface->runScheduledBuilds();
        Log::info("Completed run schedule builds command");
    }
}
