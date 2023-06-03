<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use LaravelCommon\App\Exceptions\Err;

class Authenticate extends Middleware
{
    /**
     * @param $request
     * @throws Err
     */
    protected function redirectTo($request)
    {
        Err::Throw(__("User not login"), 10000);
//        if (! $request->expectsJson()) {
//            return route('login');
//        }
    }
}
