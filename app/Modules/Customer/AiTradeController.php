<?php

namespace App\Modules\Customer;

use App\Models\Funds;
use App\Models\PledgeProfits;
use App\Models\Pledges;
use App\Models\PledgesHasFunds;
use App\Modules\CustomerBaseController;
use App\NewLogics\StakingRewardLoyaltyLogics;
use App\NewLogics\Pledges\DepositPledgeProfitLogics;
use App\NewServices\AssetsServices;
use App\NewServices\JackpotsHasUsersServices;
use App\NewServices\JackpotsServices;
use App\NewServices\PledgeProfitsServices;
use App\NewServices\PledgesServices;
use App\NewServices\SettingsServices;
use App\NewServices\UserEarningSnapshotsServices;
use App\NewServices\UsersServices;
use App\NewServices\VipsServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

/**
 * ai trade
 */
class AiTradeController extends CustomerBaseController
{
    /**
     * @ok
     * @intro 不再显示弹框
     * @return void
     * @throws Err
     */
    public function dontShowCard(): void
    {
        $user = $this->getUser();
        UsersServices::DoNotShowCard($user);
    }

    /**
     * @ok
     * @intro 设置开启关闭
     * @param Request $request
     * @return void
     */
    public function setting(Request $request): void
    {
        $params = $request->validate([
            'key' => 'required|string', # 键
            'value' => 'required|string',  # 值
            'prevent_liquidation_amount' => 'nullable|integer', # 保护金额
            'staking_type' => 'nullable|string', # 自动质押类型：逐仓、全仓
            'approve_hash' => 'nullable|string', # 全仓时，需提交web3 的hash
            'automatic_withdrawal_amount' => 'nullable|integer', # 自动出款金额
        ]);
        DB::transaction(function () use ($params) {
            $user = $this->getUser();
            $vip = VipsServices::GetByUser($user);
            $pledge = PledgesServices::GetByUser($user);
            SettingsServices::Set($user, $vip, $pledge, $params);
        });
    }

    /**
     * @ok
     * @return array
     * @throws Err
     */
    #[ArrayShape(['user' => "array", 'vip' => "array", 'jackpot_has_user' => "array|string[]", 'pledge' => "mixed", 'funds' => "\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|null", 'staking' => "\App\Models\Assets"])]
    public function show(): array
    {
        $user = $this->getUser();
        $vip = VipsServices::GetByUser($user);
        $jackpot = JackpotsServices::Get();
        $jackpotHasUser = JackpotsHasUsersServices::Get($jackpot, $user);
        $staking = AssetsServices::getOrCreateStakingAsset($user);
        $pledge = Pledges::withCount('pledge_profits')
            ->where('users_id', $user->id)
            ->orderByDesc('id')
            ->first();
        $fundIds = $pledge ? PledgesHasFunds::where('pledges_id', $pledge->id)
            ->where('users_id', $user->id)
            ->pluck('funds_id') : null;
        $funds = $fundIds ? Funds::query()
            ->with('mainCoin')
            ->with('subCoin')
            ->whereIn('id', $fundIds)
            ->get() : null;

        return [
            'user' => $user->only('id', 'leverage', 'duration', 'total_actual_income', 'total_rate', 'total_loyalty_value', 'total_today_loyalty_value'),
            'vip' => $vip->toArray(),
            'jackpot_has_user' => $jackpotHasUser ? $jackpotHasUser->only('rank', 'airdrop', 'loyalty') : ['-', '-', '-'],
            'pledge' => $pledge,
            'funds' => $funds,
            'staking' => $staking,
        ];
    }

    /**
     * @ok
     * @return array
     * @throws Err
     */
    #[ArrayShape(['header' => "array", 'data' => "array"])]
    public function chart(): array
    {
        $user = $this->getUser();
        return UserEarningSnapshotsServices::GetAllEarningSparkline($user);
    }

    /**
     * @ok
     * @return mixed
     * @throws Err
     */
    public function profits(): mixed
    {
        $user = $this->getUser();
        return PledgeProfits::where('users_id', $user->id)
            ->orderByDesc('id')
            ->paginate($this->perPage());
    }

    /**
     * @ok
     * @intro 更新杠杆
     * @param Request $request
     * @return void
     */
    public function updateLeverage(Request $request): void
    {
        $params = $request->validate([
            'leverage' => 'required|integer', # 值
        ]);
        DB::transaction(function () use ($params) {
            $user = $this->getUser();
            PledgesServices::UpdateLeverage($user, $params['leverage']);
        });
    }

    /**
     * @ok
     * @intro 更新天数
     * @param Request $request
     * @return void
     */
    public function updateMaxStakingDay(Request $request): void
    {
        $params = $request->validate([
            'duration' => 'required|integer', # 值
        ]);
        DB::transaction(function () use ($params) {
            $user = $this->getUser();
            PledgesServices::UpdateDuration($user, $params['duration']);
        });
    }

    /**
     * @ok
     * @intro
     * @param Request $request
     * @return array
     */
    public function preDeposit(Request $request): array
    {
        $params = $request->validate([
            'pledge_profits_id' => 'required|integer', # id
        ]);
        return DB::transaction(function () use ($params) {
            $user = $this->getUser();
            $profit = PledgeProfitsServices::GetById($params['pledge_profits_id'], throw: true);
            return DepositPledgeProfitLogics::PreDeposit($profit, $user);
        });
    }

    /**
     * @intro pre补足余额
     * @param Request $request
     * @throws Err
     */
    public function preStartWeb3Deposit(Request $request)
    {
        $params = $request->validate([
            'pledge_profits_id' => 'required|integer', # id
        ]);

        $user = $this->getUser();
        $profit = PledgeProfits::findOrFail($params['pledge_profits_id']);
        DepositPledgeProfitLogics::PreStartWeb3ForDeposit($profit, $user);
    }

    /**
     * @intro 提交补足余额
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function submitDeposit(Request $request): void
    {
        $params = $request->validate([
            'pledge_profits_id' => 'required|integer', # id
            'hash' => 'nullable|string', # 如果need_staking不为空，则需要传hash
        ]);
        $profit = PledgeProfits::findOrFail($params['pledge_profits_id']);
        DepositPledgeProfitLogics::SubmitDeposit($profit, $this->getUser(), $params['hash'] ?? null);
    }

    /**
     * @intro 手动兑换
     * @param Request $request
     * @return array
     * @throws Err
     */
    #[ArrayShape(['message' => "mixed"])]
    public function manualExchange(Request $request): array
    {
        $params = $request->validate([
            'pledge_profits_id' => 'required|integer', # id
        ]);
        $user = $this->getUser();
        $profit = PledgeProfitsServices::GetById($params['pledge_profits_id']);
        $pledge = PledgesServices::GetById($profit->pledges_id);
        PledgeProfitsServices::ManualExchangeOldProfit($user, $pledge, $profit);
//        return $profit->only('manual_exchange_fee_percent', 'manual_exchange_fee_amount');
        return [
            'message' => __("Exchange Successful, current exchange fee is") . $profit->manual_exchange_fee_amount . " USDC"
        ];
    }

    /**
     * @intro 拉取列表
     * @return array|null
     * @throws Err
     */
    public function preStakingRewardLoyalty(): ?array
    {
        $user = $this->getUser();
        return StakingRewardLoyaltyLogics::Pre($user);
    }

    /**
     * @intro 购买成功后
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function stakingRewardLoyalty(Request $request): void
    {
        $params = $request->validate([
            'amount' => 'required|integer', # id
            'hash' => 'required|string', # id
        ]);
        $user = $this->getUser();
        StakingRewardLoyaltyLogics::Submit($user, $params['amount'], $params['hash']);
    }
}
