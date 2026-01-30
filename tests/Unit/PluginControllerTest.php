<?php

namespace tests\Unit;

use App\Domain\Models\Customer;
use App\Domain\Models\Plugin;
use App\Domain\Models\PluginAvailableCustomer;
use App\Domain\Models\PluginSupportsSoftware;
use App\Domain\Models\Software;
use App\Domain\Models\SoftwareVersion;
use App\Domain\Models\User;
use App\Domain\Repositories\Plugin\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use App\Domain\Services\ServiceApi\GiteaApiService;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PluginControllerTest extends TestCase
{

    use RefreshDatabase;
    protected $user;
    protected $apllicationJson = 'application/json';

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('email', 'admin@sparkfore.com')->first();
        Passport::actingAs($this->user);
    }

    public function testListPlugins()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/plugin/list?page=1&per_page=10&sort_by=id&sort_desc=asc');

        // Assert

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function testGetSoftwarePlugins()
    {


        $queryMock = Mockery::mock('Illuminate\Database\Eloquent\Builder');

        // Mock the 'where' method to return the query builder itself
        $queryMock->shouldReceive('where')
            ->once()
            ->andReturn($queryMock); // Return self for method chaining

        // Mock the 'first' method to return a custom object
        $queryMock->shouldReceive('first')
            ->once()
            ->andReturn((object) ['version_id' => '1234567890']);

        // Bind the mock query builder to the SoftwareVersion model
        $this->app->bind(SoftwareVersion::class, function ($app) use ($queryMock) {
            return $queryMock; // Return the mock query builder
        });

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get(uri: '/v1/plugin/get_software_plugins?
            software_id=1
            &filter=moodle
            &sort_by=id
            &sort_desc=asc
            &supported_version=1
            &version_id=100
            ');

        // Assert

        // $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        var_dump($data);
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function testGetFormCreate()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/plugin/form_create');

        // Assert

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function testStorePlugin()
    {
        $postData = [
            'name' => 'Test Moodle',
            'git_url' => 'https://git.autotech.se/LMS-Mirror/test-moodle',
            'git_version_type_id' => 1,
            'accessibility_type' => 'master',
            'access_token' => 'master',
            'availability' => 'master',
            'softwares' => [1, 2, 3],
            'customers' => [1, 2, 3],
        ];

        $pluginRepor = $this->mock(PluginRepository::class);
        $pluginRepor->shouldReceive("storePlugin")->andReturn(123);
        $pluginRepor->shouldReceive("storePluginSupportsSoftwares");
        $pluginRepor->shouldReceive("storePluginAvailableCustomers");
        $this->app->instance(PluginRepository::class, $pluginRepor);


        $giteaApiService = $this->mock(GiteaApiService::class);
        $giteaApiService->shouldReceive('createRepo')->andReturn(['id' => 123, 'name' => 'new_repo']);
        $giteaApiService->shouldReceive('getContents')->andReturn([
            ["name" => "unit_test"]
        ]);
        $giteaApiService->shouldReceive('getContent')->andReturn([
            "content" => base64_encode("
customer: test-baseline
registry_project: test
image_name: test-baseline
languages: en sv de fr nl it es pt ru zh_cn ja ar fi")
        ]);
        $giteaApiService->shouldReceive('migrateRepos')->andReturn("Unit-test content");


        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/plugin/store', $postData);

        // Assert

        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
    }


    public function test_updatePlugin(): void
    {

        Http::fake([
            "aapi-dev.sparkfore.com/o/token/" => Http::response([
                "access_token" => "UNIT TEST TOKEN"
            ], 200),
            'aapi-dev.sparkfore.com/api/v1/site/restart' => Http::response([
                "job_id" => 0,
                "cloud" => "iu8udh_gcldGOceOXWsvb",
                "location" => "P0KCQieX0B0PJ",
                "customer" => "hzzJ6dihIRY9ua0HMWrxCZzMKwG2wzSH10qaQlIgu",
                "site" => "string",
                "status" => "string"
            ], 200),
        ]);

        $postData = [
            'id' => -1,
            'name' => 'required',
            'git_url' => 'required',
            'git_version_type_id' => 1,
            'accessibility_type' => 'required',
            'availability' => 'required',
            'softwares' => [-1],
            'customers' => [-1],
        ];

        // Mock the PluginSupportsSoftware model
        $pluginSupportsSoftwareMock = $this->mock(PluginSupportsSoftware::class, function (MockInterface $mock) {
            // Mock the where method to expect specific parameters
            $mock->shouldReceive('where')
                ->with('plugin_id', -1)
                ->andReturnSelf(); // Return self for method chaining

            $mock->shouldReceive('where')
                ->with('software_id', -1)
                ->andReturnSelf(); // Return self for method chaining

            // Mock the exists method to return false
            $mock->shouldReceive('delete');
            $mock->shouldReceive('exists')
                ->andReturn(false);

            // Mock the insertGetId method to return a dummy ID
            $mock->shouldReceive('insertGetId')
                ->andReturn(123); // Dummy inserted ID
        });

        $pluginAvailableCustomerMock = $this->mock(PluginAvailableCustomer::class, function (MockInterface $mock) {

            $mock->shouldReceive('where')
                ->andReturnSelf();

            $mock->shouldReceive('delete');

            $mock->shouldReceive('insertGetId')
                ->andReturn(-1); // Dummy inserted ID

            $mock->shouldReceive('first')->andReturn((object) [
                "id" => -1
            ]);
        });

        // Replace the actual model binding with the mocked instance
        $this->app->instance(PluginSupportsSoftware::class, $pluginSupportsSoftwareMock);
        $this->app->instance(PluginAvailableCustomer::class, $pluginAvailableCustomerMock);

        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('env', "staging");
            $route->setParameter('id', 1);
            return $route;
        });

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/plugin/update/1', $postData);


        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
    }

    public function test_edit(): void
    {

        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('env', "staging");
            $route->setParameter('id', 1);
            return $route;
        });

        $pluginMock = $this->mock(Plugin::class, function (MockInterface $mock) {

            $mock->shouldReceive('with')
                ->andReturnSelf();

            $mock->shouldReceive('find')
                ->andReturn(
                    Plugin::factory()
                        ->has(Customer::factory())
                        ->has(Software::factory())
                        ->make([
                            'id' => 1,
                            'name' => 'example-plugin',
                            'type' => 'module',
                            'git_url' => 'https://github.com/example/example-plugin.git',
                            'version_supported' => '1.0.0',
                            'git_version_type_id' => 2, // Assuming 1 for branch, 2 for tag
                            'availability' => 'public', // Other possible value: 'private'
                            'softwares' => [1, 2], // Assuming software IDs
                            'customers' => [3, 4], // Assuming customer IDs
                            'price' => 100.00,
                        ])
                );
        });

        // Replace the actual model binding with the mocked instance
        $this->app->instance(Plugin::class, $pluginMock);

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/plugin/edit/1');


        $response->assertStatus(200);
    }

    public function test_getGitPluginName(): void
    {

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/plugin/get_git_plugin_name');

        $response->assertStatus(200);
    }
    public function test_getPluginVersions(): void
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
            ->get('/v1/plugin/1/versions');

        $response->assertStatus(200);
    }
}
