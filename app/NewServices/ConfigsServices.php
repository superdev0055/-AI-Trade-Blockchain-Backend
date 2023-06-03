<?php

namespace App\NewServices;

use App\Models\Configs;
use LaravelCommon\App\Exceptions\Err;

class ConfigsServices
{
    /**
     * @todo 加缓存
     * 获取配置参数
     * @param string $key
     * @return array|null
     * @throws Err
     */
    public static function Get(string $key): ?array
    {
        $config = self::GetConfig();
        if ($config) {
            if (!$config->$key)
                return null;
            return json_decode($config->$key, true);
        } else {
            Err::Throw(__("The system config is not set"));
        }
    }

    /**
     * 保存配置参数
     * @param array $params
     * @return void
     */
    public static function Save(array $params): void
    {
        $config = self::GetConfig();
        if ($config) {
            $config->update([
                $params['key'] => $params['value']
            ]);
        } else {
            Configs::create([
                $params['key'] => $params['value']
            ]);
        }
    }

    /**
     * @todo 加缓存
     * 获取所有配置
     * @return mixed
     */
    public static function GetConfig(): mixed
    {
        return Configs::first();
    }
}
