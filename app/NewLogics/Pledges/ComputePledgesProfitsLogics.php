<?php

namespace App\NewLogics\Pledges;

use App\Enums\AssetLogsRemarkEnum;
use App\Enums\PledgesStatusEnum;
use App\Enums\UsersIdentityStatusEnum;
use App\Enums\UsersStatusEnum;
use App\Helpers\TelegramBot\TelegramBotApi;
use App\Models\AssetLogs;
use App\Models\Jackpots;
use App\Models\JackpotsHasUsers;
use App\Models\PledgeProfits;
use App\Models\Pledges;
use App\Models\PledgesHasFunds;
use App\Models\UserBalanceSnapshots;
use App\Models\UserEarningSnapshots;
use App\Models\Users;
use App\Models\Vips;
use App\NewLogics\EmailLogics;
use App\NewLogics\SysMessageLogics;
use App\NewLogics\Transfer\ExchangeAirdropLogics;
use App\NewLogics\Transfer\NewWithdrawalServices;
use App\NewLogics\Transfer\StakingLogics;
use App\NewServices\AssetLogsServices;
use App\NewServices\AssetsServices;
use App\NewServices\BonusesServices;
use App\NewServices\CoinServices;
use App\NewServices\JackpotsHasUsersServices;
use App\NewServices\JackpotsServices;
use App\NewServices\NewbieCardServices;
use App\NewServices\PledgeProfitsServices;
use App\NewServices\PledgesServices;
use App\NewServices\UserBalanceSnapshotsServices;
use App\NewServices\UserEarningSnapshotsServices;
use App\NewServices\VipsServices;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LaravelCommon\App\Exceptions\Err;

/**
 *
 */
class ComputePledgesProfitsLogics
{
    /**
     * @param Jackpots $jackpot
     * @return void
     * @throws BindingResolutionException
     * @throws Err
     * @throws Exception
     */
    public static function ComputeAll(Jackpots $jackpot): void
    {
        Users::where('status', UsersStatusEnum::Enable->name)
            ->each(function ($user) use ($jackpot) {
                $pledge = PledgesServices::GetByUser($user);
                if ($pledge) {
                    try {
                        DB::beginTransaction();
                        $vip = VipsServices::GetByUser($user);
                        $isNewbieCardValid = NewbieCardServices::IsNewbieCardValid($user);
                        $profit = PledgeProfitsServices::CreateNewProfit($isNewbieCardValid, $pledge, $user, $vip);
                        $jackpotsHasUser = JackpotsHasUsersServices::GetOrCreate($jackpot, $user);
                        self::Compute($pledge, $profit, $user, $vip, $jackpot, $jackpotsHasUser);
                        Log::debug("\t PledgesProfit::Compute($pledge->id, {$pledge->user->id}, $jackpot->id)...DONE");
                        DB::commit();
                    } catch (Exception $exception) {
                        TelegramBotApi::SendText("Profit compute error\nUser: $user->id\nPledge: $pledge->id\nError: {$exception->getMessage()}");
                        Log::debug("\t PledgesProfit::Compute($pledge->id, {$pledge->user->id}, $jackpot->id)...ERROR...{$exception->getMessage()}");
                        DB::rollBack();
                    }
                } else {
                    UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);
                    UserEarningSnapshotsServices::CreateUserEarningSnapshot($user, 0);
                }
            });
    }

    /**
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @param Users $user
     * @param Vips $vip
     * @param Jackpots $jackpot
     * @param JackpotsHasUsers $jackpotsHasUser
     * @param bool $isManual
     * @return void
     * @throws Exception
     */
    public static function Compute(Pledges $pledge, PledgeProfits $profit, Users $user, Vips $vip, Jackpots $jackpot, JackpotsHasUsers $jackpotsHasUser, bool $isManual = false): void
    {
        $isLastRound = $profit->datetime >= Carbon::parse($pledge->ended_at) || $profit->round >= $profit->duration * 4;

        // Automatic Trading 自动交易：默认打开，不可取消

        // Automatic Exchange 自动兑换：如果不打开，则币不能换成usdc，且后续收益等都不进行计算。手动打开以后再重新计算收益
        if (!$user->can_automatic_exchange && !$isManual) {
            $pledge->status = PledgesStatusEnum::Stopped->name;
            $pledge->save();
            return;
        }

        // Prevent liquidation 爆仓防护：如果开启，最终亏损则亏设定的值，最终盈利，则70%是亏损设定的值，30%盈利
        self::PreventLiquidation($profit);

        // Profit guarantee 盈利保护：如果达不到最低盈利保护，从贡献值来补，补到盈利保护；
        self::ProfitGuarantee($user, $pledge, $profit, $jackpot, $jackpotsHasUser);

        // 超过10%，进贡献
        self::intoLoyalty($user, $pledge, $profit, $jackpot, $jackpotsHasUser);

        // Leveraged investment  // 杠杆
        // Automatic loan repayment // 自动还贷
        self::LeveragedAndLoanRepayment($profit);

        // 亏损本金：如果实际income是负数，亏损本金
        self::loseOrWin($user, $pledge, $profit);

        // 计算bonus
        self::createBonus($user, $profit, $pledge);

        // Automatic Airdrop Bonus， 这里不用做什么

        // 保存数据库
        $user->total_income += $profit->income;
        $user->total_actual_income += $profit->actual_income;
        $user->save();

        $pledge->earnings_this_node = $profit->actual_income;
        $pledge->earnings_today = $profit->is_new_day ? $profit->actual_income : $pledge->earnings_today + $profit->actual_income;
        $pledge->actual_apy = $profit->actual_apy;
        $pledge->actual_loan_apy = $profit->actual_loan_apy;
        $pledge->save();

        $profit->save();

//        $jackpot->save();

        $jackpotsHasUser->save();

        // E-mail notification 邮件通知
        if ($profit->can_email_notification) {
            EmailLogics::sendPledgeProfitNoticeEmail($user, $profit);
        }

        self::AutomaticStaking($user, $pledge, $profit);

        // sys message
        SysMessageLogics::PledgeMessage($user, $profit, CoinServices::GetUSDC());

        // Create Snapshots
        UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);
        UserEarningSnapshotsServices::CreateUserEarningSnapshot($user, $profit->actual_income);

        // Automatic withdrawal
        if (!$isLastRound || !$pledge->is_trail)
            self::AutomaticWithdrawal($user);

        // 本期是否结束
        if ($isLastRound) {
            // 更新pledge状态
            $pledge->status = PledgesStatusEnum::Finished->name;
            $pledge->save();

            // 更新staking状态
            AssetsServices::UpdateStakingWhenPledgeFinished($user);

            // 试用期结束后
            // 清零：staking，withdrawable，loyalty，airdrop。。
            // 配置：根据vip恢复默认配置
            if ($pledge->is_trail) {
                StakingLogics::Clean($user);    // Staking 账户清零
                NewWithdrawalServices::Clean($user);    // withdrawable 账户清零
                JackpotsServices::Clean($jackpot, $jackpotsHasUser); // 贡献清零
                ExchangeAirdropLogics::Clean($user); // airdrop 账户清零

                // 删除 snapshots
                UserBalanceSnapshots::where('users_id', $user->id)->delete();
                UserEarningSnapshots::where('users_id', $user->id)->delete();

                // 删除 Pledges & profits
                $pledge->delete();
                PledgeProfits::where('users_id', $user->id)->delete();
                PledgesHasFunds::where('users_id', $user->id)->delete();
                AssetLogs::who($user)->delete();

                // 恢复设定
                $user->can_automatic_trade = 1;
                $user->can_automatic_exchange = 1;
                $user->can_leveraged_investment = 0;
                $user->can_automatic_loan_repayment = 0;
                $user->can_prevent_liquidation = 0;
                $user->can_profit_guarantee = 0;
                $user->can_automatic_airdrop_bonus = 0;
                $user->can_automatic_staking = 0;
                $user->can_automatic_withdrawal = 0;

                // 恢复统计数据
                $user->total_balance = 0;
                $user->total_rate = 0;
                $user->total_staking_amount = 0;
                $user->total_withdraw_amount = 0;
                $user->total_income = 0;
                $user->total_actual_income = 0;
                $user->total_loyalty_value = 0;
                $user->total_today_loyalty_value = 0;

                $user->identity_verified_at = null;
//                $user->id_front_img = null;
//                $user->id_reverse_img = null;
//                $user->self_photo_img = null;
                $user->identity_status = UsersIdentityStatusEnum::Default->name;
                $user->identity_error_message = null;
                $user->identity_error_count_today = 0;
                $user->identity_error_last_at = null;

                // 恢复用户默认设置
                $user->leverage = 1;
                $user->duration = 7;

                $user->save();
            }
        }
    }

    /**
     * @param PledgeProfits $profit
     * @return void
     */
    private static function PreventLiquidation(PledgeProfits $profit): void
    {
        // 不开启，跳过
        if (!$profit->can_prevent_liquidation) {
            return;
        }
        // 收益小于0，则亏损设定的值，刷新funds detail
        if ($profit->income < 0) {
            PledgeProfitsServices::RecomputeFundsDetailByPreventLiquidationAmount($profit, $profit->prevent_liquidation_amount);
            return;
        }

        // 收益大于0，则70%概率亏损设定的值，刷新funds detail。30%跳过
        if ($profit->income > 0) {
            $rand = rand(0, 100);
            if ($rand < 70) {
                PledgeProfitsServices::RecomputeFundsDetailByPreventLiquidationAmount($profit, $profit->prevent_liquidation_amount);
            }
        }
    }

    /**
     *
     * @param Users $user
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @param Jackpots $jackpot
     * @param JackpotsHasUsers $jackpotsHasUser
     * @return void
     */
    private static function ProfitGuarantee(Users $user, Pledges $pledge, PledgeProfits $profit, Jackpots $jackpot, JackpotsHasUsers $jackpotsHasUser): void
    {
        // 保护最小apy
        $minApy = $profit->minimum_guarantee_apy;
        $minIncome = $profit->minimum_guarantee_amount;

        // 不开启，跳过
        // 盈利大于最小保护，跳过
        if (!$profit->can_profit_guarantee || $profit->actual_loan_apy > $minApy) {
            return;
        }

        // 盈利小于最小保护，补贴贡献
        $diff = $minIncome - $profit->income;
        $profit->profit_guarantee_amount = $diff;
        if ($user->total_loyalty_value >= $diff) {
            // 有足够的贡献值，扣除贡献值
            $profit->done_profit_guarantee = true;
            $profit->actual_loan_apy = $minApy;
            $profit->actual_income = $minIncome;
            $profit->loyalty_amount = -$diff;
            JackpotsServices::TakeOutFromLoyalty($user, $pledge, $profit, $jackpot, $jackpotsHasUser, $diff);
        } else {
            // 贡献值不够
            // 当前不补，一定要贡献值达到才补 // 可以staking // 也可以贡献足够的时候回冲
            // 设置一个特殊状态
            $profit->done_profit_guarantee = false;
            SysMessageLogics::LoyaltyNotEnough($user, $pledge, $profit, $diff);
        }
    }

    /**
     * @param PledgeProfits $profit
     * @return void
     */
    public static function LeveragedAndLoanRepayment(PledgeProfits $profit): void
    {
        // 杠杆为1，不计算
        if ($profit->leverage <= 1 || $profit->loan_amount == 0)
            return;

        if ($profit->actual_loan_apy <= 0) {
            // 收益负数，不计算，以后再算
            return;
        } else {
            // 实际5% < apy < 10%
            $loanCost = $profit->loan_amount * $profit->actual_loan_apy / 365 / 4 * $profit->loan_charges; //$profit->actual_income * $profit->actual_apy / 365 / 4 * $profit->loan_charges;
            $profit->loan_charges_fee = $loanCost;
            $profit->actual_income -= $loanCost;
        }
    }

    /**
     * apy超过10%的部分，计算手续费，计算贡献值
     * @param Users $user
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @param Jackpots $jackpot
     * @param JackpotsHasUsers $jackpotsHasUser
     * @return void
     */
    private static function intoLoyalty(Users $user, Pledges $pledge, PledgeProfits $profit, Jackpots $jackpot, JackpotsHasUsers $jackpotsHasUser): void
    {
        if ($profit->income <= 0)
            return;

        if ($profit->actual_loan_apy > .1) {
            $maxIncome = ($profit->staking + $profit->loan_amount) * (.1 / 4 / 365);
            $diffAmount = $profit->income - $maxIncome;
            // 收手续费
            $profit->loyalty_fee = $diffAmount / $maxIncome * ($profit->loan_amount * .1 / 365 / 4 * $profit->loan_charges); //$diffAmount * $profit->loan_charges;
            // 进贡献
            $profit->loyalty_amount = $diffAmount - $profit->loyalty_fee;
            JackpotsServices::SendIntoLoyalty($user, $pledge, $profit, $jackpot, $jackpotsHasUser, $profit->loyalty_amount);
            // 刷新apy
            $profit->actual_loan_apy = .1;
            $profit->actual_income = $maxIncome;

        } else {
            if ($profit->is_new_day)
                $user->total_today_loyalty_value = 0;
        }
    }

    /**
     * 亏本金
     * @param Users $user
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @return void
     */
    private static function loseOrWin(Users $user, Pledges $pledge, PledgeProfits $profit): void
    {
        if ($profit->actual_income < 0) {
            if ($pledge->staking > abs($profit->actual_income)) {
                // 如果亏损 小于 本金
                $lose = $profit->actual_income;
            } else {
                // 如果亏损 大于 本金
                $lose = -$pledge->staking;
                $profit->actual_income = $lose;
                $pledge->canceled_at = now()->toDateTimeString();
                $pledge->status = PledgesStatusEnum::Canceled->name;
            }
            $pledge->staking += $lose;
            $profit->lose_staking_amount = $lose;
            if (!$pledge->is_trail)
                AssetsServices::LoseStaking($user, $lose);
        } else {
            AssetsServices::WinWithdrawable($user, $profit->actual_income);
        }
    }

    /**
     * @param Users $user
     * @param PledgeProfits $profit
     * @param Pledges $pledge
     * @return void
     */
    private static function createBonus(Users $user, PledgeProfits $profit, Pledges $pledge): void
    {
        if ($pledge->is_trail)
            return;

        if ($profit->actual_income <= 0)
            return;

        $p1 = $user->parent_1_id ? Users::find($user->parent_1_id) : null;
        $p2 = $user->parent_2_id ? Users::find($user->parent_2_id) : null;
        $p3 = $user->parent_3_id ? Users::find($user->parent_3_id) : null;

        BonusesServices::CreateByProfit($user, $p1, $profit, 'level_1_refer');
        BonusesServices::CreateByProfit($user, $p2, $profit, 'level_2_refer');
        BonusesServices::CreateByProfit($user, $p3, $profit, 'level_3_refer');
    }

    /**
     * @param Users $user
     * @return void
     * @throws Exception
     */
    private static function AutomaticWithdrawal(Users $user): void
    {
        if (!$user->can_automatic_withdrawal)
            return;

        $usdc = CoinServices::GetUSDC();
        $assets = AssetsServices::getOrCreateWithdrawAsset($user, $usdc);
        $balance = $assets->balance;
        if ($balance >= $user->automatic_withdrawal_amount) {
            $vip = VipsServices::GetByUser($user);
            if ($balance > $vip->maximum_withdrawal_limit)
                $balance = $vip->maximum_withdrawal_limit;
            NewWithdrawalServices::CreateWithdrawal($user, $vip, $usdc, $assets, $balance, false, false);
        }
    }

    /**
     * @param Users $user
     * @param Pledges $pledge
     * @return void
     * @throws Exception
     */
    private static function AutomaticStaking(Users $user, Pledges $pledge): void
    {
        if ($user->can_automatic_staking) {
            $withdrawable = AssetsServices::getOrCreateWithdrawAsset($user);
            if ($withdrawable->balance > $user->automatic_withdrawal_amount) {
                if ($pledge->is_trail) {
                    $pledge->staking += $withdrawable->balance;
                    $pledge->save();
                    AssetLogsServices::OnChange($user, $withdrawable, -$withdrawable->balance, AssetLogsRemarkEnum::WithdrawToStaking->name, '');
                    $withdrawable->balance = 0;
                    $withdrawable->save();
                    Log::debug("\t\t AutomaticStaking::Trail:: $withdrawable->balance ...DONE");
                } else {
                    StakingLogics::WithdrawableToStaking($user, $withdrawable, throw: false);
                }
            }
        }
        // todo staking from wallet
    }
}
