<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Domain\Models\User;
use App\Domain\Models\Module;
use App\Domain\Models\Permission;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $apllicationJson = 'application/json';

    public function setUp(): void
    {

        parent::setUp();
        $this->user = User::find(1);
        if (!$this->user) {
            $this->user = User::factory()->make();
        }
        Passport::actingAs($this->user);
    }

    public function test_getRolePermissions(): void
    {

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/accesscontrol/getrolepermissions');

        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('status', $data);
        $this->assertEquals("OK", $data['status']);
    }

    public function test_updateRolePermission(): void
    {

        $postData = [
            "roleperms" => [
                "UNIT_TEST_ROLE" => "UNIT_TEST_PERMISSION"
            ]
        ];
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/accesscontrol/updaterolepermission', $postData);

        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function test_readPermission(): void
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/accesscontrol/readpermission');

        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function test_createPermission(): void
    {

        $postData = [
            "module" => 1,
            "name" => "UNITTEST",
            "action" => "read",
            "description" => "UNITTEST_DESCRIPTION"
        ];
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/accesscontrol/createpermission', $postData);

        $response->assertStatus(200);
    }

    public function test_readRole(): void
    {

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/accesscontrol/readrole');
        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function test_readmodule(): void
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/accesscontrol/readmodule');

        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function test_createModule(): void
    {

        $postData = [
            "name" => "UNITTEST"
        ];
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/accesscontrol/createmodule', $postData);
        $response->assertStatus(200);

        $data = $response->decodeResponseJson();


        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));

    }

    public function test_updatemodule(): void
    {
        $model = Module::where("name", "UNIT TEST MODULE")->first();
        if (!$model) {
            $model = Module::factory()->create();

        }
        $this->assertModelExists($model);


        $postData = [
            "name" => "UNITTEST_UPDATED"
        ];
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/accesscontrol/updatemodule/' . $model->id, $postData);

        $response->assertStatus(200);

    }

    public function test_updatePermission(): void
    {
        $module = Module::factory()->create([
            "id" => 1
        ]);
        $this->assertModelExists($module);

        $model = Permission::factory()->create([
            "module_id" => $module->id,
            "id" => $module->id,
            'codename' => "test",
            "name" => "UNITTEST",
            "action" => "read",
            "description" => "UNITTEST"
        ]);





        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request, $model) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('permission', $model);
            return $route;
        });


        $postData = [
            "module" => $module->id,
            "name" => "UNITTEST",
            "action" => "read",
            "description" => "UNITTEST_UPDATED",
            'roleperms' => [
                1 => [2, 3],
                2 => [3]
            ]
        ];

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/accesscontrol/updatepermission/' . $model->id, $postData);

        $response->assertStatus(200);

    }
}
