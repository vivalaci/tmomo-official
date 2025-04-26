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
namespace app\plugins\points\index;

use app\service\SeoService;
use app\service\IntegralService;
use app\plugins\points\index\Common;
use app\plugins\points\service\BaseService;

/**
 * 积分商城 - 首页
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class Index extends Common
{
    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        // 用户积分
        $integral = empty($this->user['id']) ? [] : IntegralService::UserIntegral($this->user['id']);
        MyViewAssign('user_integral', $integral);

        // seo
        $seo_title = empty($this->plugins_config['seo_title']) ? '积分商城' : $this->plugins_config['seo_title'];
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
        return MyView('../../../plugins/points/view/index/index/index');
    }
}
?>