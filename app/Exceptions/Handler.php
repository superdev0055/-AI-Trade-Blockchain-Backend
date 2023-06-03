<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use LaravelCommon\App\Exceptions\ExceptionRender;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * @var array
     */
    protected $levels = [
        //
    ];

    /**
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * @var string[]
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        })->stop();
        $this->renderable(function (Throwable $e) {
            return ExceptionRender::Render($e);
        });
    }
}
