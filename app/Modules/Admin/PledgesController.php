<?php


namespace App\Modules\Admin;


use App\Models\Pledges;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @intro Pledges
 * Class PledgesController
 * @package App\Modules\Admin
 */
class PledgesController extends AdminBaseController
{
    /**
     * @intro list
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
            'status' => 'nullable|string', # :OnGoing,Canceled,Finished,Stopped
        ]);
        return Pledges::withUser()
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserIsDemoUser($params)
            ->ifWhereHasUserVip($params)
            ->ifWhere($params, 'is_trail')
            ->ifWhere($params, 'status')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro show for detail
     * @param Request $request
     * @return array
     */
    #[ArrayShape(['model' => "mixed", 'statistics' => "array[]"])]
    public function show(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
        ]);
        $model = Pledges::withUser()
            ->idp($params);
        return [
            'model' => $model,
            'statistics' => [
                ['title' => 'Staking', 'value' => $model->staking, 'type' => 'money'],
                ['title' => 'Apy', 'value' => $model->apy, 'type' => 'percent'],
                ['title' => 'Loyalty', 'value' => $model->loyalty, 'type' => 'money'],
            ]
        ];
    }
}
