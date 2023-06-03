<?php

namespace App\Helpers\Aws;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

class AwsApi
{
    private S3Client $s3;
    private string $bucket_name;
    private string $public_url;

    /**
     *
     */
    public function __construct()
    {
        $this->bucket_name = "cehk-test";
        $this->public_url = 'https://pub-88f60ccd9ac64623aeae95e8e225be2c.r2.dev';

        $account_id = "a35a414ed39ce5e1c493e42585e05d1d";
        $access_key_id = "77a9bb4b25c042179c2d0858e8797a68";
        $access_key_secret = "84d4f51ab2e27bb0a28587db9149cf97db842981c639aa25b263ac955d5fc6cf";

        $credentials = new Credentials($access_key_id, $access_key_secret);

        $options = [
            'region' => 'auto',
            'endpoint' => "https://$account_id.r2.cloudflarestorage.com",
            'version' => 'latest',
            'credentials' => $credentials
        ];

        $this->s3 = new S3Client($options);
    }

    /**
     * @param string $fileName
     * @param mixed $content
     * @return string
     */
    public function putObject(string $fileName, mixed $content): string
    {
        $this->s3->putObject([
            'Bucket' => $this->bucket_name,
            'Key' => $fileName,
            'Body' => $content
        ]);
        return $this->public_url . '/' . $fileName;
    }
}
