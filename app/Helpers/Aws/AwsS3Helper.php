<?php

namespace App\Helpers\Aws;

use App\Models\Users;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SplFileInfo;

class AwsS3Helper
{
    /**
     * @ok
     * @param string $base64OrUrl
     * @param string $type
     * @return string
     */
    public static function Store(string $base64OrUrl, string $type): string
    {
        if (Str::startsWith($base64OrUrl, 'https://') || Str::startsWith($base64OrUrl, '/uploads/'))
            return $base64OrUrl;
        list($ext, $content) = self::getImgInfoFromBase64($base64OrUrl);
        $uuid = uuid_create();
        $fileName = "uploads/$type/$uuid.$ext";
        Storage::disk('r2')->put($fileName, $content);
        return Storage::disk('r2')->url($fileName);
    }

    /**
     * @ok
     * @param UploadedFile $file
     * @param string $type
     * @return string
     */
    public static function StoreFile(UploadedFile $file, string $type): string
    {
        $ext = $file->getClientOriginalExtension();
        $uuid = uuid_create();
        $fileName = "uploads/$type/$uuid.$ext";
        Storage::disk('r2')->put($fileName, $file->getContent());
        return Storage::disk('r2')->url($fileName);
    }

    /**
     * @ok
     * @param string $base64
     * @return array
     */
    private static function getImgInfoFromBase64(string $base64): array
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            $ext = $result[2];
            $content = base64_decode(str_replace($result[1], '', $base64));
            return [$ext, $content];
        } else {
            return [null, null];
        }
    }
}
