<?php
namespace app\plugins\oss_1jian\index;

// 图片视频保存到阿里云/七牛/腾讯云 - 前端独立页面入口
class Index
{
    // 前端页面入口
    public function index($params = [])
    {
        // 数组组装
        MyViewAssign('data', ['hello', 'world!']);
        MyViewAssign('msg', 'hello world! index');
        return MyView('../../../plugins/view/oss_1jian/index/index/index');
    }
}
?>