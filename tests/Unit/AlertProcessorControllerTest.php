<?php

namespace Tests\Unit;

use App\Domain\Models\RemoteJob;
use App\Domain\Models\RemoteJobType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AlertProcessorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $apllicationJson = 'application/json';

    public function setUp(): void
    {

        parent::setUp();
        Passport::actingAsClient(
            Client::factory()->create()
        );
    }

    public function test_ansible_webhook(): void
    {

        $remoteJobType = RemoteJobType::firstOrCreate(
            ["id" => 1],
            RemoteJobType::factory()->make([
                'key' => REMOTE_JOB_TYPE_RESTART,
                'name' => 'Restart Installation Server'
            ])->toArray()
        );

        $remoteJob = RemoteJob::factory()->create([
            'remote_job_type_id' => $remoteJobType->id,
            "reference_id" => 1,
            "created_by" => 1
        ]);
        // invalid job id
        $postData = [
            "job_id" => $remoteJob->id,
            "product_id" => 1,
            "status" => "COMPLETE",
            "message" => "UNIT TEST MESSAGE"
        ];

        $response = $this->withHeaders(['accept' => $this->apllicationJson])
            ->post('/v1/webhook/ansible/callback', $postData);

        $response->assertStatus(200);
        $data = $response->decodeResponseJson();

        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("OK", $data['message']);
    }
}
