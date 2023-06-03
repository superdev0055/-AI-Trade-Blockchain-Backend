<?php

namespace App\Helpers\EtherScan;

use LaravelCommon\App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Http;
use Web3\Utils;

class EtherScanApi
{
    /**
     * @return string
     */
    public static function GetGasPrice(): string
    {
        $res = Http::withOptions(['proxy' => CommonHelper::GetProxies()])
            ->get('https://api.etherscan.io/api', [
                'module' => 'gastracker',
                'action' => 'gasoracle',
                'apikey' => config('web3.etherScanApiKey'),
            ])
            ->json();
        return '0x' . Utils::toWei($res['result']['FastGasPrice'], 'Gwei')->toHex();
    }

    /**
     * @param string $address
     * @param int $startBlock
     * @return array|mixed
     */
    public static function GetNormalTransactionsByAddress(string $address, int $startBlock = 0): mixed
    {
        return Http::withOptions(['proxy' => CommonHelper::GetProxies()])
            ->get('https://api.etherscan.io/api', [
                'module' => 'account',
                'action' => 'txlist',
                'address' => $address,
                'startblock' => $startBlock,
                'endblock' => 99999999,
                'page' => 1,
                'offset' => 100,
                'sort' => 'asc',
                'apikey' => config('web3.etherScanApiKey'),
            ])
            ->json()['result'];
    }

    /**
     * @param string $address
     * @param int $startBlock
     * @return array|mixed
     */
    public static function GetInternalTransactionsByAddress(string $address, int $startBlock = 0): mixed
    {
        return Http::withOptions(['proxy' => CommonHelper::GetProxies()])
            ->get('https://api.etherscan.io/api', [
                'module' => 'account',
                'action' => 'txlistinternal',
                'address' => $address,
                'startblock' => $startBlock,
                'endblock' => 99999999,
                'page' => 1,
                'offset' => 50,
                'sort' => 'desc',
                'apikey' => config('web3.etherScanApiKey'),
            ])
            ->json()['result'];
    }

    /**
     * @param string $address
     * @param int $startBlock
     * @return array|mixed
     */
    public static function GetErc20TransactionsByAddress(string $address, int $startBlock = 0): mixed
    {
        return Http::withOptions(['proxy' => CommonHelper::GetProxies()])
            ->get('https://api.etherscan.io/api', [
                'module' => 'account',
                'action' => 'tokentx',
                'address' => $address,
                'startblock' => $startBlock,
                'endblock' => 99999999,
                'page' => 1,
                'offset' => 50,
                'sort' => 'desc',
                'apikey' => config('web3.etherScanApiKey'),
            ])
            ->json()['result'];
    }
}
