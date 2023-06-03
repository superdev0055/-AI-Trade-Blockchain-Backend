<?php

namespace App\Modules;

use App\Http\Controllers\Controller;
use App\Models\Users;
use LaravelCommon\App\Exceptions\Err;

class AgentBaseController extends Controller
{
    private ?Users $user = null;

    /**
     * @return Users|null
     * @throws Err
     */
    public function getUser(): ?Users
    {
        if (!$this->user) {
            $user = auth()->user();
            if (get_class($user) != Users::class)
                Err::Throw(__("User not login"), 10000);
            $this->user = $user;
        }
        return $this->user;
    }

    /**
     * @param Users $child
     * @param bool|null $throw
     * @return void
     * @throws Err
     */
    public function checkIsMyChildren(Users $child, ?bool $throw = true): void
    {
        $me = $this->getUser();
        $flag = $child->isChildOf($me);
        if ($throw && !$flag) {
            Err::Throw(__("No permission"));
        }
    }
}
