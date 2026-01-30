<?php

namespace App\Domain\Services\Remote;

use App\Domain\DataClasses\Installation\ChangeInstallationDiskSizeDto;
use App\Domain\DataClasses\Installation\DeleteInstallationDto;
use App\Domain\DataClasses\Installation\SetupFreeInstallationDto;
use App\Domain\DataClasses\Installation\SetupInstallationDto;
use App\Domain\Exception\SparkforeException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Log;

class RemoteCallHandler implements RemoteCallHandlerInterface
{
    protected $baseURL;
    protected $clientId;
    protected $clientSecret;
    protected $username;
    protected $password;
    protected $authToken;

    public function __construct()
    {
        $this->baseURL = config('sparkfore.aapi_url');
        $this->clientId = config('sparkfore.aapi_client_id');
        $this->clientSecret = config('sparkfore.aapi_client_secret');
        $this->username = config('sparkfore.aapi_username');
        $this->password = config('sparkfore.aapi_password');
    }

    private function makeURL($part)
    {
        return $this->baseURL . $part;
    }

    public function authenticate($referesh = false)
    {
        if (!$referesh && Cache::has("aapi_password_token") && Cache::get("aapi_password_token")) {
            $this->authToken = Cache::get("aapi_password_token");
            Log::debug("Authentication using cache in Ansbile API");
        } else {
            Log::info("Getting a new Ansbile API token");
            $response = Http::asForm()->post($this->makeURL('/o/token/'), [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => GRANT_TYPE_PASSWORD,
                'username' => $this->username,
                'password' => $this->password
            ]);

            if ($response->ok()) {
                $this->authToken = $response->object()->access_token;
                Cache::put("aapi_password_token", $this->authToken, now()->addMinutes(60));
            }
        }
    }

    public function restartServer($jobId, $cloud, $location, $customer, $domain, $refreshToken = true)
    {

        $this->authenticate();

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/site/restart'), [
                    'job_id' => $jobId,
                    'cloud' => $cloud,
                    'location' => $location,
                    'customer' => $customer,
                    'site' => $domain,
                    'callback_url' => $this->getCallBackURL()
                ]);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid in restartServer. Refresing ....');
            $this->authenticate(referesh: true);
            $this->restartServer($jobId, $cloud, $location, $customer, $domain, refreshToken: false);
        }

        Log::info("Restart server response ", [
            'job_id' => $jobId,
            "response" => $response
        ]);

        return $response;
    }

    public function redeployInstance(
        $jobId,
        $cloud,
        $location,
        $customer,
        $domain,
        $branch,
        $version,
        $refreshToken = true
    ) {

        $this->authenticate();

        Log::info("adaw", [$this->authToken]);
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/site/redeploy'), [
                    'job_id' => $jobId,
                    'cloud' => $cloud,
                    'location' => $location,
                    'customer' => $customer,
                    'site' => $domain,
                    'branch' => $branch,
                    'version' => $version,
                    'callback_url' => $this->getCallBackURL()
                ]);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid in redeploy instance. Refresing ....');
            $this->authenticate(referesh: true);
            $this->redeployInstance(
                $jobId,
                $cloud,
                $location,
                $customer,
                $domain,
                $branch,
                $version,
                refreshToken: false
            );
        }

        Log::info("Redeploy instance response ", [
            'job_id' => $jobId,
            "response" => $response
        ]);

        return $response;
    }

    public function createPipeline(
        $jobId, $customer, $customerSlug, $baseProduct, $baseProductSlug,
        $name, $nameSlug, $legacy, $refreshToken = true
    ) {

        $this->authenticate();
        $requestBody = [
            'job_id' => $jobId,
            'customer' => $customer,
            'customer_slug' => $customerSlug,
            'base_product' => $baseProduct,
            'base_product_slug' => $baseProductSlug,
            'name' => $name,
            'legacy' => (bool) $legacy,
            'name_slug' => $nameSlug,
            'callback_url' => $this->getCallBackURL()
        ];
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/pipeline/add'), $requestBody);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid in createPipeline. Refresing ....');
            $this->authenticate(referesh: true);
            $this->createPipeline(
                $jobId, $customer, $customerSlug, $baseProduct,
                $baseProductSlug, $name, $nameSlug, $legacy, refreshToken: false
            );
        }

        Log::info("Create pipeline ", [
            "request" => $requestBody,
            "response" => $response
        ]);

        return $response;
    }

    public function deletePipeline(
        $jobId, $customer, $customerSlug, $baseProduct, $baseProductSlug,
        $name, $nameSlug, $refreshToken = true
    ) {

        $this->authenticate();
        $requestBody = [
            'job_id' => $jobId,
            'customer' => $customer,
            'customer_slug' => $customerSlug,
            'base_product' => $baseProduct,
            'base_product_slug' => $baseProductSlug,
            'name' => $name,
            'name_slug' => $nameSlug,
            'callback_url' => $this->getCallBackURL()
        ];
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/pipeline/delete'), $requestBody);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid in deletePipeline. Refresing ....');
            $this->authenticate(referesh: true);
            $this->deletePipeline(
                $jobId, $customer, $customerSlug, $baseProduct,
                $baseProductSlug, $name, $nameSlug, refreshToken: false
            );
        }

        Log::info("Delete pipeline ", [
            "request" => $requestBody,
            "response" => $response
        ]);

        return $response;
    }

    public function buildPipeline(
        $jobId,
        $customer,
        $customerSlug,
        $baseProduct,
        $baseProductSlug,
        $name,
        $nameSlug,
        $branch,
        $buildVersion,
        $legacy,
        $refreshToken = true
    ) {

        $this->authenticate();

        if (is_string($legacy) && $legacy == 'false') {
            $legacy = false;
        } elseif (is_string($legacy) && $legacy == 'true') {
            $legacy = true;
        }

        $requestBody = [
            'job_id' => $jobId,
            'customer' => $customer,
            'customer_slug' => $customerSlug,
            'base_product' => $baseProduct,
            'base_product_slug' => $baseProductSlug,
            'name' => $name,
            'name_slug' => $nameSlug,
            'branch' => $branch,
            'legacy' => $legacy,
            'build_version' => $buildVersion,
            'callback_url' => $this->getCallBackURL()
        ];
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/pipeline/build'), $requestBody);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid in buildPipeline. Refresing ....');
            $this->authenticate(referesh: true);
            $this->buildPipeline(
                $jobId,
                $customer,
                $customerSlug,
                $baseProduct,
                $baseProductSlug,
                $name,
                $nameSlug,
                $branch,
                $buildVersion,
                $legacy,
                refreshToken: false
            );
        }



        Log::info("Build pipeline ", [
            "request" => $requestBody,
            "response" => $response
        ]);
        return $response;
    }

    public function createCustomer($jobId, $customer, $customerSlug, $refreshToken = true)
    {

        $this->authenticate();
        $requestBody = [
            'job_id' => $jobId,
            'customer' => $customer,
            'customer_slug' => $customerSlug,
            'callback_url' => $this->getCallBackURL()
        ];
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/customer/add'), $requestBody);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid. Refresing ....');
            $this->authenticate(referesh: true);
            $this->createCustomer($jobId, $customer, $customerSlug, refreshToken: false);
        }

        Log::info("Create customer ", [
            "request" => $requestBody,
            "response" => $response
        ]);

        return $response;
    }

    public function renameCustomer(
        $jobId,
        $oldCustomer,
        $oldCustomerSlug,
        $newCustomer,
        $newCustomerSlug,
        $refreshToken = true
    ) {

        $this->authenticate();
        $requestBody = [
            'job_id' => $jobId,
            'old_customer' => $oldCustomer,
            'old_customer_slug' => $oldCustomerSlug,
            'new_customer' => $newCustomer,
            'new_customer_slug' => $newCustomerSlug,
            'callback_url' => $this->getCallBackURL()
        ];
        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/customer/rename'), $requestBody);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid. Refresing ....');
            $this->authenticate(referesh: true);
            $this->renameCustomer(
                $jobId,
                $oldCustomer,
                $oldCustomerSlug,
                $newCustomer,
                $newCustomerSlug,
                refreshToken: false
            );
        }

        Log::info("Rename customer ", [
            "request" => $requestBody,
            "response" => $response
        ]);

        return $response;
    }

    private function getCallBackURL()
    {
        $callbackURL =  route('webhook.ansible.callback');
        $pattern = "/^http:\/\//";  // Matches 'http://'

        if (!app()->environment('local')) {
            // If the environment is not local, use a secure HTTPS URL
            $callbackURL =  preg_replace($pattern, "https://", $callbackURL);
        }

        return $callbackURL;
    }



    public function setupInstallation(SetupInstallationDto $setupInstallationDto, $refreshToken = true)
    {
        $this->authenticate();
        $setupInstallationDto->setCallbackUrl($this->getCallBackURL());
        $requestBody = $setupInstallationDto->toArray();

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/site/deploy'), $requestBody);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid in installation setup. Refresing ....');
            $this->authenticate(referesh: true);
            $this->setupInstallation(
                $setupInstallationDto,
                refreshToken: false
            );
        }

        if (!$response->ok()) {
            Log::error("Setup installation call failed", [
                "request" => $requestBody,
                "response" => $response
            ]);
            throw new SparkforeException('Ansible installation creation failure', 422);
        }

        Log::info("Setup installation call", [
            "request" => $requestBody,
            "response" => $response
        ]);

        return $response;
    }

    public function setupFreeInstallation(SetupFreeInstallationDto $setupFreeInstallationDto, $refreshToken = true)
    {
        $this->authenticate();
        $setupFreeInstallationDto->setCallbackUrl($this->getCallBackURL());
        $requestBody = $setupFreeInstallationDto->toArray();

        Log::info("Setup FREE installation call", [
            "request" => $requestBody,
            "dto" => $setupFreeInstallationDto
        ]);

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/site/deploy/free'), $requestBody);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid in FEEE installation setup. Refresing ....');
            $this->authenticate(referesh: true);
            $this->setupFreeInstallation(
                $setupFreeInstallationDto,
                refreshToken: false
            );
        }

        if (!$response->ok()) {
            Log::error("Setup FREE installation call failed", [
                "request" => $requestBody,
                "response" => $response
            ]);
            throw new SparkforeException('Ansible installation creation failure', 422);
        }

        Log::info("Setup FREE installation call", [
            "request" => $requestBody,
            "response" => $response
        ]);

        return $response;
    }

    public function changeInstallationDiskSize(ChangeInstallationDiskSizeDto $changeInstallationDiskSizeDto, $refreshToken = true)
    {
        $this->authenticate();
        $changeInstallationDiskSizeDto->setCallbackUrl($this->getCallBackURL());
        $requestBody = $changeInstallationDiskSizeDto->toArray();
        if (isset($requestBody['base_product'])) {
            unset($requestBody['base_product']);
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/site/rezize/disk'), $requestBody);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid in installation disk size change. Refresing ....');
            $this->authenticate(referesh: true);
            $this->changeInstallationDiskSize(
                $changeInstallationDiskSizeDto,
                refreshToken: false
            );
        }

        if (!$response->ok()) {
            Log::error("installation disk size change call failed", [
                "request" => $requestBody,
                "response" => $response
            ]);
            throw new SparkforeException('Ansible installation disk size change failure', 422);
        }

        Log::info("installation disk size change ", [
            "request" => $requestBody,
            "response" => $response
        ]);

        return $response;
    }

    public function deleteInstallation(DeleteInstallationDto $deleteInstallationDto, $refreshToken = true)
    {
        $this->authenticate();
        $deleteInstallationDto->setCallbackUrl($this->getCallBackURL());
        $requestBody = $deleteInstallationDto->toArray();

        $response = Http::withHeaders([
            'Authorization' => "Bearer $this->authToken",
            "Accept" => 'application/json'
        ])->asJson()->post($this->makeURL('/api/v1/site/delete'), $requestBody);

        if ($response->status() == 401 && $refreshToken) {
            Log::info('Ansbile API token has expired or is invalid in delete installation. Refreshing ....');
            $this->authenticate(referesh: true);
            return $this->deleteInstallation(
                $deleteInstallationDto,
                refreshToken: false
            );
        }

        if (!$response->ok()) {
            Log::error("Delete installation call failed", [
                "request" => $requestBody,
                "response" => $response->body()
            ]);
            throw new SparkforeException('Ansible installation deletion failure', 422);
        }

        Log::info("Delete installation call succeeded", [
            "request" => $requestBody,
            "response" => $response->body()
        ]);

        return $response;
    }
}
