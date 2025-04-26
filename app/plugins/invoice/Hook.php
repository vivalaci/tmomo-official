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
namespace app\plugins\invoice;

use app\service\PluginsService;
use app\plugins\invoice\service\BaseService;
use app\plugins\invoice\service\InvoiceService;

/**
 * 发票 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 配置信息
    private $plugins_config;

    // 当前模块/控制器/方法
    private $module_name;
    private $controller_name;
    private $action_name;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-09T14:25:44+0800
     * @param    [array]                    $params [输入参数]
     */
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();

            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            // 是否开启选择
            $is_user_quick_select = isset($this->plugins_config['is_user_quick_select']) ? intval($this->plugins_config['is_user_quick_select']) : 0;
   
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // web端快捷导航操作按钮
                case 'plugins_service_quick_navigation_pc' :
                    if($is_user_quick_select == 1)
                    {
                        $this->WebQuickNavigationHandle($params);
                    }
                    break;

                // 小程序/APP端快捷导航操作按钮
                case 'plugins_service_quick_navigation_h5' :
                case 'plugins_service_quick_navigation_weixin' :
                case 'plugins_service_quick_navigation_alipay' :
                case 'plugins_service_quick_navigation_baidu' :
                case 'plugins_service_quick_navigation_qq' :
                case 'plugins_service_quick_navigation_toutiao' :
                    if($is_user_quick_select == 1)
                    {
                        $this->MiniQuickNavigationHandle($params);
                    }
                    break;

                // 用户中心左侧导航
                case 'plugins_service_users_center_left_menu_handle' :
                    $ret = $this->UserCenterLeftMenuHandle($params);
                    break;

                // 顶部小导航右侧-我的商城
                case 'plugins_service_header_navigation_top_right_handle' :
                    $ret = $this->CommonTopNavRightMenuHandle($params);
                    break;

                // 订单状态改变处理
                case 'plugins_service_order_status_change_history_success_handle' :
                    $ret = $this->OrderStatusChangeHandle($params);
                    break;

                // 拖拽可视化-页面地址
                case 'plugins_layout_service_url_value_begin' :
                    $this->LayoutServiceUrlValueBegin($params);
                    break;
                // 拖拽可视化-页面名称
                case 'plugins_layout_service_pages_list' :
                    $this->LayoutServicePagesList($params);
                    break;

                // diyapi初始化
                case 'plugins_service_diyapi_init_data' :
                    $this->DiyApiInitDataHandle($params);
                    break;
            }
            return $ret;
        }
    }

    /**
     * diyapi初始化
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function DiyApiInitDataHandle($params = [])
    {
        // 页面链接
        if(isset($params['data']['page_link_list']) && is_array($params['data']['page_link_list']))
        {
            foreach($params['data']['page_link_list'] as &$lv)
            {
                if(isset($lv['data']) && isset($lv['type']) && $lv['type'] == 'plugins')
                {
                    $lv['data'][] = [
                        'name'  => '发票',
                        'type'  => 'invoice',
                        'data'  => [
                            ['name'=>'我的发票', 'page'=>'/pages/plugins/invoice/invoice/invoice'],
                            ['name'=>'订单开票', 'page'=>'/pages/plugins/invoice/order/order'],
                        ],
                    ];
                    break;
                }
            }
        }
    }

    /**
     * 拖拽可视化-页面名称
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function LayoutServicePagesList($params = [])
    {
        $params['data']['plugins']['data'][] = [
            'name'  => '发票',
            'value' => 'activity',
            'data'  => [
                [ 'value' => 'user', 'name' => '我的发票'],
                [ 'value' => 'order', 'name' => '订单开票'],
            ],
        ];
    }

    /**
     * 拖拽可视化-页面地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function LayoutServiceUrlValueBegin($params = [])
    {
        $params['static_url_web_arr']['plugins-invoice-user'] = PluginsHomeUrl('invoice', 'user', 'index');
        $params['static_url_web_arr']['plugins-invoice-order'] = PluginsHomeUrl('invoice', 'order', 'index');
        $params['static_url_app_arr']['plugins-invoice-user'] = '/pages/plugins/invoice/user/user';
        $params['static_url_app_arr']['plugins-invoice-order'] = '/pages/plugins/invoice/order/order';
    }

    /**
     * 订单状态改变处理,状态为取消|关闭时释放积分
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function OrderStatusChangeHandle($params = [])
    {
        if(!empty($params['data']) && isset($params['data']['new_status']) && in_array($params['data']['new_status'], [5,6]) && !empty($params['order_id']))
        {
            return InvoiceService::OrderStatusChangeHandle($params['order_id']);
        }
    }

    /**
     * 用户中心左侧菜单处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function UserCenterLeftMenuHandle($params = [])
    {
        $params['data']['business']['item'][] = [
            'name'      =>  '我的发票',
            'url'       =>  PluginsHomeUrl('invoice', 'user', 'index'),
            'contains'  =>  ['invoiceuserindex', 'invoiceusersaveinfo', 'invoiceorderindex'],
            'is_show'   =>  1,
            'icon'      =>  'am-icon-print',
        ];
    }

    /**
     * 顶部小导航右侧-我的商城
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommonTopNavRightMenuHandle($params = [])
    {
        if(isset($params['data'][1]['items']) && is_array($params['data'][1]['items']))
        {
            array_push($params['data'][1]['items'], [
                'name'  => '我的发票',
                'url'   => PluginsHomeUrl('invoice', 'user', 'index'),
            ]);
        }
    }

    /**
     * web端快捷导航操作导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function WebQuickNavigationHandle($params = [])
    {
        if(isset($params['data']) && is_array($params['data']))
        {
            $nav = [
                'event_type'    => 0,
                'event_value'   => PluginsHomeUrl('invoice', 'user', 'index'),
                'name'          => '我的发票',
                'images_url'    => StaticAttachmentUrl('quick-nav-icon.png'),
                'bg_color'      => '#18277f',
            ];
            array_push($params['data'], $nav);
        }
    }

    /**
     * 小程序端快捷导航操作导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function MiniQuickNavigationHandle($params = [])
    {
        if(isset($params['data']) && is_array($params['data']))
        {
            $nav = [
                'event_type'    => 1,
                'event_value'   => '/pages/plugins/invoice/user/user',
                'name'          => '我的发票',
                'images_url'    => StaticAttachmentUrl('quick-nav-icon.png'),
                'bg_color'      => '#077aff',
            ];
            array_push($params['data'], $nav);
        }
    }
}
?>