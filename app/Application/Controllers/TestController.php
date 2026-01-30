<?php

namespace App\Application\Controllers;

use App\Domain\Events\ProductBuildLogEvent;
use App\Domain\Models\Log;
use App\Domain\Models\RemoteJob;

class TestController extends AppController
{

    public function testPusher()
    {
        ProductBuildLogEvent::dispatch(RemoteJob::find(9));
        return 'Message Sent1';
    }

    public function testLogs()
    {
        return Log::orderBy('id', 'desc')->get();
    }
}
