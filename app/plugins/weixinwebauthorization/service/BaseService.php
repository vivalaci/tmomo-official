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
namespace app\plugins\weixinwebauthorization\service;

use app\service\PluginsService;

/**
 * 微信网页授权 - 基础服务层
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
        'appid',
        'secret',
    ];

    // 来源系统类型
    public static $system_type_key = 'plugins_weixinwebauthorization_system_type_key';
    // 来源客户端
    public static $application_client_type_key = 'plugins_weixinwebauthorization_application_client_type_key';

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
        return PluginsService::PluginsDataSave(['plugins'=>'weixinwebauthorization', 'data'=>$params]);
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
        return PluginsService::PluginsData('weixinwebauthorization', self::$base_config_attachment_field, $is_cache);
    }

    /**
     * url处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-31
     * @desc    description
     * @param   [string]          $url    [url地址]
     * @param   [array]           $config [插件配置信息]
     * @param   [array]           $params [输入参数]
     */
    public static function RequestUrlHandle($params = [])
    {
        // 默认当前页面
        $url = __MY_VIEW_URL__;

        // 是否指定跳转地址
        if(!empty($params['request_url']))
        {
            $url = base64_decode(urldecode($params['request_url']));
        }

        // 带上referrer参数
        if(!empty($params['referrer']))
        {
            if(stripos($url, 'referrer=') === false)
            {
                $join = (stripos($url, '?') === false) ? '?' : '&';
                return $url.$join.'referrer='.$params['referrer'];
            }
        }
        return $url;
    }

    /**
     * 获取需要跳转的url地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-31
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UrlOpenData($params = [])
    {
        // url地址
        if(empty($params['weixinwebauthorizationjumpurl']))
        {
            return DataReturn('url地址为空', -1);
        }

        // url解析
        $url = base64_decode(urldecode($params['weixinwebauthorizationjumpurl']));
        return DataReturn('success', 0, $url);
    }

    /**
     * 设置来源系统类型
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
            MySession(self::$system_type_key, $params['system_type']);
        }
    }

    /**
     * 获取来源系统类型
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-05
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GetSystemType($params = [])
    {
        $res = MySession(self::$system_type_key);
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
            MySession(self::$application_client_type_key, $params['application_client_type']);
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
        $res = MySession(self::$application_client_type_key);
        return empty($res) ? APPLICATION_CLIENT_TYPE : $res;
    }

    /**
     * h5地址错误页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-05
     * @desc    description
     * @param   [string]        $msg    [错误信息]
     * @param   [int]           $code   [错误码]
     */
    public static function H5PageErrorUrl($msg, $code = -1)
    {
        $h5_url = MyC('common_app_h5_url');
        if(!empty($h5_url))
        {
            $join = (stripos($h5_url, '?') === false) ? '?' : '&';
            return $h5_url.'pages/error/error'.$join.'msg='.urlencode(base64_encode($msg)).'&code='.$code;
        }
        return '';
    }

    /**
     * 错误处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-05
     * @desc    description
     * @param   [array]          $ret [返回数据]
     */
    public static function ViewErrorHandle($ret)
    {
        // 来源终端
        $application_client_type = self::GetApplicationClientType();

        // 手机端则默认
        if($application_client_type == 'h5')
        {
            $url = self::H5PageErrorUrl($ret['msg'], $ret['code']);
            if(!empty($url))
            {
                return MyRedirect($url);
            }
        }

        // 默认web页面
        MyViewAssign('msg', $ret['msg']);
        return MyView('public/tips_error');
    }
}
?>