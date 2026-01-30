<?php

namespace App\Domain\Repositories\Installation;

use App\Domain\Models\Hosting;
use App\Domain\Models\HostingProvider;
use App\Domain\Models\HostingType;
use App\Domain\Models\Installation;
use App\Domain\Models\CustomerProduct;
use Illuminate\Support\Facades\DB;
use Log;

class InstallationRepository implements InstallationRepositoryInterface
{
    public function __construct(
        public readonly Hosting $hosting,
        public readonly HostingType $hostingType,
        public readonly HostingProvider $hostingProvider,
        public readonly Installation $installation,
        protected CustomerProduct $customerProduct
    ) {
    }

    public function getHostingTypes()
    {
        return $this->hostingType::all();
    }

    public function getHostingProviders()
    {
        return $this->hostingProvider::all();
    }

    public function getHostingPackages($customerId = 0)
    {

        if($customerId > 0){

            return $this->hosting::where('availability', 'public')
            ->orWhereHas('hostingCustomers', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            })
            ->orderBy('id', 'ASC')
            ->get();

        }else{
            return $this->hosting::all();
         }
    }

    public function getInstallation($id)
    {
        $installation = DB::table('installations')
            ->select([
                'installations.id as id',
                'customer_products.customer_id as customer_id',
                'domain_type',
                'url',
                'billing_terms_agreement',
                'general_terms_agreement',
                'include_backup',
                'include_staging_package',
                'hosting_id',
                'hostings.hosting_type_id',
                'hostings.hosting_provider_id',
                'installations.disk_size as disk_size',
                'hostings.disk_size as hosting_disk_size',
                'installation_target_type_id',
                'customer_products.product_id as product_id',
            ])
            ->join('customer_products', 'customer_products.id', '=', 'installations.customer_product_id')
            ->join('hostings', 'hosting_id', '=', 'hostings.id')
            ->where('installations.id', $id);

        return $installation->first();
    }

    public function getInstallationForManage($params)
    {
        $installation = $this->installation
            ->where('id', $params['id']);

        if (!empty($with = ($params['with'] ?? false))) {
            $installation = $installation->with($with);
        }

        return $installation->get();
    }

    public function listInstallations($params, $customerId = null)
    {
        // Tables
        $installation = DB::table('installations')
            ->select([
                'installations.id as id',
                'customer_products.product_id as product_id',
                'installations.url as url',
                'installations.status as status',
                'customers.name as customer',
                'customers.id as customer_id',
                'installations.status as status',
                'installations.status_code as status_code',
                'installations.available_disk_space as available_disk_space',
                'installations.last_build as last_build',
                'installations.state as state',
                'git_version_types.name as branch',
                'installation_target_types.name as target_type',
                'hosting_providers.key as hosting_provider',
                'hosting_types.key as hosting_type',
                'installations.software_version as software_version',
                'hostings.name as hosting_name',
                'products.pipeline_name as pro_pipeline_name',
            ])
            ->join('customer_products', 'customer_products.id', '=', 'installations.customer_product_id')
            ->join('products', 'products.id', '=', 'customer_products.product_id')
            ->join(
                'installation_target_types',
                'installations.installation_target_type_id',
                '=',
                'installation_target_types.id'
            )
            ->leftJoin('product_has_software', 'product_has_software.product_id', '=', 'products.id')
            ->leftJoin('softwares', 'softwares.id', '=', 'product_has_software.software_id')
            ->leftJoin('git_version_types', 'git_version_types.id', '=', 'softwares.git_version_type_id')
            ->leftJoin('customers', 'customers.id', '=', 'customer_products.customer_id')
            ->leftJoin('hostings', 'installations.hosting_id', '=', 'hostings.id')
            ->leftJoin('hosting_providers', 'hosting_providers.id', '=', 'hostings.hosting_provider_id')
            ->leftJoin('hosting_types', 'hosting_types.id', '=', 'hostings.hosting_type_id');

        $installation = $installation->where(function ($q) {
            $q->where('product_has_software.environment', '=', "production");
        });

        if (!empty($filter = ($params['filter'] ?? false))) {
            $installation = $installation->where(function ($q) use ($filter) {
                $q->where('url', 'ilike', "%$filter%")
                    ->orWhere('product_has_software.supported_version', 'ilike', "%$filter%")
                    ->orWhere('products.pipeline_name', 'ilike', "%$filter%")
                    ->orWhere('installations.status', 'ilike', "%$filter%")
                    ->orWhere('installations.url', 'ilike', "%$filter%")
                    ->orWhere('installation_target_types.name', 'ilike', "%$filter%")
                    ->orWhere(function ($q) use ($filter) {
                        $q->where('customers.name', 'ilike', "%" . $filter . "%");
                    });
            });
        }

        if ($customerId) {
            $installation->where('customer_products.customer_id', '=', $customerId);
        }

        if (($params['sort_by'] ?? false) && ($params['sort_desc'] ?? false)) {
            $sortBy = $this->getSortColumn($params['sort_by']);
            $installation = $sortBy ? $installation->orderBy($sortBy, $params['sort_desc']) : $installation;
        } else {
            $installation = $installation->orderBy('id', 'desc');
        }

        if (!empty($page = ($params['page'] ?? false)) && !empty($perPage = ($params['per_page'] ?? false))) {
            return $installation->paginate($perPage, ['*'], 'page', $page);
        }

        return $installation->get();
    }

    public function getSortColumn($column)
    {
        $return = false;

        if (in_array($column, ['id', 'url', 'status', 'last_build'])) {
            $return = 'installations.' . $column;
        } elseif (in_array($column, ['customer'])) {
            $return = 'customers.name';
        } elseif (in_array($column, ['software_version'])) {
            $return = 'softwares.version_supported';
        } elseif (in_array($column, ['hosting_name'])) {
            $return = 'hostings.name';
        } elseif (in_array($column, ['status'])) {
            $return = 'installations.status';
        }

        return $return;
    }

    public function editInstallation($data)
    {
        $id = $data['id'];
        $this->installation->where('id', $id)->update($data);
        return $id;
    }

    public function storeInstallation($data)
    {
        return $this->installation::insertGetId($data);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function deleteInstallations($installationId)
    {
        $this->installation
            ->where('id', $installationId)
            ->delete();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getCustomerProductId($customerId, $productId)
    {
        return $this->customerProduct->select(['id'])
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();
    }

    public function listInstallationsByStatus($status, $customerId = null)
    {
        $q = $this->installation::select(
            "url",
            DB::raw('to_char(updated_at, \'HH24:MI:SS\') as f_updated_at'),
            "status_code"
        )
            ->where("status", $status);

        if ($customerId) {
            $q->whereHas('customerProduct', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            });
        }
        return $q->get();
    }
}
