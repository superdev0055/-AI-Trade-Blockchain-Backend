<?php

namespace App\Modules;

use LaravelCommon\App\Exceptions\Err;
use App\Http\Controllers\Controller;
use App\Models\Admins;

class AdminBaseController extends Controller
{
    private ?Admins $user = null;

    /**
     * @return Admins|null
     * @throws Err
     */
    public function getUser(): ?Admins
    {
        if (!$this->user) {
            $user = auth()->user();
            if (get_class($user) != Admins::class)
                Err::Throw(__("User not login"), 10000);
            $this->user = $user;
        }
        return $this->user;
    }
}
