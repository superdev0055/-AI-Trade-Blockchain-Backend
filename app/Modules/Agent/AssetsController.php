<?php

namespace App\Modules\Agent;

use App\Enums\AssetsPendingTypeEnum;
use App\Enums\AssetsTypeEnum;
use App\Models\Assets;
use App\Modules\AgentBaseController;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

class AssetsController extends AgentBaseController
{
    /**
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function stakings(Request $request): mixed
    {
        $params = $request->validate([
            'user_address' => 'nullable|string', # 用户地址
            'user_vips_id' => 'nullable|integer', # Vip id
        ]);
        $user = $this->getUser();
        return Assets::where('type', AssetsTypeEnum::Staking->name)
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserVip($params)
            ->myChildren($user)
            ->withUser()
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function withdrawable(Request $request): mixed
    {
        $params = $request->validate([
            'user_address' => 'nullable|string', # 用户地址
            'user_vips_id' => 'nullable|integer', # Vip id
        ]);
        $user = $this->getUser();
        return Assets::where('type', AssetsTypeEnum::WithdrawAble->name)
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserVip($params)
            ->myChildren($user)
            ->withUser()
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function pendings(Request $request): mixed
    {
        $params = $request->validate([
            'user_address' => 'nullable|string', # 用户地址
            'user_vips_id' => 'nullable|integer', # Vip id
            'pending_type' => 'nullable|string', # type: AssetsPendingTypeEnum
            'pending_status' => 'nullable|string', # status: AssetsPendingStatusEnum
        ]);
        $user = $this->getUser();
        return Assets::where('type', AssetsTypeEnum::Pending->name)
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserVip($params)
            ->myChildren($user)
            ->ifWhere($params, 'pending_type')
            ->ifWhere($params, 'pending_status')
            ->withUser()
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 待审批列表
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function withdrawApproveList(Request $request): mixed
    {
        $params = $request->validate([
            'user_address' => 'nullable|string', # 用户地址
            'user_vips_id' => 'nullable|integer', # Vip id
            'pending_status' => 'nullable|string', # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED,APPROVE
        ]);
        $user = $this->getUser();
        return Assets::where('type', AssetsTypeEnum::Pending->name)
            ->where('pending_type', AssetsPendingTypeEnum::Withdraw->name)
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserVip($params)
            ->myChildren($user)
            ->ifWhere($params, 'pending_status')
            ->withUser()
            ->with('pending_withdrawal_approve_user:id,nickname,avatar,address,vips_id')
            ->order()
            ->paginate($this->perPage());
    }
}
