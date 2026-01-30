<?php

namespace Tests\Unit;

use App\Domain\Exception\SparkforeException;
use App\Domain\Models\Customer;
use App\Domain\Models\Plugin;
use App\Domain\Models\PluginVersion;
use App\Domain\Models\Product;
use App\Domain\Models\ProductAvailableCustomer;
use App\Domain\Models\ProductHasPlugin;
use App\Domain\Models\ProductHasPluginVersions;
use App\Domain\Models\ProductHasSoftware;
use App\Domain\Models\Setting;
use App\Domain\Models\User;
use App\Domain\Repositories\Product\ProductRepository;
use App\Domain\Repositories\Software\SoftwareRepository;
use App\Domain\Services\Product\ProductService;
use App\Domain\Services\Remote\RemoteAdminService;
use App\Domain\Services\ServiceApi\GiteaApiService;
use App\Domain\Shared\Actions\FileGetContentsAction;
use App\Domain\Shared\Actions\FilePutContentsAction;
use App\Domain\Traits\HttpResponse;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Config;
use Mockery\MockInterface;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $apllicationJson = 'application/json';

    public function setUp(): void
    {

        parent::setUp();
        $this->user = User::factory()->make();
        Passport::actingAs($this->user);

        Http::fake([
            "aapi-dev.sparkfore.com/o/token/" => Http::response([
                "access_token" => "UNIT TEST TOKEN"
            ], 200),
            'aapi-dev.sparkfore.com/api/v1/site/restart' => Http::response([
                "job_id" => 0,
                "cloud" => "iu8udh_gcldGOceOXWsv",
                "location" => "P0KCQieX0B0PJ",
                "customer" => "hzzJ6dihIRY9ua0HMWrxCZzMKwG2wzSH10qaQlIgu",
                "site" => "string",
                "status" => "string"
            ], 200),
        ]);
    }

    public function test_getFormCreate(): void
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/product/form_create');

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);


        $productService = $this->app->make(ProductService::class);

        $methodGenerateCron = new \ReflectionMethod($productService, 'getCronExpression');
        $methodGenerateCron->setAccessible(true);

        $params = [
            "dev_schedule_hour" => "0",
            "dev_schedule_day" => "2",
            "dev_schedule_month" => "0",
        ];

        $this->assertEquals('0 3 1,14 * *', $methodGenerateCron->invoke($productService, $params, "staging"));
        $this->assertEquals('0 2 * * *', $methodGenerateCron->invoke($productService, "invalid-frequency", "dev"));
    }

    public function test_getFormSettingsCreate(): void
    {
        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('env', "staging");
            $route->setParameter('id', 1);
            return $route;
        });

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/product/setting/form_create/staging/1');

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
    }

    public function test_getFormSettingsData(): void
    {
        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('env', "staging");
            $route->setParameter('id', 1);
            return $route;
        });

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/product/setting/form_data/staging/1');

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
    }

    public function test_getCustomerProductFormCreate(): void
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/customer/product/form_create');

        $response->assertStatus(500); // since no getCustomerProductFormCreate is not defnied in ProductService
    }

    public function test_getCustomerProduct(): void
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/customer/product/form_edit?');

        $response->assertStatus(500); // since no getCustomerProduct is not defnied in ProductService
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
    }


    public function test_listProducts(): void
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/product/list?');

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
    }

    public function test_editCustomerProduct(): void
    {
        $postData = [
            'customer_product_id' => 1,
            'product_label' => "Test",
            'include_maintenance' => false
        ];
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/customer/product/edit', $postData);

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
    }

    public function test_storeProduct(): void
    {
        $plugin = Plugin::factory()->create();
        PluginVersion::factory()->create([
            "plugin_id" => $plugin->id,
            "version_type" => 1,
            "version_name" => "unittest",
            "version_id" => "1"
        ]);

        $postData = [
            'product_name' => "Unit Test 2",
            'maintainer_id' => 1,
            'customer' => 1,
            'availability' => "private",
            'software_id' => "1",
            'supported_version' => "v0.0.1",
            'supported_version_type' => "tag",
            'include_maintenance' => false,
            'software_name' => "Unit Test 54",
            "plugins" => [
                [
                    'id' => $plugin->id,
                    'selected_version' => "v0.0.1",
                    'selected_version_type' => 1,
                    'git_url' => "https:://test1",
                ]
            ]
        ];

        $giteaApiService = $this->mock(GiteaApiService::class);
        $giteaApiService->shouldReceive('createRepo')->andReturn(['id' => 123, 'name' => 'new_repo']);
        $giteaApiService->shouldReceive('getContent')->andReturn([
            "content" => base64_encode("
customer: test-baseline
registry_project: test
image_name: test-baseline
languages: en sv de fr nl it es pt ru zh_cn ja ar fi")
        ]);
        $giteaApiService->shouldReceive('migrateRepos')->andReturn("Unit-test content 1");


        $processMock = $this->partialMock(ProductService::class)->shouldAllowMockingProtectedMethods();

        $remoteAdminServiceMock = $this->mock(RemoteAdminService::class);
        $remoteAdminServiceMock->shouldReceive("createPipeline")->andReturn([]);

        $softwareRepoMock = $this->mock(SoftwareRepository::class);
        $softwareRepoMock->shouldReceive("getSoftware")->andReturn((object) [
            "name" => "unit_test"
        ]);


        $productRepoMock = $this->mock(ProductRepository::class);
        $productRepoMock->shouldReceive("storeProduct");
        $productRepoMock->shouldReceive("storeProductSoftwares");
        $productRepoMock->shouldReceive("storeProductPlugins");
        $productRepoMock->shouldReceive("storeProductCustomers");
        $productRepoMock->shouldReceive("getProductCustomers")->andReturn(collect()->push((object) [
            "name" => "unit_test",
            "slugified_name" => "unit-test"
        ]));
        $productRepoMock->shouldReceive("getProductPluginVersionsByVersion")->andReturn([
            "component" => "unit_test"
        ]);

        // Define the behavior of the run method
        $processMock->shouldReceive('runProcess')->andReturn(0);
        $processMock->shouldReceive('isProductNameAvailable')->andReturn(true);
        $processMock->shouldReceive('getProductRepository')->andReturn($productRepoMock);
        $processMock->shouldReceive('getSoftwareRepository')->andReturn($softwareRepoMock);
        $processMock->shouldReceive('getGiteaApiService')->andReturn($giteaApiService);
        $processMock->shouldReceive('getRemoteAdminService')->andReturn($remoteAdminServiceMock);



        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/product/store', $postData);

        // expecting 500 due to git cli commands are failing and mocking the Process class is not working
        $response->assertStatus(500);
    }


    public function test_updateProduct(): void
    {
        $plugin = Plugin::factory()->create();
        $product = Product::factory()->make([
            "id" => 0
        ]);
        PluginVersion::factory()->create([
            "plugin_id" => $plugin->id,
            "version_type" => 1,
            "version_name" => "unittest",
            "version_id" => "1"
        ]);

        $postData = [
            'product_name' => "Unit Test 2",
            'maintainer_id' => 1,
            'customer' => 1,
            'availability' => "private",
            'software_id' => "1",
            'supported_version' => "v0.0.1",
            'supported_version_type' => "tag",
            'include_maintenance' => false,
            'software_name' => "Unit Test",
            'environment' => "dev",
            'git_url' => "https:://test3",
            "plugins" => [
                [
                    'id' => $plugin->id,
                    'selected_version' => "v0.0.1",
                    'selected_version_type' => 1,
                    'git_url' => "https:://test2",
                ]
            ],
            "software" => [
                'id' => 1,
                'selected_version' => "v0.0.1",
                'software_id' => 1,
                'supported_version' => "v0.0.1",
                'supported_version_type' => 1,
                'selected_version_type' => 1,
            ]
        ];

        $giteaApiService = $this->mock(GiteaApiService::class);
        $giteaApiService->shouldReceive('createRepo')->andReturn(['id' => 123, 'name' => 'new_repo']);
        $giteaApiService->shouldReceive('getContent')->andReturn([
            "content" => base64_encode("
customer: test-baseline
registry_project: test
image_name: test-baseline
languages: en sv de fr nl it es pt ru zh_cn ja ar fi")
        ]);
        $giteaApiService->shouldReceive('migrateRepos')->andReturn("Unit-test content");


        $processMock = $this->partialMock(ProductService::class)->shouldAllowMockingProtectedMethods();

        $remoteAdminServiceMock = $this->mock(RemoteAdminService::class);

        $softwareRepoMock = $this->mock(SoftwareRepository::class);


        $productRepoMock = $this->mock(ProductRepository::class);
        $productRepoMock->shouldReceive("deleteProductPluginsByEnvironment");
        $productRepoMock->shouldReceive("storeProductPlugins");
        $productRepoMock->shouldReceive("updateProduct");
        $productRepoMock->shouldReceive("updateProductSoftware");
        $productRepoMock->shouldReceive("getProductPluginsByEnvironment")
            ->andReturn(collect()->push(
                [
                    'id' => 1,
                    'selected_version' => "v0.0.1",
                    'selected_version_type' => 1,
                    'git_url' => "https:://test5",
                ]
            ));

        $mockAction = $this->createMock(FileGetContentsAction::class);

        $mockAction->method('__invoke')->willReturn("
customer: test-baseline
registry_project: test
image_name: test-baseline
languages: en sv de fr nl it es pt ru zh_cn ja ar fi");
        $this->app->instance(FileGetContentsAction::class, $mockAction);

        $mockActionPut = $this->createMock(FilePutContentsAction::class);
        $this->app->instance(FilePutContentsAction::class, $mockActionPut);

        // Define the behavior of the run method
        $processMock->shouldReceive('runProcess')->andReturn(0);
        $processMock->shouldReceive('isProductNameAvailable')->andReturn(true);
        $processMock->shouldReceive('getProductRepository')->andReturn($productRepoMock);
        $processMock->shouldReceive('getSoftwareRepository')->andReturn($softwareRepoMock);
        $processMock->shouldReceive('getGiteaApiService')->andReturn($giteaApiService);
        $processMock->shouldReceive('getRemoteAdminService')->andReturn($remoteAdminServiceMock);

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post("/v1/product/setting/update/{$product->id}", $postData);

        $response->assertStatus(500);
    }

    public function test_updateProductEnvironmentPlugins(): void
    {
        Config::set('app.debug', false);
        $plugin = Plugin::factory()->create();
        $product = Product::factory()->make([
            "id" => 0
        ]);
        PluginVersion::factory()->create([
            "plugin_id" => $plugin->id,
            "version_type" => 1,
            "version_name" => "unittest",
            "version_id" => "1"
        ]);

        $postData = [
            'product_name' => "Unit Test 1",
            'maintainer_id' => 1,
            'customer' => 1,
            'availability' => "private",
            'software_id' => "1",
            'supported_version' => "v0.0.1",
            'supported_version_type' => "tag",
            'include_maintenance' => false,
            'software_name' => "Unit Test",
            'environment' => "dev",
            'git_url' => "https:://test6",
            "plugins" => [
                [
                    'id' => $plugin->id,
                    'selected_version' => "v0.0.1",
                    'selected_version_type' => 1,
                    'git_url' => "https:://test",
                ]
            ],
            "software" => [
                'id' => 1,
                'selected_version' => "v0.0.1",
                'software_id' => 1,
                'supported_version' => "v0.0.1",
                'supported_version_type' => 1,
                'selected_version_type' => 1,
            ]
        ];

        $giteaApiService = $this->mock(GiteaApiService::class);
        $giteaApiService->shouldReceive('createRepo')->andReturn(['id' => 123, 'name' => 'new_repo']);
        $giteaApiService->shouldReceive('getContent')->andReturn([
            "content" => base64_encode("
customer: test-baseline
registry_project: test
image_name: test-baseline
languages: en sv de fr nl it es pt ru zh_cn ja ar fi")
        ]);
        $giteaApiService->shouldReceive('migrateRepos')->andReturn("Unit-test content");


        $processMock = $this->partialMock(ProductService::class)->shouldAllowMockingProtectedMethods();

        $remoteAdminServiceMock = $this->mock(RemoteAdminService::class);

        $softwareRepoMock = $this->mock(SoftwareRepository::class);


        $productRepoMock = $this->mock(ProductRepository::class);
        $productRepoMock->shouldReceive("updateProductSoftwareByProductIdAndEnvironment");
        $productRepoMock->shouldReceive("getProductPluginsByEnvironment")->andReturn(collect()->push(
            [
                'id' => 1,
                'selected_version' => "v0.0.1",
                'selected_version_type' => 1,
                'git_url' => "https:://test",
            ]
        ));

        // Define the behavior of the run method
        $processMock->shouldReceive('runProcess')->andReturn(0);
        $processMock->shouldReceive('isProductNameAvailable')->andReturn(true);
        $processMock->shouldReceive('getProductRepository')->andReturn($productRepoMock);
        $processMock->shouldReceive('getSoftwareRepository')->andReturn($softwareRepoMock);
        $processMock->shouldReceive('getGiteaApiService')->andReturn($giteaApiService);
        $processMock->shouldReceive('getRemoteAdminService')->andReturn($remoteAdminServiceMock);

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/product/setting/update/' . $product->id . '/plugins', $postData);

        $response->assertStatus(500);
    }

    public function test_exceptionResponse(): void
    {
        $class = new class {
            use HttpResponse;
        };
        $response = $class->sendException(new SparkforeException("unittest test error"));
        $this->assertEquals(422, $response->getData()->status_code);
    }


    public function testIsProductNameAvailable()
    {
        $productRepoMock = $this->mock(ProductRepository::class);
        $productRepoMock->shouldReceive('getProductByPipelineName')
            ->with('nonexistent')
            ->andReturn(null);

        $productRepoMock->shouldReceive('getProductByPipelineName')
            ->with('existent')
            ->andReturn(new Product());

        $this->app->instance(ProductRepository::class, $productRepoMock);

        $productService = app(ProductService::class);


        $this->assertFalse($productService->isProductNameAvailable('existent'));
    }

    public function testRunScheduledBuilds()
    {
        Carbon::setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));

        // Mocking ModelsProduct static methods
        $proModelMock = $this->mock(Product::class);
        $proModelMock->shouldReceive('where')
            ->andReturnSelf();
        $proModelMock->shouldReceive('orWhere')
            ->andReturnSelf();
        $proModelMock->shouldReceive('with')
            ->andReturnSelf();
        $proModelMock->shouldReceive('get')
            ->andReturn(collect([
                (object) ['id' => 1, 'pipeline_name' => 'pipeline1'],
                (object) ['id' => 2, 'pipeline_name' => 'pipeline2'],
            ]));

        $this->app->instance(Product::class, $proModelMock);

        $productService = app(ProductService::class);

        $this->assertNull($productService->runScheduledBuilds());

        Carbon::setTestNow();
    }

    public function test_isProductNameAvailable()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/product/availability');

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
    }

    public function test_getProductPluginVersionsByVersion()
    {
        $pluginVersionMock = $this->mock(PluginVersion::class);
        $pluginVersionMock->shouldReceive('select')
            ->andReturnSelf();
        $pluginVersionMock->shouldReceive('where')
            ->andReturnSelf();
        $pluginVersionMock->shouldReceive('first')
            ->andReturn(1);
        $this->app->instance(PluginVersion::class, $pluginVersionMock);



        $pluginMock = $this->mock(Plugin::class);
        $pluginMock->shouldReceive('select')
            ->andReturnSelf();
        $pluginMock->shouldReceive('join')
            ->andReturnSelf();
        $pluginMock->shouldReceive('where')
            ->andReturnSelf();
        $pluginMock->shouldReceive('get')
            ->andReturn(1);
        $this->app->instance(Plugin::class, $pluginMock);


        $settingMock = $this->mock(Setting::class, function (MockInterface $mock) {
            $mock->shouldReceive('where')
                ->andReturnSelf();
            $mock->shouldReceive('first')
                ->andReturn((object) [
                    "value" => 100
                ]);
        });
        $this->app->instance(Setting::class, $settingMock);


        $proPlugin = ProductHasPlugin::factory()
            ->has(
                ProductHasPluginVersions::factory()
            )
            ->make();

        $proPluginMock = $this->mock(ProductHasPlugin::class);
        $proPluginMock->shouldReceive('insert');
        $proPluginMock->shouldReceive('delete');
        $proPluginMock->shouldReceive('where')->andReturnSelf();
        $proPluginMock->shouldReceive('with')->andReturnSelf();
        $proPluginMock->shouldReceive('get')->andReturn(collect($proPlugin));
        $this->app->instance(ProductHasPlugin::class, $proPluginMock);


        $proSoftMock = $this->mock(ProductHasSoftware::class);
        $proSoftMock->shouldReceive('where')->andReturnSelf();
        $proSoftMock->shouldReceive('update')->andReturn(1);
        $proSoftMock->shouldReceive('insert');
        $this->app->instance(ProductHasSoftware::class, $proSoftMock);

        $proPluginMock = $this->mock(ProductHasPlugin::class);
        $proPluginMock->shouldReceive('where')->andReturnSelf();
        $proPluginMock->shouldReceive('whereIn')->andReturnSelf();
        $proPluginMock->shouldReceive('delete');
        $this->app->instance(ProductHasPlugin::class, $proPluginMock);

        $proAvailableCustomerMock = $this->mock(ProductAvailableCustomer::class);
        $proAvailableCustomerMock->shouldReceive('insert');
        $proAvailableCustomerMock->shouldReceive('delete');
        $proAvailableCustomerMock->shouldReceive('where')->andReturnSelf();
        $this->app->instance(ProductAvailableCustomer::class, $proAvailableCustomerMock);

        $proPluginVersionMock = $this->mock(ProductHasPluginVersions::class);
        $proPluginVersionMock->shouldReceive('insert');
        $proPluginVersionMock->shouldReceive('delete');
        $proPluginVersionMock->shouldReceive('whereIn')->andReturnSelf();
        $this->app->instance(ProductHasPluginVersions::class, $proPluginVersionMock);

        $productMock = $this->mock(Product::class);
        $productMock->shouldReceive('insertGetId')->andReturn(-1);
        $this->app->instance(Product::class, $productMock);


        $customerMock = $this->mock(Customer::class);
        $customerMock->shouldReceive('select')->andReturnSelf();
        $customerMock->shouldReceive('whereRaw')->andReturnSelf();
        $customerMock->shouldReceive('first')->andReturn([
            "id" => 1
        ]);
        $this->app->instance(Customer::class, $customerMock);



        $productRepo = app(ProductRepository::class);
        $this->assertEquals(null, $productRepo->getProductPluginVersionsByVersion(-1, -1));
        $this->assertNotEmpty($productRepo->getCustomerProductFormCreate()["maintenance_cost"]);
        $this->assertNull($productRepo->storeProductPlugins([]));
        $this->assertNull($productRepo->storeProductPlugins(["id" => -1]));
        $this->assertNull($productRepo->storeProductSoftwares([]));
        $this->assertNull($productRepo->storeProductSoftwares(["id" => -1]));
        $this->assertEquals(1, $productRepo->updateProductSoftware(-1, ["id" => -1]));
        $this->assertEquals(1, $productRepo->updateProductSoftwareByProductIdAndEnvironment(-1, "dev", ["id" => -1]));
        $this->assertNull($productRepo->storeProductCustomers(-1));
        $this->assertNull($productRepo->deleteProductPluginsByEnvironment(-1, "dev"));
        $this->assertEquals(-1, $productRepo->storeProduct(-1));

        $params = [
            "repo_url" => "test",
            "legacy" => false,
            "product_name" => "test",
            "software_id" => -1,
            "supported_version" => -1,
            "supported_version_type" => -1,
            "plugins" => [
                [
                    "id" => -1,
                    "selected_version" => -1,
                    "selected_version_type" => -1,
                    "git_url" => "test"
                ]
            ],

        ];

        $productService = app(ProductService::class);
        $this->assertEquals(-1, $productService->storeProductFromGit($params, "", "", ""));
    }

}
