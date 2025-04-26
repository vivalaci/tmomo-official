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
namespace app\plugins\signin;

use app\service\PluginsService;
use app\plugins\signin\service\BaseService;

/**
 * 签到 - 钩子入口
 * @author   Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2019-04-11
 * @desc    description
 */
class Hook
{
    // 配置信息
    private $plugins_config;

    // 导航名称
    private $nav_title = '我的签到';

    /**
     * 应用响应入口
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            // 快捷导航入口
            $is_user_quick = isset($this->plugins_config['is_user_quick']) ? intval($this->plugins_config['is_user_quick']) : 0;

            // 用户中心导航入口
            $is_user_menu = isset($this->plugins_config['is_user_menu']) ? intval($this->plugins_config['is_user_menu']) : 0;
   
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // web端快捷导航操作按钮
                case 'plugins_service_quick_navigation_pc' :
                    if($is_user_quick == 1)
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
                    if($is_user_quick == 1)
                    {
                        $this->MiniQuickNavigationHandle($params);
                    }
                    break;

                // 用户中心左侧导航
                case 'plugins_service_users_center_left_menu_handle' :
                    if($is_user_menu == 1)
                    {
                        $ret = $this->UserCenterLeftMenuHandle($params);
                    }
                    break;

                // 顶部小导航右侧-我的商城
                case 'plugins_service_header_navigation_top_right_handle' :
                    if($is_user_menu == 1)
                    {
                        $ret = $this->CommonTopNavRightMenuHandle($params);
                    }
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
                        'name'  => '签到',
                        'type'  => 'signin',
                        'data'  => [
                            ['name'=>'我的签到', 'page'=>'/pages/plugins/signin/user/user'],
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
            'name'  => '签到',
            'value' => 'signin',
            'data'  => [
                [ 'value' => 'user-qrcode', 'name' => '组队签到'],
                [ 'value' => 'user-coming', 'name' => '我的签到'],
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
        $params['static_url_web_arr']['plugins-signin-user-qrcode'] = PluginsHomeUrl('signin', 'userqrcode', 'index');
        $params['static_url_web_arr']['plugins-signin-user-coming'] = PluginsHomeUrl('signin', 'usersignin', 'index');
        $params['static_url_app_arr']['plugins-signin-user-qrcode'] = '/pages/plugins/signin/user-qrcode/user-qrcode';
        $params['static_url_app_arr']['plugins-signin-user-coming'] = '/pages/plugins/signin/user-coming-list/user-coming-list';
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
            'name'      =>  $this->nav_title,
            'url'       =>  PluginsHomeUrl('signin', 'usersignin', 'index'),
            'contains'  =>  ['signinusersigninindex', 'signinuserqrcodeindex', 'signinuserqrcodesaveinfo'],
            'is_show'   =>  1,
            'icon'      =>  'am-icon-get-pocket',
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
        array_push($params['data'][1]['items'], [
            'name'  => $this->nav_title,
            'url'   => PluginsHomeUrl('signin', 'usersignin', 'index'),
        ]);
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
                'event_value'   => PluginsHomeUrl('signin', 'usersignin', 'index'),
                'name'          => $this->nav_title,
                'images_url'    => StaticAttachmentUrl('quick-nav-icon.png'),
                'bg_color'      => '#F37B1D',
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
                'event_value'   => '/pages/plugins/signin/user/user',
                'name'          => $this->nav_title,
                'images_url'    => StaticAttachmentUrl('quick-nav-icon.png'),
                'bg_color'      => '#F37B1D',
            ];
            array_push($params['data'], $nav);
        }
    }
}
?>