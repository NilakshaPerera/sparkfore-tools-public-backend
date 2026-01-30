<?php

namespace App\Domain\Repositories\Product;

interface ProductRepositoryInterface
{
    public function getSoftwareByProductAndEnvironment($productId, $environment);

    public function getCustomers();

    public function getMaintainers();

    public function getProduct($id);

    public function getProductCustomers($id);

    public function getCustomerProductFormCreate();

    public function listProducts($params, $customerId=null);

    public function storeProduct($params);

    public function storeProductPlugins($params);

    public function storeProductSoftwares($params);

    public function updateProductSoftware($id, $params);

    public function updateProductSoftwareByProductIdAndEnvironment($productid, $environment, $params);

    public function getProductPluginVersions($id);

    public function getProductPluginsByEnvironment($id, $environment);

    public function storeProductCustomers($params);

    public function updateProduct($params, $productId);

    public function deleteProductPluginsByEnvironment($productId, $env);

    public function getProductByPipelineName($pipelineName);

    public function updateCustomerProduct($params);

    public function getCustomerProduct($customerId, $productId, $params);

    public function getProductPluginVersionsByVersion($id, $version);

    public function getProductPluginsByEnvironmentAndRequiredVersion($id, $env, $requiredVersionId);

    public function getChangeHistory($product, $env, $page, $perPage, $sortBy, $sortDesc);
}
