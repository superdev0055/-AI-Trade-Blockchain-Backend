<?php


namespace App\Modules\Agent;


use App\Models\UserFollows;
use App\Modules\AgentBaseController;
use App\NewServices\UsersServices;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro 跟进客户
 * Class UserFollowsController
 * @package App\Modules\Agent
 */
class UserFollowsController extends AgentBaseController
{
    /**
     * @intro 单个用户的跟进列表
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function list(Request $request): mixed
    {
        $params = $request->validate([
            'users_id' => 'required|integer', # 关注的用户id
        ]);
        $user = $this->getUser();
        return UserFollows::myChildren($user)
            ->where('agent_users_id', $user->id)
            ->where('users_id', $params['users_id'])
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 添加跟进
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function store(Request $request): void
    {
        $params = $request->validate([
            'users_id' => 'required|integer', # ref[Users]
            'content' => 'required|string', #
        ]);
        $params['agent_users_id'] = $this->getUser()->id;
        $user = UsersServices::GetById($params['users_id']);
        $this->checkIsMyChildren($user);
        UserFollows::create($params);
    }
}
