<?php

namespace Tests\Helpers\Web3;


use App\Helpers\Web3\WalletHelper;
use App\NewServices\UsersServices;
use Tests\TestCase;

class WalletHelperTest extends TestCase
{
    public function testGetUBalance()
    {
        WalletHelper::GetUBalance(UsersServices::GetById(1));
    }
}
