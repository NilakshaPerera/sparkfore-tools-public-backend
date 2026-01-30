<?php

namespace tests\Unit;

use App\Domain\Models\GitVersionType;
use App\Domain\Models\Software;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;

class SoftwareControllerTest extends TestCase
{

    use RefreshDatabase;
    protected $user;
    protected $apllicationJson = 'application/json';

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
        Passport::actingAs($this->user);
        GitVersionType::factory()->make();
    }

    public function testListSoftware()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/software/list?page=1&per_page=10&sort_by=id&sort_desc=asc');

        // Assert

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function testGetFormCreate()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/software/form_create');

        // Assert

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function testGetSoftwareVersions()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/software/versions?git_url=https://git.autotech.se/LMS-Mirror/moodle&git_version_type_id=1');

        // Assert

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));
    }

    public function testStoreSoftware()
    {
        $postData = [
            'name' => 'Test Moodle',
            'git_url' => 'https://git.autotech.se/LMS-Mirror/test-moodle',
            'git_version_type_id' => 1,
            'version_supported' => 'master'
        ];

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/software/store', $postData);

        // Assert

        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
    }

    public function testEditSoftware()
    {
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->get('/v1/software/edit/1');
        // Assert
        $response->assertStatus(200);
        $data = $response->decodeResponseJson();
        $this->assertIsObject($data);
    }

    public function test_updateSoftware()
    {

        $postData = [
            "id" => -1,
            'name' => 'Test Moodle',
            'git_url' => 'https://git.autotech.se/LMS-Mirror/test-moodle-WRONG',
            'git_version_type_id' => 1,
            'version_supported' => 'master'
        ];

        // Simulate route parameter binding
        $request = app('request');
        $request->setRouteResolver(function () use ($request) {
            $route = Route::getRoutes()->match($request);
            $route->setParameter('id', -1);
            return $route;
        });

        $softwareMock = $this->mock(Software::class, function (MockInterface $mock) use ($postData) {
            $mock->shouldReceive('where')
                ->andReturnSelf();

            $mock->shouldReceive('update')->andReturn((object) $postData);

        });
        $this->app->instance(Software::class, $softwareMock);

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/software/update/-1', $postData);

        // Assert
        $response->assertStatus(200);
    }
}
