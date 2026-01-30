<?php

namespace App\Application\Controllers;

use App\Application\Requests\AnsibleCallbackRequest;
use Illuminate\Http\Request;
use Log;

class AlertProcessorController extends AppController
{
    public function ansibleCallback(AnsibleCallbackRequest $request)
    {
        Log::info("Ansible callback received", [$request->all(), $request->ip(), $request->get("message")]);
        return $this->sendResponse(
            $this->appService::alertProcessor()->ansibleCallback($request->all())
        );
    }
    public function openAICallback(Request $request)
    {
        Log::info("OpenAI callback received", [$request->all(), $request->ip(), $request->get("message")]);
        return $this->sendResponse(
            $this->appService::alertProcessor()->openAICallback($request->all())
        );
    }
}
