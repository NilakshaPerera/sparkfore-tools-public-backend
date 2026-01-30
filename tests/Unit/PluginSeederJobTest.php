<?php

namespace tests\Unit;

use App\Domain\Models\User;
use App\Jobs\PluginSeeder\PluginBranchesSeederJob;
use App\Jobs\PluginSeeder\PluginTagsSeederJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PluginSeederJobTest extends TestCase
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
        Bus::fake();

        PluginTagsSeederJob::dispatchSync("test", 1, "uniqueJob1");
        Bus::assertDispatched(PluginTagsSeederJob::class);

        PluginBranchesSeederJob::dispatchSync("test", 1, "uniqueJob2");
        Bus::assertDispatched(PluginBranchesSeederJob::class);
    }

}
