<?php

namespace Tests\NewLogics;


use App\NewLogics\FakeUserLogics;
use Tests\TestCase;

class FakeUserLogicsTest extends TestCase
{

    public function testCreateUser()
    {
        FakeUserLogics::CreateUser();
    }
}
