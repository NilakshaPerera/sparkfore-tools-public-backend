<?php

namespace App\Domain\Services\Product;

use App\Domain\Models\Product;

interface ProductServiceInterface
{
    public function storeProductFromGit($params);

    public function updateProductEnvironmentPlugins($id, $params);

    public function getCustomerProduct($productId, $customerId);

    public function runScheduledBuilds();

    public function deleteProductModule(Product $product, $module);

    public function getChangeHistory($product, $env, $page, $perPage, $sortBy, $sortDesc);
}
