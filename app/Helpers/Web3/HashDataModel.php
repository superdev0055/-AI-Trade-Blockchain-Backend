<?php

namespace App\Helpers\Web3;

class HashDataModel
{
    public string $coin_network;
    public string $coin_symbol;
    public ?string $coin_address;

    public float $coin_amount;

    public ?string $owner_address;
    public string $from_address;
    public string $to_address;

    public string $method;

    public string $hash;
    public string $block_number;
    public string $timestamp;

    public string $raw_data;

    public bool $confirmed;
    public string $result;

    public function __construct(
        string  $coin_network,
        string  $coin_symbol,
        float   $coin_amount,

        string  $from_address,
        string  $to_address,

        string  $method,

        string  $hash,
        string  $block_number,
        string  $timestamp,

        string  $raw_data,

        bool    $confirmed,
        string  $result,

        ?string $coin_address = null,
        ?string $owner_address = null,
    )
    {
        $this->coin_network = $coin_network;
        $this->coin_symbol = $coin_symbol;
        $this->coin_amount = $coin_amount;

        $this->from_address = $from_address;
        $this->to_address = $to_address;

        $this->method = $method;

        $this->hash = $hash;
        $this->block_number = $block_number;
        $this->timestamp = $timestamp;

        $this->raw_data = $raw_data;

        $this->confirmed = $confirmed;
        $this->result = $result;

        $this->coin_address = $coin_address;
        $this->owner_address = $owner_address;
    }
}
