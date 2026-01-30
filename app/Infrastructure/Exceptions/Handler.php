<?php

namespace App\Infrastructure\Exceptions;

use App\Domain\Exception\SparkforeException;
use App\Domain\Traits\HttpResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Laravel\Passport\Exceptions\OAuthServerException;
use Throwable;
use Log;

class Handler extends ExceptionHandler
{
    use HttpResponse;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    public function render($request, Throwable $exception)
    {
        //Exception handler function for API
        // If the app is in debug mode
        if ($request->wantsJson()) {
            //Unauthenticated
            if ($exception instanceof AuthenticationException || $exception instanceof OAuthServerException) {
                return $this->sendErrorResponse(null, $exception->getMessage(), 401);
            }

            $code = $exception->getCode() ? $exception->getCode() : 500;

            if (config('app.debug')) {
                Log::error('Exception occurred 1', [
                    'message' => $exception->getMessage(),
                    'stack_trace' => $exception->getTraceAsString()
                ]);
                $response = $exception->getMessage();
                $debug = $exception->getTraceAsString();

                return $this->sendErrorResponse(
                    $debug,
                    $response,
                    $code
                );
            } else {
                Log::error('Exception occurred 2', [
                    'message' => $exception->getMessage(),
                    'stack_trace' => $exception->getTraceAsString()
                ]);

                $message = "Something went wrong, Please try again later.";

                if ($exception instanceof SparkforeException) {
                    return $this->sendErrorResponse([], $exception->getMessage(), $exception->getCode());
                }

                // Default
                return $this->sendErrorResponse([], $message, $code, []);
            }
        }

        return parent::render($request, $exception);
    }
}
