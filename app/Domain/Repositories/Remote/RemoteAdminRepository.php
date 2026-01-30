<?php

namespace App\Domain\Repositories\Remote;

use App\Domain\Models\Installation;
use App\Domain\Models\ProductBuild;
use App\Domain\Models\RemoteJob;
use App\Domain\Models\RemoteJobType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Log;

class RemoteAdminRepository implements RemoteAdminRepositoryInterface
{
    public function __construct(
        private Installation $installation,
        private RemoteJob $remoteJob,
        private RemoteJobType $remoteJobType
    ) {
    }

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getInstallation($id)
    {
        return $this->installation::with([
            'customerProduct.customer',
            'customerProduct.product',
            'hosting.hostingCloudSettings.hostingProvider'
        ])->find($id);
    }

    /**
     * @param $jobType
     * @param $userId
     * @param $referenceId
     * @return mixed
     */
    public function createRemoteJob($jobType, $userId, $referenceId = null, $branch = null)
    {
        if ($branch == "dev") {
            $branch = "develop";
        }
        $jobType = $this->remoteJobType::where('key', $jobType)->first();
        $remoteJobId = $this->remoteJob::insertGetId([
            'remote_job_type_id' => $jobType->id,
            'reference_id' => $referenceId,
            'branch' => $branch,
            'created_by' => $userId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        if ($jobType->key == "build_pipeline") {
            $stages = config("sparkfore.aapi_stages");
            ProductBuild::create([
                "remote_job_id" => $remoteJobId,
                $stages[0] => "RECEIVED"
            ]);
        }


        return $remoteJobId;
    }

    /**
     * @param $jobId
     * @param $params
     * @return mixed
     */
    public function updateRemoteJob($jobId, $params)
    {
        return $this->remoteJob
            ->where('id', $jobId)
            ->update($params);
    }


    public function updateInstallationState($intallationId, $state, $status, $statusCode = '200')
    {
        return $this->installation->where('id', $intallationId)->update([
            'state' => $state,
            'status' => $status,
            'status_code' => $statusCode
        ]);
    }

    public function getRemoteLogs($product, $jobTypeId, $env, $page, $perPage, $sortBy, $sortDesc, $customerId = null)
    {
        if ($env == "dev") {
            $env = "develop";
        }
        $dbRemoteJob = $this->remoteJob
            ->with("productBuild:*")
            ->where('reference_id', $product)
            ->where('branch', $env)
            ->where('remote_job_type_id', $jobTypeId);

        $totalRecords = $dbRemoteJob->count();

        if($customerId) {
            $dbRemoteJob = $dbRemoteJob->whereHas('product', function ($query) use ($customerId) {
                $query->whereHas('productCustomer', function ($query) use ($customerId) {
                    $query->where('customer_id', $customerId);
                });
            });
        }

        if ($sortBy && $sortDesc) {
            $dbRemoteJob = $dbRemoteJob->orderBy($sortBy, $sortDesc);
        } else {
            $dbRemoteJob = $dbRemoteJob->orderBy('updated_at', 'desc');
        }

        if (!empty($page) && !empty($perPage)) {
            return $dbRemoteJob->paginate($perPage, ['*'], 'page', $page);
        }

        return [
            'data' => $dbRemoteJob->get(),
            'total' => $totalRecords
        ];
    }
}
