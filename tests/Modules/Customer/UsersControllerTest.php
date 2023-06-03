<?php

namespace Tests\Modules\Customer;


use App\Models\Friends;
use App\NewServices\UsersServices;
use Illuminate\Http\Request;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    public function testGetNewbieCard()
    {
        $this->go(__METHOD__, [
            'id' => 2, # 用户地址
        ]);
    }
}
