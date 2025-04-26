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
namespace app\plugins\seckill\index;

use app\service\SeoService;
use app\plugins\seckill\index\Common;
use app\plugins\seckill\service\SeckillGoodsService;

/**
 * 限时秒杀 - 前端独立页面入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
{
    /**
     * 首页入口
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-07
     * @desc    description
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 商品数据
        $seckill = SeckillGoodsService::SeckillData($this->plugins_config);
        MyViewAssign('seckill_data', $seckill['data']);

        // seo
        $seo_title = empty($this->plugins_config['seo_title']) ? (empty($this->plugins_config['application_name']) ? '限时秒杀' : $this->plugins_config['application_name']) : $this->plugins_config['seo_title'];
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($seo_title, 2));
        if(!empty($this->plugins_config['seo_keywords']))
        {
            MyViewAssign('home_seo_site_keywords', $this->plugins_config['seo_keywords']);
        }
        if(!empty($this->plugins_config['seo_desc']))
        {
            MyViewAssign('home_seo_site_description', $this->plugins_config['seo_desc']);
        }
        return MyView('../../../plugins/seckill/view/index/index/index');
    }
}
?>