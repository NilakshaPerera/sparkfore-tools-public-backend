<?php

namespace App\Domain\Services\Hosting;

use App\Domain\Repositories\Hosting\HostingRepositoryInterfce;
use Illuminate\Pagination\LengthAwarePaginator;

class HostingService implements HostingServiceInterface
{
    public function __construct(private HostingRepositoryInterfce $hostingRepository)
    {}

    /**
     * @param $params
     * @return array|LengthAwarePaginator
     */
    public function listHosting($params)
    {
        $params['with'] = [
            'hostingCustomers',
            'hostingType'
        ];

        $paginatedResult = $this->hostingRepository->listHosting($params);

        return ($paginatedResult instanceof LengthAwarePaginator) ? $paginatedResult->through(function ($hosting) {
            $hostingType = $hosting->hostingType->name;
            $hostingName = $hosting->name . ' : ' . $hostingType;
            if ($hosting->hostingType->key == HOSTING_CLOUD) {
                $hostingName .= ' - ' . (
                    $hosting->hostingCloudSettings ?
                    $hosting->hostingCloudSettings->hostingProvider->name : ''
                );
            }
            return [
                'id' => $hosting->id,
                'name' => $hostingName,
                'production_price_month' => $hosting->production_price_month ?? '',
                'availability' => ucfirst($hosting->availability ?? ''),
                'available_for_customer' => $hosting->hostingCustomers ? $hosting->hostingCustomers->pluck('name') : ''
            ];
        }) : [];
    }

    /**
     * @return array
     */
    public function getFormCreate()
    {
        // Customer list get and modify the way form needs
        $customers = $this->hostingRepository->getCustomers()->transform(
            fn($customer) =>
            [
                'label' => $customer['name'],
                'value' => $customer['id']
            ]
        );
        $types = $this->hostingRepository->getHostingTypes();
        $providers = $this->hostingRepository->getHostingProviders();
        $basePackages = $this->hostingRepository->getHostingBasePackages();
        return [
            'customers' => $customers,
            'availability' => [
                ['text' => 'All', 'value' => 'public'],
                ['text' => 'Selected', 'value' => 'private']
            ],
            'types' => $types,
            'providers' => $providers,
            'base_packages' => $basePackages
        ];
    }

    /**
     * @param $params
     * @return void
     */
    public function storeHosting($params)
    {
        $hosting = [
            'name' => $params['name'],
            'production_price_month' => $params['production_price_month'],
            'staging_price_month' => $params['staging_price_month'],
            'yearly_price_increase' => $params['yearly_price_increase'],
            'description' => $params['description'],
            'availability' => $params['availability'],
            'config' => $params['config'] ?? '[]',
            'hosting_type_id' => $params['hosting_type_id'],
        ];

        // Get hosting ID after storing data
        $hostingId = $this->hostingRepository->storeHosting($hosting);

        // Find the type key
        $hostingType = $this->hostingRepository->getHostingTypeById($params['hosting_type_id']);

        if ($params['availability'] == 'private' && count($params['customers'])) {
            foreach ($params['customers'] as $customerId) {
                $hostingAvailableCustomers = [
                    'hosting_id' => $hostingId,
                    'customer_id' => $customerId
                ];

                $this->hostingRepository->storeHostingAvailableCustomers($hostingAvailableCustomers);
            }
        }

        if ($hostingType && $hostingType->key == HOSTING_CLOUD) {
            $hostingCloudSettings = [
                'hosting_id' => $hostingId,
                'base_package_id' => $params['base_package_id'],
                'hosting_provider_id' => $params['hosting_provider_id'],
                'backup_price_monthly' => $params['backup_price_monthly'],
                'staging_price_monthly' => $params['staging_price_month'],
                'active' => true
            ];

            $this->hostingRepository->storeHostingCloudSettings($hostingCloudSettings);
        }

        if ($hostingType && $hostingType->key == HOSTING_ON_PREM) {
            $hostingOnPremSettings = [
                'hosting_id' => $hostingId,
                'moodle_url' => $params['moodle_url'],
                'moodle_cron_url' => $params['moodle_url'],
                'reverse_proxy' => false,
                'active' => true
            ];

            $this->hostingRepository->storeHostingOnPremSettings($hostingOnPremSettings);
        }
    }

    /**
     * @param $params
     * @return void
     */
    public function updateHosting($params)
    {
        $hosting = [
            'id' => $params['id'],
            'name' => $params['name'],
            'production_price_month' => $params['production_price_month'],
            'staging_price_month' => $params['staging_price_month'],
            'yearly_price_increase' => $params['yearly_price_increase'],
            'description' => $params['description'],
            'availability' => $params['availability'],
           // 'config' => $params['config'] ?? '[]',
            'hosting_type_id' => $params['hosting_type_id'],
        ];

        // Get hosting ID after storing data
        $hostingId = $this->hostingRepository->updateHosting($hosting);

        // Find the type key
        $hostingType = $this->hostingRepository->getHostingTypeById($params['hosting_type_id']);

        // Delete all
        $this->hostingRepository->purgeHostingAvailableCustomers($params['id']);

        if ($params['availability'] == 'private' && count($params['customers'])) {

            // Then insert
            foreach ($params['customers'] as $customerId) {
                $hostingAvailableCustomers = [
                    'hosting_id' => $params['id'],
                    'customer_id' => $customerId
                ];

                $this->hostingRepository->storeHostingAvailableCustomers($hostingAvailableCustomers);
            }
        }

        if ($hostingType && $hostingType->key == HOSTING_CLOUD) {
            $hostingCloudSettings = [
                'hosting_id' => $hostingId,
                'base_package_id' => $params['base_package_id'],
                'hosting_provider_id' => $params['hosting_provider_id'],
                'backup_price_monthly' => $params['backup_price_monthly'],
                'staging_price_monthly' => $params['staging_price_month'],
                'active' => true
            ];

            $this->hostingRepository->updateHostingCloudSettings($hostingCloudSettings);
        }

        if ($hostingType && $hostingType->key == HOSTING_ON_PREM) {
            $hostingOnPremSettings = [
                'hosting_id' => $hostingId,
                'moodle_url' => $params['moodle_url'],
                'moodle_cron_url' => $params['moodle_url'],
                'reverse_proxy' => false,
                'active' => true
            ];

            $this->hostingRepository->updateHostingOnPremSettings($hostingOnPremSettings);
        }
    }

    public function edit($id)
    {
        $hosting = $this->hostingRepository->edit($id);

        if (!empty($hosting)) {
            return [
                'id' => $hosting['id'],
                'name' => $hosting['name'],
                'production_price_month' => $hosting['production_price_month'],
                'staging_price_month' => $hosting['staging_price_month'],
                'yearly_price_increase' => $hosting['yearly_price_increase'],
                'description' => $hosting['description'],
                'availability' => $hosting['availability'],
                'hosting_type_id' => $hosting['hosting_type_id'],
                'moodle_url' => $hosting['hosting_on_prem_settings']['moodle_url'] ?? '',
                'hosting_provider_id' => $hosting['hosting_cloud_settings']['hosting_provider_id'],
                'base_package_id' => $hosting['hosting_cloud_settings']['base_package_id'],
                'backup_price_monthly' => $hosting['hosting_cloud_settings']['backup_price_monthly'],
                'customers' => !empty($hosting['hosting_customers']) ?
                    collect($hosting['hosting_customers'])->pluck('id')->all() : []
            ];
        }

        return [];
    }
}
