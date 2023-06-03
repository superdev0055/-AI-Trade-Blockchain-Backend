<?php


namespace App\Modules\Admin;


use App\Models\Subscribes;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class SubscribesController
 * @package App\Modules\Admin
 */
class SubscribesController extends AdminBaseController
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
            'email' => 'nullable|string', # 模糊搜索：email
        ]);
        return Subscribes::ifWhereLike($params,'email')
            ->order()
            ->paginate($this->perPage());
    }
}
