<?php


namespace App\Modules\Admin;


use App\Models\JackpotLogs;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class JackpotLogsController
 * @package App\Modules\Admin
 */
class JackpotLogsController extends AdminBaseController
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
//            'jackpots_id' => 'nullable|integer',
//            'jackpot_has_users_id' => 'nullable|integer',
            'address' => 'nullable|string', # 地址
        ]);
        return JackpotLogs::withUser()
            ->ifWhere($params, 'jackpots_id')
            ->ifWhere($params, 'jackpot_has_users_id')
            ->order()
            ->paginate($this->perPage());
    }
}
