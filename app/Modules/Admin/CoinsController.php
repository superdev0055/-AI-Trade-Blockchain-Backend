<?php


namespace App\Modules\Admin;


use App\Models\Coins;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class CoinsController
 * @package App\Modules\Admin
 */
class CoinsController extends AdminBaseController
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
            'name' => 'nullable|string',
            'symbol' => 'nullable|string',
            'address' => 'nullable|string',
        ]);
        return Coins::ifWhereLike($params, 'name')
            ->ifWhereLike($params, 'symbol')
            ->ifWhereLike($params, 'address')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 选择coin
     * @return mixed
     */
    public function select(): mixed
    {
        return Coins::selectRaw('id as value ,symbol as label')
            ->get();
    }
}
