<?php

namespace App\Domain\DataClasses\Product;

use App\Domain\Repositories\Product\ProductRepository;
use Illuminate\Support\Facades\Log;

class GenerateProductChangeDTO
{
    private $environment;
    private $saveChangeInDb;
    private $oldPlugins;
    private $oldSoftware;
    private $newDbProductObj;
    private $newPlugins;
    private $newSoftware;
    private $productRepository;


    public function __construct($environment, $oldPlugins, $oldSoftware, $saveChangeInDb = false)
    {
        $this->environment = $environment;
        $this->oldPlugins = $oldPlugins;
        $this->oldSoftware = $oldSoftware;
        $this->saveChangeInDb = $saveChangeInDb;
        $this->productRepository = app(ProductRepository::class);
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getOldPlugins()
    {
        return $this->oldPlugins;
    }


    public function getProductRepository()
    {
        return $this->productRepository;
    }

    public function setNewDbProductObjects($newDbProductObj): void
    {
        $this->newDbProductObj = $newDbProductObj;
        $this->newPlugins = $this->productRepository->getProductPluginsByEnvironment(
            $newDbProductObj->id,
            $this->environment
        )->toArray();

        $productSoftware = $newDbProductObj->productSoftwares()
            ->where("environment", $this->environment)
            ->first();

        $this->newSoftware = [
            "supported_version" => $productSoftware->supported_version,
            "supported_version_type" => $productSoftware->supported_version_type,
            "name" => $productSoftware->software->name,
        ];
    }


    public function getSaveChangeInDb()
    {
        return $this->saveChangeInDb;
    }
    public function getOldSoftware()
    {
        return $this->oldSoftware;
    }

    public function getNewDbProductObj()
    {
        return $this->newDbProductObj;
    }

    public function getNewPlugins()
    {
        return $this->newPlugins;
    }

    public function getNewSoftware()
    {
        return $this->newSoftware;
    }

    public function setEnvironment($environment): void
    {
        $this->environment = $environment;
    }

    public function setSaveChangeInDb($saveChangeInDb): void
    {
        $this->saveChangeInDb = $saveChangeInDb;
    }

    public function setOldPlugins($oldPlugins): void
    {
        $this->oldPlugins = $oldPlugins;
    }

    public function setOldSoftware($oldSoftware): void
    {
        $this->oldSoftware = $oldSoftware;
    }

    public function setNewDbProductObj($newDbProductObj): void
    {
        $this->newDbProductObj = $newDbProductObj;
    }

    public function setNewPlugins($newPlugins): void
    {
        $this->newPlugins = $newPlugins;
    }

    public function setNewSoftware($newSoftware): void
    {
        $this->newSoftware = $newSoftware;
    }

    public function setProductRepository($productRepository): void
    {
        $this->productRepository = $productRepository;
    }
}
