<?php

namespace LaravelCommon\App\Helpers;

use Illuminate\Support\Facades\File;

class StubHelper
{
    /**
     * @param string $stubName
     * @return string
     */
    public static function GetStub(string $stubName): string
    {
        $path = resource_path('stubs/' . $stubName);
        if (!File::exists($path))
            $path = __DIR__ . '/../../resources/stubs/' . $stubName;
        return File::get($path);
    }

    /**
     * @param array $array
     * @param string $stubContent
     * @return string
     */
    public static function Replace(array $array, string $stubContent): string
    {
        foreach ($array as $key => $value) {
            $stubContent = str_replace($key, $value, $stubContent);
        }
        return $stubContent;
    }

    /**
     * @param string $filePath
     * @param string $stubContent
     * @param bool $force
     * @return string
     */
    public static function Save(string $filePath, string $stubContent, bool $force = false): string
    {
        $exists = File::exists($filePath);
        if (!$exists || $force) {
            File::makeDirectory(File::dirname($filePath), 0755, true, true);
            File::put($filePath, $stubContent);
            return "Make file...$filePath";
        } else {
            return "File exist";
        }
    }
}
