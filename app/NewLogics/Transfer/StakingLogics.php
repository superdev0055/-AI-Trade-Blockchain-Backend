<?php

namespace App\NewLogics\Transfer;

use App\Enums\AssetLogsRemarkEnum;
use App\Enums\AssetsPendingStatusEnum;
use App\Enums\AssetsPendingTypeEnum;
use App\Enums\AssetsTypeEnum;
use App\Enums\CoinSymbolEnum;
use App\Enums\StakingTypeEnum;
use App\Enums\Web3TransactionsStatusEnum;
use App\Enums\Web3TransactionsTypeEnum;
use App\Helpers\Web3\HashDataModel;
use App\Models\Assets;
use App\Models\Coins;
use App\Models\Users;
use App\Models\Web3Transactions;
use App\NewLogics\FakeUserLogics;
use App\NewLogics\Pledges\StartPledgesLogics;
use App\NewLogics\SysMessageLogics;
use App\NewServices\AssetLogsServices;
use App\NewServices\AssetsServices;
use App\NewServices\BonusesServices;
use App\NewServices\CoinServices;
use App\NewServices\ConfigsServices;
use App\NewServices\PledgesServices;
use App\NewServices\UserBalanceSnapshotsServices;
use App\NewServices\UsersServices;
use App\NewServices\VipsServices;
use App\NewServices\Web3TransactionsServices;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

class StakingLogics
{
    /**
     * @param Users $user
     * @param Coins $coin
     * @param float $amount
     * @param string|null $type
     * @return void
     * @throws Err
     */
    public static function CanStake(Users $user, Coins $coin, float $amount, string $type = null): void
    {
        if (!$type)
            $type = StakingTypeEnum::FromWallet->name;

        // 是否在试用中
        $pledge = PledgesServices::GetByUser($user);
        if ($pledge && $pledge->is_trail)
            Err::Throw(__("You are in trail, please waiting for finish the trail"));

        // 最小金额
//        $price = CoinServices::GetPrice($coin->symbol) * $amount;
        $minStaking = ConfigsServices::Get('other')['min_staking'];
        if ($amount < $minStaking)
            Err::Throw(__("You need to stake at least ") . $minStaking);

        // 如果是从可提现
        if ($type == StakingTypeEnum::FromWithdrawable->name) {
            $asset = AssetsServices::getOrCreateWithdrawAsset($user);
            if ($asset->balance < $amount)
                Err::Throw(__("Your withdrawable balance is not enough"));
        }

    }

    /**
     * @param Users $user
     * @param Assets $withdrawable
     * @param float|null $amount
     * @param bool|null $throw
     * @return void
     * @throws Err
     */
    public static function WithdrawableToStaking(Users $user, Assets $withdrawable, ?float $amount = null, ?bool $throw = true): void
    {
        try {
            if (!$amount)
                $amount = $withdrawable->balance;

            $coin = CoinServices::GetUSDC();
            self::CanStake($user, $coin, $amount);

//            $staking = AssetsServices::getOrCreateStakingAsset($user);

            AssetLogsServices::OnChange($user, $withdrawable, -$amount, AssetLogsRemarkEnum::WithdrawToStaking->name);

            self::ProcessStaking($withdrawable, $user, $amount);

            $withdrawable->balance -= $amount;
            $withdrawable->save();
            Log::error("WithdrawableToStaking::ok", [$amount]);
        } catch (Exception $exception) {
            Log::error("WithdrawableToStaking::failed", [$exception->getMessage()]);
            if ($throw)
                throw $exception;
        }
    }

    /**
     * @param Users $user
     * @return array
     * @throws Err
     * @throws Exception
     */
    #[ArrayShape(['min' => "mixed", 'usdc_price' => "float", 'usdt_price' => "float"])]
    public static function preStaking(Users $user): array
    {
        $minStaking = ConfigsServices::Get('other')['min_staking'];
//        $usdc = CoinServices::GetUSDC();
//        $helper = new ErcWeb3Helper();
//        $usdcBalance = floatval($helper->getTokenBalance($usdc->address, $user->address)->toString() / pow(10, 6));
//        $usdcPrice = round($usdcBalance * CoinServices::GetPrice($usdc->symbol), 2);
        return [
            'min' => $minStaking,
//            'usdc_price' => CoinServices::GetPrice('usdc'),
//            'usdt_price' => CoinServices::GetPrice('usdt'),
//            'balance' => $usdcBalance,
//            'usd' => $usdcPrice,
        ];
    }

    /**
     * @param Users $user
     * @param Coins $coin
     * @param float $amount
     * @param string $hash
     * @return void
     * @throws Exception
     */
    public static function CreateStaking(Users $user, Coins $coin, float $amount, string $hash): void
    {
        CommonHelper::Trans(function () use ($user, $coin, $amount, $hash) {
            $exists = Web3Transactions::where('hash', $hash)->exists();
            if ($exists)
                Err::Throw(__("Transaction is exists"));

            // add pending
            $pending = Assets::create([
                'users_id' => $user->id, #
                'type' => AssetsTypeEnum::Pending->name, # 1:Staking / 2:WithdrawAble / 3:Pending
                'coins_id' => $coin->id, #
                'symbol' => $coin->symbol, #
                'icon' => $coin->icon, #
                'balance' => $amount, #
//                'web3_transactions_id' => $trans->id, #
//                'staking_ended_at' => '', #
                'pending_type' => AssetsPendingTypeEnum::Staking->name, # 1:Withdrawing
                'pending_status' => AssetsPendingStatusEnum::PROCESSING->name, # 1:Waiting / 2:Failed / 3:Success
            ]);

            // add web3 transactions
            $config = ConfigsServices::Get('address');
            $web3 = Web3Transactions::create([
                'users_id' => $user->id, #
                'coins_id' => $coin->id, #
                'operator_type' => Assets::class, #
                'operator_id' => $pending->id, #
                'type' => Web3TransactionsTypeEnum::Staking->name, # :Staking,Withdraw,Approve,TransferFrom,AirdropStaking,LoyaltyStaking
                'coin_network' => $coin->network, # 数币网络
                'coin_symbol' => $coin->symbol, # 数币
                'coin_address' => $coin->address, # 合约地址
                'coin_amount' => $amount, # 数币金额
//                'usd_price' => '', # 折合usd
                'from_address' => $user->address, # from地址
                'to_address' => $coin->symbol == 'usdc' ? $config['usdc_receive'] : $config['usdt_receive'], // Web3Wallets::getErcReceiveTokenWallet()->address, # to地址
//                'send_transaction' => '', # 发起交易信息
                'hash' => $hash, # 交易hash
//                'block_number' => '', #
//                'receipt' => '', # 交易源数据
//                'message' => '', # 回傳訊息
                'status' => Web3TransactionsStatusEnum::PROCESSING->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
            ]);

            $pending->web3_transactions_id = $web3->id;
            $pending->save();
        });
    }

    /**
     * web3回调
     * @param Web3Transactions $web3
     * @param HashDataModel $hashData
     * @return void
     * @throws Err
     */
    public static function StakingCallback(Web3Transactions $web3, HashDataModel $hashData): void
    {
        try {
            DB::beginTransaction();

            // from_address
            if (strtolower($web3->from_address) != strtolower($hashData->from_address))
                Err::Throw(__("From address is wrong."));

            // to_address
            if (strtolower($web3->to_address) != strtolower($hashData->to_address))
                Err::Throw(__("To address is wrong."));

            // coin_address
            if ($web3->coin_symbol != strtolower(CoinSymbolEnum::ETH->name) && $web3->coin_symbol != strtolower(CoinSymbolEnum::TRX->name))
                if (strtoupper($web3->coin_address) != strtoupper($hashData->coin_address))
                    Err::Throw(__("Coin address is wrong."));

            // update web3
            $web3->coin_amount = $hashData->coin_amount;
            $web3->block_number = $hashData->block_number;
            $web3->receipt = $hashData->raw_data;
            $web3->status = Web3TransactionsStatusEnum::SUCCESS->name;
            $web3->save();

            // update pending
            $pending = AssetsServices::GetById($web3->operator_id);
            $pending->balance = $hashData->coin_amount;
            $pending->pending_status = AssetsPendingStatusEnum::SUCCESS->name;
            $pending->save();

            $user = UsersServices::GetUserById($pending->users_id);
            self::ProcessStaking($pending, $user);

            // refresh balance
            UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);

            // bonus
            BonusesServices::CreateByReferrals($user);

            Log::info("StakingServices::StakingCallback() Success");
            DB::commit();
        } catch (Exception $exception) {
            Log::error("StakingServices::StakingCallback(web3) Error:::", [$web3->toArray()]);
            DB::rollBack();
            $web3->block_number = $hashData->block_number;
            $web3->receipt = $hashData->raw_data;
            $web3->message = $exception->getMessage();
            $web3->status = Web3TransactionsStatusEnum::ERROR->name;
            $web3->save();
            throw $exception;
        }
    }

    /**
     * 处理新质押
     * @param Assets $pending
     * @param Users $user
     * @param float|null $amount
     * @return void
     * @throws Err
     * @throws Exception
     */
    public static function ProcessStaking(Assets $pending, Users $user, float $amount = null): void
    {
        if (!$amount)
            $amount = $pending->balance;

        $coin = CoinServices::GetCoin($pending->symbol);
        $usdcPrice = CoinServices::GetTokenUsdcPrice($coin, $amount); // 获得质押的token转化到usdc的数量
        $staking = AssetsServices::getOrCreateStakingAsset($user);
        $pledge = PledgesServices::GetByUser($user);
        $now = now()->addDays($user->duration)->startOfDay()->addHours(18)->toDateTimeString();

        // update user balance and snapshot
        $user->total_staking_amount += $usdcPrice;
        if (!$user->can_trail_bonus) {
            $user->can_trail_bonus = true;
            $user->trailed_at = now()->toDateTimeString();
        }

        // first staking time
        if (!$user->first_staking_time)
            $user->first_staking_time = now()->toDateTimeString();
        $user->save();

        // add staking
        AssetLogsServices::OnChange($user, $staking, $usdcPrice, AssetLogsRemarkEnum::Staking->name);
        $staking->balance += $usdcPrice;
        $staking->staking_ended_at = $now;
        $staking->save();

        // update vip
        $vip = VipsServices::UpdateUserVip($user);

        // update level
        $isFakeUser = FakeUserLogics::IsFakeUser($user);
        if ($isFakeUser) {
            $user->leverage = $vip->leveraged_investment;
            $user->save();
        }

        if ($pledge) {
            // update pledge
            $pledge->staking += $usdcPrice;
            $pledge->ended_at = $now;
            $pledge->next_round_is_1 = true;    // next round is 1
            $pledge->save();
        } else {
            // create pledge
            StartPledgesLogics::StartCommon($user, $usdcPrice);
        }

        // send message
        SysMessageLogics::Staking($user, $amount);
    }

    /**
     * @param Users $user
     * @return void
     */
    public static function Clean(Users $user): void
    {
        $staking = AssetsServices::getOrCreateStakingAsset($user);
        $staking->balance = 0;
        $staking->save();
    }

    /**
     * @param Users $user
     * @param Coins $usdc
     * @param float $amount
     * @return void
     * @throws Err
     * @throws Exception
     */
    public static function FakeStaking(Users $user, Coins $usdc, float $amount): void
    {
        $pending = AssetsServices::CreatePendingByFakeStaking($user, $usdc, $amount);
        $web3 = Web3TransactionsServices::CreateByFakeStaking($user, $pending, $usdc);
        $pending->web3_transactions_id = $web3->id;
        $pending->save();

        self::ProcessStaking($pending, $user);

        // refresh balance
        UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);

        // bonus
        BonusesServices::CreateByReferrals($user);
    }
}
