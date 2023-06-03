<?php

namespace LaravelCommon\App\Helpers;

use Closure;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommonHelper
{
    /**
     * @param Closure $closure
     * @return void
     */
    public static function Trans(Closure $closure): void
    {
        try {
            DB::beginTransaction();
            $closure();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @return ?string
     */
    public static function GetProxies(): ?string
    {
        return null;
        if (in_array(env('APP_ENV'), ['local', 'testing'])) {
            return '127.0.0.1:1087';
        } else {
            return null;
        }
    }

    public static function CacheHelper(string $method, string $tag, string $key, Closure $closure, int $minutes = 10)
    {
        return Cache::tags([$tag])->remember($key, 60 * $minutes, $closure);
    }
}
