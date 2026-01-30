<?php

namespace App\Domain\Events;

use App\Domain\Models\RemoteJob;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProductBuildLogEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $productId;
    protected $branch;
    protected $remoteJob;

    /**
     * Create a new event instance.
     */
    public function __construct(RemoteJob $remoteJob)
    {
        $this->remoteJob = $remoteJob;
        $this->productId = $remoteJob->reference_id;
        $this->branch = $remoteJob->branch;
    }

    public function broadcastOn()
    {
        return new Channel('product.build.log');
    }

    public function broadcastQueue()
    {
        return 'high';
    }

    public function broadcastWith()
    {
        $this->remoteJob->load("productBuild");
        $data = [
            "remoteJob" => [
                "id" => $this->remoteJob->id,
                "callback_log_uri" => $this->remoteJob->callback_log_uri,
                "created_at" => $this->remoteJob->toArray()["created_at"],
                "updated_at" => $this->remoteJob->toArray()["updated_at"],
                "product_build" => [
                    "preparing_build_stage" => $this->remoteJob->productBuild->preparing_build_stage,
                    "building_application_stage" => $this->remoteJob->productBuild->building_application_stage,
                    "performing_tests_stage" => $this->remoteJob->productBuild->performing_tests_stage,
                    "analyzing_result_stage" => $this->remoteJob->productBuild->analyzing_result_stage,
                    "publishing_application_stage" => $this->remoteJob->productBuild->publishing_application_stage,
                    "application_url" => $this->remoteJob->productBuild->application_url,
                    "release_note" => $this->remoteJob->productBuild->release_note,
                ]
            ]
        ];
        Log::info("sending channel {$this->broadcastOn()}  Pusher", [$this->broadcastAs(), $data]);
        return $data;
    }

    public function broadcastAs()
    {
        if ($this->branch == "develop") {
            return "callback.$this->productId.dev";
        }
        return "callback.$this->productId.$this->branch";
    }
}
