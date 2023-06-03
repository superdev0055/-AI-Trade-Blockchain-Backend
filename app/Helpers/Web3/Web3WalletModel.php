<?php

namespace App\Helpers\Web3;

class Web3WalletModel
{
    public string $address;
    public ?string $privateKey;

    public function __construct(string $address, ?string $privateKey)
    {
        $this->address = $address;
        $this->privateKey = $privateKey;
    }
}
