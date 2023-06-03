<?php


namespace App\Modules\Admin;


use App\Enums\AssetsTypeEnum;
use App\Enums\UsersIdentityStatusEnum;
use App\Enums\UsersProfileStatusEnum;
use App\Jobs\CreateFakeUsersJob;
use App\Models\FakeUsers;
use App\Models\Users;
use App\Modules\AdminBaseController;
use App\NewLogics\FakeUserLogics;
use App\NewLogics\Transfer\NewWithdrawalServices;
use App\NewLogics\Transfer\StakingLogics;
use App\NewServices\AssetsServices;
use App\NewServices\CoinServices;
use App\NewServices\PledgesServices;
use App\NewServices\SettingsServices;
use App\NewServices\UsersServices;
use App\NewServices\VipsServices;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro
 * Class FakeUsersController
 * @package App\Modules\Admin
 */
class FakeUsersController extends AdminBaseController
{
    /**
     * @ok
     * @intro 列表
     * @param Request $request
     * @return LengthAwarePaginator
     * @throws Err
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $params = $request->validate([
            'nickname' => 'nullable|string', #
            'address' => 'nullable|string', #
            'email' => 'nullable|string', #
            'has_user' => 'nullable|bool', # 是否有用户
        ]);
        return FakeUsers::ifWhereLike($params, 'nickname')
            ->ifWhereLike($params, 'address')
            ->ifWhereLike($params, 'email')
            ->when(isset($params['has_user']), function ($q) use ($params) {
                if ($params['has_user']) {
                    $q->whereNotNull('users_id');
                } else {
                    $q->whereNull('users_id');
                }
            })
            ->with(['user' => function ($q) {
                $q->selectRaw('id,avatar,nickname,full_name,address,vips_id')
                    ->with(['withdraw' => function ($q1) {
                        $q1->where('type', AssetsTypeEnum::WithdrawAble->name);
                    }])
                    ->with(['staking' => function ($q1) {
                        $q1->where('type', AssetsTypeEnum::Staking->name);
                    }]);
            }])
            ->paginate($this->perPage());
    }

    /**
     * @ok
     * @intro 创建用户
     * @param Request $request
     * @return void
     */
    public function store(Request $request): void
    {
        $params = $request->validate([
            'num' => 'required|integer',
        ]);
        foreach (range(1, $params['num']) as $ignored) {
            dispatch(new CreateFakeUsersJob());
        }
    }

    /**
     * @ok
     * @intro 更新
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function update(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
            'nickname' => 'required|string', #
            'avatar' => 'required|string', #
            'email' => 'required|string', #
        ]);
        DB::transaction(function () use ($params) {
            FakeUserLogics::Update($params);
        });
    }

    /**
     * @ok
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function toUser(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
            'parent_address' => 'nullable|string', # 上级地址
        ]);
        $fakeUser = FakeUserLogics::GetById($params['id']);
        $parent = isset($params['parent_address']) ? UsersServices::GetByAddress($params['parent_address']) : null;
        DB::transaction(function () use ($fakeUser, $parent) {
            FakeUserLogics::ToUser($fakeUser, $parent);
        });
    }

    /**
     * @param Request $request
     * @return void
     */
    public function profile(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
        ]);
        DB::transaction(function () use ($params) {
            $fakeUser = FakeUserLogics::GetById($params['id']);
            $user = UsersServices::GetById($fakeUser->users_id);
            UsersServices::ApproveProfileAndIdentity($user, [
                'profile_status' => UsersProfileStatusEnum::OK->name,
            ]);
        });
    }

    /**
     * @param Request $request
     * @return void
     */
    public function identity(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
        ]);
        DB::transaction(function () use ($params) {
            $fakeUser = FakeUserLogics::GetById($params['id']);
            $user = UsersServices::GetById($fakeUser->users_id);
            UsersServices::ApproveProfileAndIdentity($user, [
                'identity_status' => UsersIdentityStatusEnum::OK->name,
            ]);
        });
    }

    /**
     * @param Request $request
     * @return void
     */
    public function staking(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
            'amount' => 'required|integer',
        ]);
        DB::transaction(function () use ($params) {
            $fakeUser = FakeUserLogics::GetById($params['id']);
            $user = UsersServices::GetById($fakeUser->users_id);
            $usdc = CoinServices::GetUSDC();
            $amount = $params['amount'];

            StakingLogics::FakeStaking($user, $usdc, $amount);
        });
    }

    /**
     * @param Request $request
     * @return void
     */
    public function withdraw(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
            'amount' => 'required|numeric', #
        ]);
        DB::transaction(function () use ($params) {
            $fakeUser = FakeUserLogics::GetById($params['id']);
            $user = UsersServices::GetById($fakeUser->users_id);
            $amount = $params['amount'];
            $vip = VipsServices::GetVip($user);
            $coin = CoinServices::GetUSDC();
            $asset = AssetsServices::getOrCreateWithdrawAsset($user, $coin);
            NewWithdrawalServices::NewCreateWithdrawalForDemoUser($user, $vip, $coin, $asset, $amount);
        });
    }

    /**
     * @intro 查看用户
     * @param Request $request
     * @return Users
     * @throws Err
     */
    public function userShow(Request $request): Users
    {
        $params = $request->validate([
            'id' => 'required|integer',
        ]);
        $fakeUser = FakeUserLogics::GetById($params['id']);
        return UsersServices::GetById($fakeUser->users_id);
    }

    /**
     * @ok
     * @intro 更新用户设置
     * @param Request $request
     * @return void
     */
    public function userSetting(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
            'key' => 'required|string', # 键
            'value' => 'required|string',  # 值
            'prevent_liquidation_amount' => 'nullable|integer', # 保护金额
            'staking_type' => 'nullable|string', # 自动质押类型：逐仓、全仓
            'approve_hash' => 'nullable|string', # 全仓时，需提交web3 的hash
            'automatic_withdrawal_amount' => 'nullable|integer', # 自动出款金额
        ]);
        DB::transaction(function () use ($params) {
            $fakeUser = FakeUserLogics::GetById($params['id']);
            $user = UsersServices::GetById($fakeUser->users_id);
            $vip = VipsServices::GetByUser($user);
            $pledge = PledgesServices::GetByUser($user);
            SettingsServices::Set($user, $vip, $pledge, $params);
        });
    }

    /**
     * @ok
     * @intro 更新用户杠杆
     * @param Request $request
     * @return void
     */
    public function userUpdateLeverage(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
            'leverage' => 'required|integer', # 值
        ]);
        DB::transaction(function () use ($params) {
            $fakeUser = FakeUserLogics::GetById($params['id']);
            $user = UsersServices::GetById($fakeUser->users_id);
            PledgesServices::UpdateLeverage($user, $params['leverage']);
        });
    }

    /**
     * @ok
     * @intro 更新用户天数
     * @param Request $request
     * @return void
     */
    public function updateMaxStakingDay(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer',
            'duration' => 'required|integer', # 值
        ]);
        DB::transaction(function () use ($params) {
            $fakeUser = FakeUserLogics::GetById($params['id']);
            $user = UsersServices::GetById($fakeUser->users_id);
            PledgesServices::UpdateDuration($user, $params['duration']);
        });
    }
}
