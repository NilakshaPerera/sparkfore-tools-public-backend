<?php

namespace App\Domain\Repositories\Log;

use App\Domain\Models\Log;
use Carbon\Carbon;

class LogRepository implements LogRepositoryInterface
{
    public function __construct(private Log $log)
    {}

    public function createLog($entry, $type, $userId = null)
    {
        $this->log::insert(array_filter([
            'entry' => $entry,
            'user' => $userId,
            'type' => $type,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]));
    }
}
