<?php
namespace App\Domain\DataClasses\ReleaseNote;

use App\Domain\DataClasses\AppDataClass;

class GenerateReleaseNoteDTO extends AppDataClass
{

    protected $oldMoodleVersion;
    protected $newMoodleVersion;
    protected $buildId;

    protected $pluginsData;
    protected $needSummary;
    protected $registryUrl;
    protected $logsUrl;
    protected $buildTimestamp;
    protected $buildTag;

    public function __construct(
        $oldMoodleVersion,
        $newMoodleVersion,
        $buildId, $pluginsData,
        $needSummary,
        $registryUrl,
        $logsUrl,
        $buildTimestamp
    ) {
        $this->oldMoodleVersion = $oldMoodleVersion;
        $this->newMoodleVersion = $newMoodleVersion;
        $this->buildId = $buildId;
        $this->pluginsData = $pluginsData;
        $this->needSummary = $needSummary;
        $this->registryUrl = $registryUrl;
        $this->logsUrl = $logsUrl;
        $this->buildTimestamp = $buildTimestamp;
    }

    // Getter and Setter for oldMoodleVersion
    public function getOldMoodleVersion()
    {
        return $this->oldMoodleVersion;
    }

    public function setOldMoodleVersion($oldMoodleVersion)
    {
        $this->oldMoodleVersion = $oldMoodleVersion;
    }

    // Getter and Setter for newMoodleVersion
    public function getNewMoodleVersion()
    {
        return $this->newMoodleVersion;
    }

    public function setNewMoodleVersion($newMoodleVersion)
    {
        $this->newMoodleVersion = $newMoodleVersion;
    }

    // Getter and Setter for logsUrl
    public function getLogsUrl()
    {
        return $this->logsUrl;
    }

    public function setLogsUrl($logsUrl)
    {
        $this->logsUrl = $logsUrl;
    }

    // Getter and Setter for buildTimestamp
    public function getBuildTimestamp()
    {
        return $this->buildTimestamp;
    }

    public function setBuildTimestamp($buildTimestamp)
    {
        $this->buildTimestamp = $buildTimestamp;
    }

    // Getter for buildTag
    public function getBuildTag()
    {
        return $this->buildTag;
    }

    // Getter and Setter for buildId
    public function getBuildId()
    {
        return $this->buildId;
    }

    public function setBuildId($buildId)
    {
        $this->buildId = $buildId;
    }

    // Getter and Setter for pluginsData
    public function getPluginsData()
    {
        return $this->pluginsData;
    }

    public function setPluginsData($pluginsData)
    {
        $this->pluginsData = $pluginsData;
    }

    // Getter and Setter for needSummary
    public function getNeedSummary()
    {
        return $this->needSummary;
    }

    public function setNeedSummary($needSummary)
    {
        $this->needSummary = $needSummary;
    }

    // Getter and Setter for registryURL
    public function getRegistryUrl()
    {
        return $this->registryUrl;
    }

    public function setRegistryUrl($registryUrl)
    {
        $this->registryUrl = $registryUrl;
    }

    // Setter for buildTag
    public function setBuildTag($buildTag)
    {
        $this->buildTag = $buildTag;
    }
}
