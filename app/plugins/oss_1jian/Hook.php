<?php
namespace app\plugins\oss_1jian;

//use think\Controller;
use app\plugins\oss_1jian\service\OssService;
use app\service\PluginsService;
use app\plugins\oss_1jian\service\QiniuService;
use app\plugins\oss_1jian\service\TencentService;
/**
 * 商品图片保存到阿里云oss - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook 
{

    // 应用响应入口
    public function handle($params = [])
    {
   
       
        // 是否控制器钩子
        // is_backend 当前为后端业务处理
        // hook_name 钩子名称
        if(isset($params['is_backend']) && $params['is_backend'] === true && !empty($params['hook_name']))
        {
            // 参数一   描述
            // 参数二   0 为处理成功, 负数为失败
            // 参数三   返回数据
          
 // 当前模块/控制器/方法
 $module_name = strtolower(RequestModule());
 $controller_name = strtolower(RequestController());
 $action_name = strtolower(RequestAction());

 // 页面参数
 $input = input();

 $OsscConfig=array();
 $ret = PluginsService::PluginsData('oss_1jian', [], false);

 if($ret['code'] == 0)
 {
  $OsscConfig= $ret['data'];
 }

 $ret = '';



if($params['hook_name']=='plugins_view_admin_goods_list_operate')
{

//$ret= '<button type="button" class="am-btn am-btn-danger am-btn-xs am-radius am-icon-trash submit-delete" data-url="'.PluginsAdminUrl('oss_1jian', 'admin', 'delete').'" data-key="ids" data-id="'.$params["id"].'"> 删除商品和图片</button>';

}
if($params['hook_name']=='plugins_view_admin_goods_bottom_operate')
{

$ret= '<script>window.onload = function(){
　　　$("button.am-btn-block.submit-delete").hide();
　　}</script>';

}



//假如是oss
 if(isset($OsscConfig["storage_type"]) && $OsscConfig["storage_type"]=="oss" )
 {
 $os=new OssService();
 switch($params['hook_name'])
 {
     case 'plugins_service_attachment_handle_begin' :
      $os->upload_oss($params);
         break;
case 'plugins_service_attachment_delete_success':
$os->del_oss($params);
break;

case 'plugins_service_attachment_url_delete_success':

    $os->del_oss_urls($params);
    break;
case 'plugins_service_attachment_handle_end':
$os->upload_oss_end($params);

    
 }
 }
//假如是qiniu
if(isset($OsscConfig["storage_type"]) && $OsscConfig["storage_type"]=="qiniu" )
{
 
$os=new QiniuService();

switch($params['hook_name'])
{
    case 'plugins_service_attachment_handle_begin' :
     $os->upload_qiniu($params);
        break;
case 'plugins_service_attachment_delete_success':
if(!empty($params["data"]) && count($params["data"])>0)
{
    foreach($params["data"] as $v)
    {
        $os->del_qiniu($v);
    }

}    
break;

case 'plugins_service_attachment_url_delete_success':

    $os->del_qiniu_urls($params);
    break;

case 'plugins_service_attachment_handle_end':
$os->upload_qiniu_end($params);
break;
   
}
}
//假如是tencent
if(isset($OsscConfig["storage_type"]) && $OsscConfig["storage_type"]=="tencent" )
{

$os=new TencentService();

switch($params['hook_name'])
{
    case 'plugins_service_attachment_handle_begin' :
     $os->upload_tencent($params);
        break;
case 'plugins_service_attachment_delete_success':
    if(!empty($params["data"]) && count($params["data"])>0)
{
    foreach($params["data"] as $v)
    {
$os->del_tencent($v);
    }}

case 'plugins_service_attachment_url_delete_success':

    $os->del_tencent_urls($params);
    break;

case 'plugins_service_attachment_handle_end':
$os->upload_tencent_end($params);
break;
   
}
}
 return $ret;
   //         return DataReturn('返回描述', 0);

        // 默认返回视图
        } else {
            return 'hello world!';
        }
    }
}
?>