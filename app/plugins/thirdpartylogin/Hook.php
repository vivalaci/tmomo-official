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
namespace app\plugins\thirdpartylogin;

use app\service\PluginsService;
use app\service\UserService;
use app\plugins\thirdpartylogin\service\BaseService;
use app\plugins\thirdpartylogin\service\PlatformUserService;
use app\plugins\thirdpartylogin\service\ScanLoginService;
use app\plugins\thirdpartylogin\platform\DskyPlatform;

/**
 * 第三方登录 - 钩子入口
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

    // 当前模块/控制器/方法
    private $module_name;
    private $controller_name;
    private $action_name;
    private $mca;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date     2021-05-07
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
            $this->mca = $this->module_name.$this->controller_name.$this->action_name;

            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            // 开启扫码登录
            $is_web_login_scan = isset($this->plugins_config['is_web_login_scan']) && $this->plugins_config['is_web_login_scan'] == 1 && in_array($this->mca, ['indexuserlogininfo', 'indexusermodallogininfo']);
   
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 公共css
                case 'plugins_css' :
                    if($is_web_login_scan)
                    {
                        $ret = 'static/plugins/thirdpartylogin/css/index/public/login_scan.css';
                    }
                    break;

                // 公共js
                case 'plugins_js' :
                    if($is_web_login_scan)
                    {
                        $ret = 'static/plugins/thirdpartylogin/js/index/public/login_scan.js';
                    }
                    break;

                // 用户登录
                case 'plugins_view_user_login_inside_bottom' :
                    if(isset($this->plugins_config['is_logininfo_show_ent']) && $this->plugins_config['is_logininfo_show_ent'] == 1)
                    {
                        $ret = $this->IndexUserLoginBottomInfo($params);
                    }
                    break;

                // 用户注册
                case 'plugins_view_user_reg_info_inside_bottom' :
                    if(isset($this->plugins_config['is_reginfo_show_ent']) && $this->plugins_config['is_reginfo_show_ent'] == 1)
                    {
                        $ret = $this->IndexUserLoginBottomInfo($params);
                    }
                    break;

                // 用户登录、注册顶部
                case 'plugins_view_user_login_info_top' :
                case 'plugins_view_user_reg_info' :
                    $ret = $this->IndexUserLoginRegTopInfo($params);
                    break;

                // 用户登录、注册成功
                case 'plugins_service_user_login_end' :
                case 'plugins_service_user_register_end' :
                    $this->IndexUserLoginRegBind($params);
                    break;

                // 用户中心-个人资料
                case 'plugins_service_users_personal_show_field_list_handle' :
                    $ret = $this->UserPersonalHtml($params);
                    break;

                // 系统运行开始
                case 'plugins_service_system_begin' :
                    if($this->mca != 'indexpluginsindex' && $this->module_name == 'index')
                    {
                        $ret = $this->SystemBegin($params);
                    }

                // pc端顶部左侧
                case 'plugins_view_header_navigation_top_left_end' :
                    if($this->module_name == 'index')
                    {
                        $ret = $this->HeaderNavigationTopLeftHandle($params);
                    }
                    break;

                // 公共返回接口
                case 'plugins_service_base_common' :
                    $this->PluginsBaseCommonData($params);
                    break;

                // 登录顶部
                case 'plugins_view_user_login_content_inside_top' :
                    if($is_web_login_scan)
                    {
                        $ret = $this->UserLoginContentInsideTop($params);
                    }
                    break;
            }
            return $ret;
        }
    }

    /**
     * 登录顶部
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function UserLoginContentInsideTop($params = [])
    {
        $appid = MyC('common_app_mini_weixin_appid');
        $appsecret = MyC('common_app_mini_weixin_appsecret');
        $is_weixin_mini = (empty($appid) || empty($appsecret)) ? 0 : 1;
        $id = ScanLoginService::IDCreated($this->plugins_config);
        $scan_url = PluginsHomeUrl('thirdpartylogin', 'scan', 'index', ['id'=>$id]);
        $status_url = PluginsHomeUrl('thirdpartylogin', 'scan', 'check', ['id'=>$id]);
        return MyView('../../../plugins/thirdpartylogin/view/index/public/login_scan', [
                'scan_url'        => $scan_url,
                'status_url'      => $status_url,
                'id'              => $id,
                'is_weixin_mini'  => $is_weixin_mini,
            ]);
    }

    /**
     * 公共返回接口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function PluginsBaseCommonData($params = [])
    {
        $platform_type_list = BaseService::PlatformTypeList($this->plugins_config);
        if(!empty($platform_type_list))
        {
            $params['data']['plugins_thirdpartylogin_data'] = $platform_type_list;
        }
    }
    /**
     * 顶部导航左侧
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function HeaderNavigationTopLeftHandle($params = [])
    {
        // 当前用户
        $user = UserService::LoginUserInfo();
        if(empty($user))
        {
            // 平台列表
            $platform_type_list = BaseService::PlatformTypeList($this->plugins_config);
            MyViewAssign('platform_type_list', $platform_type_list);

            // 配置信息
            MyViewAssign('plugins_config', $this->plugins_config);
            return MyView('../../../plugins/thirdpartylogin/view/index/public/nav_top_left');
        }
    }

    /**
     * 系统运行开始
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-06T16:16:34+0800
     * @param    [array]          $params [输入参数]
     */
    public function SystemBegin($params = [])
    {
        // 当前用户
        $user = UserService::LoginUserInfo();
        if(empty($user))
        {
            // 待绑定记录
            $platform_user_id = MyCookie(BaseService::$bind_platform_user_key);
            if(empty($platform_user_id))
            {
                // 平台列表
                $platform_type_list = BaseService::PlatformTypeList($this->plugins_config);
                if(!empty($platform_type_list))
                {
                    $platform = '';
                    foreach($platform_type_list as $k=>$v)
                    {
                        switch($k)
                        {
                            // 钉钉
                            case 'dingding' :
                                if(isset($this->plugins_config['dingding_is_env_auto_login']) && $this->plugins_config['dingding_is_env_auto_login'] == 1 && IsDingdingEnv())
                                {
                                    $platform = $k;
                                }
                                break;

                            // 微信
                            case 'weixin' :
                                if(isset($this->plugins_config['weixin_is_env_auto_login']) && $this->plugins_config['weixin_is_env_auto_login'] == 1 && IsWeixinEnv())
                                {
                                    $platform = $k;
                                }
                                break;

                            // QQ
                            case 'qq' :
                                if(isset($this->plugins_config['qq_is_env_auto_login']) && $this->plugins_config['qq_is_env_auto_login'] == 1 && IsQQEnv())
                                {
                                    $platform = $k;
                                }
                                break;

                            // 支付宝
                            case 'alipay' :
                                if(isset($this->plugins_config['alipay_is_env_auto_login']) && $this->plugins_config['alipay_is_env_auto_login'] == 1 && IsAlipayEnv())
                                {
                                    $platform = $k;
                                }
                                break;

                            // 新浪微博
                            case 'weibo' :
                                if(isset($this->plugins_config['weibo_is_env_auto_login']) && $this->plugins_config['weibo_is_env_auto_login'] == 1 && IsWeiboEnv())
                                {
                                    $platform = $k;
                                }
                                break;
                        }
                    }
                    if(!empty($platform))
                    {
                        // 主控制器+方法
                        $main_ca_arr = [
                            'useruserverifyentry',
                        ];
                        if(!in_array($this->controller_name.$this->action_name, $main_ca_arr))
                        {
                            $up = ['platform'=>$platform];
                            if(!empty($params['params']))
                            {
                                // 指定跳转地址
                                if(!empty($params['params']['redirect_url']))
                                {
                                    $up['redirect_url'] = $params['params']['redirect_url'];
                                }

                                // 跳转到授权页面指定应用
                                if(!empty($params['params']['appoint']))
                                {
                                    $up['appoint'] = $params['params']['appoint'];
                                }
                            }

                            // 跳转到授权页面                         
                            exit(header('location:'.PluginsHomeUrl('thirdpartylogin', 'index', 'login', $up)));
                        }
                    }
                }
            }
        }
    }

    /**
     * 用户中心-个人资料
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-06T16:16:34+0800
     * @param    [array]          $params [输入参数]
     * @param    [array]          $user   [用户登录信息]
     */
    public function UserPersonalHtml($params = [])
    {
        // 当前登录用户
        $user = UserService::LoginUserInfo();
        if(!empty($user))
        {
            // 平台列表
            $platform_type_list = BaseService::PlatformTypeList($this->plugins_config);
            if(!empty($platform_type_list))
            {
                $redirect_url = urlencode(base64_encode(MyUrl('index/personal/index')));
                foreach($platform_type_list as $k=>$v)
                {
                    if(empty($v['bind_user_id']))
                    {
                        $value = '未绑定';
                        if(empty($v['config']['more_app']))
                        {
                            $tips = '<a href="'.PluginsHomeUrl('thirdpartylogin', 'index', 'login', ['platform'=>$k, 'redirect_url'=>$redirect_url]).'" title="'.$v['name'].'"><img src="'.StaticAttachmentUrl('icon/'.$k.'.png').'" style="background:'.$v['bg_color'].';" width="22" height="22" class="am-circle am-padding-xs" /><span class="am-margin-left-xs">'.MyLang('bind_title').'</span></a>';
                        } else {
                            $tips = '<a href="javascript:;" title="'.$v['name'].'" class="submit-popup" data-url="'.PluginsHomeUrl('thirdpartylogin', 'index', 'more', ['platform'=>$k]).'"><img src="'.StaticAttachmentUrl('icon/'.$k.'.png').'" style="background:'.$v['bg_color'].';" width="22" height="22" class="am-circle am-padding-xs" /><span class="am-margin-left-xs">'.MyLang('bind_title').'</span></a>';
                        }
                    } else {
                        $value = $v['bind_nickname'];
                        $tips = '<img src="'.$v['bind_avatar'].'" width="22" height="22" class="am-circle" />&nbsp;<a href="javascript:;" class="submit-ajax" data-url="'.PluginsHomeUrl('thirdpartylogin', 'index', 'unbind').'" data-id="'.$v['bind_id'].'" data-view="reload" data-msg="解绑后不可恢复、确认操作吗？"> '.MyLang('unbind_title').'</a>';
                    }

                    $params['data'][$k.'_openid'] = [
                        'is_ext'    => 1,
                        'name'      => $v['name'].MyLang('login_title'),
                        'value'     => $value,
                        'tips'      => $tips,
                    ];
                }
            }
        }
    }

    /**
     * 用户登录、注册成功
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function IndexUserLoginRegBind($params = [])
    {
        if(!empty($params) && !empty($params['user_id']))
        {
            // 需要绑定的用户
            $platform_user = PlatformUserService::PlatformUserInfo(MyCookie(BaseService::$bind_platform_user_key));
            if(!empty($platform_user))
            {
                // 更新平台用户关联id
                $platform_bind = PlatformUserService::PlatformUserBind($platform_user['id'], $params['user_id']);
                if($platform_bind['code'] != 0)
                {
                    return $platform_bind;
                }

                // 清除缓存
                BaseService::PlatformUserCacheRemove();
            }

            // 闽诊通绑定
            DskyPlatform::BindHandle($this->plugins_config);
        }
    }

    /**
     * 用户登录、注册顶部
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function IndexUserLoginRegTopInfo($params = [])
    {
        // 需要绑定的用户
        $platform_user = PlatformUserService::PlatformUserInfo(MyCookie(BaseService::$bind_platform_user_key));
        MyViewAssign('platform_user', $platform_user);

        // 平台名称
        $platform_name = empty($platform_user) ? '' : BaseService::PlatformTypeName($this->plugins_config, $platform_user['platform']);
        MyViewAssign('platform_name', $platform_name);

        // 页面类型
        $view_login_name = $params['hook_name'] == 'plugins_view_user_login_info_top' ? MyLang('login_title') : MyLang('register_title');
        MyViewAssign('view_login_name', $view_login_name);

        return MyView('../../../plugins/thirdpartylogin/view/index/public/bind_tips');
    }

    /**
     * 用户登录
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function IndexUserLoginBottomInfo($params = [])
    {
        // 排除独立登录页面的插件
        $pluginsname = MyInput('pluginsname');
        if(in_array($pluginsname, ['shop', 'realstore', 'chat', 'erp']))
        {
            return '';
        }

        // 平台列表
        $platform_type_list = BaseService::PlatformTypeList($this->plugins_config);
        MyViewAssign('platform_type_list', $platform_type_list);

        // 是否需要新窗口登录
        $platform_is_blank = ($this->mca == 'indexusermodallogininfo') ? 1 : 0;
        MyViewAssign('platform_is_blank', $platform_is_blank);
        return MyView('../../../plugins/thirdpartylogin/view/index/public/login');
    }
}
?>