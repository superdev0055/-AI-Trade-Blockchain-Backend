<?php

namespace App\Helpers\Web3\Exceptions;

use Exception;
use Throwable;

class TransactionFailedException extends Exception
{
    public function __construct(string $message = "", int $code = 20000, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
