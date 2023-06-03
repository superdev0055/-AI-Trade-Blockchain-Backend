<?php

namespace App\NewServices;

use App\Enums\PledgeProfitsExchangeStatusEnum;
use App\Enums\PledgesStatusEnum;
use App\Models\PledgeProfits;
use App\Models\Pledges;
use App\Models\PledgesHasFunds;
use App\Models\Users;
use App\Models\Vips;
use App\NewLogics\Pledges\ComputePledgesProfitsLogics;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use LaravelCommon\App\Exceptions\Err;

class PledgeProfitsServices
{
    /**
     * @ok
     * @param int $id
     * @param bool $throw
     * @return PledgeProfits
     * @throws Err
     */
    public static function GetById(int $id, bool $throw = false): PledgeProfits
    {
        $profit = PledgeProfits::find($id);
        if (!$profit && $throw)
            Err::Throw(__("Profit is not exists"));

        return $profit;
    }

    /**
     * @param bool $isNewbieCardValid
     * @param Pledges $pledge
     * @param Users $user
     * @param Vips $vip
     * @return PledgeProfits
     */
    public static function CreateNewProfit(bool $isNewbieCardValid, Pledges $pledge, Users $user, Vips $vip): PledgeProfits
    {
        $lastProfit = self::getLastProfit($pledge, $user);

        $staking = $pledge->staking;
        $leverage = $user->can_leveraged_investment ? $user->leverage : 1;

        $minimumGuaranteeApy = $pledge->is_trail || $isNewbieCardValid ? 0.05 : $vip->minimum_apy_guarantee;

        $data = [
            'pledges_id' => $pledge->id, # 所属pledge
            'users_id' => $user->id, # 所属用户
            'parent_1_id' => $user->parent_1_id, # 所属父1级用户
            'parent_2_id' => $user->parent_2_id, # 所属父2级用户
            'vips_id' => $vip->id, # 用户vip等级
            'is_trail' => $pledge->is_trail, # 是否试用
            'is_new_day' => !$lastProfit || Carbon::parse($lastProfit->datetime)->day != now()->day, # 是否新的一天
            'datetime' => now()->toDateTimeString(), # 时间
            'round' => self::getNextProfitRound($pledge, $lastProfit), # 轮数
            'staking' => $staking, # 本金
            'duration' => $user->duration, # 期限
//            'lose_staking_amount' => 0, # 损失的本金
//            'apy' => 0, # 真实利润/本金
//            'loan_apy' => 0, # 真实利润/本金*杠杆
//            'actual_apy' => 0, # 扣除费用利润/本金
//            'actual_loan_apy' => 0, # 扣除费用利润/本金*杠杆
//            'income' => 0, # 盈亏
//            'actual_income' => 0, # 最终盈亏
//            'loyalty_fee' => 0, # 进出贡献费用
//            'loyalty_amount' => 0, # 进出贡献金额
            'can_automatic_exchange' => $user->can_automatic_exchange, # 是否自动兑换
//            'manual_exchanged_at' => '', # 手动兑换时间
//            'manual_exchange_fee_percent' => '', # 手动兑换手续费比例
//            'manual_exchange_fee_amount' => '', # 手动兑换手续费金额
            'exchange_status' => $user->can_automatic_exchange ? PledgeProfitsExchangeStatusEnum::Finished->name : PledgeProfitsExchangeStatusEnum::Stopped->name, # 兑换状态:Finished,Stopped,Error
            'can_profit_guarantee' => $user->can_profit_guarantee, # 是否开启利润保护
            'minimum_guarantee_apy' => $minimumGuaranteeApy, # 最低保护apy
            'minimum_guarantee_amount' => ($staking * $leverage) * ($minimumGuaranteeApy / 4 / 365), # 最低保护金额
//            'done_profit_guarantee' => '', # 是否完成了自动保护
//            'deposit_total_amount' => '', # 贡献不够时，需要补足的总金额
//            'deposit_loyalty_amount' => '', # 从贡献补的金额
//            'deposit_staking_amount' => '', # 从质押补的金额
//            'deposit_status' => '', # 质押状态:Processing,Success,Failed
//            'deposit_web3_transactions_id' => '', # 所属web3交易
//            'deposited_at' => '', # 补足的时间
            'can_leveraged_investment' => $user->can_leveraged_investment, # 是否开启杠杆
            'can_automatic_loan_repayment' => $user->can_automatic_loan_repayment, # 是否开启自动还贷
            'leverage' => $leverage, # 杠杆，用户在修改是否开启杠杆的时候，可以不需要修改杠杆值
            'loan_amount' => $staking * ($leverage - 1), # 贷款金额
            'loan_charges' => $pledge->is_trail || $isNewbieCardValid ? .30 : $vip->loan_charges, # 贷款费率
//            'loan_charges_fee' => $staking * (1 - $leverage) * $vip->loan_charges, # 贷款费用
            'can_prevent_liquidation' => $user->can_prevent_liquidation, # 是否开启爆仓防护
            'prevent_liquidation_amount' => $user->prevent_liquidation_amount, # 爆仓防护金额
            'can_email_notification' => $user->can_email_notification, # 是否打开email通知
            'can_automatic_airdrop_bonus' => $user->can_automatic_airdrop_bonus, # 是否自动空投
            'can_automatic_staking' => $user->can_automatic_staking, # 是否自动质押
            'staking_type' => $user->staking_type, # 自动质押类型:FullPosition,IsolatedMargin
            'can_automatic_withdrawal' => $user->can_automatic_withdrawal, # 是否自动出款
            'automatic_withdrawal_amount' => $user->automatic_withdrawal_amount, # 自动出款金额
//            'child_1_total_income_eth' => '', #
//            'child_2_total_income_eth' => '', #
        ];
        $profit = PledgeProfits::create($data);
        self::getFundsDetailJson($pledge, $profit, $user, $vip);
        return $profit;
    }

    /**
     * @param Pledges $pledge
     * @param $user
     * @return ?PledgeProfits
     */
    private static function getLastProfit(Pledges $pledge, $user): ?PledgeProfits
    {
        return PledgeProfits::where('pledges_id', $pledge->id)
            ->where('users_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @param Users $user
     * @param Vips $vip
     * @param bool $isManualExchange
     * @return void
     */
    private static function getFundsDetailJson(Pledges $pledge, PledgeProfits $profit, Users $user, Vips $vip, bool $isManualExchange = false): void
    {
        $totalIncome = $totalFeeAmount = 0;
        $fundsDetailJson = [];
        $feePercent = rand(2, 5) / 1000;

        PledgesHasFunds::with('fund')
            ->with('main_coin')
            ->with('sub_coin')
            ->where('users_id', $user->id)
            ->where('pledges_id', $pledge->id)
            ->each(function (PledgesHasFunds $item) use ($user, $vip, $pledge, $profit, &$totalIncome, &$totalFeeAmount, &$fundsDetailJson, $feePercent, $isManualExchange) {
                $name = $item->sub_coin ? "{$item->main_coin->symbol}/{$item->sub_coin->symbol}" : $item->main_coin->symbol;
                $capitalBefore = ($profit->staking + $profit->loan_amount) / 4;
                $exchangePriceBefore = 1 / $item->main_coin_price;
                $tokensBefore = $capitalBefore * $exchangePriceBefore;
                $apy = self::GetApyByFundProfits($user, $item);
                $tokensAfter = $capitalBefore * $exchangePriceBefore * (1 + $apy / 4 / 365);

                $feeAmount = 0;

                if ($user->can_automatic_exchange || $isManualExchange) {
                    list($exchangePriceAfter, $realRate, $finalRate, $coin1PriceNow) = self::GetExchangePriceAfter($item, $pledge, $profit, $user, $vip);
                    $capitalAfter = $tokensAfter * $exchangePriceAfter;

                    // 手动兑换收手续费
                    if ($isManualExchange) {
                        $feeAmount = $capitalAfter * $feePercent;
                        if ($feeAmount > 50 / 4)
                            $feeAmount = 50 / 4;
                        $capitalAfter -= $feeAmount;
                    }

                    $income = $capitalAfter - $capitalBefore;
                    // 更新一下价格
                    $item->main_coin_price = $coin1PriceNow;
                } else {
                    $exchangePriceAfter = $realRate = $finalRate = $capitalAfter = $income = 0;
                }
                $fundsDetailJson[] = [
                    'id' => $item->id,
                    'name' => $name,
                    'capital_before' => $capitalBefore,
                    'exchange_price_before' => $exchangePriceBefore,
                    'tokens_before' => $tokensBefore,
                    'apy' => $apy,
                    'tokens_after' => $tokensAfter,
                    'exchange_price_after' => $exchangePriceAfter,
                    'fee_percent' => $feePercent,
                    'fee_amount' => $feeAmount,
                    'capital_after' => $capitalAfter,
                    'income' => $income,
                    'icons' => [
                        $item->main_coin->only('id', 'symbol', 'icon'),
                        $item->sub_coin?->only('id', 'symbol', 'icon')
                    ],
                    '$realRate' => $realRate, // todo 生产记得删除
                    '$finalRate' => $finalRate, // todo 生产记得删除
                ];
                $totalIncome += $income;
                $totalFeeAmount += $feeAmount;
                $item->apy_current = $apy;
                $item->save();
            });

        $profit->funds_detail_json = json_encode($fundsDetailJson);
        $profit->income = $profit->actual_income = $totalIncome;
        $profit->apy = $profit->actual_apy = $totalIncome / $profit->staking * 4 * 365;
        $profit->loan_apy = $profit->actual_loan_apy = $totalIncome / ($profit->staking + $profit->loan_amount) * 4 * 365;
        $profit->manual_exchange_fee_percent = $feePercent;
        $profit->manual_exchange_fee_amount = $totalFeeAmount;
        $profit->save();
    }

    /**
     * @param Users $user
     * @param PledgesHasFunds $item
     * @return float
     */
    private static function GetApyByFundProfits(Users $user, PledgesHasFunds $item): float
    {
        $duration = $user->duration == 3 ? 7 : $user->duration;
        $fundProfit = json_decode($item->profits ?? '[]', true);
        $aprStart = floatval($fundProfit[$duration]['apr_start']);
        $aprEnd = floatval($fundProfit[$duration]['apr_end']);
        return round(rand($aprStart * 100, $aprEnd * 100) / 100, 2);
    }

    /**
     * @param PledgesHasFunds $item
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @param Users $user
     * @param Vips $vip
     * @return array
     * @throws Err
     * @throws Exception
     */
    private static function GetExchangePriceAfter(PledgesHasFunds $item, Pledges $pledge, PledgeProfits $profit, Users $user, Vips $vip): array
    {
        // 真实的涨跌
        $coin1PriceBefore = $item->main_coin_price;
        $coin1PriceNow = CoinServices::GetPrice($item->main_coin->symbol);
        $realRate = ($coin1PriceNow - $coin1PriceBefore) / $coin1PriceBefore;

        // kill rate
        list($rate1, $rate2) = KillRateServices::GetRate($pledge, $profit, $user, $vip);
        $rateUp = max($rate1, $rate2);
        $rateDown = min($rate1, $rate2);

        if ($realRate >= $rateUp) {
            $finalRate = $rateUp;
        } else {
            $finalRate = max($realRate, $rateDown);
        }

        return [$coin1PriceBefore * (1 + $finalRate / 100), $realRate, $finalRate, $coin1PriceNow];
    }

    /**
     * @param PledgeProfits $profit
     * @param float $amount
     * @return void
     */
    public static function RecomputeFundsDetailByPreventLiquidationAmount(PledgeProfits $profit, float $amount): void
    {
        $items = json_decode($profit->funds_detail_json, true);

        $fundsDetailJson = [];

        foreach ($items as $item) {
            $data = [
                'id' => $item['id'],
                'name' => $item['name'],
                'capital_before' => $item['capital_before'],
                'exchange_price_before' => $item['exchange_price_before'],
                'tokens_before' => $item['tokens_before'],
                'apy' => $item['apy'],
                'tokens_after' => $item['tokens_after'],

                'exchange_price_after' => round(($item['capital_before'] - $amount / 4) / $item['tokens_after'], 6),
                'capital_after' => $item['capital_before'] - $amount / 4,
                'income' => -$amount / 4,
                'icons' => $item['icons'],
                '$realRate' => $item['$realRate'], // todo 生产记得删除
                '$finalRate' => $item['$finalRate'], // todo 生产记得删除
            ];
            $fundsDetailJson[] = $data;
            PledgesHasFunds::where('id', $item['id'])->update([
                'main_coin_price' => $data['exchange_price_after']
            ]);
        }
        $profit->funds_detail_json = json_encode($fundsDetailJson);
        $profit->income = $profit->actual_income = -$amount;
        $profit->apy = $profit->actual_apy = -$amount / $profit->staking * 4 * 365;
        $profit->loan_apy = $profit->actual_loan_apy = -$amount / ($profit->staking + $profit->loan_amount) * 4 * 365;
        $profit->save();
    }

    /**
     * @param Users $user
     * @param Pledges|null $pledge
     * @param PledgeProfits $profit
     * @return void
     * @throws Err
     * @throws Exception
     */
    public static function ManualExchangeOldProfit(Users $user, ?Pledges $pledge, PledgeProfits $profit): void
    {
        if ($user->id != $pledge->users_id)
            Err::Throw("Don't hack me");

        if ($pledge->status != PledgesStatusEnum::Stopped->name)
            Err::Throw(__("Automatic exchange is not closed"));

        if ($profit->exchange_status != PledgeProfitsExchangeStatusEnum::Stopped->name)
            Err::Throw(__("Automatic exchange is not closed"));
        try {
            DB::beginTransaction();

            $vip = VipsServices::GetByUser($user);
            $jackpot = JackpotsServices::Get();
            $jackpotsHasUser = JackpotsHasUsersServices::Get($jackpot, $user);
            $profit->exchange_status = PledgeProfitsExchangeStatusEnum::Finished->name;
            $pledge->status = PledgesStatusEnum::OnGoing->name;
            self::getFundsDetailJson($pledge, $profit, $user, $vip, true);
            ComputePledgesProfitsLogics::Compute($pledge, $profit, $user, $vip, $jackpot, $jackpotsHasUser, true);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param Pledges $pledge
     * @param PledgeProfits|null $lastProfit
     * @return int
     */
    private static function getNextProfitRound(Pledges $pledge, ?PledgeProfits $lastProfit): int
    {
        if ($pledge->next_round_is_1) {
            $pledge->next_round_is_1 = false;
            $pledge->save();
            return 1;
        }

        return $lastProfit ? $lastProfit->round + 1 : 1;
    }
}
