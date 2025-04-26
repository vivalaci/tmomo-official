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
namespace app\plugins\backgroundad;

use app\plugins\backgroundad\service\BaseService;

/**
 * 背景广告 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 模块、控制器、方法
    private $module_name;
    private $controller_name;
    private $action_name;
    private $mca;

    // 配置信息
    private $plugins_config;

    /**
     * 应用响应入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-10-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function handle($params = [])
    {
        if(!empty($params['hook_name']))
        {
            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();
            $this->mca = $this->module_name.$this->controller_name.$this->action_name;

            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            // 是否开启
            $is_open = !empty($this->plugins_config['bg_images']) && isset($this->plugins_config['is_open']) && $this->plugins_config['is_open'] == 1 && $this->mca == 'indexindexindex';

            $ret = '';
            if($is_open)
            {
                switch($params['hook_name'])
                {
                    // 公共文件css
                    case 'plugins_css' :
                        $ret = 'static/plugins/backgroundad/css/index/style.css';
                        break;

                    // 公共头部css
                    case 'plugins_common_header' :
                        $ret = $this->HeaderCss($params);
                        break;

                    // 页面顶部html
                    case 'plugins_view_common_top' :
                        $ret = $this->CommonPageTopHtml($params);
                        break;
                }
            }
            return $ret;
        }
    }

    /**
     * css
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-06T16:16:34+0800
     * @param    [array]          $params [输入参数]
     */
    public function HeaderCss($params = [])
    {
        return '<style type="text/css">
                    body {
                        background-image: url('.$this->plugins_config['bg_images'].');
                    }
                </style>';
    }

    /**
     * 页面顶部html
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommonPageTopHtml($params = [])
    {
        return MyView('../../../plugins/backgroundad/view/index/public/top', [
            'plugins_config' => $this->plugins_config,
        ]);
    }
}
?>