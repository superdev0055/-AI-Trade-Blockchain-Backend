<?php


namespace App\Modules\Admin;


use App\Models\JackpotsHasUsers;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class JackpotsHasUsersController
 * @package App\Modules\Admin
 */
class JackpotsHasUsersController extends AdminBaseController
{
    /**
     * @intro 列表
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function list(Request $request): mixed
    {
        $params = $request->validate([
            'jackpots_id' => 'nullable|integer', # Jackpot id
            'user_address' => 'nullable|string', # 用户地址
            'is_demo_user' => 'nullable|boolean', # 0 or 1
            'user_vips_id' => 'nullable|integer', # Vip id
            'can_automatic_airdrop_bonus' => 'nullable|boolean', # status:NotReady,Ready,Expired,Finished
        ]);
        return JackpotsHasUsers::withUser()
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserIsDemoUser($params)
            ->ifWhereHasUserVip($params)
            ->ifWhere($params, 'jackpots_id')
            ->ifWhere($params, 'can_automatic_airdrop_bonus')
            ->order()
            ->paginate($this->perPage());
    }
}
