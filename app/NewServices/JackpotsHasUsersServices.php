<?php

namespace App\NewServices;

use App\Helpers\TelegramBot\TelegramBotApi;
use App\Models\Jackpots;
use App\Models\JackpotsHasUsers;
use App\Models\Users;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LaravelCommon\App\Exceptions\Err;

class JackpotsHasUsersServices
{
    /**
     * @ok
     * @param Jackpots $jackpot
     * @param Users $user
     * @return JackpotsHasUsers|null
     */
    public static function Get(Jackpots $jackpot, Users $user): ?JackpotsHasUsers
    {
        $model = JackpotsHasUsers::where('jackpots_id', $jackpot->id)
            ->where('users_id', $user->id)
            ->first();

        if (!$model) {
            $model = JackpotsHasUsers::create([
                'jackpots_id' => $jackpot->id, #
                'users_id' => $user->id, #
//                'web3_transactions_id' => '', #
//                'loyalty' => '', #
//                'airdrop' => '', #
//                'rank' => '', #
//                'expired_at' => '', #
//                'can_automatic_airdrop_bonus' => '', #
//                'status' => '', # status:NotReady,Ready,Expired,Finished
            ]);
        }

        return $model;
    }

    /**
     * @param Jackpots $jackpot
     * @return void
     * @throws Err
     */
    public static function RefreshWhenRoundFinished(Jackpots $jackpot): void
    {
        try {
            DB::beginTransaction();
            $i = 1;

            // 重新计算总额
            $balance = JackpotsHasUsers::where('jackpots_id', $jackpot->id)
                ->whereHas('user', function ($query) {
                    $query->where('can_automatic_airdrop_bonus', true);
                })->sum('loyalty');
            $jackpot->balance = $balance;
            $jackpot->save();

            if ($balance != 0) {
                // 计算每个人的airdrop和rank
                $goal = $jackpot->send_airdrop;
                JackpotsHasUsers::with('user')
                    ->where('jackpots_id', $jackpot->id)
                    ->orderByDesc('loyalty')
                    ->each(function (JackpotsHasUsers $item) use (&$i, $balance, $goal) {
                        if ($item->user->can_automatic_airdrop_bonus) {
                            $item->airdrop = $item->loyalty / $balance * $goal;
                            $item->rank = $i;
                            $i++;
                        } else {
                            $item->airdrop = 0;
                            $item->rank = null;
                        }
                        $item->can_automatic_airdrop_bonus = $item->user->can_automatic_airdrop_bonus;
                        $item->save();
                    });
            }
            DB::commit();
        } catch (Exception $exception) {
            TelegramBotApi::SendText("RefreshWhenRoundFinished\nError\n{$exception->getMessage()}");
            Log::error("RefreshWhenRoundFinished:::Error:::{$exception->getMessage()}");
            DB::rollBack();
            Err::Throw("RefreshWhenRoundFinished:::Error:::{$exception->getMessage()}");
        }
    }

    /**
     * @param Jackpots $jackpot
     * @param Users $user
     * @return JackpotsHasUsers|null
     */
    public static function GetOrCreate(Jackpots $jackpot, Users $user): JackpotsHasUsers|null
    {
        $model = JackpotsHasUsers::where('jackpots_id', $jackpot->id)
            ->where('users_id', $user->id)
            ->first();

        if ($model)
            return $model;

        return JackpotsHasUsers::create([
            'jackpots_id' => $jackpot->id, #
            'users_id' => $user->id, #
            'loyalty' => 0, #
            'airdrop' => 0, #
            'rank' => 0,
        ]);
    }
}
