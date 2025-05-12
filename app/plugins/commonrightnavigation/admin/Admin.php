<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2099 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://opensource.org/licenses/mit-license.php )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\plugins\commonrightnavigation\admin;

use app\service\PluginsService;

/**
 * 右侧快捷导航 - 管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Admin
{
    // 基础数据附件字段
    private $plugins_config_attachment_field = [
        'alipay_mini_qrcode_images',
        'alipay_fuwu_qrcode_images',
        'weixin_mini_qrcode_images',
        'weixin_fuwu_qrcode_images',
        'baidu_mini_qrcode_images',
        'qq_mini_qrcode_images',
        'toutiao_mini_qrcode_images',
        'douyin_mini_qrcode_images',
        'h5_qrcode_images',
        'app_download_images',
    ];

    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        $ret = PluginsService::PluginsData('commonrightnavigation', $this->plugins_config_attachment_field);
        if($ret['code'] == 0)
        {
            MyViewAssign('data', $ret['data']);
            return MyView('../../../plugins/commonrightnavigation/view/admin/admin/index');
        } else {
            return $ret['msg'];
        }
    }

    /**
     * 编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        $ret = PluginsService::PluginsData('commonrightnavigation', $this->plugins_config_attachment_field);
        if($ret['code'] == 0)
        {
            MyViewAssign('data', $ret['data']);
            return MyView('../../../plugins/commonrightnavigation/view/admin/admin/saveinfo');
        } else {
            return $ret['msg'];
        }
    }

    /**
     * 数据保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        return PluginsService::PluginsDataSave(['plugins'=>'commonrightnavigation', 'data'=>$params], $this->plugins_config_attachment_field);
    }
}
?>