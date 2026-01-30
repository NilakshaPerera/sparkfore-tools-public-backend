<?php

namespace tests\Unit;

use App\Domain\Models\Customer;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;

class CustomerControllerTest extends TestCase
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

    public function test_listCustomers()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/customer/list');

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
    }

    public function test_listCustomerProducts()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/customer/product/list');

        $response->assertStatus(200);
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
            $route->setParameter('id', -1);
            return $route;
        });

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/customer/edit/-1');


        $response->assertStatus(200);
    }

    public function test_updateCustomer(): void
    {

        $postData = [
            'id' => -1,
            'name' => 'test',
            'organization_no' => 'test',
            'invoice_type' => 'test',
            'invoice_address' => 'test',
            'invoice_email' => 'test@test.com',
            'invoice_reference' => 'test',
            'invoice_annotation' => 'test'
        ];

        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('env', "staging");
            $route->setParameter('id', -1);
            return $route;
        });

        $customerMock = $this->mock(Customer::class, function (MockInterface $mock) {

            $mock->shouldReceive('where')
                ->andReturnSelf();

            $mock->shouldReceive('update');
        });

        // Replace the actual model binding with the mocked instance
        $this->app->instance(Customer::class, $customerMock);

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/customer/update/-1', $postData);
        $response->assertStatus(200);
    }

    public function test_storeCustomer(): void
    {

        $postData = [
            'name' => 'test',
            'organization_no' => 'test',
            'invoice_type' => 'test',
            'invoice_address' => 'test',
            'invoice_email' => 'test@test.com',
            'invoice_reference' => 'test',
            'invoice_annotation' => 'test',
            'slugified_name' => 'unit_test'
        ];

        $customerMock = $this->mock(Customer::class, function (MockInterface $mock) {

            $mock->shouldReceive('insertGetId')
                ->andReturn(1);
        });

        // Replace the actual model binding with the mocked instance
        $this->app->instance(Customer::class, $customerMock);

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/customer/store', $postData);

        var_dump($response->getContent());
        $response->assertStatus(200);
    }


}
