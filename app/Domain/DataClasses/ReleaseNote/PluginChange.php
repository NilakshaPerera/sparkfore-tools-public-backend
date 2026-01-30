<?php
namespace App\Domain\DataClasses\ReleaseNote;

class PluginChange
{
    protected $gitURL;
    protected $pluginName;
    protected $preVersion;
    protected $newVersion;

    // Getter and Setter for gitURL
    public function getGitURL()
    {
        return $this->gitURL;
    }

    public function setGitURL($gitURL)
    {
        $this->gitURL = $gitURL;
    }

    // Getter and Setter for pluginName
    public function getPluginName()
    {
        return $this->pluginName;
    }

    public function setPluginName($pluginName)
    {
        $this->pluginName = $pluginName;
    }

    // Getter and Setter for preVersion
    public function getPreVersion()
    {
        return $this->preVersion;
    }

    public function setPreVersion($preVersion)
    {
        $this->preVersion = $preVersion;
    }

    // Getter and Setter for newVersion
    public function getNewVersion()
    {
        return $this->newVersion;
    }

    public function setNewVersion($newVersion)
    {
        $this->newVersion = $newVersion;
    }
}
