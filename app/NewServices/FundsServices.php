<?php

namespace App\NewServices;

use App\Models\Funds;
use LaravelCommon\App\Exceptions\Err;

class FundsServices
{
    /**
     * @param int $id
     * @param bool $throw
     * @return Funds|null
     * @throws Err
     */
    public static function GetById(int $id, bool $throw = true): ?Funds
    {
        $model = Funds::find($id);
        if (!$model && $throw)
            Err::Throw(__("Funds not found"));
        return $model;
    }
}
