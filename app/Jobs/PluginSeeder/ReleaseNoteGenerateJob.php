<?php

namespace App\Jobs\PluginSeeder;

use App\Domain\DataClasses\ReleaseNote\GenerateReleaseNoteDTO;
use App\Domain\Services\OpenAI\SparkforeOpenAIInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domain\Traits\MoodleTrait;
use Log;

class ReleaseNoteGenerateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MoodleTrait;

    protected $generateReleaseNoteDTO;

    /**
     * Create a new job instance.
     */
    public function __construct(GenerateReleaseNoteDTO $generateReleaseNoteDTO)
    {
        $this->generateReleaseNoteDTO = $generateReleaseNoteDTO;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sparkforeOpenAI = app(SparkforeOpenAIInterface::class);
        $sparkforeOpenAI->generateReleaseNote($this->generateReleaseNoteDTO);
    }


}
