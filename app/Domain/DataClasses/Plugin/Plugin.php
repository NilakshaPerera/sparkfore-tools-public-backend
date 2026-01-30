<?php

namespace App\Domain\DataClasses\Plugin;

use App\Domain\DataClasses\AppDataClass;

class Plugin extends AppDataClass
{
    protected $id;
    protected $name;
    protected $gitUrl;
    protected $githubUrl;
    protected $description;
    protected $gitVersionTypeId; // Renamed from gitersion_type_id
    protected $accessibilityType; // Renamed from accessibility_type
    protected $accessToken; // Renamed from access_token
    protected $availability;
    protected $isMirrored; // Renamed from is_mirrored

    protected $createdBy;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Plugin
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsMirrored()
    {
        return $this->isMirrored;
    }

    /**
     * @param mixed $isMirrored
     * @return Plugin
     */
    public function setIsMirrored($isMirrored)
    {
        $this->isMirrored = $isMirrored;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Plugin
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return Plugin
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGitUrl()
    {
        return $this->gitUrl;
    }

    /**
     * @param mixed $gitUrl
     * @return Plugin
     */
    public function setGitUrl($gitUrl)
    {
        $this->gitUrl = $gitUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGitHubUrl()
    {
        return $this->githubUrl;
    }

    /**
     * @param mixed $githubUrl
     * @return Plugin
     */
    public function setGitHubUrl($githubUrl)
    {
        $this->githubUrl = $githubUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGitVersionTypeId()
    {
        return $this->gitVersionTypeId;
    }

    /**
     * @param mixed $gitVersionTypeId
     * @return Plugin
     */
    public function setGitVersionTypeId($gitVersionTypeId)
    {
        $this->gitVersionTypeId = $gitVersionTypeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @param mixed $availability
     * @return Plugin
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccessibilityType()
    {
        return $this->accessibilityType;
    }

    /**
     * @param mixed $accessibilityType
     * @return Plugin
     */
    public function setAccessibilityType($accessibilityType)
    {
        $this->accessibilityType = $accessibilityType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     * @return Plugin
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }
}
