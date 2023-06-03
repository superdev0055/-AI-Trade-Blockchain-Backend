<?php

namespace App\NewLogics;


use App\Enums\CoinNetworkEnum;
use App\Enums\Web3TransactionsStatusEnum;
use App\Helpers\Web3\ErcWeb3Helper;
use App\Helpers\Web3\TrcWeb3Helper;
use App\Models\Web3Transactions;
use Exception;
use IEXBase\TronAPI\Exception\TRC20Exception;
use IEXBase\TronAPI\Exception\TronException;
use LaravelCommon\App\Exceptions\Err;

class Web3Logics
{
    /**
     * @param Web3Transactions $web3
     * @return void
     * @throws Err
     */
    public static function SendCryptoToUser(Web3Transactions $web3): void
    {
        try {
            // 判断是否存在有错误的交易
            if ($web3->coin_symbol == 'ETH')
                self::SendEthToUser($web3);
//        if ($web3->coin_symbol == 'TRX')
//            self::SendTrxToUser($web3);
            elseif ($web3->coin_network == CoinNetworkEnum::ERC20->name)
                self::sendErc20TokensToUser($web3);
//        elseif ($web3->coin_network == CoinNetworkEnum::TRC20->name)
//            self::sendTrc20TokenToUser($web3);
            else
                return;
        } catch (Exception $exception) {
            $web3->status = Web3TransactionsStatusEnum::ERROR->name;
            $web3->message = $exception->getMessage();
            $web3->save();
            throw $exception;
        }
    }

    /**
     * @param Web3Transactions $web3
     * @return void
     * @throws Err
     */
    private static function SendEthToUser(Web3Transactions $web3): void
    {
        $helper = new ErcWeb3Helper();
        $hash = $helper->Send(
            $web3->to_address,
            $web3->coin_amount,
        );

        $web3->status = Web3TransactionsStatusEnum::PROCESSING->name;
        $web3->hash = $hash;
        $web3->save();
    }

    public static function HashIsExists(string $hash, $param, $param1, bool $true)
    {
        $exists = Web3Transactions::where('hash', $hash)->lockForUpdate()->exists();

    }

    /**
     * @param Web3Transactions $web3
     * @return void
     * @throws Err
     * @throws TronException
     */
    private static function SendTrxToUser(Web3Transactions $web3): void
    {
        $helper = new TrcWeb3Helper();
        $hash = $helper->Send(
            $web3->to_address,
            $web3->coin_amount,
        );

        $web3->status = Web3TransactionsStatusEnum::PROCESSING->name;
        $web3->hash = $hash;
        $web3->save();
    }

    /**
     * @param Web3Transactions $web3
     * @return void
     * @throws Err
     */
    private static function sendErc20TokensToUser(Web3Transactions $web3): void
    {
        $helper = new ErcWeb3Helper();
        $hash = $helper->SendToken(
            $web3->coin_address,
            $web3->to_address,
            $web3->coin_amount
        );

        $web3->hash = $hash;
        $web3->status = Web3TransactionsStatusEnum::PROCESSING->name;
        $web3->save();
    }

    /**
     * @param Web3Transactions $web3
     * @return void
     * @throws Err
     * @throws TronException
     * @throws TRC20Exception
     */
    private static function sendTrc20TokenToUser(Web3Transactions $web3): void
    {
        $helper = new TrcWeb3Helper();
        $hash = $helper->SendToken(
            $web3->coin_address,
            $web3->to_address,
            $web3->coin_amount
        );

        $web3->hash = $hash;
        $web3->status = Web3TransactionsStatusEnum::PROCESSING->name;
        $web3->save();
    }
}
