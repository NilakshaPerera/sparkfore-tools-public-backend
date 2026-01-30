<?php

namespace tests\Unit;

use App\Application\Controllers\InstallationController;
use App\Domain\Models\CustomerProduct;
use App\Domain\Models\Installation;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;
use Mockery\MockInterface;
use Tests\TestCase;

class InstallationControllerTest extends TestCase
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



    public function test_getFormCreate()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/installation/form_create');

        $response->assertStatus(200);
    }

    public function test_listInstallations()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/installation/list');

        $response->assertStatus(200);
    }

    public function test_validateDomain()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/installation/validate?domain=testing.com');
        $response->assertStatus(200);
    }

    public function test_getInstallation()
    {
        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('id', 1);
            return $route;
        });

        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/installation/1');
        $response->assertStatus(200);
    }


    public function test_getInstallationForManage()
    {
        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('id', 1);
            return $route;
        });

        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/installation/manage/1');

        $response->assertStatus(200);
    }

    public function test_storeInstallation()
    {

        $postData = [
            // Assuming DOMAIN_TYPE_STANDARD is a constant that has been defined elsewhere
            'domain_type' => DOMAIN_TYPE_STANDARD,
            'sub_domain' => 'example', // Example subdomain
            'domain' => 'example.com', // This value is used if domain_type is not DOMAIN_TYPE_STANDARD
            'hosting_provider' => 3, // Example hosting provider ID, affects the hostingType value
            'customer_id' => 123, // Example customer ID
            'product_id' => 456, // Example product ID
            'billing_terms' => true, // Example billing terms agreement
            'general_terms' => true, // Example general terms agreement
            'include_backup' => true, // Example backup inclusion
            'include_staging' => false, // Example staging package inclusion
            'hosting_package' => 789, // Example hosting package ID
        ];


        $installationMock = $this->mock(Installation::class, function (MockInterface $mock) {

            // Mock the insertGetId method to return a dummy ID
            $mock->shouldReceive('insertGetId')
                ->andReturn(123); // Dummy inserted ID
        });
        $customerProductMock = $this->mock(CustomerProduct::class, function (MockInterface $mock) {

            // Mock the insertGetId method to return a dummy ID
            $mock->shouldReceive('insertGetId')
                ->andReturn(123); // Dummy inserted ID

            $mock->shouldReceive('select')
                ->andReturnSelf();

            $mock->shouldReceive('where')
                ->andReturnSelf();

            $mock->shouldReceive('first')
                ->andReturn((object) [
                    'id' => 1,
                    'customer_id' => 123,
                    'domain_type' => 'example_domain_type',
                    'url' => 'https://example.com',
                    'billing_terms_agreement' => true,
                    'general_terms_agreement' => true,
                    'include_backup' => true,
                    'include_staging_package' => false,
                    'hosting_id' => 456,
                    'hosting_type_id' => 789,
                    'hosting_provider_id' => 1011,
                    'installation_target_type_id' => 1213
                ]);
        });

        // Replace the actual model binding with the mocked instance
        $this->app->instance(Installation::class, $installationMock);
        $this->app->instance(CustomerProduct::class, $customerProductMock);

        $response = $this->withHeaders(['accept' => $this->apllicationJson])->post('/v1/installation/store', $postData);
        $response->assertStatus(200);
    }

    public function test_editInstallation()
    {

        $postData = [
            // Assuming DOMAIN_TYPE_STANDARD is a constant that has been defined elsewhere
            'domain_type' => DOMAIN_TYPE_STANDARD,
            'sub_domain' => 'example', // Example subdomain
            'domain' => 'example.com', // This value is used if domain_type is not DOMAIN_TYPE_STANDARD
            'hosting_provider' => 3, // Example hosting provider ID, affects the hostingType value
            'customer_id' => 123, // Example customer ID
            'product_id' => 456, // Example product ID
            'billing_terms' => true, // Example billing terms agreement
            'general_terms' => true, // Example general terms agreement
            'include_backup' => true, // Example backup inclusion
            'include_staging' => false, // Example staging package inclusion
            'hosting_package' => 789, // Example hosting package ID,
            'id' => -1,
        ];



        $installationMock = $this->mock(Installation::class, function (MockInterface $mock) {

            // Mock the insertGetId method to return a dummy ID
            $mock->shouldReceive('insertGetId')
                ->andReturn(123); // Dummy inserted ID

            $mock->shouldReceive('where')
                ->andReturnSelf();

            $mock->shouldReceive('update');

        });

        // Replace the actual model binding with the mocked instance
        $this->app->instance(Installation::class, $installationMock);
        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('id', -1);
            return $route;
        });


        $response = $this->withHeaders(['accept' => $this->apllicationJson])->put('/v1/installation/-1', $postData);

        $response->assertStatus(200);
    }

    public function test_deleteInstallations()
    {

        $installationMock = $this->mock(Installation::class, function (MockInterface $mock) {
            $mock->shouldReceive('where')
                ->andReturnSelf();
            $mock->shouldReceive('delete');

        });
        $this->app->instance(Installation::class, $installationMock);

        $response = $this->withHeaders(['accept' => $this->apllicationJson])->delete('/v1/installation/delete');

        $response->assertStatus(200);
    }

    public function test_buildInstallation()
    {

        $installationMock = $this->mock(Installation::class, function (MockInterface $mock) {
            $mock->shouldReceive('where')
                ->andReturnSelf();
            $mock->shouldReceive('delete');

        });
        $this->app->instance(Installation::class, $installationMock);

        // Replace the actual model binding with the mocked instance
        $this->app->instance(Installation::class, $installationMock);
        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('id', -1);
            return $route;
        });

        $response = $this->withHeaders(['accept' => $this->apllicationJson])->post('/v1/installation/-1');

        $response->assertStatus(200);
    }




}
