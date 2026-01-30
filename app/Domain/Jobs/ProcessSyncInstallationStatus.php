<?php

namespace App\Domain\Jobs;

use App\Domain\Repositories\Command\CommandRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Log;
use Illuminate\Support\Str;

class ProcessSyncInstallationStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $cleuraData;
    protected $digitaloceanData;
    protected $commandRepository;

    /**
     * Create a new job instance.
     */
    public function __construct($id, $cleuraData, $digitaloceanData, CommandRepositoryInterface $commandRepository)
    {
        $this->onQueue('default');
        $this->id = $id;
        $this->cleuraData = $cleuraData;
        $this->digitaloceanData = $digitaloceanData;
        $this->commandRepository = $commandRepository;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //$this->syncInstalationStatus($this->id, $this->cleuraData, $this->digitaloceanData);
    }


    /**
     * @return void
     */
    public function syncInstalationStatus($installationId, $cleuraData, $digitaloceanData)
    {
        ini_set('max_execution_time', 360);
        echo 'ID is ' . $installationId;
        $installation = $this->commandRepository->getInstallation($installationId);
        $installation->load("hosting.hostingProvider");
        echo 'Checking :' . $installation->url;

        $url = self::formatURL($installation->url);
        if ($url) {
            $installationData = null;
            if ($installation->hosting->hostingProvider->key == 'cleura') {
                $installationData = $this->getMatchingInstallationData(
                    $cleuraData,
                    $installation->hosting->hostingProvider->key,
                    $installation->url
                );
            } elseif ($installation->hosting->hostingProvider->key == 'digitalocean') {
                $installationData = $this->getMatchingInstallationData(
                    $digitaloceanData,
                    $installation->hosting->hostingProvider->key,
                    $installation->url
                );
            }

            $availableDiskSpace = $installationData ? round(
                ($installationData['value'][1]) / (1024 * 1024 * 1024),
                1
            ) : -1;

            $moodleVersion = $installation->software_version;
            // Curl get the URL
            try {
                $response = Http::connectTimeout(2)
                    ->timeout(3)
                    ->head($url);
                $status = $response->ok() ? STATUS_ONLINE : STATUS_OFFLINE;
                $statusCode = $response->status();
                $moodleVersion = $this->getMoodelVersion($installation->url, $moodleVersion);
            } catch (\Throwable $e) {
                Log::info("Installation $installation->url error checking status", [$e->getMessage()]);
                $status = STATUS_OFFLINE;
                $statusCode = 500;
            }

            $installation->update([
                'status' => $status,
                'software_version' => $moodleVersion,
                'available_disk_space' => $availableDiskSpace,
                'status_code' => $statusCode,
                'updated_at' => Carbon::now()->toDateTimeString()
            ]);
        }
    }

    private function getMoodelVersion($url, $moodleVersion)
    {
        try {
            $content = file_get_contents(config('constants.HTTPS') . $url . '/lms_version.txt');
            $pattern = '/# Core files\s*(Moodle:[^\r\n]*)/si';

            // Using preg_match to extract the text between 'Moodle:' and 'CorePatches:'
            if (preg_match($pattern, $content, $matches)) {
                // $matches[1] will contain the text between 'Moodle:' and 'CorePatches:'
                $moodleText = $matches[1];
                $moodleText = str_replace('Moodle:', '', $moodleText);

                // Optionally, remove any special characters (except alphanumeric and dots)
                $moodleVersion = preg_replace('/[^a-zA-Z0-9.\s]/', '', $moodleText);
            }
        } catch (\Throwable $e) {
            Log::error("Error getting moodle version for $url", [$e->getMessage()]);
        }

        return trim($moodleVersion);
    }

    /**
     * @param $url
     * @return false|Application|UrlGenerator|\Illuminate\Foundation\Application|string
     */
    public static function formatURL($url)
    {

        // If the URL doesn't have a scheme (http:// or https://), add it
        if (!Str::startsWith($url, ['http://', config('constants.HTTPS')])) {
            $url = config('constants.HTTPS') . $url;
        }

        // Check if the URL is valid
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // Use the Laravel url helper to format the URL
        return url($url);
    }

    private function getMatchingInstallationData($installationsData, $hostingProvider, $url)
    {

        $resultList = $installationsData['data']['result'];
        $filteredResults = null;
        if ($hostingProvider == 'cleura') {
            $filteredResults = array_filter($resultList, function ($result) use ($url) {
                $site = isset($result['metric']['site']) ? $result['metric']['site'] : null;
                return $site !== null && $site == str_replace(".", "-", $url);
            });
        } else {
            $filteredResults = array_filter($resultList, function ($result) use ($url) {
                return $result['metric']['instance'] == $url;
            });
        }

        if ($filteredResults) {
            $filteredResults = reset($filteredResults);
        }

        return $filteredResults;
    }
}
