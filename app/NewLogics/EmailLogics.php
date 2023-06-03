<?php

namespace App\NewLogics;

use App\Mail\PledgeProfitNoticeMail;
use App\Models\PledgeProfits;
use App\Models\Users;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailLogics
{
    /**
     * @param Users $user
     * @param PledgeProfits $profit
     * @return void
     * @throws Exception
     */
    public static function sendPledgeProfitNoticeEmail(Users $user, PledgeProfits $profit): void
    {
        dispatch(function () use ($user, $profit) {
            dump("_to_{$user->email}_");
            Mail::to($user->email)->send(new PledgeProfitNoticeMail($user, $profit));
        })->catch(function (Throwable $e) use ($user, $profit) {
            Log::error("sendPledgeProfitNoticeEmail Error:::{$e->getMessage()}", [$user->id, $profit->id]);
        })->onQueue('email');
    }
}
