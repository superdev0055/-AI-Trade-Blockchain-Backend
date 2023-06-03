<?php

namespace App\Modules\Customer;

use App\Enums\AssetsPendingStatusEnum;
use App\Enums\AssetsPendingTypeEnum;
use App\Enums\AssetsTypeEnum;
use App\Enums\Web3TransactionsStatusEnum;
use App\Models\Assets;
use App\Models\Coins;
use App\Models\Web3Transactions;
use App\Modules\CustomerBaseController;
use App\NewLogics\Transfer\NewWithdrawalServices;
use App\NewLogics\Transfer\StakingLogics;
use App\NewServices\AssetsServices;
use App\NewServices\CoinServices;
use App\NewServices\ConfigsServices;
use App\NewServices\NewbieCardServices;
use App\NewServices\VipsServices;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

class TransferController extends CustomerBaseController
{
    /**
     * @intro show
     * @return array
     * @throws Err
     * @throws Exception
     */
    #[ArrayShape(['transactions' => "array", 'recent_sends' => "mixed", 'price' => "array", 'coins' => "mixed"])]
    public function show(): array
    {
        $user = $this->getUser();
        return [
            'transactions' => [
                'Withdrawal' => Assets::who($user)
                    ->where('type', AssetsTypeEnum::Pending->name)
                    ->where('pending_type', AssetsPendingTypeEnum::Withdraw->name)
                    ->descID()
                    ->take(20)
                    ->get()
                    ->toArray(),
                'Staking' => Assets::who($user)
                    ->where('type', AssetsTypeEnum::Pending->name)
                    ->where('pending_type', AssetsPendingTypeEnum::Staking->name)
                    ->descID()
                    ->take(20)
                    ->get()
                    ->toArray(),
            ],
            'recent_sends' => Web3Transactions::who($user)
                ->where('status', Web3TransactionsStatusEnum::SUCCESS->name)
                ->take(20)
                ->orderByDesc('id')
                ->get(),
            'price' => [
                'usdc' => CoinServices::GetPrice('usdc'),
                'usdt' => CoinServices::GetPrice('usdt'),
            ],
            'coins' => Coins::whereIn('symbol', ['usdc', 'usdt'])->get(),
        ];
    }

    /**
     * @intro showStaking
     * @return array
     * @throws Err
     * @throws Exception
     */
    public function showStaking(): array
    {
        $user = $this->getUser();
        return [
            'transactions' => [
                'Staking' => AssetsServices::getStakingAsset($user)
            ]
        ];
    }

    /**
     * @intro 准备withdrawal交易
     * @return int[]
     * @throws Err
     * @throws Exception
     */
    #[ArrayShape(['balance' => "float|int|string", 'usd' => "float|int", 'min' => "float|int|string", 'max' => "float|int|string", 'fee' => "float|int", 'canFree' => 'boolean'])]
    public function preWithdrawal(): array
    {
        $user = $this->getUser();
        $vip = VipsServices::GetVip($user);
        $asset = AssetsServices::getOrCreateWithdrawAsset($user);

        $networkFee = floatval($vip->network_fee);
        $fee = intval(ConfigsServices::Get('fee')['withdraw_base_fee']) * $networkFee;


        return [
            'balance' => $asset->balance,
            'min' => $vip->minimum_withdrawal_limit,
            'max' => $vip->maximum_withdrawal_limit,
            'fee' => $fee,
            'canFree' => NewbieCardServices::CanZeroFeeOfWithdraw($user)
//            'canFree' => UsersServices::getFirstWithdrawalFree($user)
        ];
    }

    /**
     * @intro 提交withdraw
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function withdraw(Request $request): void
    {
        $params = $request->validate([
            'input_amount' => 'required|numeric', #
            'useFree' => 'nullable|boolean'
        ]);

        $user = $this->getUser();
        $useFree = $params['useFree'] ?? false;

        if ($useFree) {
            if(!NewbieCardServices::CanZeroFeeOfWithdraw($user))
                Err::Throw(__('Your newbie card is not valid'));
        }

        $amount = $params['input_amount'];

        $vip = VipsServices::GetVip($user);
        $coin = CoinServices::GetUSDC();

        NewWithdrawalServices::NewCreateWithdrawal($user, $vip, $coin, $amount, $useFree);
    }

    /**
     * @return array
     * @throws Err
     */
    #[ArrayShape(['min' => "mixed", 'balance' => "float", 'usd' => "float"])]
    public function preStaking(): array
    {
        $user = $this->getUser();
        return StakingLogics::preStaking($user);
    }

    /**
     * @intro 准备staking
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function Staking(Request $request): void
    {
        $params = $request->validate([
            'type' => 'required|string', # 类型：FromWallet, FromWithdrawable
            'symbol' => 'required|string', # coin的symbol：USDC
            'input_amount' => 'required|numeric', # 金额
        ]);
        $user = $this->getUser();
        $coin = CoinServices::GetCoin($params['symbol']);
        StakingLogics::CanStake($user, $coin, $params['input_amount'], $params['type']);
    }

    /**
     * @intro 提交staking：从可提现
     * @param Request $request
     * @return void
     * @throws Err
     * @throws Exception
     */
    public function stakingFromWithdrawable(Request $request): void
    {
        $params = $request->validate([
            'amount' => 'required|numeric', #
        ]);
        $user = $this->getUser();
        $asset = AssetsServices::getOrCreateWithdrawAsset($user);
        $amount = $params['amount'];
        StakingLogics::WithdrawableToStaking($user, $asset, $amount);
    }

    /**
     * @intro 提交staking：从钱包
     * @param Request $request
     * @return void
     * @throws Err
     * @throws Exception
     */
    public function stakingFromWallet(Request $request): void
    {
        $params = $request->validate([
            'symbol' => 'required|string', #
            'amount' => 'required|numeric', #
            'hash' => 'required|string', #
        ]);
        $amount = $params['amount'];
        $hash = $params['hash'];
        $user = $this->getUser();
        $coin = CoinServices::GetCoin($params['symbol']);
        StakingLogics::CreateStaking($user, $coin, $amount, $hash);
    }

    /**
     * @intro 取消朋友帮助
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function cancelFriendHelp(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', #
        ]);

        DB::transaction(function () use ($params) {
            $asset = AssetsServices::GetById($params['id'], lock: true);
            if ($asset->type != AssetsTypeEnum::Pending->name)
                Err::Throw(__("invalid asset type"));
            if ($asset->pending_type != AssetsPendingTypeEnum::Withdraw->name)
                Err::Throw(__("invalid pending type"));
            if ($asset->pending_status != AssetsPendingStatusEnum::APPROVE->name)
                Err::Throw(__("invalid pending status"));

            NewWithdrawalServices::RollbackWithdrawal($asset, [
                'message' => __("Canceled by user")
            ]);
        });
    }
}
