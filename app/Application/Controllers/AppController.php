<?php

namespace App\Application\Controllers;

use App\Domain\Services\AppServiceInterface;
use App\Domain\Traits\HttpResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Traits\Macroable;

class AppController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, Macroable, HttpResponse;

    protected $appService;

    public function __construct(AppServiceInterface $appService)
    {
        $this->appService = $appService;
    }
}
