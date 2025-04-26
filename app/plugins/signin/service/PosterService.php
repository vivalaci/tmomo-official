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
namespace app\plugins\signin\service;

use app\service\ResourcesService;

/**
 * 签到 - 推广海报服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class PosterService
{
    /**
     * 获取用户分享url地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-15
     * @desc    description
     * @param   [int]          $qrcode_id [签到码id]
     * @param   [int]          $user_id   [用户id]
     */
    public static function UserShareUrl($qrcode_id, $user_id = 0)
    {
        return PluginsHomeUrl('signin', 'index', 'detail', ['id' => $qrcode_id]);
    }

    /**
     * 用户分享二维码生成
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-29
     * @desc    description
     * @param   [int]          $qrcode_id   [签到码id]
     * @param   [int]          $user_id     [用户id]
     * @param   [boolean]      $is_refresh  [是否刷新]
     * @param   [string]       $client_type [客户端类型，默认pc]
     */
    public static function UserShareQrcodeCreate($qrcode_id, $user_id, $is_refresh = false, $client_type = 'pc')
    {
        if(!empty($qrcode_id) && !empty($user_id))
        {
            // 自定义路径和名称
            $path = 'download'.DS.'plugins_signin'.DS.'qrcode'.DS.$client_type.DS.$qrcode_id.DS;
            $filename = $qrcode_id.$user_id.'.png';

            // 二维码处理参数
            $params = [
                'path'      => DS.$path,
                'filename'  => $filename,
                'dir'       => ROOT.'public'.DS.$path.$filename,
            ];

            // 目录不存在则创建
            if(\base\FileUtil::CreateDir(ROOT.'public'.DS.$path) !== true)
            {
                return DataReturn('二维码目录创建失败', -1);
            }

            // 不存在则创建
            if(!file_exists($params['dir']) || $is_refresh === true)
            {
                // 根据客户端类型生成不同的二维码
                switch($client_type)
                {
                    // 微信小程序
                    case 'weixin' :
                        $ret = self::UserCreateMiniWechatQrcode($params, $qrcode_id);
                        if($ret['code'] != 0)
                        {
                            return $ret;
                        }
                        break;

                    // 支付宝小程序
                    case 'alipay' :
                        $ret = self::UserCreateMiniAlipayQrcode($params, $qrcode_id);
                        if($ret['code'] != 0)
                        {
                            return $ret;
                        }
                        break;

                    // 头条小程序
                    case 'toutiao' :
                        $ret = self::UserCreateMiniToutiaoQrcode($params, $qrcode_id);
                        if($ret['code'] != 0)
                        {
                            return $ret;
                        }
                        break;

                    // 百度小程序
                    case 'baidu' :
                        $ret = self::UserCreateMiniBaiduQrcode($params, $qrcode_id);
                        if($ret['code'] != 0)
                        {
                            return $ret;
                        }
                        break;

                    // 默认
                    default :
                        // url
                        $params['content'] = self::UserShareUrl($qrcode_id);

                        // 创建二维码
                        $ret = (new \base\Qrcode())->Create($params);
                        if($ret['code'] != 0)
                        {
                            return $ret;
                        }
                }
            }
            return DataReturn(MyLang('handle_success'), 0, ResourcesService::AttachmentPathViewHandle($params['path'].$params['filename']));
        }
        return DataReturn('签到码或用户id有误', -100);
    }

    /**
     * 微信小程序获取二维码
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-15
     * @desc    description
     * @param   [array]        $params    [二维码相关参数]
     * @param   [int]          $qrcode_id [签到码id]
     * @param   [int]          $user_id   [用户id]
     */
    private static function UserCreateMiniWechatQrcode($params, $qrcode_id)
    {
        // 配置信息
        $appid = MyC('common_app_mini_weixin_appid');
        $appsecret = MyC('common_app_mini_weixin_appsecret');
        if(empty($appid) || empty($appsecret))
        {
            return DataReturn('微信小程序密钥未配置', -1);
        }

        // 请求参数
        $request_params = [
            'page'  => 'pages/plugins/signin/index-detail/index-detail',
            'scene' => 'share=signin&id='.$qrcode_id,
            'width' => 300,
        ];
        $ret = (new \base\Wechat($appid, $appsecret))->MiniQrCodeCreate($request_params);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 保存二维码
        if(@file_put_contents($params['dir'], $ret['data']) !== false)
        {
            return DataReturn(MyLang('get_success'), 0);
        }
        return DataReturn('二维码保存失败', -1);
    }

    /**
     * 支付宝小程序获取二维码
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-15
     * @desc    description
     * @param   [array]        $params    [二维码相关参数]
     * @param   [int]          $qrcode_id [签到码id]
     * @param   [int]          $user_id   [用户id]
     */
    private static function UserCreateMiniAlipayQrcode($params, $qrcode_id)
    {
        // 配置信息
        $appid = MyC('common_app_mini_alipay_appid');
        if(empty($appid))
        {
            return DataReturn('支付宝小程序密钥未配置', -1);
        }

        // 请求参数
        $request_params = [
            'appid' => $appid,
            'page'  => 'pages/plugins/signin/index-detail/index-detail',
            'scene' => 'share=signin&id='.$qrcode_id,
            'width' => 300,
        ];
        $ret = (new \base\Alipay())->MiniQrCodeCreate($request_params);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 保存二维码
        if(@file_put_contents($params['dir'], file_get_contents($ret['data'])) !== false)
        {
            return DataReturn(MyLang('get_success'), 0);
        }
        return DataReturn('二维码保存失败', -1);
    }

    /**
     * 头条小程序获取二维码
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-15
     * @desc    description
     * @param   [array]        $params    [二维码相关参数]
     * @param   [int]          $qrcode_id [签到码id]
     * @param   [int]          $user_id   [用户id]
     */
    private static function UserCreateMiniToutiaoQrcode($params, $qrcode_id)
    {
        // 配置信息
        $config = [
            'appid'     => MyC('common_app_mini_toutiao_appid'),
            'secret'    => MyC('common_app_mini_toutiao_appsecret'),
        ];
        if(empty($config['appid']) || empty($config['secret']))
        {
            return DataReturn('头条小程序密钥未配置', -1);
        }

        // 请求参数
        $request_params = [
            'page'  => 'pages/plugins/signin/index-detail/index-detail',
            'scene' => 'share=signin&id='.$qrcode_id,
            'width' => 300,
        ];

        $ret = (new \base\Toutiao($config))->MiniQrCodeCreate($request_params);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 保存二维码
        if(@file_put_contents($params['dir'], $ret['data']) !== false)
        {
            return DataReturn(MyLang('get_success'), 0);
        }
        return DataReturn('二维码保存失败', -1);
    }

    /**
     * 百度小程序获取二维码
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-15
     * @desc    description
     * @param   [array]        $params    [二维码相关参数]
     * @param   [int]          $qrcode_id [签到码id]
     * @param   [int]          $user_id   [用户id]
     */
    private static function UserCreateMiniBaiduQrcode($params, $qrcode_id)
    {
        // 配置信息
        $config = [
            'appid'     => MyC('common_app_mini_baidu_appid'),
            'key'       => MyC('common_app_mini_baidu_appkey'),
            'secret'    => MyC('common_app_mini_baidu_appsecret'),
        ];
        if(empty($config['appid']) || empty($config['key']) || empty($config['secret']))
        {
            return DataReturn('百度小程序密钥未配置', -1);
        }

        // 请求参数
        $request_params = [
            'page'  => 'pages/plugins/signin/index-detail/index-detail',
            'scene' => 'share=signin&id='.$qrcode_id,
            'width' => 300,
        ];

        $ret = (new \base\Baidu($config))->MiniQrCodeCreate($request_params);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 保存二维码
        if(@file_put_contents($params['dir'], $ret['data']) !== false)
        {
            return DataReturn(MyLang('get_success'), 0);
        }
        return DataReturn('二维码保存失败', -1);
    }
}
?>