<?php

namespace App\Domain\Repositories\Product;

use App\Domain\Models\Customer;
use App\Domain\Models\PipelineMaintainer;
use App\Domain\Models\Plugin;
use App\Domain\Models\ProductAvailableCustomer;
use App\Domain\Models\Product;
use App\Domain\Models\ProductHasPlugin;
use App\Domain\Models\ProductHasSoftware;
use App\Domain\Models\PluginVersion;
use App\Domain\Models\Setting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Domain\Models\CustomerProduct;
use App\Domain\Models\ProductChangeHistory;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        protected Product $product,
        protected ProductHasPlugin $productPlugin,
        protected ProductHasSoftware $productSoftware,
        protected ProductAvailableCustomer $productAvailableCustomer,
        protected Plugin $plugin
    ) {
    }

    /**
     * @return Collection
     */
    public function getSoftwareByProductAndEnvironment($productId, $environment)
    {
        return $this->productSoftware
            ->select(
                'product_has_software.id as id',
                'product_has_software.software_id as software_id',
                'product_has_software.supported_version as supported_version',
                'product_has_software.supported_version_type as supported_version_type',
                'product_has_software.environment as environment',
                'softwares.name as software_name'
            )
            ->join('softwares', 'product_has_software.software_id', '=', 'softwares.id')
            ->where('product_has_software.product_id', $productId)
            ->where('product_has_software.environment', $environment)
            ->get();
    }


    /**
     * @return mixed
     */
    public function getProduct($id)
    {
        return $this->product->select([
            'id',
            'pipeline_name',
            'git_url',
            'pipeline_maintainer_id',
            'release_notes',
            'pipeline_build_status',
            'last_build',
            'development_scheduled_build',
            'staging_scheduled_build',
            'production_scheduled_build',
            'availability',
            'created_at',
            'updated_at'
        ])
            ->where('id', $id)
            ->first();
    }

    /**
     * @return mixed
     */
    public function getProductCustomers($id)
    {
        return Customer::select('customers.id', 'customers.name', 'customers.slugified_name')
            ->join('product_available_customers', 'customers.id', '=', 'product_available_customers.customer_id')
            ->where('product_available_customers.product_id', $id)
            ->get();
    }

    /**
     * @return mixed
     */
    public function getProductPluginsByEnvironment($id, $environment)
    {
        return $this->plugin->select(
            'plugins.name',
            'plugins.id',
            'product_has_plugins.selected_version',
            'product_has_plugins.selected_version_type',
            'plugins.git_url'
        )
            ->join('product_has_plugins', 'plugins.id', '=', 'product_has_plugins.plugin_id')
            ->where('product_has_plugins.product_id', $id)
            ->where('product_has_plugins.environment', $environment)
            ->get();
    }

    public function getProductPluginsByEnvironmentAndRequiredVersion($id, $environment, $requiredVersionId)
    {
        return Plugin::select(['name', 'id', 'git_url'])
            ->whereHas('productHasPlugins', function (Builder $query) use ($id, $environment) {
                $query->select(['selected_version', 'selected_version_type'])
                    ->where('product_id', $id)
                    ->where('environment', $environment);
            })
            ->whereHas('pluginVersions', function (Builder $query) use ($requiredVersionId) {
                $query->where(function ($query) use ($requiredVersionId) {
                    $query->where('required_version_id', '')
                        ->orWhereRaw(
                            'NULLIF(SUBSTRING(required_version_id FROM 1 FOR 10), \'\')::double precision <= ?',
                            [intval($requiredVersionId)]
                        );
                });
            })
            ->get();
    }

    /**
     * @return mixed
     */
    public function getProductPluginVersionsByVersion($id, $version)
    {
        return PluginVersion::select('plugin_versions.component')
            ->where('plugin_versions.plugin_id', $id)
            ->where('plugin_versions.version_name', $version)
            ->first();
    }
    /**
     * @return mixed
     */
    public function getProductPluginVersions($id)
    {
        return $this->plugin->select(
            'plugins.id as plugin_id',
            'plugins.name as plugin_name',
            'product_has_plugin_versions.selected_version as version'
        )
            ->join(
                'product_has_plugins',
                'plugins.id',
                '=',
                'product_has_plugins.plugin_id'
                )
            ->join(
                'product_has_plugin_versions',
                'product_has_plugins.id',
                '=',
                'product_has_plugin_versions.product_has_plugin_id'
            )
            ->where('product_has_plugins.product_id', $id)
            ->groupBy('plugins.id', 'plugins.name', 'product_has_plugin_versions.selected_version')
            ->get();
    }


    /**
     * @return mixed
     */
    public function getCustomers()
    {
        return Customer::select(['id as value', 'name as label'])
            ->where('status', 'active')
            ->get();
    }

    /**
     * @return mixed
     */
    public function updateCustomerProduct($params)
    {

        return CustomerProduct::where('id', $params['id'])
            ->update($params);
    }

    /**
     * @return Collection
     */
    public function getMaintainers()
    {
        return PipelineMaintainer::all();
    }

    /**
     * @param $params
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function listProducts($params, $customerId = null)
    {
        // Modal
        $product = $this->product;

        if (!empty($with = ($params['with'] ?? false))) {
            $product = $product->with($with);
        }

        if (!empty($filter = ($params['filter'] ?? false))) {
            $product = $product->where(function ($q) use ($filter) {
                $q->where('pipeline_name', 'like', "%" . $filter . "%");
            });
        }

        if (($params['sort_by'] ?? false) && ($params['sort_desc'] ?? false)) {
            $product = $product->orderBy($params['sort_by'], $params['sort_desc']);
        } else {
            $product = $product->orderBy('id', 'desc');
        }

        if ($customerId) {
            $product = $product->whereHas('productCustomer', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            });
        }

        if (!empty($page = ($params['page'] ?? false)) && !empty($perPage = ($params['per_page'] ?? false))) {
            return $product->paginate($perPage, ['*'], 'page', $page);
        }



        return $product->get();
    }

    /**
     * @return array
     */
    public function getCustomerProductFormCreate()
    {
        $setting = Setting::where('key', SETTINGS_PRODUCT_MAINTENANCE_COST)->first();

        return [
            'maintenance_cost' => $setting->value
        ];
    }

    /**
     * @param $params
     * @return mixed
     */
    public function storeProductPlugins($plugins)
    {
        if (empty($plugins)) {
            return;
        }

        $this->productPlugin::insert($plugins);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function storeProductSoftwares($softwares)
    {
        if (empty($softwares)) {
            return;
        }

        $this->productSoftware::insert($softwares);
    }

    public function updateProductSoftware($id, $params)
    {
        return $this->productSoftware
            ->where('id', $id)
            ->update($params);
    }

    public function updateProductSoftwareByProductIdAndEnvironment($productid, $environment, $params)
    {
        return $this->productSoftware
            ->where('product_id', $productid)
            ->where('environment', $environment)
            ->update($params);
    }

    public function deleteProductPluginsByEnvironment($productId, $env)
    {

        $this->productPlugin
            ->where('product_id', $productId)
            ->where('environment', $env)
            ->delete();
    }

    /**
     * @param $params
     * @return mixed
     */
    public function storeProductCustomers($params)
    {
        $this->productAvailableCustomer::insert($params);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function storeProduct($params)
    {
        return $this->product::insertGetId($params);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function updateProduct($params, $productId)
    {
        return $this->product
            ->where('id', $productId)
            ->update($params);
    }

    /**
     * Get a product by pipeline name.
     *
     * @param string $pipelineName
     * @return mixed
     */
    public function getProductByPipelineName($pipelineName)
    {
        return $this->product->select(['id', 'pipeline_name'])
            ->whereRaw('LOWER(pipeline_name) = ?', [strtolower($pipelineName)])
            ->first();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getCustomerProduct($customerId, $productId, $params)
    {
        return CustomerProduct::select(['id', 'label', 'include_maintenance', 'product_id'])
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->with($params['with'])
            ->first();
    }

    public function getChangeHistory($product, $env, $page, $perPage, $sortBy, $sortDesc)
    {
        if ($env == "develop") {
            $env = "dev";
        }
        $query = ProductChangeHistory::with("user:id,f_name,l_name", "product:id,pipeline_name")
            ->where('product_id', $product)
            ->where('branch', $env);

        $totalRecords = $query->count();

        if ($sortBy && $sortDesc) {
            $query = $query->orderBy($sortBy, $sortDesc);
        } else {
            $query = $query->orderBy('updated_at', 'desc');
        }

        if (!empty($page) && !empty($perPage)) {
            return $query->paginate($perPage, ['*'], 'page', $page);
        }

        return [
            'data' => $query->get(),
            'total' => $totalRecords
        ];
    }
}
