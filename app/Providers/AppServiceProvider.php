<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelCommon\App\Helpers\MySQLHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {

    }

    /**
     * @return void
     */
    public function boot(): void
    {
        MySQLHelper::Schema();
    }
}
