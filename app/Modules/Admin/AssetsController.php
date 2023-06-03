<?php

namespace App\Modules\Admin;

use App\Enums\AssetsPendingTypeEnum;
use App\Enums\AssetsTypeEnum;
use App\Models\Assets;
use App\Modules\AdminBaseController;
use App\NewLogics\Transfer\NewWithdrawalServices;
use App\NewLogics\Transfer\StakingLogics;
use App\NewServices\CoinServices;
use App\NewServices\UsersServices;
use Exception;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

class AssetsController extends AdminBaseController
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
            'is_demo_user' => 'nullable|boolean', # 0 or 1
            'user_vips_id' => 'nullable|integer', # Vip id
        ]);
        return Assets::where('type', AssetsTypeEnum::Staking->name)
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserIsDemoUser($params)
            ->ifWhereHasUserVip($params)
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
            'is_demo_user' => 'nullable|boolean', # 0 or 1
            'user_vips_id' => 'nullable|integer', # Vip id
        ]);
        return Assets::where('type', AssetsTypeEnum::WithdrawAble->name)
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserIsDemoUser($params)
            ->ifWhereHasUserVip($params)
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
            'is_demo_user' => 'nullable|boolean', # Yes or No
            'user_vips_id' => 'nullable|integer', # Vip id
            'pending_type' => 'nullable|string', # type: AssetsPendingTypeEnum
            'pending_status' => 'nullable|string', # status: AssetsPendingStatusEnum
        ]);
        return Assets::where('type', AssetsTypeEnum::Pending->name)
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserIsDemoUser($params)
            ->ifWhereHasUserVip($params)
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
            'is_demo_user' => 'nullable|boolean', # Yes or No
            'user_vips_id' => 'nullable|integer', # Vip id
            'pending_status' => 'nullable|string', # status:WAITING,PROCESSING,ERROR,SUCCESS,EXPIRED,REJECTED,APPROVE
        ]);
        return Assets::where('type', AssetsTypeEnum::Pending->name)
            ->where('pending_type', AssetsPendingTypeEnum::Withdraw->name)
            ->ifWhereHasUserAddress($params)
            ->ifWhereHasUserIsDemoUser($params)
            ->ifWhereHasUserVip($params)
            ->ifWhere($params, 'pending_status')
            ->withUser()
            ->with('pending_withdrawal_approve_user:id,nickname,avatar,address,vips_id')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 审批
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function confirmWithdraw(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
            'approve' => 'required|boolean', # 是否审批通过
            'pending_withdrawal_type' => 'nullable|string', # 审批选是的时候必须填写：Automatic, Manual
            'hash' => 'nullable|string', # 审批是是，且方式选自动的时候，必须填写，手工发送的hash,
            'message' => 'nullable|string', # 审批不通过的信息
        ]);
        $asset = Assets::findOrFail($params['id']);
        NewWithdrawalServices::Approve($asset, $params);
    }

    /**
     * @intro 补Staking单
     * @param Request $request
     * @return void
     * @throws Err
     * @throws Exception
     */
    public function manualStaking(Request $request): void
    {
        $params = $request->validate([
            'user_address' => 'required|string', # 用户地址
            'coin_symbol' => 'required|string', # 币种
            'amount' => 'required|numeric', # 数量
            'hash' => 'required|string', # hash
        ]);
        $user = UsersServices::GetByAddress($params['user_address'], throw: true);
        $coin = CoinServices::GetCoin($params['coin_symbol']);
        $amount = $params['amount'];
        $hash = $params['hash'];
        StakingLogics::CreateStaking($user, $coin, $amount, $hash);
    }
}
