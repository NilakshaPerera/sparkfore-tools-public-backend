<?php

namespace App\Domain\Services\Remote;

use App\Domain\Exception\SparkforeException;
use App\Domain\Models\RemoteJob;
use App\Domain\Repositories\Log\LogRepositoryInterface;
use App\Domain\Repositories\Remote\RemoteAdminRepositoryInterface;
use App\Domain\Traits\PipelineTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RemoteAdminService implements RemoteAdminServiceInterface
{
    use PipelineTrait;
    private const STATUS_RECEIVED = 'RECEIVED';

    public function __construct(
        private RemoteCallHandlerInterface $remoteCallHandler,
        private RemoteAdminRepositoryInterface $remoteAdminRepository,
        private LogRepositoryInterface $logRepository
    ) {
    }

    public function restartServer($params)
    {
        $installation = $this->remoteAdminRepository->getInstallation($params['installation_id']);
        // Params
        $cloud = $installation->hosting->hostingCloudSettings->hostingProvider->key;
        $location = $installation->hosting->hosting_location;
        $customer = $installation->customerProduct->customer->slugified_name;

        // Create job
        $jobId = $this->remoteAdminRepository->createRemoteJob(
            REMOTE_JOB_TYPE_RESTART,
            $params['user_id'],
            $params['installation_id']
        );
        $params['environment'] = Str::lower($params['environment']);
        if($params['environment'] == "development") {
            $params['environment'] = "develop";
        }

        // Call Ansible API
        $response = $this->remoteCallHandler->redeployInstance(
            $jobId,
            $cloud,
            $location,
            trim($customer),
            $installation->url,
            $params['environment'],
            $params['version']
        );

        // GET success
        $success = $response->ok() && $response->object()->status == self::STATUS_RECEIVED;

        // Log creation
        $this->logRepository->createLog(
            json_encode($response->json()),
            $success ? 'success' : 'error',
            $params['user_id']
        );

        // Installation state update
        $this->remoteAdminRepository->updateInstallationState(
            $params['installation_id'],
            INSTALLATION_STATE_RESTARTING,
            INSTALATION_STATUS_OFFLINE,
            INSTALATION_STATUS_CODE_SERVICE_UNAVAILABLE
        );

        if ($success) {
            return [
                'success' => true,
                'message' => 'Restart in progress'
            ];
        }

        return [
            'success' => true,
            'message' => 'Restart in progress'
        ];
    }

    /**
     * Create a pipeline.
     *
     * @param array $params
     * @return array
     */
    public function createpipeline(array $params): array
    {
        // Create job
        $jobId = $this->remoteAdminRepository->createRemoteJob(
            REMOTE_JOB_TYPE_CREATE_PIPELINE,
            $params['user_id'],
            $params['package_id']
        );

        // Call Ansible API
        $response = $this->remoteCallHandler->createPipeline(
            $jobId,
            $params['customer'],
            $params['customer_slug'],
            $params['base_product'],
            $params['base_product_slug'],
            $params['name'],
            $params['name_slug'],
            $params['legacy']
        );
        // GET success
        $success = $response->ok() && $response->object()->status == 'RECEIVED';
        RemoteJob::where("id", $jobId)->update([
            "response" => json_encode($response->json())
        ]);
        // Log creation
        $this->logRepository->createLog(
            json_encode($response->json()),
            $success ? 'success' : 'error',
            $params['user_id']
        );

        if ($success) {
            return [
                'success' => true,
                'message' => 'Pipeline creation in progress'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create pipeline'
        ];
    }
    public function deletePipeline($params)
    {
        // Create job
        $jobId = $this->remoteAdminRepository->createRemoteJob(
            REMOTE_JOB_TYPE_DELETE_PIPELINE,
            $params['user_id'],
            $params['package_id']
        );

        // Call Ansible API
        $response = $this->remoteCallHandler->deletePipeline(
            $jobId,
            $params['customer'],
            $params['customer_slug'],
            $params['base_product'],
            $params['base_product_slug'],
            $params['name'],
            $params['name_slug']
        );
        // GET success
        $success = $response->ok() && $response->object()->status == 'RECEIVED';
        RemoteJob::where("id", $jobId)->update([
            "response" => json_encode($response->json())
        ]);
        // Log creation
        $this->logRepository->createLog(
            json_encode($response->json()),
            $success ? 'success' : 'error',
            $params['user_id']
        );

        if ($success) {
            return 'Pipeline deletion in progress';
        }

        throw new SparkforeException("Error deleteing pipeline on remote server", 422);
    }

    public function getRemoteLogs($product, $jobTypeId, $env, $page, $perPage, $sortBy, $sortDesc, $customerId = null)
    {
        return $this->remoteAdminRepository->getRemoteLogs(
            $product,
            $jobTypeId,
            $env,
            $page,
            $perPage,
            $sortBy,
            $sortDesc,
            $customerId
        );
    }

    public function downloadRemoteJobFile($remoteJob, $fileType)
    {
        $remoteJob = RemoteJob::find($remoteJob);
        if ($remoteJob) {
            if ($fileType == "log" && Storage::disk('pipeline-logs-s3')->exists($remoteJob->callback_log_uri)) {
                // Generate a temporary download link
                $preSignedUrl = Storage::disk('pipeline-logs-s3')
                    ->temporaryUrl($remoteJob->callback_log_uri, now()->addMinutes(5));

                return [
                    "download_url" => $preSignedUrl
                ];
            } elseif (
                $fileType == "release-note"
                && Storage::disk('pipeline-logs-s3')->exists($remoteJob->productBuild->release_note)
            ) {
                // Generate a temporary download link
                $preSignedUrl = Storage::disk('pipeline-logs-s3')
                    ->temporaryUrl($remoteJob->productBuild->release_note, now()->addMinutes(5));

                return [
                    "download_url" => $preSignedUrl
                ];
            }

            throw new SparkforeException("Error getting the log file");
        }
    }
}
