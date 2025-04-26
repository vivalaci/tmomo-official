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
namespace app\plugins\thirdpartylogin\platform;

/**
 * 第三方登录 - 谷歌
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class GooglePlatform
{
    // 平台类型
    public static $platform = 'google';

    // 配置信息
    public static $config;

    /**
     * app绑定处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [array]          $config [平台配置信息]
     * @param   [array]          $params [输入参数]
     */
    public static function AppBind($config, $params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'openid',
                'error_msg'         => 'openid为空',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 获取用户昵称
        $params = self::GetUserInfo($params);

        // 返回统一格式
        $user = self::UserReturnData($params);
        return DataReturn('success', 0, $user);
    }

    /**
     * 用户统一返回格式
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UserReturnData($params)
    {
        // 统一格式
        return [
            'openid'    => $params['openid'],
            'unionid'   => empty($params['unionid']) ? '' : $params['unionid'],
            'nickname'  => empty($params['nickname']) ? '' : $params['nickname'],
            'avatar'    => '',
            'gender'    => 0,
            'province'  => '',
            'city'      => '',
        ];
    }

    /**
     * 用户统一返回格式
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function GetUserInfo($params = [])
    {
        if(!empty($params['access_token']))
        {
            $res = CurlGet('https://www.googleapis.com/oauth2/v3/userinfo?access_token='.$params['access_token']);
            if(!empty($res))
            {
                $res = json_decode($res, true);
                if(!empty($res))
                {
                    $params['nickname'] = empty($res['name']) ? (empty($res['email']) ? '' : $res['email']) : $res['name'];
                }
            }
        }
        return $params;
    }
}
?>