<?php


namespace App\Modules\Admin;


use App\Enums\SysMessageTypeEnum;
use App\Models\SysMessages;
use App\Models\Users;
use App\Modules\AdminBaseController;
use App\NewServices\NewbieCardServices;
use App\NewServices\UsersServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @intro Users
 * Class UsersController
 * @package App\Modules\Admin
 */
class UsersController extends AdminBaseController
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
            'nickname' => 'nullable|string',
            'address' => 'nullable|string',
            'parent_address' => 'nullable|string',
            'trailed' => 'nullable|boolean',
            'email_verified' => 'nullable|boolean',
            'profile_status' => 'nullable|string', # 1:Default / 2:Waiting / 3:OK
            'identity_status' => 'nullable|string', # 1:Default / 2:Waiting / 3:OK
            'status' => 'nullable|string',
            'is_cool_user' => 'nullable|boolean', # Yes / No
            'user_vips_id' => 'nullable|integer',
            'invite_code' => 'nullable|string',
        ]);
        return $this->getQuery($params)->paginate($this->perPage());
    }

    /**
     * @intro Download
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function download(Request $request): StreamedResponse|string
    {
        $params = $request->validate([
            'nickname' => 'nullable|string',
            'address' => 'nullable|string',
            'parent_address' => 'nullable|string',
            'trailed' => 'nullable|boolean',
            'email_verified' => 'nullable|boolean',
            'profile_status' => 'nullable|string', # 1:Default / 2:Waiting / 3:OK
            'identity_status' => 'nullable|string', # 1:Default / 2:Waiting / 3:OK
            'status' => 'nullable|string',
            'is_cool_user' => 'nullable|boolean', # Yes / No
            'user_vips_id' => 'nullable|integer',
            'download_type' => 'required|integer' # 下载类型：1当前页(含查询条件)，2所有页(含查询条件)，3所有记录(不含查询条件)
        ]);
        return UsersServices::Export($this->getQuery($params));
    }

    /**
     * @intro update
     * @param Request $request
     * @return void
     */
    public function update(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
            'parent_address' => 'nullable|string', # 上级地址
            'profile_status' => 'nullable|string', # 1:Default / 2:Waiting / 3:OK / 4:Failed
            'profile_error_message' => 'nullable|string', # 错误信息，选择Failed时，需要提供原因
            'identity_status' => 'nullable|string', # 1:Default / 2:Waiting / 3:OK / 4:Failed
            'identity_error_message' => 'nullable|string', # 错误信息，选择Failed时，需要提供原因
            'status' => 'nullable|string', # 1:Disable / 2:Enable
        ]);

        DB::transaction(function () use ($params) {
            $user = Users::idp($params);
            UsersServices::ApproveProfileAndIdentity($user, $params);
        });
    }

    /**
     * @intro show for detail
     * @param Request $request
     * @return array
     */
    #[ArrayShape(['model' => "", 'statistics' => "array[]"])]
    public function show(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
        ]);
        $model = Users::withParents()->with('vip:id,name')->idp($params);
        return [
            'model' => $model,
            'statistics' => [
                ['title' => __('Total Balance'), 'value' => $model->total_balance, 'type' => 'money'],
                ['title' => __('Total Staking'), 'value' => $model->total_staking_amount, 'type' => 'money'],
                ['title' => __('Total Loyalty'), 'value' => $model->total_loyalty_value, 'type' => 'money'],
            ]
        ];
    }

    /**
     * @intro 禁言/解禁
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function toggleCanSay(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
        ]);
        $user = UsersServices::GetById($params['id']);
        $user->can_say = !$user->can_say;
        $user->save();
    }

    /**
     * @intro 查看用户发送的留言信息
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function userMessage(Request $request): mixed
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
        ]);
        $user = UsersServices::GetById($params['id']);
        return SysMessages::where('users_id', $user->id)
            ->where('type', SysMessageTypeEnum::FriendMessage->name)
            ->paginate($this->perPage());
    }

    /**
     * @intro 查看用户的推荐人列表
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function referralList(Request $request): mixed
    {
        $params = $request->validate([
            'from_address' => 'nullable|string',
            'to_address' => 'nullable|string',
        ]);
        return Users::selectRaw('id,parent_1_id,nickname,email,avatar,address,created_at')
            ->with('parent_1:id,nickname,email,avatar,address')
            ->whereNotNull('parent_1_id')
            ->ifWhereLike($params, 'to_address', 'address')
            ->ifWhereHas($params, 'from_address', 'parent_1', function ($q) use ($params) {
                $q->where('address', 'like', "%{$params['from_address']}%");
            })
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 给用户添加代理的账号密码
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function updateUsernameAndPassword(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', # 用户id
            'username' => 'required|string', # 用户名
            'password' => 'required|string', # 密码
        ]);
        $this->crypto($params);
        $user = UsersServices::GetById($params['id']);
        $exists = Users::where('username', $params['username'])->where('id', '!=', $user->id)->exists();
        if ($exists)
            Err::Throw(__("Username already exists"));
        $user->update($params);
    }

    /**
     * @param array $params
     * @return mixed
     */
    private function getQuery(array $params): mixed
    {
        return Users::withParents()
            ->with('parent_1:id,address')
            ->with('vip:id,name')
            ->order()
            ->when(!isset($params['download_type']) || $params['download_type'] == 2, function ($q) use ($params) {
                return $q
                    ->ifWhereLike($params, 'nickname')
                    ->ifWhereLike($params, 'full_name')
                    ->ifWhereLike($params, 'address')
                    ->ifWhereHas($params, 'parent_address', 'parent_1', function ($q) use ($params) {
                        $q->where('address', 'like', "%{$params['parent_address']}%");
                    })
                    ->when(isset($params['trailed']), function ($q) use ($params) {
                        return $params['trailed'] ? $q->where('trailed_at', '!=', null) : $q->where('trailed_at', null);
                    })
                    ->when(isset($params['email_verified']), function ($q) use ($params) {
                        return $params['email_verified'] ? $q->where('email_verified_at', '!=', null) : $q->where('email_verified_at', null);
                    })
                    ->ifWhere($params, 'invite_code')
                    ->ifWhere($params, 'profile_status')
                    ->ifWhere($params, 'identity_status')
                    ->ifWhere($params, 'invite_code')
                    ->ifWhere($params, 'status')
                    ->ifWhere($params, 'is_cool_user')
                    ->ifWhere($params, 'user_vips_id', 'vips_id');
            });
    }

    /**
     * @intro 领取新手卡
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function manualOpenCard(Request $request): void
    {
        $params = $request->validate([
            'id' => 'required|integer', # 用户id
        ]);
        $user = UsersServices::GetById($params['id']);
        NewbieCardServices::UserGetNewbieCard($user);
    }
}
