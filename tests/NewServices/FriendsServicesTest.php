<?php

namespace Tests\NewServices;


use App\NewServices\FriendsServices;
use App\NewServices\UsersServices;
use Tests\TestCase;

class FriendsServicesTest extends TestCase
{

    public function testFollow()
    {
        $a = UsersServices::GetById(1);
        $b = UsersServices::GetById(2);
        FriendsServices::Follow($a, $b);
    }
}
