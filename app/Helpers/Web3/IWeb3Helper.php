<?php

namespace App\Helpers\Web3;

interface IWeb3Helper
{
    public function Send(string $toAddress, float $amount, ?string $fromAddress = null, ?string $fromPrivateKey = null, bool $debug = false): mixed;

    public function SendToken(string $contractAddress, string $toAddress, float $amount, ?string $fromAddress = null, ?string $fromPrivateKey = null, bool $debug = false): mixed;

    public function GetTransactionByHash(string $hash, bool $debug = false): ?HashDataModel;

    public function GetTransactionsByAddress(string $address, ?int $start = null, ?bool $debug = false): array;
}
