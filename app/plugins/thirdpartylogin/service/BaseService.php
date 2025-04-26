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
namespace app\plugins\thirdpartylogin\service;

use think\facade\Db;
use app\service\PluginsService;
use app\service\UserService;
use app\plugins\thirdpartylogin\service\PlatformUserService;

/**
 * 第三方登录 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [];

    // 基础私有字段
    public static $base_config_private_field = [
        'alipay_app_id',
        'alipay_rsa_public',
        'alipay_rsa_private',
        'alipay_out_rsa_public',
        'weixin_app_id',
        'weixin_app_secret',
        'weixin_web_id',
        'weixin_web_secret',
        'weixin_public_id',
        'weixin_public_secret',
        'qq_app_id',
        'qq_app_secret',
        'qq_web_id',
        'qq_web_secret',
        'weibo_app_id',
        'weibo_app_secret',
        '3dsky_app_id',
        '3dsky_app_secret',
        'dingding_app_name',
        'dingding_app_id',
        'dingding_app_secret',
    ];

    // 登录绑定平台用户 id 缓存 key
    public static $bind_platform_user_key = 'plugins_thirdpartylogin_platform_user_id';

    // 指定跳转地址缓存 key
    public static $back_redirect_url_key = 'plugins_thirdpartylogin_back_redirect_url';

    // 防止csrf攻击请求 key
    public static $request_state_key = 'plugins_thirdpartylogin_request_state_key';

    // 来源标识 key
    public static $request_key = 'plugins_thirdpartylogin_request_key';

    // 来源系统标识
    public static $system_type_key = 'plugins_thirdpartylogin_system_type_key';

    // 来源客户端
    public static $application_client_type_key = 'plugins_thirdpartylogin_application_client_type_key';
    /**
     * 基础配置信息保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseConfigSave($params = [])
    {
        // 钉钉更多帐号
        if(!empty($params['dingding_more_app']) && is_array($params['dingding_more_app']))
        {
            $dingding_more_app = [];
            foreach($params['dingding_more_app'] as $v)
            {
                if(!empty($v['app_id']) && !empty($v['app_secret']))
                {
                    $dingding_more_app[md5($v['app_id'])] = $v;
                }
            }
            $params['dingding_more_app'] = $dingding_more_app;
        }

        return PluginsService::PluginsDataSave(['plugins'=>'thirdpartylogin', 'data'=>$params]);
    }
    
    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [boolean]          $is_cache [是否缓存中读取]
     */
    public static function BaseConfig($is_cache = true)
    {
        return PluginsService::PluginsData('thirdpartylogin', self::$base_config_attachment_field, $is_cache);
    }

    /**
     * 静态数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-19
     * @desc    description
     */
    public static function ConstData($key = '')
    {
        $lang = MyLang('platform_type_list');
        $data = [
            // 绑定平台列表
            'platform_type_list' => [
                'dingding' => [
                    'name'      => $lang['dingding'],
                    'bg_color'  => '#E5F6FE',
                    'config'    => [],
                ],
                'alipay' => [
                    'name'      => $lang['alipay'],
                    'bg_color'  => '#E5F6FE',
                    'config'    => [],
                ],
                'weixin' => [
                    'name'      => $lang['weixin'],
                    'bg_color'  => '#E5F8E5',
                    'config'    => [],
                ],
                'qq' => [
                    'name'      => $lang['qq'],
                    'bg_color'  => '#E5F6FE',
                    'config'    => [],
                ],
                'weibo' => [
                    'name'      => $lang['weibo'],
                    'bg_color'  => '#FCE7E7',
                    'config'    => [],
                ],
                '3dsky' => [
                    'name'      => $lang['3dsky'],
                    'bg_color'  => '#3d86da',
                    'config'    => [],
                ],
                'apple' => [
                    'name'      => $lang['apple'],
                    'bg_color'  => '#F2F2F2',
                    'config'    => [],
                ],
                'google' => [
                    'name'      => $lang['google'],
                    'bg_color'  => '#F7E7E6',
                    'config'    => [],
                ],
                'line' => [
                    'name'      => $lang['line'],
                    'bg_color'  => '#D8F3E1',
                    'config'    => [],
                ],
            ],
        ];
        return empty($key) ? $data : (isset($data[$key]) ? $data[$key] : $key);
    }

    /**
     * 平台列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [array]           $config       [配置信息]
     * @param   [boolean]         $is_config    [是否需要配置信息]
     */
    public static function PlatformTypeList($config, $is_config = false)
    {
        $result = [];
        if(!empty($config) && is_array($config))
        {
            // 来源终端
            $application_client_type = BaseService::GetApplicationClientType();

            // 平台列表
            $platform_type_list = self::ConstData('platform_type_list');

            // 钉钉
            $platform = 'dingding';
            if(isset($config['dingding_is_enable']) && $config['dingding_is_enable'] == 1 && isset($platform_type_list[$platform]))
            {
                $platform_config = $platform_type_list[$platform];
                if($is_config)
                {
                    $platform_config['config'] = [
                        'app_name'          => empty($config['dingding_app_name']) ? '' : $config['dingding_app_name'],
                        'app_id'            => empty($config['dingding_app_id']) ? '' : $config['dingding_app_id'],
                        'app_secret'        => empty($config['dingding_app_secret']) ? '' : $config['dingding_app_secret'],
                        'is_env_auto_login' => empty($config['dingding_is_env_auto_login']) ? '' : $config['dingding_is_env_auto_login'],
                        'more_app'          => empty($config['dingding_more_app']) ? [] : $config['dingding_more_app'],
                    ];
                }
                $result[$platform] = $platform_config;
            }

            // 支付宝
            $platform = 'alipay';
            if(isset($config['alipay_is_enable']) && $config['alipay_is_enable'] == 1 && isset($platform_type_list[$platform]))
            {
                $platform_config = $platform_type_list[$platform];
                if($is_config)
                {
                    $platform_config['config'] = [
                        'app_id'            => empty($config['alipay_app_id']) ? '' : $config['alipay_app_id'],
                        'rsa_public'        => empty($config['alipay_rsa_public']) ? '' : $config['alipay_rsa_public'],
                        'rsa_private'       => empty($config['alipay_rsa_private']) ? '' : $config['alipay_rsa_private'],
                        'out_rsa_public'    => empty($config['alipay_out_rsa_public']) ? '' : $config['alipay_out_rsa_public'],
                        'is_env_auto_login' => empty($config['alipay_is_env_auto_login']) ? '' : $config['alipay_is_env_auto_login'],
                    ];
                }
                $result[$platform] = $platform_config;
            }

            // 微信
            $platform = 'weixin';
            if(isset($config['weixin_is_enable']) && $config['weixin_is_enable'] == 1 && isset($platform_type_list[$platform]))
            {
                $platform_config = $platform_type_list[$platform];
                if($is_config)
                {
                    if(in_array($application_client_type, ['pc', 'h5']))
                    {
                        if(IsWeixinEnv())
                        {
                            $app_id = empty($config['weixin_public_id']) ? '' : $config['weixin_public_id'];
                            $app_secret = empty($config['weixin_public_secret']) ? '' : $config['weixin_public_secret'];
                        } else {
                            $app_id = empty($config['weixin_web_id']) ? '' : $config['weixin_web_id'];
                            $app_secret = empty($config['weixin_web_secret']) ? '' : $config['weixin_web_secret'];
                        }
                    } else {
                        $app_id = empty($config['weixin_app_id']) ? '' : $config['weixin_app_id'];
                        $app_secret = empty($config['weixin_app_secret']) ? '' : $config['weixin_app_secret'];
                    }
                    $platform_config['config'] = [
                        'app_id'                => $app_id,
                        'app_secret'            => $app_secret,
                        'public_is_auth_base'   => (isset($config['weixin_public_is_auth_base']) && $config['weixin_public_is_auth_base'] == 1) ? 1 : 0,
                    ];
                }
                $result[$platform] = $platform_config;
            }

            // QQ
            $platform = 'qq';
            if(isset($config['qq_is_enable']) && $config['qq_is_enable'] == 1 && isset($platform_type_list[$platform]))
            {
                $platform_config = $platform_type_list[$platform];
                if($is_config)
                {
                    if(in_array($application_client_type, ['pc', 'h5']))
                    {
                        $app_id = empty($config['qq_web_id']) ? '' : $config['qq_web_id'];
                        $app_secret = empty($config['qq_web_secret']) ? '' : $config['qq_web_secret'];
                    } else {
                        $app_id = empty($config['qq_app_id']) ? '' : $config['qq_app_id'];
                        $app_secret = empty($config['qq_app_secret']) ? '' : $config['qq_app_secret'];
                    }
                    $platform_config['config'] = [
                        'app_id'            => $app_id,
                        'app_secret'        => $app_secret,
                        'is_env_auto_login' => empty($config['qq_is_env_auto_login']) ? '' : $config['qq_is_env_auto_login'],
                    ];
                }
                $result[$platform] = $platform_config;
            }

            // 新浪微博
            $platform = 'weibo';
            if(isset($config['weibo_is_enable']) && $config['weibo_is_enable'] == 1 && isset($platform_type_list[$platform]))
            {
                $platform_config = $platform_type_list[$platform];
                if($is_config)
                {
                    $platform_config['config'] = [
                        'app_id'            => empty($config['weibo_app_id']) ? '' : $config['weibo_app_id'],
                        'app_secret'        => empty($config['weibo_app_secret']) ? '' : $config['weibo_app_secret'],
                        'is_env_auto_login' => empty($config['weibo_is_env_auto_login']) ? '' : $config['weibo_is_env_auto_login'],
                    ];
                }
                $result[$platform] = $platform_config;
            }

            // 闽诊通、仅支持微信小程序
            // 这个仅支持 闽诊通 那边跳转过来，参数 token

            // 苹果、目前仅支持ios端
            if(APPLICATION_CLIENT_TYPE == 'ios')
            {
                $platform = 'apple';
                if(isset($config['apple_is_enable']) && $config['apple_is_enable'] == 1 && isset($platform_type_list[$platform]))
                {
                    $platform_config = $platform_type_list[$platform];
                    if($is_config)
                    {
                        $platform_config['config'] = [];
                    }
                    $result[$platform] = $platform_config;
                }
            }

            // 谷歌、目前仅支持app端
            if(in_array(APPLICATION_CLIENT_TYPE, ['ios', 'android']))
            {
                $platform = 'google';
                if(isset($config['google_is_enable']) && $config['google_is_enable'] == 1 && isset($platform_type_list[$platform]))
                {
                    $platform_config = $platform_type_list[$platform];
                    if($is_config)
                    {
                        $platform_config['config'] = [];
                    }
                    $result[$platform] = $platform_config;
                }
            }

            if(!empty($result))
            {
                // 绑定数据处理
                $platform_user_list = [];
                $user = UserService::LoginUserInfo();
                if(!empty($user))
                {
                    // 平台用户列表
                    $platform_user_list = PlatformUserService::PlatformUserList($user['id']);
                    if(!empty($platform_user_list))
                    {
                        $platform_user_list = array_column($platform_user_list, null, 'platform');
                    }
                }
                foreach($result as $k=>$v)
                {
                    // icon增加和登录
                    $up = [
                        'platform'                  => $k,
                        'system_type'               => SYSTEM_TYPE,
                        'application_client_type'   => APPLICATION_CLIENT_TYPE,
                    ];
                    $result[$k]['login_url'] = PluginsHomeUrl('thirdpartylogin', 'index', 'login', $up);
                    $result[$k]['icon'] = StaticAttachmentUrl('icon/'.$k.'.png');

                    // 绑定数据处理
                    if(!empty($platform_user_list) && array_key_exists($k, $platform_user_list))
                    {
                        $temp = $platform_user_list[$k];
                        $result[$k]['bind_avatar'] = $temp['avatar'];
                        $result[$k]['bind_nickname'] = $temp['nickname'];
                        $result[$k]['bind_user_id'] = $temp['user_id'];
                        $result[$k]['bind_id'] = $temp['id'];
                    } else {
                        $result[$k]['bind_avatar'] = '';
                        $result[$k]['bind_nickname'] = '';
                        $result[$k]['bind_user_id'] = 0;
                        $result[$k]['bind_id'] = 0;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取平台名称
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-22
     * @desc    description
     * @param   [array]          $config   [插件配置信息]
     * @param   [string]         $platform [平台标记]
     */
    public static function PlatformTypeName($config, $platform)
    {
        $platform_list = self::ConstData('platform_type_list');
        if(!empty($platform_list) && array_key_exists($platform, $platform_list))
        {
            return $platform_list[$platform]['name'];
        }
        return '';
    }

    /**
     * 设置指定跳转地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SetRedirectUrl($params = [])
    {
        // 指定跳转地址
        if(!empty($params['redirect_url']))
        {
            self::SetRedirectUrlValue(base64_decode(urldecode($params['redirect_url'])));
        }
    }

    /**
     * 设置指定跳转地址值
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-23
     * @desc    description
     * @param   [string]           $redirect_url [跳转地址]
     */
    public static function SetRedirectUrlValue($redirect_url)
    {
        // 指定跳转地址
        if(!empty($redirect_url))
        {
            MyCookie(self::$back_redirect_url_key, $redirect_url);
        }
    }

    /**
     * 获取回调跳转地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-23
     * @desc    description
     */
    public static function BackRedirectUrl()
    {
        $url = MyCookie(self::$back_redirect_url_key);
        return empty($url) ? __MY_URL__ : $url;
    }

    /**
     * 清除缓存-平台用户信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-23
     * @desc    description
     */
    public static function PlatformUserCacheRemove()
    {
        MyCookie(self::$bind_platform_user_key, null);
    }

    /**
     * 清除缓存-指定跳转地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-23
     * @desc    description
     */
    public static function RedirectUrlCacheRemove()
    {
        MyCookie(self::$back_redirect_url_key, null);
    }

    /**
     * 防止csrf攻击state值生成
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-24
     * @desc    description
     * @param   [string]          $platform [平台标识]
     */
    public static function RequestStateCreate($platform)
    {
        $value = RandomString(10);
        MyCookie(self::$request_state_key.$platform, $value);
        return $value;
    }

    /**
     * 防止csrf攻击state值获取
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-24
     * @desc    description
     * @param   [string]          $platform [平台标识]
     */
    public static function RequestStateValue($platform)
    {
        return MyCookie(self::$request_state_key.$platform);
    }

    /**
     * 设置来源标识数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-24
     * @desc    description
     * @param   [string]          $value [标识值]
     */
    public static function SetRequestValue($value = null)
    {
        MySession(self::$request_key, $value);
    }

    /**
     * 获取来源标识数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-24
     * @desc    description
     */
    public static function GetRequestValue()
    {
        return MySession(self::$request_key);
    }

    /**
     * 防止csrf攻击state值清除
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-24
     * @desc    description
     * @param   [string]          $platform [平台标识]
     */
    public static function RequestStateCacheRemove($platform)
    {
        MyCookie(self::$request_state_key.$platform, null);
    }

    /**
     * 设置系统标识
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-05
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SetSystemType($params = [])
    {
        if(!empty($params['system_type']))
        {
            MyCookie(self::$system_type_key, $params['system_type']);
        }
    }

    /**
     * 获取系统标识
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-05
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GetSystemType($params = [])
    {
        $res = MyCookie(self::$system_type_key);
        return empty($res) ? SYSTEM_TYPE : $res;
    }

    /**
     * 设置客户端类型
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-05
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SetApplicationClientType($params = [])
    {
        if(!empty($params['application_client_type']))
        {
            MyCookie(self::$application_client_type_key, $params['application_client_type']);
        }
    }

    /**
     * 获取客户端类型
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-05
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GetApplicationClientType($params = [])
    {
        $res = MyCookie(self::$application_client_type_key);
        return empty($res) ? APPLICATION_CLIENT_TYPE : $res;
    }

    /**
     * h5地址错误页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-05
     * @desc    description
     * @param   [array]         $config [配置信息]
     * @param   [string]        $msg    [错误信息]
     * @param   [int]           $code   [错误码]
     */
    public static function H5PageErrorUrl($config, $msg, $code = -1)
    {
        $h5_url = empty($config['h5_url']) ? MyC('common_app_h5_url') : $config['h5_url'];
        if(!empty($h5_url))
        {
            $join = (stripos($h5_url, '?') === false) ? '?' : '&';
            return $h5_url.'pages/login/login'.$join.'msg='.urlencode(base64_encode($msg)).'&code='.$code;
        }
        return '';
    }

    /**
     * h5地址成功页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-05
     * @desc    description
     * @param   [array]         $config [配置信息]
     * @param   [array]         $data   [成功的数据]
     */
    public static function H5PageSuccessUrl($config, $data)
    {
        $h5_url = empty($config['h5_url']) ? MyC('common_app_h5_url') : $config['h5_url'];
        if(!empty($h5_url))
        {
            $join = (stripos($h5_url, '?') === false) ? '?' : '&';
            return $h5_url.'pages/login/login'.$join.'thirdpartylogin='.urlencode(base64_encode(json_encode($data)));
        }
        return '';
    }

    /**
     * 用户openid值
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-19
     * @desc    description
     * @param   [int]           $user_id  [用户id]
     * @param   [string]        $platform [平台类型]
     */
    public static function UserOpenidValue($user_id, $platform)
    {
        $openid = array_filter(Db::name('PluginsThirdpartyloginUser')->where(['user_id'=>$user_id, 'platform'=>$platform, 'status'=>1])->column('openid'));
        return empty($openid) ? '' : $openid[0];
    }
}
?>