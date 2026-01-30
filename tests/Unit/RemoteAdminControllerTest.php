<?php

namespace tests\Unit;

use App\Domain\Models\Customer;
use App\Domain\Models\CustomerProduct;
use App\Domain\Models\Hosting;
use App\Domain\Models\HostingCloudSetting;
use App\Domain\Models\HostingProvider;
use App\Domain\Models\Installation;
use App\Domain\Models\Log;
use App\Domain\Models\RemoteJob;
use App\Domain\Models\RemoteJobType;
use App\Domain\Models\User;
use App\Domain\Services\Remote\RemoteAdminService;
use App\Domain\Services\Remote\RemoteCallHandler;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;

class RemoteAdminControllerTest extends TestCase
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

    public function test_restartComplete(): void
    {
        $installation = Installation::factory()
            ->has(
                CustomerProduct::factory()->has(
                    Customer::factory()
                )
            )
            ->has(
                Hosting::factory()->has(
                    HostingCloudSetting::factory()->has(
                        HostingProvider::factory()
                    )
                )
            )
            ->create();


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


        $postData = [
            "installation_id" => $installation->id,
            "environment" => "test",
            "version" => "43"
        ];
        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/remote/restart', $postData);

        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('result', $data);
        $this->assertIsArray($data['result']);
        $this->assertGreaterThan(0, count($data['result']));

    }


    public function test_createPipeline()
    {
        Http::fake([
            '/api/v1/pipeline/add' => Http::response([
                "job_id" => 0,
                "status" => 'RECEIVED'
            ], 200),
            '/api/v1/pipeline/build' => Http::response([
                "job_id" => 1,
                "status" => 'RECEIVED'
            ], 200),
        ]);

        $rmJobTypeMock = $this->mock(RemoteJobType::class);
        $rmJobTypeMock->shouldReceive('where')->andReturnSelf();
        $rmJobTypeMock->shouldReceive('first')->andReturn((object) [
            "id" => -1,
            "key" => "build_pipeline"
        ]);
        $this->app->instance(RemoteJobType::class, $rmJobTypeMock);

        $logMock = $this->mock(Log::class);
        $logMock->shouldReceive('insert');
        $this->app->instance(Log::class, $logMock);

        $rmJobMock = $this->mock(RemoteJob::class);
        $rmJobMock->shouldReceive('insertGetId')->andReturn(-1);
        $this->app->instance(RemoteJob::class, $rmJobMock);

        $rmHandler = app(RemoteCallHandler::class);
        $rmAdminService = app(RemoteAdminService::class);

        $this->assertEquals(
            0,
            $rmHandler->createPipeline(-1, -1, -1, -1, -1, -1, -1, -1)["job_id"]
        );
        $this->assertEquals(
            1,
            $rmHandler->buildPipeline(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1)["job_id"]
        );

        $this->assertEquals(
            "Pipeline creation in progress",
            $rmAdminService->createpipeline([
                "user_id" => -1,
                "package_id" => -1,
                "customer" => -1,
                "customer_slug" => -1,
                "base_product" => -1,
                "base_product_slug" => -1,
                "name" => -1,
                "name_slug" => -1,
                "legacy" => true,

            ])["message"]);

    }
}
