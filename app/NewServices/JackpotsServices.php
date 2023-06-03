<?php

namespace App\NewServices;

use App\Enums\JackpotsStatusEnum;
use App\Helpers\TelegramBot\TelegramBotApi;
use App\Models\Jackpots;
use App\Models\JackpotsHasUsers;
use App\Models\PledgeProfits;
use App\Models\Pledges;
use App\Models\Users;
use App\NewLogics\SysMessageLogics;
use Exception;
use Illuminate\Support\Facades\DB;
use LaravelCommon\App\Exceptions\Err;

class JackpotsServices
{
    /**
     * @return mixed
     * @throws Err
     */
    public static function CreateNewJackpot(): mixed
    {
        $config = ConfigsServices::Get('other');

        return Jackpots::create([
            'goal' => $config['jackpot_goal_amount'], #
            'send_airdrop' => $config['jackpot_send_airdrop_amount'], #
            'balance' => 0, #
            'started_at' => now()->toDateTimeString(), #
        ]);
    }

    /**
     * @ok
     * @return Jackpots|null
     */
    public static function Get(): ?Jackpots
    {
        return Jackpots::where('status', JackpotsStatusEnum::OnGoing->name)
            ->orderByDesc('id')
            ->sole();
    }

    /**
     * @param Users $user
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @param Jackpots $jackpot
     * @param JackpotsHasUsers $jackpotsHasUser
     * @param float $outAmount
     * @return void
     */
    public static function TakeOutFromLoyalty(Users $user, Pledges $pledge, PledgeProfits $profit, Jackpots $jackpot, JackpotsHasUsers $jackpotsHasUser, float $outAmount): void
    {
        $user->total_today_loyalty_value = ($profit->is_new_day) ? -$outAmount : $user->total_today_loyalty_value - $outAmount;
        $user->total_loyalty_value += -$outAmount;
//        $jackpot->balance += -$outAmount;
        $jackpotsHasUser->loyalty += -$outAmount;
//        JackpotLogs::create([
//            'users_id' => $user->id, #
//            'jackpots_id' => $jackpot->id, #
//            'pledges_id' => $pledge->id,
//            'pledge_profits_id' => $profit->id, #
//            'before' => $jackpotsHasUser->loyalty,
//            'amount' => -$outAmount, #
//            'after' => $jackpotsHasUser->loyalty - $outAmount,
//            'remark' => 'In Loyalty'
//        ]);
    }

    /**
     * @param Users $user
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @param Jackpots $jackpot
     * @param JackpotsHasUsers $jackpotsHasUser
     * @param float $inAmount
     * @return void
     */
    public static function SendIntoLoyalty(Users $user, Pledges $pledge, PledgeProfits $profit, Jackpots $jackpot, JackpotsHasUsers $jackpotsHasUser, float $inAmount): void
    {
        $user->total_today_loyalty_value = ($profit->is_new_day) ? $inAmount : $user->total_today_loyalty_value + $inAmount;
        $user->total_loyalty_value += $inAmount;
        $jackpotsHasUser->loyalty += $inAmount;
    }

    /**
     * @param Jackpots $jackpot
     * @return void
     * @throws Err
     */
    public static function WhenJackpotFinished(Jackpots $jackpot): void
    {
        try {
            DB::beginTransaction();

            if ($jackpot->balance >= $jackpot->goal) {
                // finish old one
                $jackpot->status = JackpotsStatusEnum::Finished->name;
                $jackpot->save();

                // create new one
                self::CreateNewJackpot();

                $usdc = CoinServices::GetUSDC();

                // send airdrops
                JackpotsHasUsers::where('jackpots_id', $jackpot->id)
                    ->where('airdrop', '>', 0)
                    ->whereHas('user', function ($query) {
                        $query->where('can_automatic_airdrop_bonus', true);
                    })
                    ->with('user')
                    ->each(function (JackpotsHasUsers $item) use ($usdc) {
                        $user = $item->user;
                        // 发放空投
                        AssetsServices::SendAirdrop($user, $item->airdrop);
                        SysMessageLogics::Airdrop($user, $item, $usdc);
                        // 清空贡献
                        $user->total_loyalty_value = 0;
                        $user->total_today_loyalty_value = 0;
                        $user->save();
                    });
            }

            DB::commit();
        } catch (Exception $exception) {
            TelegramBotApi::SendText("Jackpot Finished\nError\n{$exception->getMessage()}");
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param Jackpots $jackpot
     * @param JackpotsHasUsers|null $jackpotsHasUser
     * @return void
     */
    public static function Clean(Jackpots $jackpot, ?JackpotsHasUsers $jackpotsHasUser): void
    {
        $amount = $jackpotsHasUser->loyalty;

//        $jackpot->balance -= $amount;
//        $jackpot->save();

        $jackpotsHasUser->loyalty = 0;
        $jackpotsHasUser->airdrop = 0;
        $jackpotsHasUser->rank = null;
        $jackpotsHasUser->save();
    }
}
