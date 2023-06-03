<?php

namespace App\Helpers\Web3;

use App\Consts\Constants;

class BaseWeb3Helper
{
    /**
     * @param mixed $contractAddress
     * @return array|null
     */
    protected function getCoinByAddress(mixed $contractAddress): ?array
    {
        foreach (Constants::COINS as $coin) {
            if ($coin['address'] == $contractAddress)
                return $coin;
        }
        return null;
    }

    /**
     * @param string $symbol
     * @return array|null
     */
    protected function getCoinBySymbol(string $symbol): ?array
    {
        foreach (Constants::COINS as $coin) {
            if ($coin['symbol'] == $symbol)
                return $coin;
        }
        return null;
    }
}
