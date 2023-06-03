<?php


namespace App\Modules\Admin;


use App\Models\PledgesHasFunds;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class PledgesHasFundsController
 * @package App\Modules\Admin
 */
class PledgesHasFundsController extends AdminBaseController
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
            'pledges_id' => 'nullable|integer', # 模糊搜索：名称
        ]);
        return PledgesHasFunds::ifWhere($params,'pledges_id')
            ->order()
            ->paginate($this->perPage());
    }
}
