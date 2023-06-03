<?php


namespace App\Modules\Agent;


use App\Models\Users;
use App\Modules\AgentBaseController;
use LaravelCommon\App\Exceptions\Err;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @intro 登录
 * Class AuthController
 * @package App\Modules\Admin
 */
class AuthController extends AgentBaseController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('login');
    }

    /**
     * @intro 登录
     * @param Request $request
     * @return array
     * @throws Err
     */
    public function login(Request $request): array
    {
        $params = $request->validate([
            'username' => 'required|string', # 用户名
            'password' => 'required|string', # 密码
        ]);
        // 验证用户密码
        $user = Users::where('username', $params['username'])->first();
        if (!$user || !Hash::check($params['password'], $user->password)) {
            Err::Throw(__("Account or password error"));
        }
        // 删除其他token
        $user->tokens()->delete();
        // 返回信息
        return [
            'user' => $user->only('id', 'username', 'avatar', 'nickname'),
            'token' => ['access_token' => $user->createToken('admin', ['admin'])->plainTextToken],
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
     * @return array
     * @throws Err
     */
    public function me(): array
    {
        $user = $this->getUser();
        return [
            'user' => $user->only('id', 'username'),
        ];
    }
}
