<?php

namespace App\Modules\Admin;

use App\Modules\AdminBaseController;
use App\NewServices\ConfigsServices;
use Illuminate\Http\Request;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro 参数配置
 * Class ConfigsController
 * @package App\Modules\Admin
 */
class ConfigsController extends AdminBaseController
{
    /**
     * @intro 获取配置
     * @param Request $request
     * @return array|null
     * @throws Err
     */
    public function get(Request $request): ?array
    {
        $params = $request->validate([
            'key' => 'required|string',
        ]);
        return ConfigsServices::Get($params['key']);
    }

    /**
     * @intro 保存配置
     * @param Request $request
     * @return array
     */
    public function save(Request $request): array
    {
        $params = $request->validate([
            'key' => 'required|string',
            'value' => 'required|json',
        ]);
        ConfigsServices::Save($params);
        return [];
    }
}
