<?php

namespace Tests\Helpers\Aws;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AwsS3HelperTest extends TestCase
{

    public function testStore()
    {

    }

    public function testStoreFile()
    {
        // path 是目录
        // contents是文件内容
        // 组合起来 /movies/test.txt
        $path = 'movies/test.txt';
        $content = "123";
        Storage::disk('r2')->put($path, $content);
        $finalUrl = 'https://upload.aitrade.com.co/' . $path;
        dump($finalUrl);
    }
}
