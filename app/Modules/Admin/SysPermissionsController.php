<?php


namespace App\Modules\Admin;


use App\Models\SysPermissions;
use App\Modules\AdminBaseController;
use Exception;
use Illuminate\Http\Request;
use LaravelCommon\App\Helpers\CommonHelper;

/**
 * @intro 权限管理
 * Class SysPermissionsController
 * @package App\Modules\Admin
 */
class SysPermissionsController extends AdminBaseController
{
    /**
     * @intro 列表
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request): mixed
    {
        $params = $request->validate([
            'parent_id' => 'nullable|integer', # 上级ID
        ]);
        if (isset($params['parent_id']))
            return SysPermissions::defaultOrder()
                ->descendantsAndSelf($params['parent_id'])
                ->toTree();
        else
            return SysPermissions::defaultOrder()
                ->get()
                ->toTree();
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
            'title' => 'required|string', # 标题
            'url' => 'nullable|string', # 菜单链接
            'parent_id' => 'nullable|integer', # 上级ID
        ]);
        SysPermissions::unique($params, ['name'], '名称');
        if (isset($params['parent_id'])) {
            $parent = SysPermissions::findOrFail($params['parent_id']);
            unset($params['parent_id']);
            $parent->children()->create($params);
        } else {
            SysPermissions::create($params);
        }
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
            'id' => 'required|integer', # ID
            'name' => 'required|string', # 名称
            'title' => 'required|string', # 标题
            'url' => 'nullable|string', # 菜单链接
            'parent_id' => 'nullable|integer', # 上级ID
        ]);
        SysPermissions::unique($params, ['name'], '名称');
        CommonHelper::Trans(function () use ($params) {
            $model = SysPermissions::idp($params);
            $model->update($params);
            $model->fixTree();
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
            'id' => 'required|integer', # ID
        ]);
        $node = SysPermissions::idp($params);
        $node->delete();
        return [];
    }

    /**
     * @intro 修改等级
     * @param Request $request
     * @return array
     */
    public function move(Request $request): array
    {
        $params = $request->validate([
            'id' => 'required|integer', # ID
            'type' => 'required|string|in:up,down', # 类型：up 升级，down 降级
        ]);
        $node = SysPermissions::idp($params);
        if ($params['type'] == 'up')
            $node->up();
        else
            $node->down();
        return [];
    }

    /**
     * @intro 树形结构
     * @return mixed
     */
    public function tree(): mixed
    {
        return SysPermissions::selectRaw('id,CONCAT(id,"_",title) as name,parent_id,_lft,_rgt')->defaultOrder()
            ->get()
            ->toTree();
    }
}
