<?php

namespace App\Domain\Services\Webhook;

use App\Domain\DataClasses\ReleaseNote\GenerateReleaseNoteDTO;
use App\Domain\Events\ProductBuildLogEvent;
use App\Domain\Events\ServerRestartedEvent;
use App\Domain\Models\Product;
use App\Domain\Models\ProductBuild;
use App\Domain\Models\RemoteJob;
use App\Domain\Repositories\Log\LogRepositoryInterface;
use App\Domain\Repositories\Webhook\AlertProcessorRepositoryInterface;
use App\Domain\Services\Email\PostmarkService;
use App\Domain\Services\Installation\PublicInstallationService;
use App\Domain\Services\Remote\RemoteAdminService;
use App\Domain\Services\Installation\InstallationService;
use App\Jobs\PluginSeeder\ReleaseNoteGenerateJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;
use Postmark\Models\PostmarkAttachment;

class AlertProcessorService implements AlertProcessorServiceInterface
{
    public function __construct(
        private AlertProcessorRepositoryInterface $alertProcessorRepository,
        private LogRepositoryInterface $logRepository
    ) {
    }

    private function restartComplete($params)
    {
        $status = $params['status'];
        if (strtolower($status) == 'success') {
            $this->alertProcessorRepository->updateInstallation(
                $params["installationId"],
                INSTALLATION_STATE_RUNNING,
                INSTALATION_STATUS_ONLINE
            );
        } else {
            $this->alertProcessorRepository->updateInstallation(
                $params["installationId"],
                INSTALLATION_STATE_STOPPED,
                INSTALATION_STATUS_OFFLINE
            );
        }

        ServerRestartedEvent::dispatch($params["installationId"]);
    }

    public function ansibleCallback($params)
    {
        $jobUpdate = [
            "callback_msg" => $params["message"],
            "callback_status" => $params["status"]
        ];
        if (isset($params["uri"])) {
            $jobUpdate["callback_log_uri"] = $params["uri"];
        }
        $remoteJob = RemoteJob::find($params['job_id']);
        $remoteJob->update($jobUpdate);

        if (REMOTE_JOB_TYPE_RESTART == $remoteJob->remoteJobType->key) {
            $params["installationId"] = $remoteJob->reference_id;
            $this->restartComplete($params);
        } elseif (REMOTE_JOB_TYPE_BUILD_PIPELINE == $remoteJob->remoteJobType->key) {
            $this->handleProductBuild($remoteJob, $params);
        } elseif (
            REMOTE_JOB_TYPE_DELETE_PIPELINE == $remoteJob->remoteJobType->key
            && $remoteJob->callback_status == "COMPLETE"
        ) {
            Product::find($remoteJob->reference_id)->update(
                [
                    "pipeline_deleted_at" => Carbon::now()
                ]
            );
        } elseif (REMOTE_JOB_TYPE_PUBLIC_INSTALLATION == $remoteJob->remoteJobType->key) {
            app(PublicInstallationService::class)->handleAnsibleCallback($remoteJob);
        } elseif (REMOTE_JOB_TYPE_DELETE_INSTALLATION == $remoteJob->remoteJobType->key) {
            app(InstallationService::class)->handleAnsibleCallback($remoteJob);
        }
        return 'Successfully acknowledged';
    }

    public function handleProductBuild(RemoteJob $remoteJob, $params)
    {
        $remoteJob->load("productBuild");
        $stages = config("sparkfore.aapi_stages");
        $stageFromMessage = Str::snake($params["message"]) . "_stage";

        if ($params["status"] == "COMPLETE") {
            $this->markAllBuildStagesComplete($remoteJob, $stages, $params);
        } elseif ($params["status"] == "ERROR") {

            $productBuild = $remoteJob->productBuild;

            foreach ($stages as $stage) {
                if ($productBuild[$stage] == "PROCESSING" || $productBuild[$stage] == "RECEIVED") {
                    $remoteJob->productBuild()->update([
                        $stage => "ERROR"
                    ]);
                }
            }
        } else {
            $passedStages = [];
            foreach ($stages as $stage) {
                if ($stage == $stageFromMessage) {
                    $passedStages[$stage] = "PROCESSING";
                    $remoteJob->productBuild()->update($passedStages);
                    break;
                } else {
                    $passedStages[$stage] = "COMPLETE";
                }
            }
        }

        ProductBuildLogEvent::dispatch($remoteJob);
    }

    private function markAllBuildStagesComplete(RemoteJob $remoteJob, $stages, $params)
    {
        Product::find($remoteJob->reference_id)->update(
            [
                "last_build" => Carbon::now()
            ]
        );

        $updateArray = [];
        foreach ($stages as $stage) { // marking all the stages as complete
            $updateArray[$stage] = "COMPLETE";
        }

        if (isset($params["registry_url"])) {
            $updateArray["application_url"] = explode(',', $params["registry_url"])[0];
        }
        if (isset($params["tag"])) {
            $updateArray["tag"] = $params["tag"];
        }

        if (isset($params["build_time"])) {
            $utcDateTime = Carbon::createFromTimestamp($params["build_time"] / 1000)->setTimezone('UTC');
            $updateArray["built_at"] = $utcDateTime;
        }

        $remoteJob->productBuild()->update($updateArray);
        $this->generateReleaseNote($remoteJob);
    }

    private function generateReleaseNote(RemoteJob $remoteJob)
    {
        $remoteJob->fresh();
        $remoteJob->load("productBuild");
        $releaseNoteDto = new GenerateReleaseNoteDTO(
            $this->getOldMoodleVersion($remoteJob),
            $remoteJob->productBuild->git_version,
            $remoteJob->id,
            $remoteJob->productBuild->changes,
            true,
            $remoteJob->productBuild->application_url,
            $remoteJob->callback_log_uri,
            $remoteJob->productBuild->built_at
        );
        $releaseNoteDto->setBuildTag($remoteJob->productBuild->tag);

        ReleaseNoteGenerateJob::dispatch($releaseNoteDto);
        Log::info("Release note generation job dispatched");
    }

    private function getOldMoodleVersion($remoteJob)
    {
        if (empty($remoteJob->productBuild->changes)) {
            return $remoteJob->productBuild->git_version;
        }

        if (is_string($remoteJob->productBuild->changes)) {
            $jsonChanges = json_decode($remoteJob->productBuild->changes, true);
        } else {
            $jsonChanges = $remoteJob->productBuild->changes;
        }

        if (
            !is_string($jsonChanges)
            && array_key_exists("softwareChanges", $jsonChanges)
            && !empty($jsonChanges["softwareChanges"])
            ) {
            return $jsonChanges["softwareChanges"][0]["from"];
        }

        return $remoteJob->productBuild->git_version;
    }

    public function openAICallback($params)
    {
        if (array_key_exists("release_note_url", $params) && !empty($params["release_note_url"])) {
            ProductBuild::where("remote_job_id", $params["build_id"])->update([
                "release_note" => $params["release_note_url"]
            ]);
            $remoteJob = RemoteJob::find($params["build_id"]);
            $remoteJob->product()->update([
                "plugin_changes" => ""
            ]);
            ProductBuildLogEvent::dispatch($remoteJob);
            $this->sendReleaseNoteEmail($remoteJob);
        }

    }

    private function handleServerReDeploy(RemoteJob $remoteJob, $params)
    {
        $remoteAdminService = app(RemoteAdminService::class);
        $remoteAdminService->restartServer($params);
    }

    private function sendReleaseNoteEmail(RemoteJob $remoteJob)
    {
        $responseJson = json_decode($remoteJob->response);
        $postMarkEmailService = new PostmarkService();
        $attachments = [];
        $attachments[] = PostmarkAttachment::fromBase64EncodedData(
            base64_encode(
                Storage::disk('pipeline-logs-s3')
                    ->get($remoteJob->productBuild->release_note)
            ),
            basename($remoteJob->productBuild->release_note),
            "application/pdf"
        );
        $email = "hello@sparkfore.com";
        $remoteJob->load("product.productCustomer.customer.customerUser");
        if (
            $remoteJob->product->availability == "private" &&
            isset($remoteJob->product->productCustomer) &&
            isset($remoteJob->product->productCustomer->customer) &&
            isset($remoteJob->product->productCustomer->customer->customerUser)
        ) {
            $email = $remoteJob->product->productCustomer->customer->customerUser->email;
        }
        Log::info("Sending release note email to: " . $email);

        return $postMarkEmailService->sendEmailWithTemplate(
            $email,
            38781511,
            [
                "name" => $responseJson->customer,
                "product_package" => $responseJson->base_product,
                "release_date" => Carbon::parse($remoteJob->productBuild->built_at)->format("d-m-Y"),
                "release_tag" => $remoteJob->productBuild->tag,
                "application_url" => $remoteJob->productBuild->application_url,
                "action_url" => "https://sparkfore.com",
                "product_name" => $responseJson->name,
            ],
            $attachments
        );
    }
}
