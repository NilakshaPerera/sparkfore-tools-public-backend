<?php

namespace App\Domain\Repositories\Hosting;

use App\Domain\Models\BasePackage;
use App\Domain\Models\Customer;
use App\Domain\Models\Hosting;
use App\Domain\Models\HostingAvailableCustomer;
use App\Domain\Models\HostingCloudSetting;
use App\Domain\Models\HostingOnPremSetting;
use App\Domain\Models\HostingProvider;
use App\Domain\Models\HostingType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class HostingRepository implements HostingRepositoryInterfce
{
    public function __construct(
        private Hosting $hosting,
        private HostingType $hostingType,
        private HostingProvider $hostingProvider,
        private BasePackage $basePackage,
        private HostingCloudSetting $hostingCloudSetting,
        private HostingOnPremSetting $hostingOnPremSetting,
        private HostingAvailableCustomer $hostingAvailableCustomer
    ) {}

    /**
     * @param $params
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function listHosting($params)
    {
        // Modal
        $hosting = $this->hosting;

        if (!empty($with = ($params['with'] ?? false))) {
            $hosting = $hosting->with($with);
        }

        if (!empty($filter = ($params['filter'] ?? false))) {
            $hosting = $hosting->where(function ($q) use ($filter) {
                $q->where('name', 'ilike', "%$filter%")
                    ->orWhere('description', 'ilike', "%$filter%");
            });
        }

        if (($params['sort_by'] ?? false) && ($params['sort_desc'] ?? false)) {
            $hosting =  $hosting->orderBy($params['sort_by'], $params['sort_desc']);
        } else {
            $hosting = $hosting->orderBy('id', 'desc');
        }

        if (!empty($page = ($params['page'] ?? false)) && !empty($perPage = ($params['per_page'] ?? false))) {
            return $hosting->paginate($perPage, ['*'], 'page', $page);
        }

        return $hosting->get();
    }

    /**
     * @return mixed
     */
    public function getCustomers()
    {
        return Customer::where('status', 'active')->get()->map(
            fn($customer) =>
            collect($customer->toArray())
                ->only('id', 'name')
                ->all()
        );
    }

    /**
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getHostingTypes()
    {
        return $this->hostingType->all()->transform(
            fn($hostingType) =>
            [
                'text' => $hostingType['name'],
                'value' => $hostingType['id']
            ]
        );
    }

    /**
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getHostingProviders()
    {
        return $this->hostingProvider->all()->transform(
            fn($hostingType) =>
            [
                'text' => $hostingType['name'],
                'value' => $hostingType['id'],
                'active' => $hostingType['active']
            ]
        );
    }

    /**
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getHostingBasePackages()
    {
        return $this->basePackage->all()->transform(
            fn($hostingType) =>
            [
                'text' => $hostingType['name'],
                'value' => $hostingType['id']
            ]
        );
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getHostingTypeById($id)
    {
        return $this->hostingType->find($id);
    }

    /**
     * @param $param
     * @return int
     */
    public function storeHosting($param)
    {
        // Using DB facade since it's easy to insert and get the ID
        // This will have no impact on the software principles we use
        return DB::table('hostings')->insertGetId($param);
    }

    /**
     * @param $param
     * @return int
     */
    public function updateHosting($param)
    {
        // Using DB facade since it's easy to insert and get the ID
        // This will have no impact on the software principles we use
        return DB::table('hostings')->where('id', $param['id'])->update($param);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function storeHostingCloudSettings($params)
    {
        return $this->hostingCloudSetting::insertGetId($params);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function updateHostingCloudSettings($params)
    {
        return $this->hostingCloudSetting::where('hosting_id', $params['hosting_id'])->update($params);
    }

    /**
     * @param $params
     * @return bool
     */
    public function storeHostingOnPremSettings($params)
    {
        return $this->hostingOnPremSetting::insertGetId($params);
    }

    /**
     * @param $params
     * @return bool
     */
    public function updateHostingOnPremSettings($params)
    {
        return $this->hostingOnPremSetting::where('hosting_id', $params['hosting_id'])->update($params);
    }

    /**
     * @param $params
     * @return bool
     */
    public function storeHostingAvailableCustomers($params)
    {
        return $this->hostingAvailableCustomer::insertGetId($params);
    }

    public function purgeHostingAvailableCustomers($hostingId)
    {
        return $this->hostingAvailableCustomer::where('hosting_id', $hostingId)->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->hosting->with(['hostingCustomers', 'hostingCloudSettings'])->find($id)->toArray();
    }
}
