<?php

namespace App\Domain\DataClasses\ProductPlugin;

use App\Domain\DataClasses\AppDataClass;

class ProductPlugin extends AppDataClass
{
    protected $id;
    protected $productId;
    protected $pluginId;
    protected $selectedVersion;
    protected $environment;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ProductPlugin
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param mixed $product_id
     * @return ProductPlugin
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * @param mixed $plugin_id
     * @return ProductPlugin
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSelectedVersion()
    {
        return $this->selectedVersion;
    }

    /**
     * @param mixed $selected_version
     * @return ProductPlugin
     */
    public function setSelectedVersion($selectedVersion)
    {
        $this->selectedVersion = $selectedVersion;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param mixed $environment
     * @return ProductPlugin
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'plugin_id' => $this->pluginId,
            'selected_version' => $this->selectedVersion,
            'environment' => $this->environment
        ];
    }
}
