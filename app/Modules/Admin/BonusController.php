<?php


namespace App\Modules\Admin;


use App\Models\Bonuses;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class BonusController
 * @package App\Modules\Admin
 */
class BonusController extends AdminBaseController
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
            'from_address' => 'nullable|string', # 模糊搜索
            'to_address' => 'nullable|string', # 模糊搜索
            'type' => 'nullable|integer', # 1:Referral / 2:Referred / 3:PledgeProfit / 4:VerifyIdentity
            'status' => 'nullable|boolean', # 1:Waiting / 2:Failed / 3:Success
        ]);
        return Bonuses::query()
            ->with('from:id,nickname,address,avatar')
            ->with('to:id,nickname,address,avatar')
            ->ifWhereHas($params, 'from_address', 'from', function ($q) use ($params) {
                $q->ifWhereLike($params, 'from_address', 'address');
            })
            ->ifWhereHas($params, 'to_address', 'to', function ($q) use ($params) {
                $q->ifWhereLike($params, 'to_address', 'address');
            })
            ->ifWhere($params, 'type')
            ->ifWhere($params, 'status')
            ->order()
            ->paginate($this->perPage());
    }
}
