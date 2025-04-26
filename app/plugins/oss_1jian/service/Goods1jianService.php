<?php

namespace app\plugins\oss_1jian\service;

use app\service\ResourcesService;
use think\facade\Db;
use app\service\PluginsService;


class Goods1jianService
{

 public static function  DelGoodsPics($params)
 {

     if(empty($params["ids"]))
     return DataReturn('删除失败', -1);
     $AttachmentIDs=[];
      $urls=[];

    
//先获取主图
$images = Db::name('goods_photo')->where("goods_id","=",$params["ids"])->column("images");



$urls=array_merge($urls,$images);
//首页推荐图
$goods = Db::name('goods')->where("id","=",$params["ids"])->find();
if(!empty($goods["images"]))
$urls[]=$goods["images"];

if(!empty($goods["home_recommended_images"]))
$urls[]=$goods["home_recommended_images"];


//详情图
if(!empty($goods["content_web"]))
{
$content_web=$goods["content_web"];
$pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.ss2|\.jpg|\.png|\.SS2]))[\'|\"].*?[\/]?>/";
preg_match_all($pattern,$content_web,$match);
if(!empty($match) && count($match)>=2)
{
$images=$match[1];

if(!empty($images))
$urls=array_merge($urls,$images);


}

}

//视频
if(!empty($goods["video"]))
$urls[]=$goods["video"];


//sku图
$values = Db::name('goods_spec_type')->where("goods_id","=",$params["ids"])->column("value");
foreach($values as $value)
{
    if( empty($value))
    continue;
$value=json_decode($value,true);

foreach($value as $v)
{
if(!empty($v["images"]))
{
$urls[]=$v["images"];
}
}
}


//app的图
$images = Db::name('goods_content_app')->where("goods_id","=",$params["ids"])->column("images");
if(!empty($images))
$urls=array_merge($urls,$images);



 if(empty($urls))
return DataReturn('没有需要删除的地址', -1);

$request=request();
$domain=$request->domain();
//去掉本地图片的域名
foreach ($urls as $k => $v) {
   if(strstr($v,$domain))
   {
       $v=str_replace($domain."/public","",$v);
        $v=str_replace($domain."","",$v);
        $urls[$k]=$v;
   }
}


$AttachmentIDs = Db::name('attachment')->where("url","in",$urls)->column("id");


 if(empty($AttachmentIDs))
     return DataReturn('没有需要删除的id', -1);

   
foreach($AttachmentIDs as $v)
{
$r=ResourcesService::AttachmentDelete(["id"=>$v]);
//echo json_encode($r);
}

return DataReturn('删除成功', 0);
 }

}
?>