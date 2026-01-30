<?php

namespace App\Domain\DataClasses\Plugin;

use App\Domain\DataClasses\AppDataClass;

class PluginDescriptionDto extends AppDataClass
{
    protected $pluginId;

    protected $githubUrl;

    public function __construct(string $pluginId, string $githubUrl)
    {
        $this->pluginId = $pluginId;
        $this->githubUrl = $githubUrl;
    }

    public function getPluginId(): string
    {
        return $this->pluginId;
    }
    public function getGithubUrl(): string
    {
        return $this->githubUrl;
    }

    public function setPluginId(string $pluginId): void
    {
        $this->pluginId = $pluginId;
    }

    public function setGithubUrl(string $githubUrl): void
    {
        $this->githubUrl = $githubUrl;
    }
}
