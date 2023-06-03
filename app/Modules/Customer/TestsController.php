<?php

namespace App\Modules\Customer;

use LaravelCommon\App\Exceptions\Err;
use App\Models\JackpotsHasUsers;
use App\Models\PledgeProfits;
use App\Models\Pledges;
use App\Models\PledgesHasFunds;
use App\Modules\CustomerBaseController;
use Exception;
use LaravelCommon\App\Helpers\CommonHelper;

class TestsController extends CustomerBaseController
{
    /**
     * @return void
     * @throws Err
     */
    public function setEmail(): void
    {
        $user = $this->getUser();
        $user->email = 'test@helloworld.com';
        $user->email_verified_at = now()->toDateTimeString();
        $user->save();
    }

    /**
     * @return void
     * @throws Err
     */
    public function setInfo(): void
    {
        $user = $this->getUser();
        $user->profile_verified_at = now()->toDateTimeString();
        $user->save();
    }

    /**
     * @return void
     * @throws Err
     */
    public function setIdentity(): void
    {
        $user = $this->getUser();
        $user->identity_verified_at = now()->toDateTimeString();
        $user->save();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function resetTrail(): void
    {
        CommonHelper::Trans(function () {
            $user = $this->getUser();
            $user->can_trail_bonus = false;
            $user->show_card_at = null;
            $user->trailed_at = null;
            $user->save();

            Pledges::where('users_id', $user->id)->delete();
            PledgeProfits::where('users_id', $user->id)->delete();
            PledgesHasFunds::where('users_id', $user->id)->delete();
            JackpotsHasUsers::where('users_id', $user->id)->delete();
        });
    }
}
