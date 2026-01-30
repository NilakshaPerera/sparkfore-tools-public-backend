<?php

namespace App\Application\Controllers\Public;

use App\Application\Controllers\AppController;
use App\Application\Requests\PublicCreateInstallationRequest;
use Log;
use App\Domain\Services\Installation\PublicInstallationService;

class NewInstallationController extends AppController
{
    public function viewPublicCreateInstallation()
    {
        return view('public.installation.create');
    }


    public function publicCreateInstallation(PublicCreateInstallationRequest $request)
    {
        try {
            $params = $request->validated();
            $installation = (new PublicInstallationService())->createPublicInstallation($params);
            $url = $installation->url;
            $parsedUrl = parse_url($url);
            if (!isset($parsedUrl['scheme'])) {
                // If $useHttps is true, prepend 'https://', otherwise 'http://'
                $url = 'https://'. $url;
            }

            return view('public.installation.in-progress', ['installationUrl' => $url]);
        } catch (\Throwable $th) {
            Log::error("Error creating public installation", [$th->getMessage()]);
            return redirect()->back()->with('error', 'An error occurred while creating the installation. Please try again later.');
        }

    }

    public function viewPublicCreateInstallationInProgress()
    {
        return view('public.installation.in-progress', ['installationUrl' => url('')]);
    }

}
