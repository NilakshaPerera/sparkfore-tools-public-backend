<?php

namespace tests\Unit;

use App\Domain\Models\Hosting;
use App\Domain\Models\HostingAvailableCustomer;
use App\Domain\Models\HostingCloudSetting;
use App\Domain\Models\HostingType;
use App\Domain\Models\User;
use App\Domain\Repositories\Hosting\HostingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;
use Mockery;
use Tests\TestCase;

class HostingControllerTest extends TestCase
{

    use RefreshDatabase;
    protected $user;
    protected $apllicationJson = 'application/json';

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
        Passport::actingAs($this->user);
    }



    public function test_listLogs()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/hosting/list');

        $response->assertStatus(200);
    }

    public function test_getFormCreate()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/hosting/form_create');

        $response->assertStatus(200);
    }

    public function test_storeHosting()
    {


        $params = [
            'name' => 'Test Hosting 1',
            'production_price_month' => 100.00,
            'staging_price_month' => 50.00,
            'yearly_price_increase' => 5.00,
            'description' => 'This is a test hosting service 1 .',
            'availability' => 'private',
            'config' => '{"key": "value"}',
            'hosting_type_id' => 1,
            'customers' => [1, 2, 3],
            'base_package_id' => 1,
            'hosting_provider_id' => 1,
            'backup_price_monthly' => 10.00,
            'moodle_url' => 'https://testmoodle1.com',
            'moodle_cron_url' => 'https://testmoodle.com/cron.php'
        ];

        // Mock the DB facade
        $mock = Mockery::mock('alias:' . DB::class);

        // Expect a call to insertGetId with $param and return a mocked ID
        $mock->shouldReceive('table->insertGetId')
            ->andReturn(123); // Replace with the mocked ID you want to return

        $mockHosting = \Mockery::mock(HostingAvailableCustomer::class);
        $mockHosting->shouldReceive('insertGetId')
            ->andReturn(123);
        $this->app->instance(HostingAvailableCustomer::class, $mockHosting);

        $mockHostingCloud = \Mockery::mock(HostingCloudSetting::class);
        $mockHostingCloud->shouldReceive('insertGetId')
            ->once()
            ->andReturn(123);
        $this->app->instance(HostingCloudSetting::class, $mockHostingCloud);

        $response = $this->withHeaders(['accept' => $this->apllicationJson])->post('/v1/hosting/store', $params);

        $response->assertStatus(200);
    }

    public function test_updateHosting()
    {


        $params = [
            'id' => 1,
            'name' => 'Test Hosting',
            'production_price_month' => 100.00,
            'staging_price_month' => 50.00,
            'yearly_price_increase' => 5.00,
            'description' => 'This is a test hosting service.',
            'availability' => 'private',
            'config' => '{"key": "value"}',
            'hosting_type_id' => 1,
            'customers' => [1, 2, 3],
            'base_package_id' => 1,
            'hosting_provider_id' => 1,
            'backup_price_monthly' => 10.00,
            'moodle_url' => 'https://testmoodle.com',
            'moodle_cron_url' => 'https://testmoodle.com/cron.php'
        ];

        // Mock the DB facade
        $mockDB = Mockery::mock('alias:' . DB::class);

        // Expect a call to insertGetId with $param and return a mocked ID
        $mockDB->shouldReceive('table->update')
            ->andReturn(123); // Replace with the mocked ID you want to return

        $mockDB->shouldReceive('table')
            ->with('hostings')
            ->andReturnSelf(); // Return self to mock chaining

        $mockDB->shouldReceive('where')
            ->andReturnSelf(); // Return self to mock chaining

        $mockHosting = \Mockery::mock(HostingAvailableCustomer::class);
        $mockHosting->shouldReceive('insertGetId')
            ->andReturn(123);
        $this->app->instance(HostingAvailableCustomer::class, $mockHosting);

        $mockHostingCloud = \Mockery::mock(HostingCloudSetting::class);

        // Expect a call to where() and update() with $params and return the number of affected
        // rows (simulating the update operation)
        $mockHostingCloud->shouldReceive('where')
            ->andReturnSelf();


        $hostingRepo = \Mockery::mock(HostingRepository::class);

        $hostingRepo->shouldReceive('updateHosting')
            ->once()
            ->andReturn(1);
        $hostingRepo->shouldReceive('getHostingTypeById')
            ->once()
            ->andReturn((object)[
                "key" => HOSTING_CLOUD
            ]);
        $hostingRepo->shouldReceive('purgeHostingAvailableCustomers')
            ->once();
        $hostingRepo->shouldReceive('storeHostingAvailableCustomers');
        $hostingRepo->shouldReceive('updateHostingCloudSettings')
            ->once();

        // Bind the mock instance into the container for dependency injection
        $this->app->instance(HostingCloudSetting::class, $mockHostingCloud);
        $this->app->instance(HostingRepository::class, $hostingRepo);

        $mockHostingType = \Mockery::mock(HostingType::class);
        $this->app->instance(HostingType::class, $mockHostingType);






        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('id', 1);
            return $route;
        });

        $response = $this->withHeaders(['accept' => $this->apllicationJson])->post('/v1/hosting/update/1', $params);

        $response->assertStatus(200);
    }


    public function test_edit()
    {
        $id = 1;


        $params = [
            'id' => 1,
            'name' => 'Test Hosting',
            'production_price_month' => 100.00,
            'staging_price_month' => 50.00,
            'yearly_price_increase' => 5.00,
            'description' => 'This is a test hosting service.',
            'availability' => 'private',
            'hosting_type_id' => 1,
            'moodle_url' => 'https://testmoodle.com',
            'hosting_cloud_settings' => [
                'hosting_provider_id' => 1,
                'base_package_id' => 1,
                'backup_price_monthly' => 10.00,
            ],
            'hosting_customers' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ],
        ];

        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('id', 1);
            return $route;
        });


        $mockHosting = \Mockery::mock(Hosting::class);

        // Define the ID you expect to retrieve

        $mockHosting->shouldReceive('with')
            ->with(['hostingCustomers', 'hostingCloudSettings'])
            ->andReturn($mockHosting);

        $mockHosting->shouldReceive('find')
            ->with($id)
            ->andReturn($mockHosting); // Return the mock instance itself

        $mockHosting->shouldReceive('toArray')
            ->andReturn($params);

        // Bind the mock instance into the container for dependency injection
        $mockHosting->shouldReceive('newInstance')->andReturn($mockHosting);

        $this->app->instance(Hosting::class, $mockHosting);


        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/hosting/edit/1');

        $response->assertStatus(200);
    }
}
