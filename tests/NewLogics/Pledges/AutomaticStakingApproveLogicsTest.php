<?php

namespace Tests\NewLogics\Pledges;


use App\Models\Users;
use App\NewLogics\Pledges\AutomaticStakingApproveLogics;
use Tests\TestCase;

class AutomaticStakingApproveLogicsTest extends TestCase
{

    public function testProcess()
    {
//        0xb111aa6d9417fb65357387b7c98d1c4f6b13010425e0900a981ae5a49b93b238
        $user = Users::findOrFail(1);
        AutomaticStakingApproveLogics::Create($user, '0xb111aa6d9417fb65357387b7c98d1c4f6b13010425e0900a981ae5a49b93b238');
    }

    public function testWeb3Callback()
    {

    }

    public function testCreate()
    {

    }
}
