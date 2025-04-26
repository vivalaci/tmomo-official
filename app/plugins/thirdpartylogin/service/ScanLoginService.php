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
use app\service\UserService;
use app\service\ApiService;
use app\service\ResourcesService;
use app\service\SystemService;
use app\plugins\thirdpartylogin\service\BaseService;

/**
 * 第三方登录 - 扫码登录服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class ScanLoginService
{
    // 用户信息key
    public static $key = 'plugins_thirdpartylogin_scan_user_key_';

    /**
     * id生成
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-02
     * @desc    description
     * @param   [array]           $plugins_config [插件配置]
     */
    public static function IDCreated($plugins_config = [])
    {
        return md5(UUId().date('YmdHis').GetNumberCode(6));
    }

    /**
     * 登录url地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function LoginUrl($params = [])
    {
        return PluginsHomeUrl('thirdpartylogin', 'index', 'login', ['platform'=>WebEnv()]);
    }

    /**
     * 扫码开始
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SetScanValue($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '登录标识为空、请重新扫码登录',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'status',
                'error_msg'         => '请传状态值',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 匹配提示信息
        $msg_arr = [
            0 => '正在登录中...',
            1 => '扫码成功、请在手机上点击确认登录',
            2 => '授权登录成功',
            3 => '需要绑定账号',
        ];
        $msg = array_key_exists($params['status'], $msg_arr) ? $msg_arr[$params['status']] : '';

        // 记录状态值
        $data = [
            'status'                  => $params['status'],
            'user'                    => empty($params['user']) ? '' : $params['user'],
            'msg'                     => $msg,
            'bind_platform_user_id'   => isset($params['bind_platform_user_id']) ? $params['bind_platform_user_id'] : '',
        ];
        MyCache(md5(self::$key.$params['id']), $data, 600);
        return DataReturn('success', 0, $data);
    }

    /**
     * 扫码状态数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ScanStatusData($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'plugins_config',
                'error_msg'         => MyLang('plugins_config_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '登录标识为空、请重新扫码登录',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 记录状态值
        $data = MyCache(md5(self::$key.$params['id']));
        if(empty($data))
        {
            return DataReturn('未扫码！', 0);
        }
        return DataReturn('success', 0, $data);
    }

    /**
     * 登录确认
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ConfirmLogin($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'plugins_config',
                'error_msg'         => MyLang('plugins_config_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '登录标识为空、请重新扫码登录',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 记录状态值
        $params['status'] = 2;
        self::SetScanValue($params);
        return DataReturn(MyLang('login_success'), 0);
    }

    /**
     * 扫码状态校验
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ScanCheck($params = [])
    {
        // 获取扫码数据
        $scan = self::ScanStatusData($params);
        if($scan['code'] != 0)
        {
            return $scan;
        }

        // 是否已登录成功
        if(!empty($scan['data']) && isset($scan['data']['status']))
        {
            if($scan['data']['status'] == 2 && !empty($scan['data']['user']))
            {
                // 用户登录处理
                if(UserService::UserLoginRecord(0, $scan['data']['user'], $params))
                {
                    // 复制提示信息
                    $scan['msg'] = MyLang('login_success');
                    // 跳转页面
                    $scan['data']['referer_url'] = UserService::UserLoginOrRegBackRefererUrl();
                }
            } else {
                // 是否需要绑定账号
                if($scan['data']['status'] == 3 && !empty($scan['data']['bind_platform_user_id']))
                {
                    MyCookie(BaseService::$bind_platform_user_key, $scan['data']['bind_platform_user_id']);
                }
            }
        }
        return $scan;
    }
}
?>