<?php

namespace App\Domain\Services\ServiceApi;

interface PrometheusApiServiceInterface
{
    public function getInstallations($query, $server);
}
