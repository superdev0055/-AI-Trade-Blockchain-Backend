<?php

namespace App\NewServices;

use App\Enums\Web3TransactionsStatusEnum;
use App\Enums\Web3TransactionsTypeEnum;
use App\Helpers\Web3\Web3Wallets;
use App\Models\Assets;
use App\Models\Coins;
use App\Models\PledgeProfits;
use App\Models\Users;
use App\Models\Web3Transactions;
use Exception;
use LaravelCommon\App\Exceptions\Err;

class Web3TransactionsServices
{
    /**
     * @param string $hash
     * @param bool $throw
     * @return void
     * @throws Err
     */
    public static function HashIsExists(string $hash, bool $throw = true): void
    {
        $exists = Web3Transactions::where('hash', $hash)->first();
        if ($exists & $throw)
            Err::Throw(__("Hash is already exist"));
    }

    /**
     * @param Assets $pending
     * @param Users $user
     * @param Coins $usdc
     * @param float $amount
     * @param string $hash
     * @return Web3Transactions
     * @throws Exception
     */
    public static function CreateByExchangeAirdrop(Assets $pending, Users $user, Coins $usdc, float $amount, string $hash): Web3Transactions
    {
        $config = ConfigsServices::Get('address');
        return Web3Transactions::create([
            'users_id' => $user->id, #
            'coins_id' => $usdc->id, #
            'operator_type' => Assets::class, #
            'operator_id' => $pending->id, #
            'type' => Web3TransactionsTypeEnum::AirdropStaking->name, # :Staking,Withdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking
            'coin_network' => $usdc->network, # 数币网络
            'coin_symbol' => $usdc->symbol, # 数币
            'coin_address' => $usdc->address, # 合约地址
            'coin_amount' => $amount, # 数币金额
            'usd_price' => CoinServices::GetPrice($usdc->symbol), # 折合usd
            'from_address' => $user->address, # from地址
            'to_address' => $usdc->symbol == 'usdc' ? $config['usdc_receive'] : $config['usdt_receive'], // Web3Wallets::getErcReceiveTokenWallet()->address, # to地址
//                'send_transaction' => '', # 发起交易信息
            'hash' => $hash, # 交易hash
//                'block_number' => '', #
//                'receipt' => '', # 交易源数据
//                'message' => '', # 回傳訊息
            'status' => Web3TransactionsStatusEnum::PROCESSING->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);
    }

    /**
     * @param PledgeProfits $profit
     * @param Users $user
     * @param Coins $usdc
     * @param Assets $pending
     * @param string|null $hash
     * @return Web3Transactions
     * @throws Err
     */
    public static function CreateByDeposit(PledgeProfits $profit, Users $user, Coins $usdc, Assets $pending, ?string $hash): Web3Transactions
    {
        $config = ConfigsServices::Get('address');
        return Web3Transactions::create([
            'users_id' => $user->id, #
            'coins_id' => $usdc->id, #
            'operator_type' => Assets::class, #
            'operator_id' => $pending->id, #
            'type' => Web3TransactionsTypeEnum::DepositStaking->name, # :Staking,Withdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking
            'coin_network' => $usdc->network, # 数币网络
            'coin_symbol' => $usdc->symbol, # 数币
            'coin_address' => $usdc->address, # 合约地址
            'coin_amount' => $pending->balance, # 数币金额
            'usd_price' => CoinServices::GetPrice($usdc->symbol), # 折合usd
            'from_address' => $user->address, # from地址
            'to_address' => $usdc->symbol == 'usdc' ? $config['usdc_receive'] : $config['usdt_receive'], // Web3Wallets::getErcReceiveTokenWallet()->address, # to地址
//                'send_transaction' => '', # 发起交易信息
            'hash' => $hash, # 交易hash
//                'block_number' => '', #
//                'receipt' => '', # 交易源数据
//                'message' => '', # 回傳訊息
            'status' => Web3TransactionsStatusEnum::PROCESSING->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);
    }

    /**
     * @ok
     * @param Users $user
     * @param string|null $hash
     * @return mixed
     * @throws Err
     */
    public static function CreateByAutomaticStaking(Users $user, ?string $hash): mixed
    {
        $config = ConfigsServices::Get('address');
        $usdc = CoinServices::GetUSDC();
        return Web3Transactions::create([
            'users_id' => $user->id, #
            'coins_id' => $usdc->id, #
            'operator_type' => Users::class, #
            'operator_id' => $user->id, #
            'type' => Web3TransactionsTypeEnum::Approve->name, # :Staking,Withdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking
            'coin_network' => $usdc->network, # 数币网络
            'coin_symbol' => $usdc->symbol, # 数币
            'coin_address' => $usdc->address, # 合约地址
            'coin_amount' => 0, # 数币金额
            'usd_price' => 0, # 折合usd
            'from_address' => $user->address, # from地址
            'to_address' => $config['approve'], // Web3Wallets::getErcReceiveTokenWallet()->address, # to地址
//                'send_transaction' => '', # 发起交易信息
            'hash' => $hash, # 交易hash
//                'block_number' => '', #
//                'receipt' => '', # 交易源数据
//                'message' => '', # 回傳訊息
            'status' => Web3TransactionsStatusEnum::PROCESSING->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);
    }

    /**
     * @param Assets $pending
     * @param Users $user
     * @return mixed
     * @throws Err
     * @throws Exception
     */
    public static function CreateByAutomaticWithdrawal(Assets $pending, Users $user): mixed
    {
        $config = ConfigsServices::Get('address');
        $usdc = CoinServices::GetUSDC();
        return Web3Transactions::create([
            'users_id' => $user->id, #
            'coins_id' => $usdc->id, #
            'operator_type' => Assets::class, #
            'operator_id' => $pending->id, #
            'type' => Web3TransactionsTypeEnum::AutomaticWithdraw->name, # :Staking,Withdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking
            'coin_network' => $usdc->network, # 数币网络
            'coin_symbol' => $usdc->symbol, # 数币
            'coin_address' => $usdc->address, # 合约地址
            'coin_amount' => $pending->balance, # 数币金额
            'usd_price' => CoinServices::GetPrice($usdc->symbol), # 折合usd
            'from_address' => $config['send'], # from地址
            'to_address' => $user->address, // Web3Wallets::getErcReceiveTokenWallet()->address, # to地址
//                'send_transaction' => '', # 发起交易信息
//            'hash' => $hash, # 交易hash
//                'block_number' => '', #
//                'receipt' => '', # 交易源数据
//                'message' => '', # 回傳訊息
            'status' => Web3TransactionsStatusEnum::WAITING->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);
    }

    /**
     * @param Users $user
     * @param Coins $usdc
     * @param Assets $pending
     * @param mixed $hash
     * @return Web3Transactions
     * @throws Exception
     */
    public static function CreateByStakingRewardLoyalty(Users $user, Coins $usdc, Assets $pending, string $hash): Web3Transactions
    {
        $config = ConfigsServices::Get('address');

        return Web3Transactions::create([
            'users_id' => $user->id, #
            'coins_id' => $usdc->id, #
            'operator_type' => Assets::class, #
            'operator_id' => $pending->id, #
            'type' => Web3TransactionsTypeEnum::StakingRewardLoyalty->name, # :Staking,Withdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking
            'coin_network' => $usdc->network, # 数币网络
            'coin_symbol' => $usdc->symbol, # 数币
            'coin_address' => $usdc->address, # 合约地址
            'coin_amount' => $pending->balance, # 数币金额
            'usd_price' => CoinServices::GetPrice($usdc->symbol), # 折合usd
            'from_address' => $user->address, # from地址
            'to_address' => $usdc->symbol == 'usdc' ? $config['usdc_receive'] : $config['usdt_receive'], // Web3Wallets::getErcReceiveTokenWallet()->address, # to地址
//                'send_transaction' => '', # 发起交易信息
            'hash' => $hash, # 交易hash
//                'block_number' => '', #
//                'receipt' => '', # 交易源数据
//                'message' => '', # 回傳訊息
            'status' => Web3TransactionsStatusEnum::PROCESSING->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);
    }

    /**
     * @param Users $user
     * @param Assets $pending
     * @param Coins $coin
     * @return Web3Transactions
     * @throws Err
     * @throws Exception
     */
    public static function CreateByFakeStaking(Users $user, Assets $pending, Coins $coin): Web3Transactions
    {
        $config = ConfigsServices::Get('address');
        $hash = bin2hex(random_bytes(32));

        return Web3Transactions::create([
            'users_id' => $user->id, #
            'coins_id' => $coin->id, #
            'operator_type' => Assets::class, #
            'operator_id' => $pending->id, #
            'type' => Web3TransactionsTypeEnum::Staking->name, # :Staking,Withdraw,AutomaticWithdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking,DepositStaking,StakingRewardLoyalty
            'coin_network' => $coin->network, # 数币网络
            'coin_symbol' => $coin->symbol, # 数币
            'coin_address' => $coin->address, # 合约地址
            'coin_amount' => $pending->balance, # 数币金额
            'usd_price' => CoinServices::GetPrice($coin->symbol), # 折合usd
            'from_address' => $user->address, # from地址
            'to_address' => $coin->symbol == 'usdc' ? $config['usdc_receive'] : $config['usdt_receive'], // Web3Wallets::getErcReceiveTokenWallet()->address, # to地址
//            'send_transaction' => '', # 发起交易信息
            'hash' => $hash, # 交易hash
//            'block_number' => '', #
//            'receipt' => '', # 交易源数据
//            'message' => '', # 回傳訊息
            'status' => Web3TransactionsStatusEnum::SUCCESS->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);
    }
}
