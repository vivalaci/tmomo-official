<?php

namespace app\plugins\oss_1jian\service;
use think\Db;
use app\service\PluginsService;

require __DIR__.'/../qiniu/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
/**
 * 会员等级服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class QiniuService
{
    private  $qiniu_bucket ;
    private $qiniu_domain ;
    private $is_keep_on_server;
    private $stateInfo;
    private $qiniu_accessKey;
    private $qiniu_secretKey;
    private $OSS_ENDPOINT;
    public function __construct($qiniu_accessKey="",$qiniu_secretKey="",$qiniu_bucket="",$qiniu_domain="")
    {
      if($qiniu_accessKey!="" || $qiniu_secretKey!="" || $qiniu_bucket!="")
      {
$this->qiniu_accessKey=$qiniu_accessKey;
$this->qiniu_secretKey=$qiniu_secretKey;
$this->qiniu_bucket=$qiniu_bucket;
$this->qiniu_domain=$qiniu_domain;
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
        if( isset($OsscConfig["qiniu_is_keep_on_server"]))
        $this->is_keep_on_server=$OsscConfig["qiniu_is_keep_on_server"];
/*
        if(isset($OsscConfig["storage_type"]) && $OsscConfig["storage_type"]=="qiniu" && isset($OsscConfig["qiniu_is_keep_on_server"]))
        $this->is_keep_on_server=$OsscConfig["qiniu_is_keep_on_server"];

        if(isset($OsscConfig["storage_type"]) && $OsscConfig["storage_type"]=="tencent" && isset($OsscConfig["tencent_is_keep_on_server"]))
        $this->is_keep_on_server=$OsscConfig["tencent_is_keep_on_server"];
*/
        
        $this->qiniu_accessKey = $OsscConfig["qiniu_accessKey"];
        $this->qiniu_secretKey = $OsscConfig["qiniu_secretKey"];
        $this->qiniu_bucket = $OsscConfig["qiniu_bucket"];
        $this->qiniu_domain = $OsscConfig["qiniu_domain"];
        
    
      }
       
     
      



    }

    //上传文件
 public function  upload_qiniu(&$params)
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

      // 构建鉴权对象
$auth = new Auth($this->qiniu_accessKey, $this->qiniu_secretKey);

// 生成上传 Token
$token = $auth->uploadToken($this->qiniu_bucket);
$uploadMgr = new UploadManager();
list($r, $err) = $uploadMgr->putFile($token, $object, $path);

if ($err !== null) {
  //var_dump($err);
  $params["data"]["title"]="none";
  return;
} else {
  //var_dump($ret);
}
 


     if(isset($r["key"]) && $r["key"]!="")
     {
        $params["params"]["url"]=$this->qiniu_domain."/".$r["key"];
        $params["data"]["url"]=$this->qiniu_domain."/".$r["key"];
     
     } else {
         
       
     }
   
if($this->is_keep_on_server==null || $this->is_keep_on_server!=1)
{
  
    \base\FileUtil::UnlinkFile($path);
}
}
//删除文件
 public function  del_qiniu($params)
 {

 $url=$params["url"];
if(!strstr($url,"http"))
return;
 $url= parse_url($url);
 $path=$url["path"];
 $path=substr($path,1);




   $r="";
   try{
   
 // 构建鉴权对象
 $auth = new Auth($this->qiniu_accessKey, $this->qiniu_secretKey);


 $uploadMgr = new UploadManager();
 $config = new \Qiniu\Config();
$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

 list($r, $err) = $bucketManager->delete($this->qiniu_bucket, $path);
 if ($err) {
  return;
}

   }
   catch(Exception $e) {

     return;
   }
  
 }

 public function  del_qiniu_urls($params)
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


  

  try{
   // 构建鉴权对象
   $auth = new Auth($this->qiniu_accessKey, $this->qiniu_secretKey);
   
   

   $config = new \Qiniu\Config();
  $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
  
 
    $ops = $bucketManager->buildBatchDelete($this->qiniu_bucket, $objects);
list($ret, $err) = $bucketManager->batch($ops);
    
    if ($err) {
     return;
   }
   
      }
      catch(Exception $e) {
   
        return;
      }


 }

 public function upload_qiniu_end(&$params)
 {

  if($params["data"]["title"]=="none")
  {
    $params["params"]["state"]="上传七牛错误请检查配置";
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
    
 
 // 构建鉴权对象
$auth = new Auth($this->qiniu_accessKey, $this->qiniu_secretKey);

// 生成上传 Token
$token = $auth->uploadToken($this->qiniu_bucket);


// 上传到七牛后保存的文件名
$key = 'qiniu/test2.png';

// 初始化 UploadManager 对象并进行文件的上传。
$uploadMgr = new UploadManager();

// 调用 UploadManager 的 putFile 方法进行文件的上传。
$mime  = 'image/png';
list($r, $err) = $uploadMgr->put($token, $key, $content,null,$mime);
//echo "\n====> putFile result: \n";
if ($err !== null) {
  $rs=["status"=>1,"msg"=>"上传失败，请检查七牛配置"];
  return $rs;
} else {
   // var_dump($ret);
}


    }
    catch(Exception  $e) {
    
    $this->stateInfo = $this->getStateInfo($e->getMessage());
      return;
    }

if(isset($r["key"]) && $r["key"]!="")
{
  $rs=["status"=>1,"msg"=>"上传成功","url"=>$this->qiniu_domain."/".$r["key"]];
  return $rs;

} else {
    
  
}

 }
}
?>