<?php

namespace LaravelCommon\App\Helpers;

use DocBlockReader\Reader;
use Exception;
use Illuminate\Support\Arr;
use ReflectionException;
use ReflectionMethod;

class ReflectHelper
{
    /**
     * @param string $filePath
     * @param string $className
     * @param string $methodName
     * @return array
     * @throws ReflectionException
     */
    public static function GetMethodParamsArray(string $filePath, string $className, string $methodName): array
    {
        $rules = self::GetMethodValidateRules($filePath, $className, $methodName);
        $data = self::CheckValidateParams($rules, ']);', '$params = $request->validate([');
        if (empty($data)) {
            $data = self::CheckValidateParams($rules, ');', '$params = $request->validate(');
            if (!empty($data)) {
                list($class, $method) = explode('::', $data[0]);
                $className = "\App\Validates\\$class";
                $methodName = str_replace('()', '', $method);
                $filePath = app_path('Validates') . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
                $rules = self::GetMethodValidateRules($filePath, $className, $methodName);
                $data = self::CheckValidateParams($rules, '];', 'return [');
            }
        }
        return self::GetMethodParamsData($data);
    }

    /**
     * @param $data
     * @return array
     */
    public static function GetMethodParamsData($data): array
    {
        $arr = [];
        foreach ($data as $item) {
            $t1 = explode('\'', $item);
            if (count($t1) < 3) continue;
            $t2 = explode('|', $t1[3]);
            $t3 = explode('#', $t1[4]);
            $t4 = [
                str_replace('.*.', '.\*.', $t1[1]),
                $t2[0] == 'nullable' ? '-' : 'Y',
                $t2[1],
                (count($t3) > 1) ? trim($t3[1]) : '-'
            ];
            $arr[] = $t4;
        }
        return $arr;
    }

    /**
     * @param string $filePath
     * @param string $className
     * @param string $methodName
     * @return array
     * @throws ReflectionException
     */
    public static function GetMethodValidateRules(string $filePath, string $className, string $methodName): array
    {
        $result = new ReflectionMethod($className, $methodName);
        $startLine = $result->getStartLine();
        $endLine = $result->getEndLine();
        $length = $endLine - $startLine;
        $source = file($filePath);
        return array_slice($source, $startLine, $length);
    }

    /**
     * @param string $className
     * @param string $methodName
     * @return array
     * @throws Exception
     */
    public static function GetMethodAnnotation(string $className, string $methodName): array
    {
        $reader = new Reader($className, $methodName);
        $data = $reader->getParameters();
        return Arr::only($data, ['intro', 'responseParams', 'response']);
    }

    /**
     * @param string $className
     * @return mixed|string
     * @throws Exception
     */
    public static function GetControllerAnnotation(string $className): mixed
    {
        $reader = new Reader($className);
        $data = $reader->getParameters();
        return $data['intro'] ?? '';
    }

    /**
     * @param array $data
     * @param string $strStart
     * @param string $strEnd
     * @return array
     */
    public static function CheckValidateParams(array $data, string $strStart, string $strEnd): array
    {
        $start = $end = false;
        $arr = [];
        foreach ($data as $line) {
            $t = trim($line);
            if ($t == $strStart) $end = true;
            if ($start && !$end)
                $arr[] = $t;
            if ($t == $strEnd) $start = true;
        }
        return $arr;
    }
}
