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
namespace app\plugins\popupscreen;

use app\plugins\popupscreen\service\BaseService;

/**
 * 首页弹屏广告 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @date     2019-06-23
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
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date     2019-06-23
     * @param    [array]                    $params [输入参数]
     */
    public function handle($params = [])
    {
        $ret = '';
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

            // 终端状态
            switch($this->module_name)
            {
                // web端
                case 'index' :
                    if(!isset($this->plugins_config['is_web_enable']) || $this->plugins_config['is_web_enable'] != 1)
                    {
                        return $ret;
                    }

                    // 是否关闭状态
                    $cv = intval(MyCookie('plugins_popupscreen_key', '', false));
                    $pv = empty($this->plugins_config['interval_time']) ? 86400 : intval($this->plugins_config['interval_time']);
                    if(!empty($cv) && $cv+$pv > time())
                    {
                        return $ret;
                    }

                    // 非全局
                    if(isset($this->plugins_config['is_overall']) && $this->plugins_config['is_overall'] != 1)
                    {
                        // 非首页则空
                        if($this->mca != 'indexindexindex')
                        {
                            return $ret;
                        }
                    }
                    break;
            }

            // 是否有效
            if($this->plugins_config['is_valid'] != 1)
            {
                return $ret;
            }

            // 钩子匹配
            switch($params['hook_name'])
            {
                case 'plugins_css' :
                    $ret = 'static/plugins/popupscreen/css/index/style.css';
                    break;

                // html
                case 'plugins_view_common_bottom' :
                    $ret = $this->Html($this->plugins_config, $params);
                    break;

                // 底部js
                case 'plugins_common_page_bottom' :
                    $ret = $this->Javascript($this->plugins_config, $params);
                    break;
            }
        }
        return $ret;
    }

    /**
     * html代码
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-23
     * @desc    description
     * @param   [array]          $params [参数]
     */
    private function Html($params = [])
    {
        MyViewAssign('plugins_config', $this->plugins_config);
        return MyView('../../../plugins/popupscreen/view/index/public/content');
    }

     /**
     * js代码
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-23
     * @desc    description
     * @param   [array]          $data   [基础配置信息]
     * @param   [array]          $params [参数]
     */
    private function Javascript($data, $params = [])
    {
        $js = '<script type="text/javascript">';

        // 自动关闭
        if(!empty($data['close_time']))
        {
            $js .= '$(function()
            {
                // 自动关闭
                setTimeout(function()
                {
                    $(".plugins-popupscreen-ad-content .submit-ajax").trigger("click");
                }, '.(intval($data['close_time'])*1000).');
            });';
        }

        // 点击关闭
        $js .= '// 点击关闭
                $(".plugins-popupscreen-ad-content .submit-ajax").on("click touchend", function()
                {
                    $.AMUI.utils.cookie.set("plugins_popupscreen_key", '.time().', null, "/", "'.MyFileConfig('common_cookie_domain', '', '', true).'");
                    $(".plugins-popupscreen-ad").hide();
                });';

        // 关闭回调
        $js .= 'function PluginsPopupscreenCloseBack(e)
        {
            $.AMUI.progress.done();
            if(e.code == 0)
            {
                //$(".plugins-popupscreen-ad").hide();
            } else {
                Prompt(e.msg);
            }
        }';

        $js .= '</script>';
        return $js;
    }
}
?>