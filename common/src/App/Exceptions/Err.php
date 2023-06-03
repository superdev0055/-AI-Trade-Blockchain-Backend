<?php

namespace LaravelCommon\App\Exceptions;

use Exception;

class Err extends Exception
{
    const BadRequest = ['message' => 'Bad Request', 'code' => 999];
    const VipNoRight = ['message' => 'Please upgrade your vip level', 'code' => 10004];

    /**
     * @param string $message
     * @param int $code
     * @return mixed
     * @throws Err
     */
    public static function Throw(string $message, int $code = 9999): mixed
    {
        throw new static($message, $code);
    }
}
