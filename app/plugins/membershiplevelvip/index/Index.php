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
namespace app\plugins\membershiplevelvip\index;

use app\service\PluginsService;
use app\service\UserService;
use app\service\SeoService;
use app\plugins\membershiplevelvip\index\Common;
use app\plugins\membershiplevelvip\service\BaseService;
use app\plugins\membershiplevelvip\service\LevelService;
use app\plugins\membershiplevelvip\service\IntroduceService;
use app\plugins\membershiplevelvip\service\LevelBuyService;
use app\plugins\membershiplevelvip\service\PayService;

/**
 * 会员等级增强版插件 - 首页
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
{
    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-11T15:36:19+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 等级列表
        $ret = LevelService::DataList(['where'=>['is_enable'=>1, 'is_supported_pay_buy'=>1]]);
        MyViewAssign('level_list', $ret['data']);

        // 介绍列表
        $ret = IntroduceService::DataList();
        MyViewAssign('introduce_list', $ret['data']);

        // 默认图片数据
        $default_images_data = BaseService::DefaultImagesData($this->plugins_config);
        MyViewAssign('default_images_data', $default_images_data);

        // seo
        $seo_title = empty($this->plugins_config['seo_title']) ? (empty($this->plugins_config['application_name']) ? '会员中心' : $this->plugins_config['application_name']) : $this->plugins_config['seo_title'];
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($seo_title, 2));
        if(!empty($this->plugins_config['seo_keywords']))
        {
            MyViewAssign('home_seo_site_keywords', $this->plugins_config['seo_keywords']);
        }
        $seo_desc = empty($this->plugins_config['seo_desc']) ? (empty($this->plugins_config['describe']) ? '' : $this->plugins_config['describe']) : $this->plugins_config['seo_desc'];
        if(!empty($seo_desc))
        {
            MyViewAssign('home_seo_site_description', $seo_desc);
        }
        return MyView('../../../plugins/membershiplevelvip/view/index/index/index');
    }
}
?>