<?php

namespace tests\Feature\User;

use App\Domain\Models\Customer;
use App\Domain\Models\Role;
use App\Domain\Models\User;
use App\Domain\Services\User\UserServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testUserRouteRequiresAuthentication()
    {
        $response = $this->withHeaders(['accept' => 'application/json'])->get('/v1/profile');
        $response->assertStatus(401);
    }


    public function testUserProfileRetrieval()
    {
        $user = User::factory()->make();

        Passport::actingAs($user);
        $response = $this->get('/v1/profile');

        $response
            ->assertStatus(200) // Ensure 200 status. //Unauthorized response for unauthenticated access
            ->assertJsonFragment([
                "status_code" => 200,
                "status" => "OK",
                "message" => "OK"
            ]);
    }

    public function testUserCreation()
    {
        Role::factory()->make([
            'id' => 2,
            'name' => 'customer',
        ]);

        $customer = Customer::factory()->make();
        // Arrange
        $userService = app(UserServiceInterface::class); // Create an instance of UserService (or use a mock).

        // Act
        $response = $userService->createUser(new Request([
            'f_name' => 'John',
            'customer_id' => $customer->id,
            'l_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => bcrypt('passworkajdhaj$%$%$d123'),
            'lang_id' => 'en',
            'trial' => true,
            'role_id' => 1,
            'account_type_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        // Assert
        $this->assertIsObject($response);
        $this->assertInstanceOf(User::class, $response);
    }
}
