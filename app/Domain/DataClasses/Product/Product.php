<?php

namespace App\Domain\DataClasses\Product;

use App\Domain\DataClasses\AppDataClass;

class Product extends AppDataClass
{
    protected $id;
    protected $pipelineMaintainerId;
    protected $pipelineName;
    protected $releaseNotes;
    protected $pipelineBuildStatus;
    protected $lastBuild;
    protected $developmentScheduledBuild;
    protected $stagingScheduledBuild;
    protected $productionScheduledBuild;
    protected $availability;
    protected $gitUrl;
    protected $legacy;
    protected $legacyProductName;
    protected $createdAt;
    protected $updatedAt;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Product
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPipelineMaintainerId()
    {
        return $this->pipelineMaintainerId;
    }

    /**
     * @param mixed $pipelineMaintainerId
     * @return Product
     */
    public function setPipelineMaintainerId($pipelineMaintainerId)
    {
        $this->pipelineMaintainerId = $pipelineMaintainerId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPipelineName()
    {
        return $this->pipelineName;
    }

    /**
     * @param mixed $pipelineName
     * @return Product
     */
    public function setPipelineName($pipelineName)
    {
        $this->pipelineName = $pipelineName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReleaseNotes()
    {
        return $this->releaseNotes;
    }

    /**
     * @param mixed $releaseNotes
     * @return Product
     */
    public function setReleaseNotes($releaseNotes)
    {
        $this->releaseNotes = $releaseNotes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPipelineBuildStatus()
    {
        return $this->pipelineBuildStatus;
    }

    /**
     * @param mixed $pipelineBuildStatus
     * @return Product
     */
    public function setPipelineBuildStatus($pipelineBuildStatus)
    {
        $this->pipelineBuildStatus = $pipelineBuildStatus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastBuild()
    {
        return $this->lastBuild;
    }

    /**
     * @param mixed $lastBuild
     * @return Product
     */
    public function setLastBuild($lastBuild)
    {
        $this->lastBuild = $lastBuild;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDevelopmentScheduledBuild()
    {
        return $this->developmentScheduledBuild;
    }

    /**
     * @param mixed $developmentScheduledBuild
     * @return Product
     */
    public function setDevelopmentScheduledBuild($developmentScheduledBuild)
    {
        $this->developmentScheduledBuild = $developmentScheduledBuild;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStagingScheduledBuild()
    {
        return $this->stagingScheduledBuild;
    }

    /**
     * @param mixed $stagingScheduledBuild
     * @return Product
     */
    public function setStagingScheduledBuild($stagingScheduledBuild)
    {
        $this->stagingScheduledBuild = $stagingScheduledBuild;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductionScheduledBuild()
    {
        return $this->productionScheduledBuild;
    }

    /**
     * @param mixed $productionScheduledBuild
     * @return Product
     */
    public function setProductionScheduledBuild($productionScheduledBuild)
    {
        $this->productionScheduledBuild = $productionScheduledBuild;
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
     * @return Product
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;
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
     * @param mixed $availability
     * @return Product
     */
    public function setGitUrl($gitUrl)
    {
        $this->gitUrl = $gitUrl;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return Product
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return Product
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getLegacy()
    {
        return $this->legacy;
    }

    public function setLegacy($legacy)
    {
        $this->legacy = $legacy;
        return $this;
    }
    public function setLegacyProductName($legacyProductName)
    {
        $this->legacyProductName = $legacyProductName;
        return $this;
    }
}
