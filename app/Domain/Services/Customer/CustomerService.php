<?php

namespace App\Domain\Services\Customer;

use App\Domain\DataClasses\Customer\Customer;
use App\Domain\Repositories\Customer\CustomerRepositoryInterface;
use App\Domain\Repositories\Remote\RemoteAdminRepositoryInterface;
use App\Domain\Services\Remote\RemoteCallHandlerInterface;
use App\Domain\Traits\InstallationTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService implements CustomerServiceInterface
{
    use InstallationTrait;
    protected $customerRepository;
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->customerRepository->edit($id);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function updateCustomer($params)
    {
        if (isset($params["name"])) { #468 disabling customer name rename
            unset($params["name"]);
        }

        if (isset($params["slugified_name"])) { #468  disabling customer name rename
            unset($params["slugified_name"]);
        }
        $this->customerRepository->updateCustomer($params);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function storeCustomer($data)
    {
        $customer = (new Customer())
            ->setName($data['name'])
            ->setSlugifiedName($data['slugified_name'])
            ->setOrganizationNo($data['organization_no'])
            ->setInvoiceType($data['invoice_type'])
            ->setInvoiceAddress($data['invoice_address'])
            ->setInvoiceEmail($data['invoice_email'])
            ->setInvoiceReference($data['invoice_reference'])
            ->setInvoiceAnnotation($data['invoice_annotation']);

        $customerId = $this->customerRepository->storeCustomer(array_filter($customer->toArray()));

        $remoteAdminRepository = app(RemoteAdminRepositoryInterface::class);
        $remoteCallHandler = app(RemoteCallHandlerInterface::class);

        $jobId = $remoteAdminRepository->createRemoteJob(
            REMOTE_JOB_TYPE_CREATE_CUSTOMER,
            auth()->user()->id,
            $customerId
        );
        //call ancible API
        $responce = $remoteCallHandler->createCustomer(
            $jobId,
            $data['name'],
            $data['slugified_name']
        );

        $job = [
            'response' => $responce
        ];

        $jobId = $remoteAdminRepository->updateRemoteJob(
            $jobId,
            $job
        );
    }

    public function listCustomers($params)
    {
        $params['with'] = [
            'products',
            'customerProducts.installations',
            //'user'
        ];

        $paginatedResult = $this->customerRepository->listCustomers($params);

        return ($paginatedResult instanceof LengthAwarePaginator) ? $paginatedResult->through(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'products' => count($customer->products),
                'installations' => $customer->customerProducts->flatMap->installations->count(),
                'contact' => '',
                'last_login' => ''
            ];
        }) : [];
    }

    public function listCustomerProducts($params)
    {
        $params['with'] = [
            'product.productSoftwares',
            'installations.hosting.hostingType',
            'customer',
        ];

        $result = $this->customerRepository->listCustomerProducts($params);

        if ($result) {
            return $result->transform(function ($item) {

                $installations = $this->getInstallationsForListable($item);
                $productName = $item['label'];
                if (isset($item['product'])) {
                    $productName = $item['product']["pipeline_name"];
                }
                return [
                    "customer_id" => $item['customer_id'],
                    "product_id" => $item['product_id'],
                    "product_name" => $productName,
                    "installations" => $installations ?? []
                ];
            })->all();
        }
        return [];
    }

    private function getInstallationsForListable($item)
    {
        $installations = [];
        if (!empty($item['installations'])) {
            foreach ($item['installations'] as $installation) {
                $installations[] = [
                    "id" => $installation['id'] ?? '',
                    "url" => $installation['url'] ?? '',
                    "status" => $installation['status'] ?? '',
                    "version" => $installation['software_version'] ? $installation['software_version'] : '',
                    "hosting" => $installation['hosting']['name'] ?? '',
                    "hosting_type" => $installation['hosting']['hostingType']['key'] ?? '',
                    "include_staging" => $installation['include_staging_package'],
                    "installation_type" => $installation["targetType"]["name"],
                    "installation_status" => $this->getInstallationStatus(
                        $installation['id'],
                        [REMOTE_JOB_TYPE_PUBLIC_INSTALLATION, REMOTE_JOB_TYPE_STANDARD_INSTALLATION]
                    )
                ];
            }
        }
        return $installations;
    }
}
