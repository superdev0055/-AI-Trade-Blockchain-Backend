<?php

namespace App\NewLogics;

use LaravelCommon\App\Exceptions\Err;
use Vinkla\Hashids\Facades\Hashids;

class CommonLogics
{
    /**
     * @param string $key
     * @param string $code
     * @return int
     * @throws Err
     */
    public static function GetHashId(string $key, string $code): int
    {
        $hash = Hashids::connection($key)->decode($code);
        if (count($hash) == 0)
            Err::Throw(__("The code is invalid"));
        return $hash[0];
    }

    /**
     * @param string $key
     * @param int $id
     * @return string
     */
    public static function GetHashCode(string $key, int $id): string
    {
        return Hashids::connection($key)->encode($id);
    }
}
