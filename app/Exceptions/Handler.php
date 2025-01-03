<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Constants\HttpStatusCodes;

class Handler extends ExceptionHandler
{
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
            $response = [];
            $status   = 400;
            if ($this->isHttpException($exception)) {
                $status                 = $exception->getStatusCode();
            }  
            $response['status_code']    = $status;
            $response['error']          = true;
            $response['message']        = $exception->getMessage() == "" ? HttpStatusCodes::getMessageForCode($status) : $exception->getMessage();
            $response['exception']      = get_class($exception);
            if(env("APP_ENV") != 'production') {
                $response['trace'] = $exception->getTrace();
            }
            return response()->json($response, $status);
    }

}
