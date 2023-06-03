<?php


namespace App\Modules\Admin;


use App\Models\PledgeProfits;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class PledgeProfitsController
 * @package App\Modules\Admin
 */
class PledgeProfitsController extends AdminBaseController
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
            'user_address' => 'nullable|string', # 用户地址
            'is_demo_user' => 'nullable|boolean', # 0 or 1
            'user_vips_id' => 'nullable|integer', # Vip id
            'is_trail' => 'nullable|boolean', # Yes Or No
            'created_at' => 'nullable|array', # 数组["2020-02-02"，"2020-02-03"]
        ]);
        return PledgeProfits::withUser()
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserIsDemoUser($params)
            ->ifWhereHasUserVip($params)
            ->ifWhere($params, 'is_trail')
            ->ifRange($params, 'created_at')
            ->order()
            ->paginate($this->perPage());
    }
}
