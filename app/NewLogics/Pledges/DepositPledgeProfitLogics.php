<?php

namespace App\NewLogics\Pledges;

use App\Enums\AssetLogsRemarkEnum;
use App\Enums\AssetsPendingStatusEnum;
use App\Enums\CoinSymbolEnum;
use App\Enums\PledgeProfitsDepositStatusEnum;
use App\Enums\Web3TransactionsStatusEnum;
use App\Helpers\Web3\HashDataModel;
use App\Models\Assets;
use App\Models\PledgeProfits;
use App\Models\Users;
use App\Models\Web3Transactions;
use App\NewLogics\SysMessageLogics;
use App\NewLogics\Transfer\StakingLogics;
use App\NewServices\AssetLogsServices;
use App\NewServices\AssetsServices;
use App\NewServices\BonusesServices;
use App\NewServices\CoinServices;
use App\NewServices\JackpotsHasUsersServices;
use App\NewServices\JackpotsServices;
use App\NewServices\PledgeProfitsServices;
use App\NewServices\PledgesServices;
use App\NewServices\UserBalanceSnapshotsServices;
use App\NewServices\UsersServices;
use App\NewServices\VipsServices;
use App\NewServices\Web3TransactionsServices;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

class DepositPledgeProfitLogics
{
    /**
     * @ok
     * @param PledgeProfits $profit
     * @param float $needStaking
     * @param bool $throw
     * @return void
     * @throws Err
     */
    public static function CanDeposit(PledgeProfits $profit, float $needStaking, bool $throw = false): void
    {
        if ($profit->is_trail && $needStaking > 0 && $throw)
            Err::Throw(__("The record of trade is in trail, can't deposit"));

        if ($profit->deposit_status != null)
            Err::Throw(__("You had processed this profit"));

        if (Carbon::parse($profit->created_at)->addDays(7) < now())
            Err::Throw(__("You can only process orders with no more than 7 days"));

        if (!$profit->can_profit_guarantee)
            Err::Throw(__("You have not open profit guarantee"));
    }

    /**
     * @ok
     * @param PledgeProfits $profit
     * @param Users $user
     * @param bool $throw
     * @return array
     * @throws Err
     */
    #[ArrayShape(['total_deposit_amount' => "float", 'user_loyalty_amount' => "float|int|string", 'need_loyalty_amount' => "mixed", 'need_staking_amount' => "float|int"])]
    public static function PreDeposit(PledgeProfits $profit, Users $user, bool $throw = false): array
    {
        $totalAmount = $profit->profit_guarantee_amount;

        $jackpot = JackpotsServices::Get();
        $jackpotsHasUser = JackpotsHasUsersServices::Get($jackpot, $user);

        # 计算需要的贡献、质押金额
        $needLoyalty = min($totalAmount, $jackpotsHasUser->loyalty);
        $needStaking = round($totalAmount - $jackpotsHasUser->loyalty, 6);
        if ($needStaking < 0)
            $needStaking = 0;

        self::CanDeposit($profit, $needStaking, $throw);

        return [
            'total_deposit_amount' => $totalAmount,
            'user_loyalty_amount' => $jackpotsHasUser->loyalty,
            'need_loyalty_amount' => $needLoyalty,
            'need_staking_amount' => $needStaking
        ];
    }

    /**
     * @param PledgeProfits $profit
     * @param Users $user
     * @return void
     * @throws Err
     */
    public static function PreStartWeb3ForDeposit(PledgeProfits $profit, Users $user): void
    {
        $amounts = self::PreDeposit($profit, $user);
        self::CanDeposit($profit, $amounts['need_staking_amount'], true);
    }

    /**
     * @param PledgeProfits $profit
     * @param Users $user
     * @param string|null $hash
     * @return void
     * @throws Err
     */
    public static function SubmitDeposit(PledgeProfits $profit, Users $user, ?string $hash = null): void
    {
        $amounts = self::PreDeposit($profit, $user, true);

        try {
            DB::beginTransaction();

            $jackpot = JackpotsServices::Get();
            $jackpotsHasUser = JackpotsHasUsersServices::Get($jackpot, $user);

            // 扣除贡献
            if ($amounts['need_loyalty_amount']) {
                $user->total_loyalty_value -= $amounts['need_loyalty_amount'];
                $user->save();
                $jackpotsHasUser->loyalty -= $amounts['need_loyalty_amount'];
                $jackpotsHasUser->save();
                $jackpot->balance -= $amounts['need_loyalty_amount'];
                $jackpot->save();
            }

            // 更新 profit actual_income
            $profit->deposit_total_amount = $amounts['total_deposit_amount'];
            $profit->deposit_loyalty_amount = $amounts['need_loyalty_amount'];
            $profit->deposit_staking_amount = $amounts['need_staking_amount'];
            $profit->deposit_status = PledgeProfitsDepositStatusEnum::Processing->name;
            $profit->save();

            if ($amounts['need_staking_amount'] === 0) {
                // 不需要staking，直接处理
                self::reComputeProfit($profit, $user);
            } else {
                // 需要staking，提交hash
                if (!$hash)
                    Err::Throw(__("You need staking from wallet"));

                $usdc = CoinServices::GetUSDC();
                // create pending assets
                $pending = AssetsServices::CreateDepositPendingAssets($user, $usdc, $profit);
                // create web3
                $web3 = Web3TransactionsServices::CreateByDeposit($profit, $user, $usdc, $pending, $hash);
                // update pending
                $pending->web3_transactions_id = $web3->id;
                $pending->save();
            }

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param Web3Transactions $web3
     * @param HashDataModel $hashData
     * @return void
     * @throws Err
     */
    public static function DepositWeb3Callback(Web3Transactions $web3, HashDataModel $hashData): void
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

            // coin_amount
            if ($web3->coin_amount != $hashData->coin_amount)
                Err::Throw(__("Wrong amount value"));

            // update web3
            $web3->block_number = $hashData->block_number;
            $web3->receipt = $hashData->raw_data;
            $web3->status = Web3TransactionsStatusEnum::SUCCESS->name;
            $web3->save();

            // update pending
            $pending = Assets::findOrFail($web3->operator_id);
            $pending->pending_status = AssetsPendingStatusEnum::SUCCESS->name;
            $pending->save();

            $user = UsersServices::GetUserById($pending->users_id);
            $profit = PledgeProfitsServices::GetById($pending->pledge_profits_id);
            self::reComputeProfit($profit, $user);

            // refresh balance
            UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);

            // bonus
            BonusesServices::CreateByReferrals($user);

            Log::info("DepositPledgeProfitLogics::DepositWeb3Callback() Success");
            DB::commit();
        } catch (Exception $exception) {
            Log::error("DepositPledgeProfitLogics::DepositWeb3Callback() Error:::", [$web3->toArray()]);
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
     * @param PledgeProfits $profit
     * @param Users $user
     * @return void
     * @throws Err
     * @throws Exception
     */
    public static function reComputeProfit(PledgeProfits $profit, Users $user): void
    {
        // 当时的vip等级
        $vip = VipsServices::GetById($profit->vips_id);

//        $totalDeposit = $profit->is_trail ? $profit->deposit_total_amount

        // 最低保护盈利apy和金额
        $minApy = $profit->is_trail ? 0.05 : $vip->minimum_apy_guarantee;
        $minIncome = ($profit->staking + $profit->loan_amount) * ($minApy / 4 / 365);

        $pledge = PledgesServices::GetByUser($user);

        // todo test 补回 profit->staking 和 asset->staking（非trail）
        $loseStakingAmountDiff = $profit->lose_staking_amount >= 0 ? 0 : abs($profit->lose_staking_amount);
        if ($loseStakingAmountDiff != 0) {
            if ($profit->is_trail) {
                // 试用期只补pledge
                if ($pledge->is_trail) {
                    $pledge->staking += $loseStakingAmountDiff;
                    $pledge->save();
                }
            } else {
                // 非试用期补profit和asset
                $pending = new Assets();
                $pending->symbol = 'usdc';
                $pending->balance = $loseStakingAmountDiff;
                StakingLogics::ProcessStaking($pending, $user);
            }
        }

        // todo test 补扣贷款金额
        $loanCost = $profit->loan_amount * $minApy / 365 / 4 * $profit->loan_charges; //$profit->actual_income * $profit->actual_apy / 365 / 4 * $profit->loan_charges;
//        $loanChargesFeeDiff = $loanCost - $profit->loan_charges_fee;
//        $totalDeposit -= $loanChargesFeeDiff;
        $oldActualIncome = $profit->actual_income;
        $profit->loan_charges_fee = $loanCost;
        $profit->actual_income = $minIncome - $loanCost;

        // todo test 补回 withdrawable
        $actualIncomeDiff = $profit->actual_income - $profit->lose_staking_amount - $oldActualIncome;
        $asset = AssetsServices::getOrCreateWithdrawAsset($user);
        AssetLogsServices::OnChange($user, $asset, $actualIncomeDiff, AssetLogsRemarkEnum::DepositProfit->name);
        $asset->balance += $actualIncomeDiff;
        $asset->save();

        $profit->actual_loan_apy = $minApy;
        $profit->done_profit_guarantee = true;
        $profit->lose_staking_amount = 0;
        $profit->save();

        $pledge->earnings_this_node = $profit->actual_income;
        $pledge->earnings_today = $profit->is_new_day ? $profit->actual_income : $pledge->earnings_today + $profit->actual_income;
        $pledge->actual_apy = $profit->actual_apy;
        $pledge->actual_loan_apy = $profit->actual_loan_apy;
        $pledge->save();

        // 刷新
        UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);

        // 发送消息
        SysMessageLogics::ResumeOrder($user, $profit, CoinServices::GetUSDC());
    }
}
