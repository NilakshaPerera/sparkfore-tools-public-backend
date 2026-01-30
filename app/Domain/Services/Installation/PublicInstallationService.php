<?php

namespace App\Domain\Services\Installation;

use App\Domain\DataClasses\Moodle\MoodleUpdateUserDto;
use App\Domain\Jobs\AnsibleCallbackForPublicInstallationJob;
use App\Domain\Models\AccountType;
use App\Domain\Models\Customer;
use App\Domain\Models\CustomerProduct;
use App\Domain\Models\Hosting;
use App\Domain\Models\HostingProvider;
use App\Domain\Models\Product;
use App\Domain\Models\ProductAvailableCustomer;
use App\Domain\Models\PublicInstallation;
use App\Domain\Models\RemoteJob;
use App\Domain\Models\Role;
use App\Domain\Models\User;
use App\Domain\Services\Email\PostmarkService;
use App\Domain\Services\ServiceApi\MoodleApiService;
use Crypt;
use Hash;
use Illuminate\Support\Str;
use Log;

class PublicInstallationService implements PublicInstallationServiceInterface
{
    private $postMarkEmailService;
    private $freeInstallationUserId = 3;

    public function __construct()
    {
        $this->postMarkEmailService = new PostmarkService("75620aa0-bc1a-4a80-b1d1-4a4b8a8183e4");
    }
    public function createPublicInstallation($params)
    {
        $publicInstallation = null;

        try {
            Log::info("Creating public installation for customer: " . $params['email']);
            $publicInstallation = PublicInstallation::create(
                [
                    "site_name" => $params['siteName'],
                    "password" => Crypt::encryptString($params['password']),
                    "first_name" => $params['firstName'],
                    "last_name" => $params['lastName'],
                    "email" => $params['email'],
                    "phone" => $params['phone'],
                    "terms_accepted" => $params['terms_and_conditions'],
                ]
            );

            $customer = Customer::create([
                "name" => $params['firstName'] . " " . $params['lastName'],
                "invoice_address" => $params['phone'],
                "slugified_name" => Str::slug($params['firstName'] . " " . $params['lastName']),
                "invoice_email" => $params['email'],
                "organization_no" => "public-nstallation",
                "invoice_reference" => "public-installation",
                "invoice_type" => "email",
            ]);
            Log::info("Customer created: ", [$params['email'], $customer->id]);

            $remoteAdminRepository = app('App\Domain\Repositories\Remote\RemoteAdminRepositoryInterface'::class);
            $remoteCallHandler = app('App\Domain\Services\Remote\RemoteCallHandlerInterface'::class);

            $jobId = $remoteAdminRepository->createRemoteJob(
                REMOTE_JOB_TYPE_CREATE_CUSTOMER,
                -1,
                $customer->id
            );
            //call ancible API
            $responce = $remoteCallHandler->createCustomer(
                $jobId,
                $customer->name,
                $customer->slugified_name,
            );

            $job = [
                'response' => $responce
            ];

            $jobId = $remoteAdminRepository->updateRemoteJob(
                $jobId,
                $job
            );

            $user = User::create([
                "f_name" => $params['firstName'],
                "l_name" => $params['lastName'],
                "email" => $params['email'],
                "trial" => false,
                "password" => Hash::make($params['password']),
                "account_type_id" => AccountType::where('name', 'customer')->first()->id,
                "customer_id" => $customer->id,
                "role_id" => Role::where('name', 'customer')->first()->id,
            ]);

            Log::info("User created: ", [$params['email'], $user->id]);

            $hostingProvider = HostingProvider::where('key', 'digitalocean')
                ->orderBy('id', 'asc')
                ->first();
            $hostingPackage = Hosting::where('hosting_provider_id', $hostingProvider->id)
                ->where('name', 'Standard+')
                ->first();

            $product = Product::where('pipeline_name', config("sparkfore.free_installation.package_pipeline_name"))->first();
            $installationParams = [
                "domain_type" => DOMAIN_TYPE_STANDARD,
                "sub_domain" => $params["siteName"],
                "billing_terms" => true,
                "general_terms" => true,
                "include_backup" => false,
                "include_staging" => false,
                "hosting_package" => $hostingPackage->id,
                "hosting_provider" => $hostingProvider->id,
                "customer_id" => $customer->id,
                "product_id" => $product->id,
                "installation_type" => "free",
                "public_installation" => $publicInstallation->id,
            ];

            ProductAvailableCustomer::create([
                "customer_id" => $customer->id,
                "product_id" => $installationParams["product_id"]
            ]);

           CustomerProduct::create([
                "customer_id" => $customer->id,
                "product_id" => $installationParams["product_id"],
                'label' => $product->pipeline_name ?? 'N/A',
                'base_price_increase_yearly' => 0,
                'base_price_per_user_increase_yearly' => 0,
                'include_maintenance' => 0
            ]);

            Log::info("Customer product created: ", [$params['email'], $customer->id]);

            $installationService = app(InstallationService::class);
            $installation = $installationService->storeInstallation($installationParams);

            Log::info("Public installation created for customer: " . $params['email']);

            return $installation;
        } catch (\Throwable $th) {
            Log::error("Error creating public installation for customer: " . $params['email'], [$th->getMessage(), $th->getTraceAsString()]);
            if ($publicInstallation) {
                $publicInstallation->installation_status = "TOOLING_FAILED";
                $publicInstallation->save();
            }
            $this->postMarkEmailService->sendEmailWithTemplate(
                $params['email'],
                39001737,
                [
                    "name" => $params['firstName'] . " " . $params['lastName'],  // NAME OF USER
                    "product_url" => $params['siteName'] . '.' . SPARKFORE_DOMAIN
                ],
            );
        }


    }

    public function handleAnsibleCallback(RemoteJob $remoteJob)
    {
        $publicInstallation = PublicInstallation::where('remote_job_id', $remoteJob->id)->first();

        if (!$publicInstallation) {
            Log::info("Public installation not found for remote job: " . $remoteJob->id);
            return;
        }
        $publicInstallation->load("user.customer");

        if ($remoteJob->callback_status == "ANSIBLE_COMPLETE") {
            AnsibleCallbackForPublicInstallationJob::dispatch($remoteJob->id)
            ->delay(now()->addMinutes(2))
            ->onQueue('pluginsSync');

            Log::info("Dispatched Ansible callback job for Free installation remote job: " . $remoteJob->id);
        } elseif ($remoteJob->callback_status == "ERROR") {
            Log::info("Public installation creation failed for remote job: " . $remoteJob->id);
            $publicInstallation->installation_status = "ANSIBLE_FAILED";
            $publicInstallation->save();

            $this->postMarkEmailService->sendEmailWithTemplate( // admin email
                "hello@sparkfore.com",
                39001714,
                [
                    "name" => $publicInstallation->first_name,
                    "url" => config('constants.HTTPS') . $publicInstallation->site_name . '.' . SPARKFORE_DOMAIN, // URL TO INSTALLATION
                ],
            );

            $this->postMarkEmailService->sendEmailWithTemplate( // customer email
                $publicInstallation->email,
                39001737,
                [
                    "name" => $publicInstallation->first_name,  // NAME OF USER
                    "product_url" => config('constants.HTTPS') . $publicInstallation->site_name . '.' . SPARKFORE_DOMAIN, // URL TO INSTALLATION
                    "product_name" => config("sparkfore.free_installation.package_pipeline_name"), // NAME OF PRODUCT
                    "login_url" => config('constants.HTTPS') . $publicInstallation->site_name . '.' . SPARKFORE_DOMAIN, // URL TO INSTALLATION
                    "username" => $publicInstallation->email, // USERNAME
                    "password" => Crypt::decryptString($publicInstallation->password), // DECRYPT PASSWORD
                ],
            );
        }


    }

    private function updateInstallaionUser(PublicInstallation $publicInstallation)
    {
        $moodleService = app(MoodleApiService::class, [
            "baseUrl" => config('constants.HTTPS') . $publicInstallation->site_name . '.' . SPARKFORE_DOMAIN,
        ]);
        Log::info("Updating user in Moodle: " . $publicInstallation->email);
        $moodleService->coreUserUpdateUser(
            new MoodleUpdateUserDto(
                $this->freeInstallationUserId,
                $publicInstallation->email,
                Crypt::decryptString($publicInstallation->password),
                $publicInstallation->first_name,
                $publicInstallation->last_name,
                $publicInstallation->email
            )
        );
    }
}
