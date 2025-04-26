<?php
namespace app\plugins\oss_1jian\admin;

//use think\Controller;
use app\plugins\oss_1jian\service\UeditorService;
use app\service\PluginsService;
use app\admin\controller\Common;
use app\plugins\oss_1jian\service\OssService;
use app\plugins\oss_1jian\service\QiniuService;
use app\plugins\oss_1jian\service\TencentService;
use app\plugins\oss_1jian\service\Goods1jianService;

use app\service\GoodsService;
class Admin extends Common
{
    public function __construct()
    {
        // 调用父类前置方法
        parent::__construct();

   
    }
    // 后台管理入口
    public function index($params = [])
    {
        
        // 数组组装
        $ret = PluginsService::PluginsData('oss_1jian', [], false);
        if($ret['code'] == 0)
        {
         

            MyViewAssign('data', $ret['data']);
        }
        MyViewAssign('msg', 'hello world! admin');
        return MyView('../../../plugins/oss_1jian/view/admin/admin/index');
    }
    
   
    public function Ueditor($params = [])
    {
  
        $ret = UeditorService::Run(input());
        if($ret['code'] == 0)
        {
            return json($ret['data']);
        }
        return $ret['msg'];
    }

    public function saveinfo($params = [])
    {

        $ret = PluginsService::PluginsData('oss_1jian', [], false);
        if($ret['code'] == 0)
        {
         

            MyViewAssign('data', $ret['data']);
            return MyView('../../../plugins/oss_1jian/view/admin/admin/saveinfo');
        } else {
            return $ret['msg'];
        }
       
   
    }

//保存配置信息
    public function save($params = [])
    {

        return PluginsService::PluginsDataSave(['plugins'=>'oss_1jian', 'data'=>$params]);
    }

    //修改js ueditor.config
    public function change_js($params = [])
    {
 //$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
 //$domain=$_SERVER["HTTP_HOST"];
 //修改 ueditor.config.js
 $SERVER_URL="/admin.php?s=/plugins/index/pluginsname/oss_1jian/pluginscontrol/admin/pluginsaction/ueditor.html";
  $file_name="./public/static/common/lib/ueditor/ueditor.config.js";
  $file_size =filesize($file_name);

 $myfile = fopen( $file_name, "r") or die("Unable to open file!");


 $content=fread($myfile, $file_size);

 $content=str_replace('($(\'#upload-editor-view\').length > 0) ? $(\'#upload-editor-view\').data(\'url\') : \'\'',"'".$SERVER_URL."'", $content);
 fclose($myfile);

 $myfile = fopen( $file_name, "w") or die("Unable to open file!");

 fwrite($myfile, $content);
 fclose($myfile);

 //修改image.js
 
  $file_name="./public/static/common/lib/ueditor/dialogs/image/image.js";
  $file_size =filesize($file_name);

 $myfile = fopen( $file_name, "r") or die("Unable to open file!");


 $content=fread($myfile, $file_size);

 $content=str_replace('?action=deletefile',"&action=deletefile", $content);
 fclose($myfile);

 $myfile = fopen( $file_name, "w") or die("Unable to open file!");

 fwrite($myfile, $content);
 fclose($myfile);
 //video.js
 
 $file_name="./public/static/common/lib/ueditor/dialogs/video/video.js";
 $file_size =filesize($file_name);

$myfile = fopen( $file_name, "r") or die("Unable to open file!");


$content=fread($myfile, $file_size);

$content=str_replace('?action=deletefile',"&action=deletefile", $content);
fclose($myfile);

$myfile = fopen( $file_name, "w") or die("Unable to open file!");

fwrite($myfile, $content);
fclose($myfile);
}

     //还原js ueditor.config
     public function restor_js($params = [])
     {
      //  $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
      

      //  $domain=$_SERVER["HTTP_HOST"];
        $SERVER_URL="/admin.php?s=/plugins/index/pluginsname/oss_1jian/pluginscontrol/admin/pluginsaction/ueditor.html";
         $file_name="./public/static/common/lib/ueditor/ueditor.config.js";
         $file_size =filesize($file_name);
       
        $myfile = fopen( $file_name, "r") or die("Unable to open file!");
       
       
        $content=fread($myfile, $file_size);
       
        $content=str_replace("'".$SERVER_URL."'", '($(\'#upload-editor-view\').length > 0) ? $(\'#upload-editor-view\').data(\'url\') : \'\'',$content);
        fclose($myfile);
       
        $myfile = fopen( $file_name, "w") or die("Unable to open file!");
       
        fwrite($myfile, $content);
        fclose($myfile);
     }


     public function test_oss($params = [])
     {

if($params["OSS_ACCESS_ID"]=="")
{
    return ["status"=>0,"msg"=>"ACCESS_ID不可为空"];
}
if($params["OSS_ACCESS_KEY"]=="")
{
    return ["status"=>0,"msg"=>"ACCESS_KEY不可为空"];
}
if($params["OSS_ENDPOINT"]=="")
{
    return ["status"=>0,"msg"=>"ENDPOINT不可为空"];
}
if($params["OSS_TEST_BUCKET"]=="")
{
    return ["status"=>0,"msg"=>"BUCKET不可为空"];
}
//PluginsService::PluginsDataSave(['plugins'=>'oss_1jian', 'data'=>$params]);
       $test_service= new   OssService($params["OSS_ACCESS_ID"],$params["OSS_ACCESS_KEY"],$params["OSS_TEST_BUCKET"],$params["OSS_ENDPOINT"]);
       return   $test_service->test();

     }


     public function test_qiniu($params = [])
     {
 
if($params["qiniu_accessKey"]=="")
{
    return ["status"=>0,"msg"=>"accessKey不可为空"];
}
if($params["qiniu_secretKey"]=="")
{
    return ["status"=>0,"msg"=>"secretKey不可为空"];
}
if($params["qiniu_bucket"]=="")
{
    return ["status"=>0,"msg"=>"bucket不可为空"];
}

//PluginsService::PluginsDataSave(['plugins'=>'oss_1jian', 'data'=>$params]);
       $test_service= new   QiniuService($params["qiniu_accessKey"],$params["qiniu_secretKey"],$params["qiniu_bucket"],$params["qiniu_domain"]);
       return   $test_service->test();

     }


     public function test_tencent($params = [])
     {
 
if($params["TENCENT_APPID"]=="")
{
    return ["status"=>0,"msg"=>"APPID不可为空"];
}
if($params["TENCENT_region"]=="")
{
    return ["status"=>0,"msg"=>"存储区域不可为空"];
}
if($params["TENCENT_APPSECRET"]=="")
{
    return ["status"=>0,"msg"=>"APPSECRET不可为空"];
}
if($params["TENCENT_BUCKET"]=="")
{
    return ["status"=>0,"msg"=>"存储桶不可为空"];
}
if($params["tencent_domain"]=="")
{
    return ["status"=>0,"msg"=>"域名不可为空"];
}
//PluginsService::PluginsDataSave(['plugins'=>'oss_1jian', 'data'=>$params]);
       $test_service= new   TencentService($params["TENCENT_APPID"],$params["TENCENT_APPSECRET"],$params["TENCENT_region"],$params["TENCENT_BUCKET"],$params["tencent_domain"]);
       return   $test_service->test();

     }

//删除商品和图片
     public function delete($params = [])
    {
        if(empty($params["ids"]))
       return DataReturn('参数不可为空', -1);

//删除图片
$r=Goods1jianService::DelGoodsPics($params);
//删除商品
	$params['admin'] = $this->admin;
	$r= GoodsService::GoodsDelete($params);
      // return DataReturn('删除成功', 1);
      return $r;
    }
}
?>