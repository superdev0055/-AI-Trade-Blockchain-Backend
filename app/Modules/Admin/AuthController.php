<?php


namespace App\Modules\Admin;


use App\Models\Reports;
use Illuminate\Support\Facades\Cache;
use LaravelCommon\App\Exceptions\Err;
use App\Models\Admins;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Helpers\LaravelPermissionHelper;

/**
 * @intro 登录
 * Class AuthController
 * @package App\Modules\Admin
 */
class AuthController extends AdminBaseController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('login');
    }

    /**
     * @intro 登录
     * @param Request $request
     * @param LaravelPermissionHelper $helper
     * @return array
     * @throws Err
     */
    #[ArrayShape(['user' => "mixed", 'token' => "array", 'permissions' => "array"])]
    public function login(Request $request, LaravelPermissionHelper $helper): array
    {
        $params = $request->validate([
            'username' => 'required|string', # 用户名
            'password' => 'required|string', # 密码
        ]);
        // 验证用户密码
        $user = Admins::where('username', $params['username'])->first();
        if (!$user || !Hash::check($params['password'], $user->password)) {
            Err::Throw(__("Account or password error"));
        }
        // 删除其他token
        $user->tokens()->delete();
        // 返回信息
        return [
            'user' => $user->only('id', 'username'),
            'token' => ['access_token' => $user->createToken('admin', ['admin'])->plainTextToken],
            'permissions' => $helper->getPermissionByUser($user)
        ];
    }

    /**
     * @intro 退出登录
     * @return array
     * @throws Err
     */
    public function logout(): array
    {
        $user = $this->getUser();
        $user->tokens()->delete();
        return [];
    }

    /**
     * @intro 获取我的信息
     * @param LaravelPermissionHelper $helper
     * @return array
     * @throws Err
     */
    #[ArrayShape(['user' => "array", 'permissions' => "array", 'distributor' => "mixed"])]
    public function me(LaravelPermissionHelper $helper): array
    {
        $user = $this->getUser();
        return [
            'user' => $user->only('id', 'username'),
            'permissions' => $helper->getPermissionByUser($user),
        ];
    }

    public function upload(Request $request)
    {
//        $params = $request->validate([
//            'avatar' => 'required|file'
//        ]);
//        $file = request()->file('avatar');
//        $path = $file->store('public');
//        return ['url' => '/storage/' . str_replace('public/', '', $path)];
    }

    /**
     * @return array
     */
    public function report(): array
    {
        return [
            'today' => Reports::where('day', now()->toDateString())->first()->toArray(),
            'all' => Cache::tags(['reports'])->get('all')
        ];
    }
}
