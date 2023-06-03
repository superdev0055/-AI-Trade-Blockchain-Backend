<?php


namespace App\Modules\Admin;


use App\Models\AssetLogs;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class AssetLogsController
 * @package App\Modules\Admin
 */
class AssetLogsController extends AdminBaseController
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
            'type' => 'nullable|string', # 类型:AssetsTypeEnum
            'remark' => 'nullable|string', # 备注
        ]);
        return AssetLogs::ifWhereHasUserAddress($params)
            ->ifWhereHasUserIsDemoUser($params)
            ->ifWhereHasUserVip($params)
            ->ifWhere($params, 'type')
            ->ifWhereLike($params, 'remark')
            ->order()
            ->withUser()
            ->paginate($this->perPage());
    }
}
