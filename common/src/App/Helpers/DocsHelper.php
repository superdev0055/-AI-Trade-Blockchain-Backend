<?php

namespace LaravelCommon\App\Helpers;

use DocBlockReader\Reader;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionClass;
use ReflectionMethod;

class DocsHelper
{
    /**
     * @return array
     */
    public static function GetReadmeMenu(): array
    {
        $path = resource_path('laravel-dev/readme');
        if (!File::isDirectory($path))
            $path = __DIR__ . '/../../resources/readme';
        return self::getReadmeChildrenDirs($path);
    }

    /**
     * @param string $path
     * @param string $subDir
     * @return array
     * @throws Exception
     */
    public static function GetModulesMenu(string $path, string $subDir = ''): array
    {
        $arr = [];
        foreach (File::directories($path . $subDir) as $dir) {
            $key = str_replace($path, '', $dir);
            $t = explode(DIRECTORY_SEPARATOR, $dir);
            $title = end($t);
            $arr[] = [
                'key' => str_replace('/', '\\', $key),
                'title' => $title,
                'subTitle' => self::getSubTitleForFolder(str_replace('/', '\\', $key)),
                'children' => self::GetModulesMenu($path, $key)
            ];
        }
        foreach (File::files($path . $subDir) as $file) {
            $fileName = $file->getFilename();
            $fileName = str_replace('.php', '', $fileName);
            $nameSpace = str_replace(DIRECTORY_SEPARATOR, '\\', $subDir . DIRECTORY_SEPARATOR . $fileName);
            if (Str::startsWith($nameSpace, '\\'))
                $nameSpace = substr($nameSpace, 1, strlen($nameSpace) - 1);
            $ref = new ReflectionClass('App\\Modules\\' . $nameSpace);
            $arr[] = [
                'key' => $nameSpace,
                'title' => $fileName,
                'subTitle' => ReflectHelper::GetControllerAnnotation('App\\Modules\\' . $nameSpace),
                'children' => self::getModulesActions($ref)
            ];
        }
        return $arr;
    }

    /**
     * @return array
     */
    public static function GetDatabaseMenu(): array
    {
        $tables = TableHelper::GetTables();
        $return = [];
        foreach ($tables as $table) {
            $return[] = [
                'key' => $table->getName(),
                'title' => $table->getName(),
                'subTitle' => $table->getComment(),
                'isLeaf' => true
            ];
        }
        return $return;
    }

    /**
     * @param $key
     * @return array
     */
    #[ArrayShape(['content' => "string"])]
    public static function GetReadmeContent($key): array
    {
        $path = resource_path('laravel-dev/readme/' . $key);
        if (!File::exists($path))
            $path = __DIR__ . '/../../resources/readme/' . $key;
        return [
            'content' => File::get($path)
        ];
    }

    /**
     * @param $key
     * @return string[]
     * @throws Exception
     */
    #[ArrayShape(['content' => "string"])]
    public static function GetModulesContent($key): array
    {
        $fullName = '\\App\\Modules\\' . $key;
        $t1 = explode('@', $fullName);
        $className = $t1[0];
        $methodName = $t1[1];
        $filePath = app_path('Modules') . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, explode('@', $key)[0]) . '.php';
        $content = '# Nothing';
        foreach (Route::getRoutes() as $route) {
            if (!Str::startsWith($route->uri, 'api/') && !Str::startsWith($route->uri, 'tenant/'))
                continue;
            if (!Str::startsWith($route->getAction()['controller'] ?? '', '\\App\\'))
                continue;
            if ($route->getAction()['controller'] != $fullName)
                continue;

            $content = GenHelper::GenApiMD($route, $filePath, $className, $methodName);
            break;
        }
        return [
            'content' => $content
        ];
    }

    /**
     * @param string $key
     * @return array
     */
    public static function GetDatabaseContent(string $key): array
    {
        $tables = TableHelper::GetTables();
        foreach ($tables as $table) {
            if ($table->getName() != $key)
                continue;
            return [
                'content' => GenHelper::GenDatabaseMD($table)
            ];
        }
        return [];
    }

    /**
     * @param string $path
     * @param string $subDir
     * @return array
     */
    private static function getReadmeChildrenDirs(string $path, string $subDir = ''): array
    {
        $arr = [];
        foreach (File::directories($path . $subDir) as $dir) {
            $key = str_replace($path, '', $dir);
            $t = explode(DIRECTORY_SEPARATOR, $dir);
            $title = end($t);
            $arr[] = [
                'key' => $key,
                'title' => $title,
                'children' => self::getReadmeChildrenDirs($path, $key)
            ];
        }
        foreach (File::files($path . $subDir) as $file) {
            $key = str_replace($path, '', $file->getPath()) . DIRECTORY_SEPARATOR . $file->getRelativePathname();
            $title = $file->getRelativePathname();
            $arr[] = [
                'key' => $key,
                'title' => $title,
                'isLeaf' => true
            ];
        }
        return $arr;
    }

    /**
     * @param $key
     * @return mixed|string
     */
    private static function getSubTitleForFolder($key): mixed
    {
        $arr = config('common.docs.foldersSubTitleConfig');
        return isset($arr[$key]) ? $arr[$key] : '';
    }

    /**
     * @param ReflectionClass $ref
     * @return array
     * @throws Exception
     */
    private static function getModulesActions(ReflectionClass $ref): array
    {
        $files = [];
        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class != $ref->getName() || $method->name == '__construct')
                continue;
            $action = new Reader($ref->getName(), $method->name);
            $files[] = [
                'key' => str_replace('App\\Modules\\', '', $ref->getName()) . '@' . $method->name,
                'title' => $method->name,
                'subTitle' => $action->getParameter('intro'),
                'isLeaf' => true,
            ];
        }
        return $files;
    }
}
