<?php
namespace App\Domain\Services\OpenAI;

use App\Domain\DataClasses\Plugin\PluginDescriptionDto;
use App\Domain\DataClasses\ReleaseNote\GenerateReleaseNoteDTO;

interface SparkforeOpenAIInterface
{
    function generateReleaseNote(GenerateReleaseNoteDTO $releaseNoteDTO);

    function getPluginDescription(PluginDescriptionDto $pluginDescriptionDto);
}
