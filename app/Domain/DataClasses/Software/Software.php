<?php

namespace App\Domain\DataClasses\Software;

use App\Domain\DataClasses\AppDataClass;
use App\Domain\Models\SoftwareSlug;
class Software extends AppDataClass
{
    protected $id;
    protected $name;
    protected $gitUrl;
    protected $gitVersionTypeId;
    protected $versionSupported;
    protected $slug;

    protected $software_slug;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Software
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSoftwareSlug()
    {
        return $this->software_slug;
    }

    public function setSoftwareSlug($software_slug)
    {
        $this->software_slug = $software_slug;
        return $this;
    }

    public function setSoftwareSlugByValue($software_slug)
    {
        // get id from SoftwareSlug model
        $software_slug = SoftwareSlug::where('value', $software_slug)->first();
        if ($software_slug) {
            $software_slug = $software_slug->id;
        } else {
            $software_slug = null;
        }
        $this->software_slug = $software_slug;
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
     * @return Software
     */
    public function setGitUrl($gitUrl)
    {
        $this->gitUrl = $gitUrl;
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
     * @return Software
     */
    public function setGitVersionTypeId($gitVersionTypeId)
    {
        $this->gitVersionTypeId = $gitVersionTypeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Software
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
    */
    public function getVersionSupported()
    {
        return $this->versionSupported;
    }

    /**
     * @param mixed $name
     * @return Software
     */
    public function setVersionSupported($versionSupported)
    {
        $this->versionSupported = $versionSupported;
        return $this;
    }
}
