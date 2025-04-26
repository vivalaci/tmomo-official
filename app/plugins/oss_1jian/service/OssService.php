<?php

namespace app\plugins\oss_1jian\service;
use think\Db;
use app\service\PluginsService;

require_once __DIR__ .'/../oss/act/Common2.php';
use OSS\OssClient;

/**
 * 会员等级服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class OssService
{
    private  $bucketName ;
    private $ossClient ;
    private $is_keep_on_server;
    private $stateInfo;
    private $OSS_ACCESS_ID;
    private $OSS_ACCESS_KEY;
    private $OSS_ENDPOINT;
    private $OSS_DOMAIN;
    public function __construct($OSS_ACCESS_ID="",$OSS_ACCESS_KEY="",$bucketName="",$OSS_ENDPOINT="")
    {
      if($OSS_ACCESS_ID!="" || $OSS_ACCESS_KEY!="" || $bucketName!="" || $OSS_ENDPOINT!="")
      {
$this->bucketName=$bucketName;
$this->OSS_ACCESS_ID=$OSS_ACCESS_ID;
$this->OSS_ACCESS_KEY=$OSS_ACCESS_KEY;
$this->OSS_ENDPOINT=$OSS_ENDPOINT;
      }
      else {
        $OsscConfig=array();
        $ret = PluginsService::PluginsData('oss_1jian', [], false);
     
        if($ret['code'] == 0)
        {
         $OsscConfig= $ret['data'];
        }
      
        if($OsscConfig==null || count($OsscConfig)<=0 )
        {
            $this->stateInfo = $this->getStateInfo("OSS_CONNECT_ERROR");
            
            return;
        }
      //假如是否保存文件在web服务器
        if( isset($OsscConfig["is_keep_on_server"]))
        $this->is_keep_on_server=$OsscConfig["is_keep_on_server"];
/*
        if(isset($OsscConfig["storage_type"]) && $OsscConfig["storage_type"]=="qiniu" && isset($OsscConfig["qiniu_is_keep_on_server"]))
        $this->is_keep_on_server=$OsscConfig["qiniu_is_keep_on_server"];

        if(isset($OsscConfig["storage_type"]) && $OsscConfig["storage_type"]=="tencent" && isset($OsscConfig["tencent_is_keep_on_server"]))
        $this->is_keep_on_server=$OsscConfig["tencent_is_keep_on_server"];
*/
        
        $this->bucketName = $OsscConfig["OSS_TEST_BUCKET"];
        $this->OSS_ACCESS_ID = $OsscConfig["OSS_ACCESS_ID"];
        $this->OSS_ACCESS_KEY = $OsscConfig["OSS_ACCESS_KEY"];
        $this->OSS_ENDPOINT = $OsscConfig["OSS_ENDPOINT"];
        $this->OSS_DOMAIN= $OsscConfig["OSS_DOMAIN"];
      }
       
     
      
try{
$this->ossClient =\Common2::getOssClient($this->OSS_ACCESS_ID,$this->OSS_ACCESS_KEY,$this->OSS_ENDPOINT);
$this->ossClient->setTimeout(2000);
}
catch(OssException $e) {
  $this->stateInfo = $this->getStateInfo("连接oss失败,请检查oss参数设置");
    return;
  }

if (is_null($this->ossClient)) 
{

    $this->stateInfo = $this->getStateInfo("OSS_CONNECT_ERROR");
    return;
}

    }

    //上传文件
 public function  upload_oss(&$params)
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
     $OSS_DOMAIN=$_SERVER['HTTP_HOST'];
 

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
      
        // $r=$ossClient->uploadFile($bucketName, $object, "exa$objemple.jpg");
 
         $r=$this->ossClient->putObject($this->bucketName, $object,$content);
      

         }
         catch(Exception  $e) {
       
         $this->stateInfo = $this->getStateInfo($e->getMessage());
           return;
         }
       if($r==null)
       {
        $params["data"]["title"]="none";
       // $this->getStateInfo("上传文件失败，请检查");
       }
if($r!="")
     $r=\Common2::object_array($r);
    
 

     if(isset($r["oss-request-url"]) && $r["oss-request-url"]!="")
     {
  
       $url=$r["oss-request-url"];
     
       if($this->OSS_DOMAIN!=null && $this->OSS_DOMAIN!="")
       {
        $url_parse= parse_url($url);
        $path2=$url_parse["path"];
        $url=$this->OSS_DOMAIN."".$path2;
       }
       else {
    
$url=str_replace("-internal","",$url);
       }
      

        $params["params"]["url"]=$url;
        $params["data"]["url"]=$url;
     
     } else {
         
       
     }
   
if($this->is_keep_on_server==null || $this->is_keep_on_server!=1)
{
  
    \base\FileUtil::UnlinkFile($path);
}
}
//删除文件
 public function  del_oss($params)
 {
  $objects = array();
if(isset($params["data"]) && count($params["data"])>0)
{
  foreach($params["data"] as $v)
  {
 $url=$v["url"];
if(!strstr($url,"http"))
return;
 $url= parse_url($url);
 $path=$url["path"];
 $path=substr($path,1);

   $objects[] =$path;
  }
}
   if (is_null($this->ossClient)) 
   {

    return;
   }
   
   $r="";
   try{
   
   $r=$this->ossClient->deleteObjects($this->bucketName, $objects);
  
   }
   catch(OssException $e) {
 
     return;
   }
   $r=\Common2::object_array($r);
 }

 //批量删除符合条件的oss
 public function  del_oss_urls($params)
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

if (is_null($this->ossClient)) 
{

 return;
}

$r="";
try{

$r=$this->ossClient->deleteObjects($this->bucketName, $objects);
}
catch(OssException $e) {

 return;
}
 }

 public function upload_oss_end(&$params)
 {
 
  if($params["data"]["title"]=="none")
  {
    $params["params"]["state"]="上传oss错误请检查oss配置";
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
$object="oss/oss_test.png";
$r="";
try{
    $content = file_get_contents($test_url);
    
 
   // $r=$ossClient->uploadFile($bucketName, $object, "exa$objemple.jpg");
//获取$content图片内容的长度

    $r=$this->ossClient->putObject($this->bucketName, $object,$content);


    }
    catch(Exception  $e) {
   
    $this->stateInfo = $this->getStateInfo($e->getMessage());
      return $e->getMessage();
    }
  if($r==null)
  {
$rs=["status"=>0,"msg"=>"上传失败,oss配置错误"];
return $rs;

  }
if($r!="")
$r=\Common2::object_array($r);



if(isset($r["oss-request-url"]) && $r["oss-request-url"]!="")
{
$url=$r["oss-request-url"];
$url=str_replace("-internal","",$url);
  
  $rs=["status"=>1,"msg"=>"上传成功","url"=>$url];
  return $rs;

} else {
    
  
}

 }
}
?>