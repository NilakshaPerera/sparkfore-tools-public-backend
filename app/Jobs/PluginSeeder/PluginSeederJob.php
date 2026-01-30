<?php

namespace App\Jobs\PluginSeeder;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domain\Traits\MoodleTrait;
use Illuminate\Support\Facades\Cache;

class PluginSeederJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MoodleTrait;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }

    protected function setPluginSyncCompleteStatus($jobId)
    {
        if (Cache::get($jobId . "-branch") == 'completed' && Cache::get($jobId . "-tag") == 'completed') {
            Cache::put($jobId, 'completed', 3600);
        }
    }



    public function extractSomething($decodedContent, $something)
    {
        $extraction = '';

        // Find the "release" key in the PHP serialized data
        // Match any key-value pair in the format $variable = 'value'
        $lines = explode("\n", $decodedContent);

        $releaseContent = '';

        foreach ($lines as $line) {
            if (strpos($line, $something) !== false) {
                $parts = explode('//', $line);
                $releaseContent = $parts[0];
            }
        }


        $equalsPosition = strpos($releaseContent, '=');



        // If '=' is found, extract the version part
        if ($equalsPosition !== false) {
            $extractionStart = $equalsPosition + 1;
            $extractionString = trim(substr($releaseContent, $extractionStart), " ';\n\r\t");
            $extraction = $extractionString ?: 'Unknown';
        }

        return $extraction;
    }
}
