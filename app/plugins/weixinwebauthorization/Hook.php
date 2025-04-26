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
namespace app\plugins\weixinwebauthorization;

use app\service\UserService;
use app\plugins\weixinwebauthorization\service\BaseService;
use app\plugins\weixinwebauthorization\service\AuthService;

/**
 * 微信登录 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
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
        $ret = '';
        if(!empty($params['hook_name']))
        {
            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();

            // 获取登录用户
            $user = UserService::LoginUserInfo();
            switch($params['hook_name'])
            {
                // 用户登录页面顶部钩子
                // 用户注册页面钩子
                case 'plugins_view_user_login_info_top' :
                case 'plugins_view_user_reg_info' :
                    if(empty($user) && IsMobile())
                    {
                        $ret = $this->ButtonHtml($params);
                    }
                    break;

                // 公共顶部小导航钩子-左侧
                case 'plugins_view_header_navigation_top_left_end' :
                    if(empty($user) && IsMobile())
                    {
                        $ret = $this->NavTextHtml($params);
                    }
                    break;

                // 系统运行开始
                case 'plugins_service_system_begin' :
                    // 主控制器+方法
                    $main_ca_arr = [
                        'useruserverifyentry',
                    ];
                    if($this->module_name.$this->controller_name.$this->action_name != 'indexpluginsindex' && $this->module_name == 'index' && !in_array($this->controller_name.$this->action_name, $main_ca_arr))
                    {
                        $ret = $this->SystemBegin($params, $user);
                    }

                    // 是否指定跳转地址
                    if(!empty($params['params']) && !empty($params['params']['request_url']))
                    {
                        // 记录回调地址
                        MySession(AuthService::$request_callback_url, BaseService::RequestUrlHandle($params['params']));

                        // 是否存在token则登录用户
                        if(!empty($params['params']['application_client_type']) && $params['params']['application_client_type'] == 'h5' && !empty($params['params']['token']))
                        {
                            $user = UserService::CacheUserTokenData($params['params']['token']);
                            if(!empty($user))
                            {
                                // 记录用户
                                MySession(UserService::$user_login_key, $user);
                            }
                        }
                    }
                    break;
            }
        }
        return $ret;
    }

    /**
     * 系统运行开始
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-06T16:16:34+0800
     * @param    [array]          $params [输入参数]
     * @param    [array]          $user   [用户登录信息]
     */
    public function SystemBegin($params = [], $user = [])
    {
        // 微信环境中自动跳转授权登录
        if(IsWeixinEnv())
        {
            // 用户未登录则校验
            if(empty($user))
            {
                // 获取配置信息
                $base = BaseService::BaseConfig();
                if(!empty($base['data']) && isset($base['data']['is_weixin_env_auto_login']) && $base['data']['is_weixin_env_auto_login'] == 1)
                {
                    // 跳转处理、在当前插件跳转控制器的时候不跳转、存在指定跳转地址也不跳转
                    $pca = input('pluginsname').input('pluginscontrol').input('pluginsaction');
                    if($pca != 'weixinwebauthorizationindexjump' && empty($params['params']['weixinwebauthorizationjumpurl']))
                    {
                        // 处理url地址并跳转到控制器完成跳转的过程
                        unset($params['params']['pluginsname'],
                            $params['params']['pluginscontrol'],
                            $params['params']['pluginsaction']);
                        $url = PluginsHomeUrl('weixinwebauthorization', 'auth', 'index', $params['params']);
                        $params['params']['weixinwebauthorizationjumpurl'] = urlencode(base64_encode($url));

                        // 去授权
                        die(header('location:'.PluginsHomeUrl('weixinwebauthorization', 'index', 'jump', $params['params'])));
                    }
                }
            }
        }
    }

    /**
     * 登录登录html
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-05-24
     * @desc    description
     * @param   array           $params [description]
     */
    private function ButtonHtml($params = [])
    {
        if(IsWeixinEnv())
        {
            $base = BaseService::BaseConfig();
            MyViewAssign('plugins_data', $base['data']);
            return MyView('../../../plugins/weixinwebauthorization/view/index/public/auth_button');
        }
        return '';
    }

    /**
     * 文字登录html
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-05-24
     * @desc    description
     * @param   array           $params [description]
     */
    private function NavTextHtml($params = [])
    {
        if(IsWeixinEnv())
        {
            $base = BaseService::BaseConfig();
            MyViewAssign('plugins_data', $base['data']);
            return MyView('../../../plugins/weixinwebauthorization/view/index/public/auth_text');
        }
        return '';
    }
}
?>