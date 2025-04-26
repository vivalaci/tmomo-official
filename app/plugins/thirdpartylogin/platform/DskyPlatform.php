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

use app\plugins\thirdpartylogin\service\PlatformUserService;

/**
 * 第三方登录 - 闽诊通
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class DskyPlatform
{
    // 平台类型
    public static $platform = '3dsky';

    // 配置信息
    public static $config;

    /**
     * 绑定处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [array]          $config  [平台配置信息]
     * @param   [array]          $params  [输入参数]
     */
    public static function BindHandle($config, $params = [])
    {
        // 请求参数
        $system_params = MyInput();
        if(empty($system_params['mzt_token']))
        {
            return DataReturn('无需处理', 0);
        }

        // 配置校验
        $ret = self::ConfigCheck($config);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 获取平台用户信息
        $data = [
            'appId'        => self::$config['3dsky_app_id'],
            'bizObj'       => json_encode(['token'=>$system_params['mzt_token']]),
            'encryptType'  => 'DES',
            'method'       => 'blueStar/getBsTokenInfo/1.0.0',
            'platform'     => 'net-hospital-mzt',
            'signType'     => 'MD5',
            'timestamp'    => date('YmdHis'),
            'version'      => '1.0.0',
        ];
        $data['sign'] = self::SignCreated($data, self::$config['3dsky_app_secret']);

        // 用appid前8位加密app_secret
        $temp_pwd = self::DesEncrypt(self::$config['3dsky_app_secret'], substr(self::$config['3dsky_app_id'], 0, 8));
        // 截取臨時密鑰前8字節
        $des_pwd = substr($temp_pwd, 0, 8);
        // 用新密鑰加密JSON
        $data['bizContent'] = self::DesEncrypt($data['bizObj'], $des_pwd);

        // 请求接口
        $url = 'https://netapp.3dsky.com.cn/open-platform/api/gateway.do';
        $ret = CurlPost($url, $data, 1);
        if($ret['code'] != 0)
        {
            return $ret;
        }
        $res = json_decode($ret['data'], true);
        if(isset($res['retCode']) && $res['retCode'] == '0000')
        {
            // 解密数据
            $res['bizObj'] = self::DesDecrypt($res['bizContent'], $des_pwd);
            // 验证签名
            $sign = self::SignCreated($res, self::$config['3dsky_app_secret']);
            if($sign != $res['sign'])
            {
                return DataReturn('回调签名验证失败', -1);
            }

            // 得到用户信息
            $user = json_decode($res['bizObj'], true);
            if(!empty($user))
            {
                if(!empty($user['doctorId']))
                {
                    return PlatformUserService::PlatformUserLoginHandle(self::$platform, [
                        'openid'    => $user['doctorId'],
                        'nickname'  => empty($user['doctorName']) ? '' : $user['doctorName'],
                        'mobile'    => empty($user['doctorPhone']) ? '' : $user['doctorPhone'],
                    ], $config);
                }
            }
            return DataReturn('用户信息解密失败，不需要处理', 0);
        } else {
            $msg = empty($res['retMsg']) ? '请求失败' : $res['retMsg'];
            return DataReturn($msg, -1);
        }
    }

    /**
     * 生成签名
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2025-04-17
     * @desc    description
     * @param   [array]          $data [待签名的数据]
     */
    public static function SignCreated($data)
    {
        // 内容参数排序
        if(!empty($data['bizObj']))
        {
            if(!is_array($data['bizObj']))
            {
                $data['bizObj'] = json_decode($data['bizObj'], true);
            }
            if(is_array($data['bizObj']))
            {
                ksort($data['bizObj']);
                $data['bizObj'] = json_encode($data['bizObj'], JSON_UNESCAPED_UNICODE);
            }
        }
        
        // 排序
        ksort($data);
        $str = '';
        foreach($data as $k=>$v)
        {
            if(!in_array($k, ['sign', 'bizContent']) && $v !== '')
            {
                $str .= $k.'='.$v.'&';
            }
        }
        $str .= 'key='.self::$config['3dsky_app_secret'];
        return strtoupper(md5($str));
    }

    /**
     * 数据加密
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2025-04-17
     * @desc    description
     * @param   [string]          $str [加密数据]
     * @param   [string]          $key [加密key]
     * @param   [string]          $iv  [iv]
     */
    public static function DesEncrypt($str, $key, $iv = '12345678')
    {
        return base64_encode(openssl_encrypt($str, 'DES-CBC', $key, OPENSSL_RAW_DATA, $iv));
    }

    /**
     * 数据解密
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2025-04-17
     * @desc    description
     * @param   [string]          $str [解密数据]
     * @param   [string]          $key [解密key]
     * @param   [string]          $iv  [iv]
     */
    public static function DesDecrypt($str, $key, $iv = '12345678')
    {
        return openssl_decrypt(base64_decode($str), 'DES-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * 配置信息交易
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [array]          $config [配置信息]
     */
    public static function ConfigCheck($config)
    {
        // 配置信息
        if(empty($config) || !is_array($config))
        {
            return DataReturn('配置信息有误', -1);
        }

        // 配置信息参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => '3dsky_app_id',
                'error_msg'         => 'appid配置为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => '3dsky_app_secret',
                'error_msg'         => 'appsecret配置为空',
            ],
        ];
        $ret = ParamsChecked($config, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 属性赋值
        self::$config = $config;

        return DataReturn('success', 0);
    }
}
?>