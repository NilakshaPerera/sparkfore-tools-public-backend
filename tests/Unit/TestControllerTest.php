<?php

namespace tests\Unit;

use App\Domain\Events\ServerRestartedEvent;
use App\Domain\Jobs\ProcessSyncInstallationStatus;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TestControllerTest extends TestCase
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



    public function test_testInstallationSync()
    {
        Bus::fake();

        $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/test/sync_installation');
        Bus::assertDispatched(ProcessSyncInstallationStatus::class);
    }

    public function test_ttestPusher()
    {
        Bus::fake();

        $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/test/soketi');
        Bus::assertDispatched(ServerRestartedEvent::class);
    }

    public function test_testLogs()
    {
        Bus::fake();

        $response = $this->withHeaders(['accept' => $this->apllicationJson])->get('/v1/test/logs');
        $response->assertStatus(200);
    }

}
