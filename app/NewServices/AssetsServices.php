<?php

namespace App\NewServices;

use App\Enums\AssetLogsRemarkEnum;
use App\Enums\AssetsPendingStatusEnum;
use App\Enums\AssetsPendingTypeEnum;
use App\Enums\AssetsTypeEnum;
use App\Models\AssetLogs;
use App\Models\Assets;
use App\Models\Coins;
use App\Models\PledgeProfits;
use App\Models\UserBalanceSnapshots;
use App\Models\Users;
use App\Models\Web3Transactions;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

class AssetsServices
{

    /**
     * @param Users $user
     * @return array[]
     */
    #[ArrayShape(['header' => "array", 'data' => "array"])]
    public static function getAllBalanceSparkline(Users $user): array
    {
        $all = UserBalanceSnapshots::selectRaw('datetime, balance')
            ->where('users_id', $user->id);

        return [
            'header' => [
                'balance' => $user->total_balance,
                'rate' => $user->total_rate,
            ],
            'data' => [
                '1D' => (clone $all)->whereBetween('created_at', [now()->subDay()->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])->get()->toArray(),
                '1W' => (clone $all)->whereBetween('created_at', [now()->subDays(7)->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])->get()->toArray(),
                '1M' => (clone $all)->whereBetween('created_at', [now()->subDays(30)->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])->get()->toArray(),
                '1Y' => (clone $all)->whereBetween('created_at', [now()->subDays(365)->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])->get()->toArray(),
                'ALL' => (clone $all)->get()->toArray(),
            ]
        ];
    }

    /**
     * @param Users $user
     * @return array
     */
    #[ArrayShape(['staking' => "mixed", 'withdrawable' => "mixed", 'pending' => "mixed"])]
    public static function getAllAsserts(Users $user): array
    {
        $list = Assets::who($user)->where('balance', '>', 0)->descID();

        return [
            'staking' => (clone $list)->where('type', AssetsTypeEnum::Staking->name)->get()->toArray(),
            'withdrawable' => (clone $list)->where('type', AssetsTypeEnum::WithdrawAble->name)->get()->toArray(),
            'pending' => (clone $list)->where('type', AssetsTypeEnum::Pending->name)->get()->toArray(),
        ];
    }

    /**
     * 亏本金
     * @param Users $user
     * @param float $amount
     * @return void
     * todo need test
     */
    public static function LoseStaking(Users $user, float $amount): void
    {
        $asset = self::getOrCreateStakingAsset($user);
        AssetLogsServices::OnChange($user, $asset, $amount, AssetLogsRemarkEnum::LoseStaking->name);
        $asset->balance += $amount;
        $asset->save();
    }

    /**
     * @param Users $user
     * @param float $amount
     * @return void
     */
    public static function WinWithdrawable(Users $user, float $amount): void
    {
        $asset = self::getOrCreateWithdrawAsset($user);
        AssetLogsServices::OnChange($user, $asset, $amount, AssetLogsRemarkEnum::WinWithdrawable->name);
        $asset->balance += $amount;
        $asset->save();
    }

    /**
     * @param Users $user
     * @return void
     * @throws Exception
     */
    public static function UpdateStakingWhenPledgeFinished(Users $user): void
    {
        $usdc = CoinServices::GetUSDC();
        $staking = AssetsServices::getOrCreateStakingAsset($user, $usdc);
        if ($staking->balance <= 0)
            return;

        $withdrawable = AssetsServices::getOrCreateWithdrawAsset($user, $usdc);

        AssetLogsServices::OnChange($user, $staking, -$staking->balance, AssetLogsRemarkEnum::FinishPledge->name);
        AssetLogsServices::OnChange($user, $withdrawable, $staking->balance, AssetLogsRemarkEnum::FinishPledge->name);

        $withdrawable->balance += $staking->balance;
        $withdrawable->save();

        $staking->balance = 0;
        $staking->staking_ended_at = null;
        $staking->save();
    }

    /**
     * @ok
     * @param Users $user
     * @param Coins $coin
     * @param string $type
     * @return Assets
     */
    private static function getOrCreateUserAsset(Users $user, Coins $coin, string $type): Assets
    {
        $asset = Assets::where('users_id', $user->id)
            ->where('type', $type)
            ->where('coins_id', $coin->id)
            ->lockForUpdate()
            ->first();

        if (!$asset) {
            $asset = Assets::create([
                'users_id' => $user->id, #
//                'web3_transactions_id' => '', #
                'type' => $type, # 1:Staking / 2:WithdrawAble / 3:Pending / 4:Wallet
                'coins_id' => $coin->id, #
                'symbol' => $coin->symbol, #
                'icon' => $coin->icon, #
                'balance' => 0, #
//                'staking_ended_at' => '', #
//                'pending_type' => '', # 1:Withdrawing / 2:Airdrop / 3:Referrals
//                'referrals_id' => '', #
//                'airdrops_id' => '', #
            ]);
        }

        return $asset;
    }

    /**
     * @param Users $user
     * @param Coins|null $coin
     * @return Assets
     */
    public static function getOrCreateWithdrawAsset(Users $user, ?Coins $coin = null): Assets
    {
        if (!$coin)
            $coin = CoinServices::GetUSDC();
        return self::getOrCreateUserAsset($user, $coin, AssetsTypeEnum::WithdrawAble->name);
    }

    /**
     * @param Users $user
     * @return array
     */
    public static function getStakingAsset(Users $user): array
    {
        return Assets::who($user)
            ->where('type', AssetsTypeEnum::Staking->name)
//            ->where('pending_type', AssetsPendingTypeEnum::Staking->name)
            ->descID()
            ->take(20)
            ->get()
            ->toArray();
    }

    public  static  function getAllUserStaking(Users $user): float
    {
        $assets = AssetsServices::getStakingAsset($user);

        $balanceSum = 0.0;

        foreach ($assets as $asset) {
            if (isset($asset['balance'])) {
                $balanceSum += $asset['balance'];
            }
        }

        return $balanceSum;
    }

    /**
     * @ok
     * @param Users $user
     * @param Coins|null $coin
     * @return Assets
     */
    public static function getOrCreateStakingAsset(Users $user, ?Coins $coin = null): Assets
    {
        if (!$coin)
            $coin = CoinServices::GetUSDC();
        return self::getOrCreateUserAsset($user, $coin, AssetsTypeEnum::Staking->name);
    }

    /**
     * @param Users $user
     * @return Assets
     */
    public static function getOrCreateAirdropAsset(Users $user): Assets
    {
        $coin = CoinServices::GetUSDC();
        return self::getOrCreateUserAsset($user, $coin, AssetsTypeEnum::Airdrop->name);
    }

    /**
     * @param Users $user
     * @param Coins $usdc
     * @param float $amount
     * @return Assets
     */
    public static function CreateAirdropPendingAsset(Users $user, Coins $usdc, float $amount): Assets
    {
        return Assets::create([
            'users_id' => $user->id, #
            'type' => AssetsTypeEnum::Pending->name, # 类别:Staking,WithdrawAble,Pending,Airdrop
            'coins_id' => $usdc->id, #
            'symbol' => $usdc->symbol, #
            'icon' => $usdc->icon, #
            'balance' => $amount, #
//                'staking_ended_at' => '', #
            'pending_type' => AssetsPendingTypeEnum::ExchangeAirdrop->name, # type:Staking,Withdraw
            'pending_status' => AssetsPendingStatusEnum::WAITING->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
//                'web3_transactions_id' => '', #
        ]);
    }

    /**
     * @param Users $user
     * @param Coins $usdc
     * @param PledgeProfits $profit
     * @return Assets
     */
    public static function CreateDepositPendingAssets(Users $user, Coins $usdc, PledgeProfits $profit): Assets
    {
        return Assets::create([
            'users_id' => $user->id, #
            'type' => AssetsTypeEnum::Pending->name, # 类别:Staking,WithdrawAble,Pending,Airdrop
            'coins_id' => $usdc->id, #
            'symbol' => $usdc->symbol, #
            'icon' => $usdc->icon, #
            'balance' => $profit->deposit_staking_amount, #
//                'staking_ended_at' => '', #
            'pending_type' => AssetsPendingTypeEnum::DepositStaking->name, # type:Staking,Withdraw
            'pending_status' => AssetsPendingStatusEnum::WAITING->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
            'pledge_profits_id' => $profit->id, #
//            'web3_transactions_id' => $web3->id, #
        ]);
    }

    /**
     * @param mixed $user
     * @param float $amount
     * @return void
     */
    public static function SendAirdrop(mixed $user, float $amount): void
    {
        $asset = AssetsServices::getOrCreateAirdropAsset($user);
        $asset->balance += $amount;
        $asset->save();
    }

    /**
     * @param mixed $assets_id
     * @param bool $throw
     * @param bool $lock
     * @return Assets
     * @throws Err
     */
    public static function GetById(mixed $assets_id, bool $throw = true, bool $lock = false): Assets
    {
        if ($lock)
            $asset = Assets::lockForUpdate()->find($assets_id);
        else
            $asset = Assets::find($assets_id);
        if (!$asset && $throw)
            Err::Throw(__("Assets is not exists"));
        return $asset;
    }

    /**
     * @param Users $user
     * @param Coins $usdc
     * @param int $amount
     * @param int $reward
     * @return Assets
     */
    public static function CreateByStakingRewardLoyalty(Users $user, Coins $usdc, int $amount, int $reward): Assets
    {
        return Assets::create([
            'users_id' => $user->id, #
            'type' => AssetsTypeEnum::Pending->name, # 类别:Staking,WithdrawAble,Pending,Airdrop
            'coins_id' => $usdc->id, #
            'symbol' => $usdc->symbol, #
            'icon' => $usdc->icon, #
            'balance' => $amount, #
            'pending_type' => AssetsPendingTypeEnum::StakingRewardLoyalty->name, # type:Staking,Withdraw
            'reward_loyalty_amount' => $reward,
            'pending_status' => AssetsPendingStatusEnum::WAITING->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED
        ]);
    }

    /**
     * @param Users $user
     * @param Coins $usdc
     * @return mixed
     */
    public static function GetStakingRewardLoyaltyBalances(Users $user, Coins $usdc): mixed
    {
        return Assets::where('users_id', $user->id)
            ->where('type', AssetsTypeEnum::Pending->name)
            ->where('pending_type', AssetsPendingTypeEnum::StakingRewardLoyalty->name)
            ->where('coins_id', $usdc->id)
            ->pluck('balance')
            ->toArray();
    }

    /**
     * @param Users $user
     * @param Coins $coin
     * @param mixed $amount
     * @return Assets
     */
    public static function CreatePendingByFakeStaking(Users $user, Coins $coin, float $amount): Assets
    {
        return Assets::create([
            'users_id' => $user->id, #
            'type' => AssetsTypeEnum::Pending->name, # 类别:Staking,WithdrawAble,Pending,Airdrop
            'coins_id' => $coin->id, #
            'symbol' => $coin->symbol, #
            'icon' => $coin->icon, #
            'balance' => $amount, #
//            'staking_ended_at' => '', #
            'pending_type' => AssetsPendingTypeEnum::Staking->name, # type:Staking,Withdraw,ExchangeAirdrop,DepositStaking,StakingRewardLoyalty
//            'pending_fee' => '', #
//            'reward_loyalty_amount' => '', #
            'pending_status' => AssetsPendingStatusEnum::SUCCESS->name, # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED,APPROVE
//            'pending_withdrawal_type' => '', # pending_withdrawal_type:Automatic,Manual
//            'pending_withdrawal_approve_users' => '', #
//            'pledge_profits_id' => '', #
//            'web3_transactions_id' => $trans->id, #
        ]);
    }
}
