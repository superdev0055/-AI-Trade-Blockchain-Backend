<?php

namespace Tests\NewServices;

use App\Models\Users;
use App\NewServices\BonusesServices;
use Tests\TestCase;

class BonusesServicesTest extends TestCase
{

    public function testCreateByVerifyIdentity()
    {
        BonusesServices::CreateByVerifyIdentity(Users::findOrFail(1));
    }
}
