<?php

use LaravelCommon\App\Helpers\RouteHelper;
use LaravelCommon\App\Http\Controllers\DocsController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('/api/docs')->name('docs.')->group(function ($router) {
    if (config('app.debug')) {
        RouteHelper::New($router, DocsController::class);
    }
});
