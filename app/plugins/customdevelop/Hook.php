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
namespace app\plugins\customdevelop;

use app\plugins\customdevelop\service\BaseService;

/**
 * 自定义开发 - 钩子入口
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class Hook
{
    // 配置信息
    private $plugins_config;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date     2021-05-07
     * @param    [array]        $params [输入参数]
     */
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];
   
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 头部css
                case 'plugins_common_header' :
                case 'plugins_admin_common_header' :
                    $ret = $this->CommonHeaderHandle($params);
                    break;

                // 底部js
                case 'plugins_common_page_bottom' :
                case 'plugins_admin_common_page_bottom' :
                    $ret = $this->CommonPageBottomHandle($params);
                    break;

                // 页面底部html
                case 'plugins_view_common_bottom' :
                case 'plugins_admin_view_common_bottom' :
                    $ret = $this->ViewCommonHandle($params);
                    break;
            }
            return $ret;
        }
    }

    /**
     * 页面底部html
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ViewCommonHandle($params = [])
    {
        return empty($this->plugins_config['html_content']) ? '' : $this->plugins_config['html_content'];
    }

    /**
     * 底部js
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommonPageBottomHandle($params = [])
    {
        return empty($this->plugins_config['js_content']) ? '' : '<script type="text/javascript">'.$this->plugins_config['js_content'].'</script>';
    }

    /**
     * 头部css
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommonHeaderHandle($params = [])
    {
        return empty($this->plugins_config['css_content']) ? '' : '<style type="text/css">'.$this->plugins_config['css_content'].'</style>';
    }
}
?>