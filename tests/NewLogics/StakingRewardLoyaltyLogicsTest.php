<?php

namespace Tests\NewLogics;


use App\NewLogics\StakingRewardLoyaltyLogics;
use App\NewServices\UsersServices;
use Tests\TestCase;

class StakingRewardLoyaltyLogicsTest extends TestCase
{

    public function testPre()
    {
        $user = UsersServices::GetById(1);
        dd(StakingRewardLoyaltyLogics::Pre($user));
    }

    public function testSubmit()
    {

    }
}
