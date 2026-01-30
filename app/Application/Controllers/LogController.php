<?php

namespace App\Application\Controllers;

use App\Domain\Models\Log;
use Illuminate\Http\JsonResponse;

class LogController extends AppController
{

    /**
     * @return JsonResponse
     */
    public function listLogs()
    {
        return $this->sendResponse(Log::all()->toArray());
    }

}
