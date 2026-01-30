<?php

namespace tests\Unit;

use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class LogControllerTest extends TestCase
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
        $response = $this->withHeaders(['accept'=> $this->apllicationJson])->get('/v1/log/list');

        $response->assertStatus(200);
    }

}
