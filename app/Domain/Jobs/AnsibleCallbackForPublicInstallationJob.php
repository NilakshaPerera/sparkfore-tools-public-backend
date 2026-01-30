<?php

namespace App\Domain\Jobs;

use App\Domain\Models\PublicInstallation;
use App\Domain\Services\Email\PostmarkService;
use App\Domain\Services\ServiceApi\MoodleApiService;
use App\Domain\DataClasses\Moodle\MoodleUpdateUserDto;
use Crypt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class AnsibleCallbackForPublicInstallationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $remoteJobId;

    /**
     * Create a new job instance.
     */
    public function __construct($remoteJobId)
    {
        $this->remoteJobId = $remoteJobId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $publicInstallation = PublicInstallation::where('remote_job_id', $this->remoteJobId)->first();

        if (!$publicInstallation) {
            Log::info("Public installation not found for remote job: " . $this->remoteJobId);
            return;
        }
        $publicInstallation->load("user.customer");

        if (
            $publicInstallation->installation_status == "RECEIVED" &&
            isset($publicInstallation->user) &&
            isset($publicInstallation->user->customer)
        ) {
            $this->updateInstallaionUser($publicInstallation);
            $postMarkEmailService = new PostmarkService("75620aa0-bc1a-4a80-b1d1-4a4b8a8183e4");
            $postMarkEmailService->sendEmailWithTemplate( // admin email
                "hello@sparkfore.com",
                39001700,
                [
                    "name" => $publicInstallation->first_name,
                    "url" => config('constants.HTTPS') . $publicInstallation->site_name . '.' . SPARKFORE_DOMAIN, // URL TO INSTALLATION
                ],
            );

            $email = $publicInstallation->email;

            Log::info("Sending public installation created customer email to: " . $email);
            $postMarkEmailService->sendEmailWithTemplate(
                $email,
                39001715,
                [
                    "name" => $publicInstallation->first_name,  // NAME OF USER
                    "product_url" => config('constants.HTTPS') . $publicInstallation->site_name . '.' . SPARKFORE_DOMAIN, // URL TO INSTALLATION
                    "product_name" => "moodle-free", // NAME OF PRODUCT
                    "login_url" => config('constants.HTTPS') . $publicInstallation->site_name . '.' . SPARKFORE_DOMAIN, // URL TO INSTALLATION
                    "username" => $publicInstallation->email, // USERNAME
                    "password" => Crypt::decryptString($publicInstallation->password), // PASSWORD
                ],
            );
        } else {
            Log::info("Public installation required details are not available: " . $this->remoteJobId);
        }
    }

    private function updateInstallaionUser(PublicInstallation $publicInstallation)
    {
        $moodleService = app(MoodleApiService::class, [
            "baseUrl" => config('constants.HTTPS') . $publicInstallation->site_name . '.' . SPARKFORE_DOMAIN,
        ]);
        Log::info("Updating user in Moodle: " . $publicInstallation->email);
        $moodleService->coreUserUpdateUser(
            new MoodleUpdateUserDto(
                3,
                $publicInstallation->email,
                Crypt::decryptString($publicInstallation->password),
                $publicInstallation->first_name,
                $publicInstallation->last_name,
                $publicInstallation->email
            )
        );
    }
}
