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
namespace app\plugins\orderexportprint;

use app\service\PluginsService;
use app\plugins\orderexportprint\service\BaseService;

/**
 * 订单导出打印 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-11T21:51:08+0800
 */
class Hook
{
    // 模块、控制器、方法
    private $module_name;
    private $controller_name;
    private $action_name;
    private $mca;

    private $pluginsname;
    private $pluginscontrol;
    private $pluginsaction;
    private $pca;

    // 配置信息
    private $plugins_config;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-11T14:25:44+0800
     * @param    [array]          $params [输入参数]
     */
    public function handle($params = [])
    {
        // 钩子名称
        $ret = '';
        if(!empty($params['hook_name']))
        {
            // 插件配置信息
            $base = BaseService::BaseConfig();
            $this->plugins_config = $base['data'];

            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();
            $this->mca = $this->module_name.$this->controller_name.$this->action_name;

            // 插件
            $this->pluginsname = PluginsRequestName();
            $this->pluginscontrol = PluginsRequestController();
            $this->pluginsaction = PluginsRequestAction();
            $this->pca = $this->pluginsname.$this->pluginscontrol.$this->pluginsaction;

            // 导出按钮
            $is_export = (isset($this->plugins_config['is_export']) && $this->plugins_config['is_export'] == 1) ? 1 : 0;

            // 打印按钮
            $is_print = 0;
            if(isset($this->plugins_config['is_print']) && $this->plugins_config['is_print'] == 1)
            {
                if(empty($this->plugins_config['print_order_status_ids_all']) || (!empty($params['data']) && in_array($params['data']['status'], $this->plugins_config['print_order_status_ids_all'])))
                {
                    $is_print = 1;
                }
            }

            // 多商户导出按钮
            $is_export_shop = ($is_export == 1 && isset($this->plugins_config['is_export_shop']) && $this->plugins_config['is_export_shop'] == 1) ? 1 : 0;

            // 是否引入多商户样式
            $is_shop_style = $this->module_name == 'index' && in_array($this->pluginsname.$this->pluginscontrol, ['orderexportprintprinting']);

            switch($params['hook_name'])
            {
                // 公共css
                case 'plugins_css' :
                    // 引入多商户样式
                    if($is_shop_style)
                    {
                        $ret = 'static/plugins/shop/css/index/public/shop_admin.css';
                    }
                    break;

                // 公共js
                case 'plugins_js' :
                    if($is_export_shop == 1 && $this->pca == 'shoporderindex')
                    {
                        $ret = 'static/plugins/orderexportprint/js/index/style.js';
                    }
                    break;

                // 后台公共js
                case 'plugins_admin_js' :
                    if($is_export == 1 && $this->mca == 'adminorderindex')
                    {
                        $ret = 'static/plugins/orderexportprint/js/index/style.js';
                    }
                    break;

                // 导出按钮
                case 'plugins_view_admin_order_top_operate' :
                    if($is_export == 1)
                    {
                        $ret = $this->AdminOrderViewTopHtml($params);
                    }
                    break;

                // 打印按钮
                case 'plugins_view_admin_order_list_operate' :
                    if($is_print == 1)
                    {
                        $ret = $this->AdminOrderViewListHtml($params);
                    }
                    break;

                // 订单顶部操作 - 多商户
                case 'plugins_view_index_plugins_shop_order_top_operate' :
                    if($is_export_shop == 1)
                    {
                        $ret = $this->AdminOrderViewTopHtml($params);
                    }
                    break;

                // 订单列表操作 - 多商户
                case 'plugins_view_index_plugins_shop_order_list_operate' :
                    if($is_print == 1 && isset($this->plugins_config['is_print_shop']) && $this->plugins_config['is_print_shop'] == 1)
                    {
                        $ret = $this->AdminOrderViewListHtml($params);
                    }
                    break;
            }
        }
        return $ret;
    }

    /**
     * 导出按钮
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function AdminOrderViewTopHtml($params = [])
    {
        if(isset($this->plugins_config['is_export']) && $this->plugins_config['is_export'] == 1)
        {
            $fun = ($params['hook_name'] == 'plugins_view_index_plugins_shop_order_top_operate') ? 'PluginsHomeUrl' : 'PluginsAdminUrl';
            $url = $fun('orderexportprint', 'export', 'index', input());
            MyViewAssign('export_url', $url);
            return MyView('../../../plugins/orderexportprint/view/public/top');
        }
    }

    /**
     * 打印按钮
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function AdminOrderViewListHtml($params = [])
    {
        $fun = ($params['hook_name'] == 'plugins_view_index_plugins_shop_order_list_operate') ? 'PluginsHomeUrl' : 'PluginsAdminUrl';
            $url = $fun('orderexportprint', 'printing', 'index', ['id'=>$params['id']]);
        MyViewAssign('print_url', $url);
        return MyView('../../../plugins/orderexportprint/view/public/list');
    }
}
?>