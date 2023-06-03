<?php

namespace Tests\NewLogics;


use App\NewLogics\StakingRewardLoyaltyLogics;
use App\NewServices\UsersServices;
use Tests\TestCase;

class BuyLoyaltyLogicsTest extends TestCase
{

    public function testPreBuyLoyalty()
    {
        StakingRewardLoyaltyLogics::PreBuyLoyalty(UsersServices::GetById(1));
    }
}
