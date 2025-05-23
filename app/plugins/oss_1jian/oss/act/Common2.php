<?php

if (is_file(__DIR__ . '/../autoload.php')) {
    require_once __DIR__ . '/../autoload.php';
}
if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
require_once __DIR__ . '/Config.php';

use OSS\OssClient;
use OSS\Core\OssException;

/**
 * Class Common
 *
 * The Common class for 【Samples/*.php】 used to obtain OssClient instance and other common functions
 */
class Common2
{
    const endpoint = ConfigOss::OSS_ENDPOINT;
    const accessKeyId = ConfigOss::OSS_ACCESS_ID;
    const accessKeySecret = ConfigOss::OSS_ACCESS_KEY;
    const bucket = ConfigOss::OSS_TEST_BUCKET;

    /**
     * Get an OSSClient instance according to config.
     *
     * @return OssClient An OssClient instance
     */
    public static function getOssClient($accessKeyId,$accessKeySecret,$endpoint)
    {
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }
        return $ossClient;
    }

    public static function getBucketName()
    {
    //return "xxx";
        return self::bucket;
    }


    //PHP stdClass Object转array  
    public static function object_array($array) {  
    if(is_object($array)) {  
        $array = (array)$array;  
     } if(is_array($array)) {  
         foreach($array as $key=>$value) {  
             $array[$key] = self::object_array($value);  
             }  
     }  
     return $array;  
}
    /**
     * A tool function which creates a bucket and exists the process if there are exceptions
     */
    public static function createBucket()
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) exit(1);
        $bucket = self::getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            $ossClient->createBucket($bucket, $acl);
        } catch (OssException $e) {

            $message = $e->getMessage();
            if (\OSS\Core\OssUtil::startsWith($message, 'http status: 403')) {
                echo "Please Check your AccessKeyId and AccessKeySecret" . "\n";
                exit(0);
            } elseif (strpos($message, "BucketAlreadyExists") !== false) {
                echo "Bucket already exists. Please check whether the bucket belongs to you, or it was visited with correct endpoint. " . "\n";
                exit(0);
            }
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
    }

    public static function println($message)
    {
        if (!empty($message)) {
         //   echo strval($message) . "\n";
        }
    }
}

# Common::createBucket();
