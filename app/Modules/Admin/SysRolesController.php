<?php


namespace App\Modules\Admin;


use LaravelCommon\App\Exceptions\Err;
use App\Models\SysPermissions;
use App\Models\SysRoles;
use App\Modules\AdminBaseController;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use Spatie\Permission\Models\Role;

/**
 * @intro 角色管理
 * Class SysRolesController
 * @package App\Modules\Admin
 */
class SysRolesController extends AdminBaseController
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
            'name' => 'nullable|string', # 模糊搜索：名称
        ]);
        return SysRoles::ifWhereLike($params, 'name')
            ->order()
            ->paginate($this->perPage());
    }

    /**
     * @intro 添加
     * @param Request $request
     * @return array
     */
    public function store(Request $request): array
    {
        $params = $request->validate([
            'name' => 'required|string', # 名称
            'color' => 'nullable|string' # 颜色
        ]);
        $params['guard_name'] = 'sanctum';
        SysRoles::unique($params, ['name'], '角色名称');
        SysRoles::create($params);
        return [];
    }

    /**
     * @intro 修改
     * @param Request $request
     * @return array
     */
    public function update(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # ID
            'name' => 'required|string', # 名称
            'color' => 'nullable|string' # 颜色
        ]);
        SysRoles::unique($params, ['name'], '名称');
        SysRoles::idp($params)->update($params);
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
            'id' => 'required|integer' # ID
        ]);
        SysRoles::idp($params)->delete();
        return [];
    }

    /**
     * @intro 获取角色列表
     * @return mixed
     */
    public function select(): mixed
    {
        return SysRoles::selectRaw("id,name")->get();
    }

    /**
     * @intro 获取角色权限ID
     * @param Request $request
     * @return array
     */
    #[ArrayShape(['permissions_ids' => "array"])]
    public function getPermissions(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer' # ID
        ]);
        $role = Role::findOrFail($params['id']);
        return [
            'permissions_ids' => array_column($role->getAllPermissions()->toArray(), 'id')
        ];
    }

    /**
     * @intro 添加角色权限
     * @param Request $request
     * @return array
     */
    public function setPermissions(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # ID
            'permissions_ids' => 'required|array' # 权限ID
        ]);
        $role = Role::findOrFail($params['id']);
        $sysPermissions = SysPermissions::whereIn('id', $params['permissions_ids'])->pluck('id')->toArray();
        $role->syncPermissions($sysPermissions);
        return [];
    }
}
