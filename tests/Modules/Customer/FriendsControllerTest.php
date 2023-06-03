<?php

namespace Tests\Modules\Customer;


use App\Models\Friends;
use App\NewServices\UsersServices;
use Illuminate\Http\Request;
use Tests\TestCase;

class FriendsControllerTest extends TestCase
{
    public function testMyFriends()
    {
        $user = UsersServices::GetById(1);
        dd(Friends::where('from_users_id', $user->id)
            ->with('to_user:id,nickname,address')
            ->paginate()->toArray());
    }
}
