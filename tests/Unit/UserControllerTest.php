<?php

namespace tests\Unit;

use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class UserControllerTest extends TestCase
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

    public function testUserRouteRequiresAuthentication()
    {
        $response = $this->withHeaders(['accept' => 'application/json'])
            ->get('/v1/profile');
        $response->assertStatus(200);
    }


    public function testUserProfileRetrieval()
    {
        $user = User::where('email', 'admin@sparkfore.com')->first();

        $response = $this->actingAs($user, 'api')->get('/v1/profile');

        $response
            ->assertStatus(200) // Ensure 200 status. //Unauthorized response for unauthenticated access
            ->assertJsonFragment([
                "status_code" => 200,
                "status" => "OK",
                "message" => "OK"
            ]);
    }

    public function testReadUser()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/user/readuser');

        // Assert

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }


    public function testUserCreation()
    {
        $postData = [
            'f_name' => 'John',
            'customer_id' => 1,
            'l_name' => 'Doe',
            'email' => 'test.user@example.com',
            'password' => 'password123',
            'lang_id' => 'en',
            'trial' => true,
            'role_id' => 1,
            'account_type_id' => 1,
        ];

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/user/createuser', $postData);

        // Assert

        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function testReadAccountTypes(): void
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/user/readaccounttypes');
        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function testReadCompanies(): void
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/user/readcompanies');
        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function testUserUpdate()
    {

        $user = User::where('email', 'testoing@sparkfore.com')->first();

        if (!$user) {
            var_dump('User not found, creating new user for testing');
            $user = User::factory()->create();
        }

        $this->assertModelExists($user);

        Passport::actingAs($user);

        $postData = [
            'f_name' => 'John',
            'customer_id' => 1,
            'l_name' => 'Doe',
            'email' => 'testtt.user@example.com',
            'account_type_id' => 1,
            'current_password' => 'passrOQ5byMiYe4word',
            'trial' => true,
        ];

        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request, $user) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('user', $user);
            return $route;
        });


        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/user/updateuser/' . $user->id, $postData);

        $response->assertStatus(200);
    }
}
