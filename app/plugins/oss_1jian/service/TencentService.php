<?php

namespace app\plugins\oss_1jian\service;
use think\Db;
use app\service\PluginsService;

require __DIR__.'/../tencent/vendor/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
/**
 * 会员等级服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class TencentService
{
    private  $TENCENT_APPID ;
    private $TENCENT_APPSECRET ;
    private $is_keep_on_server;
    private $TENCENT_region;
    private $TENCENT_BUCKET;
    private $cosClient;
private  $tencent_domain;
    public function __construct($TENCENT_APPID="",$TENCENT_APPSECRET="",$TENCENT_region="",$TENCENT_BUCKET="",$tencent_domain="")
    {
      if($TENCENT_APPID!="" || $TENCENT_region!="" || $TENCENT_APPSECRET!="" || $TENCENT_BUCKET!="" || $tencent_domain!="")
      {
       
$this->TENCENT_APPID=$TENCENT_APPID;
$this->TENCENT_APPSECRET=$TENCENT_APPSECRET;
$this->TENCENT_region=$TENCENT_region;
$this->TENCENT_BUCKET=$TENCENT_BUCKET;
$this->tencent_domain=$tencent_domain;


      }
      else {
        $OsscConfig=array();
        $ret = PluginsService::PluginsData('oss_1jian', [], false);
     
        if($ret['code'] == 0)
        {
         $OsscConfig= $ret['data'];
        }
      
        if($OsscConfig==null || count($OsscConfig)<=0)
        {
            $this->stateInfo = $this->getStateInfo("OSS_CONNECT_ERROR");
            
            return;
        }
      //假如是否保存文件在web服务器
        if( isset($OsscConfig["tencent_is_keep_on_server"]))
        $this->is_keep_on_server=$OsscConfig["tencent_is_keep_on_server"];

        
        $this->TENCENT_APPID = $OsscConfig["TENCENT_APPID"];
        $this->TENCENT_APPSECRET = $OsscConfig["TENCENT_APPSECRET"];
        $this->TENCENT_region = $OsscConfig["TENCENT_region"];
        $this->TENCENT_BUCKET = $OsscConfig["TENCENT_BUCKET"];
        $this->tencent_domain = $OsscConfig["tencent_domain"];
        
      }
       
   
      $this->cosClient = new \Qcloud\Cos\Client(
        array(
            'region' => $this->TENCENT_region,
            'schema' => 'http', //协议头部，默认为http
            'credentials'=> array(
                'secretId'  => $this->TENCENT_APPID ,
                'secretKey' => $this->TENCENT_APPSECRET)));

         

    }

    //上传文件
 public function  upload_tencent(&$params)
{
if(isset($params["params"]) && isset($params["params"]["path"]) && $params["params"]["path"]!="")
{
}
else {
  return;
}
     $url=$params["params"]["url"];
     $path=$params["params"]["path"];
     if($path==null && $path=="" || strstr($path,"http"))
     return;


     $object= $params["data"]["url"];
     $object=substr($object,1);
     $r="";

    try{
      $content = '';
      $fp = fopen($path, 'r'); //这里就是input中类型是file名字是file
      if ($fp) {
      while (!feof($fp)) {
      $content .= fgets($fp, 8888);
       }
       fclose($fp);
      }
     $r = $this->cosClient->putObject(array(
      'Bucket' => $this->TENCENT_BUCKET,
      'Key' =>$object,
      'Body' =>$content,
     // "Contenttype"=>"image/png"
      ));
    
    }
    catch(Excetion $e)
{
  $params["data"]["title"]="none";
}

 
        $params["params"]["url"]=$this->tencent_domain."/".$object;
        $params["data"]["url"]=$this->tencent_domain."/".$object;
     

   
if($this->is_keep_on_server==null || $this->is_keep_on_server!=1)
{
  
    \base\FileUtil::UnlinkFile($path);
}
}
//删除文件
 public function  del_tencent($params)
 {

 $url=$params["url"];
if(!strstr($url,"http"))
return;
 $url= parse_url($url);
 $path=$url["path"];
 $path=substr($path,1);




   $r="";
   try {
    $result = $this->cosClient->deleteObject(array(
        'Bucket' => $this->TENCENT_BUCKET, //格式：BucketName-APPID
        'Key' => $path,
    ));
    // 请求成功
 //   print_r($result);
} catch (\Exception $e) {
    // 请求失败
   // echo($e);
}

  
 }

 public function  del_tencent_urls($params)
 {
  $objects = array();

  foreach($params["data"] as $k=>$v)
  {
    $url=$v["url"];
    $path = parse_url($url, PHP_URL_PATH);  
    if(!empty($path))
    $path=substr($path,1);

   
   $objects[] =$path;



}

if (is_null($this->cosClient)) 
{

 return;
}

foreach($objects as $path)
{
  try {
    $result = $this->cosClient->deleteObject(array(
        'Bucket' => $this->TENCENT_BUCKET, //格式：BucketName-APPID
        'Key' => $path,
    ));
    // 请求成功
 //   print_r($result);
} catch (\Exception $e) {
    // 请求失败
   // echo($e);
}
}

 }

 public function upload_tencent_end(&$params)
 {

  if($params["data"]["title"]=="none")
  {
    $params["params"]["state"]="上传腾讯云错误请检查配置";
   //$params["params"]["msg"]="上传oss错误，请检查oss配置";
  }

  else
    $params["params"]["url"]=$params["data"]["url"];
 }

 public function getStateInfo($msg)
 {
$r=array("msg"=>$msg,"status"=>-1);
echo json_encode($r);
return $r;
 }

 //测试上传
 public function  test()
 {
 
$test_url="http://cn19block.oss-cn-hangzhou.aliyuncs.com/static/upload/images/goods/2019/09/30/1569819137655756.png?noCache=k15xs813";

$r="";

    $content = file_get_contents($test_url);
    
    try {

      $r = $this->cosClient->putObject(array(
        'Bucket' => $this->TENCENT_BUCKET,
        'Key' =>"test/test.png",
        'Body' =>$content,
       // "Contenttype"=>"image/png"
        ));

      

    
      // 请求成功
    //  return($r);
  } catch (\Exception $e) {
      // 请求失败
      $rs=["status"=>0,"msg"=>"上传失败，请检查腾讯云配置".$e];
      return $rs;
     // echo("7777".$e);
  }


  $rs=["status"=>1,"msg"=>"上传成功","url"=>$this->tencent_domain."/"."test/test.png"];
  return $rs;



 }
}
?>