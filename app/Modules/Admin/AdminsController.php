<?php


namespace App\Modules\Admin;


use App\Models\Admins;
use App\Modules\AdminBaseController;
use Exception;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

/**
 * @intro 管理员
 * Class AdminsController
 * @package App\Modules\Admin
 */
class AdminsController extends AdminBaseController
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
            'username' => 'nullable|string', # 模糊搜索：名称
        ]);
        return Admins::ifWhereLike($params, 'username')
            ->with('roles:id,name,color')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 添加
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function store(Request $request): array
    {
        $params = $request->validate([
            'username' => 'required|string', # 用户名
            'password' => 'required|string', # 密码
            'roles_id' => 'nullable|array', # 角色ID
        ]);
        Admins::unique($params, ['username'], '用户名');
        CommonHelper::Trans(function () use ($params) {
            $this->crypto($params);
            $model = Admins::create($params);
            if (isset($params['roles_id'])) {
                $model->syncRoles($params['roles_id']);
            }
        });
        return [];
    }

    /**
     * @intro 修改
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function update(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
            'username' => 'required|string', # 用户名
            'password' => 'nullable|string', # 密码
            'roles_id' => 'nullable|array', # 角色ID
        ]);
        $this->crypto($params);
        Admins::unique($params, ['username'], '用户名');
        CommonHelper::Trans(function () use ($params) {
            $model = Admins::idp($params);
            $model->update($params);
            if (isset($params['roles_id'])) {
                $model->syncRoles($params['roles_id']);
            }
        });
        return [];
    }

    /**
     * @intro 删除
     * @param Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # id
        ]);
        Admins::idp($params)->delete();
        return [];
    }
}
