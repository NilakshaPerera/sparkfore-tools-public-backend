<?php

namespace App\Domain\Services\OpenAI;

use App\Domain\DataClasses\ReleaseNote\GenerateReleaseNoteDTO;
use App\Domain\Services\OpenAI\SparkforeOpenAIInterface;
use Illuminate\Support\Facades\Http;
use Log;

class SparkforeOpenAI implements SparkforeOpenAIInterface
{
    protected $baseURL;

    public function __construct()
    {
        $this->baseURL = config('sparkfore.sparkfore_open_ai_url_base');
    }


    public function generateReleaseNote(GenerateReleaseNoteDTO $releaseNoteDTO)
    {
        Log::info("Sending release note generation request for build id: " . $releaseNoteDTO->getBuildId());
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseURL . "release-notes/generate/", $releaseNoteDTO->toArray());

        Log::info("Response from OpenAI release note generation: ", [$releaseNoteDTO->toArray(), $response->json()]);
        if ($response->ok()) {
            return $response->json();
        }

    }



    public function getPluginDescription(\App\Domain\DataClasses\Plugin\PluginDescriptionDto $pluginDescriptionDto)
    {
        Log::info("Start getting plugin description for plugin id: " . $pluginDescriptionDto->getPluginId());
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseURL . "plugins/description/", $pluginDescriptionDto->toArray());

        Log::info("Response from OpenAI plugin description: ", [$response->json()]);
        if ($response->ok()) {
            return $response->json()["data"]["description"];
        }

    }



}
