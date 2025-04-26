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
namespace app\plugins\sms\service;

use app\service\PluginsService;

/**
 * 短信 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class BaseService
{
    // 基础私有字段
    public static $plugins_config_private_field = [];

    // 基础数据附件字段
    public static $plugins_config_attachment_field = [];

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
        return PluginsService::PluginsDataSave(['plugins'=>'sms', 'data'=>$params], self::$plugins_config_attachment_field);
    }
    
    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [boolean]          $is_cache    [是否缓存中读取]
     * @param   [boolean]          $is_private  [是否读取隐私字段]
     */
    public static function BaseConfig($is_cache = true, $is_private = true)
    {
        return PluginsService::PluginsData('sms', self::$plugins_config_attachment_field, $is_cache);
    }

    /**
     * 静态数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-06
     * @desc    description
     */
    public static function ConstData($key)
    {
        $data = [
            // 短信平台
            'sms_platform_list'    => [
                [
                    'name'     => '腾讯云',
                    'value'    => 'tencent',
                ],
                [
                    'name'     => '华为云',
                    'value'    => 'huawei',
                ],
                [
                    'name'     => '百度云',
                    'value'    => 'baidu',
                ],
                [
                    'name'     => '云片',
                    'value'    => 'yunpian',
                ],
                [
                    'name'     => 'LoginSms',
                    'value'    => 'loginsms',
                ],
            ],

            // 短信模板
            'sms_template_list' => [
                [
                    'name'  => '后台管理员登录',
                    'key'   => 'admin_sms_login_template',
                ],
                [
                    'name'  => '用户通用',
                    'key'   => 'common_sms_currency_template',
                ],
                [
                    'name'  => '用户登录',
                    'key'   => 'home_sms_login_template',
                ],
                [
                    'name'  => '用户注册',
                    'key'   => 'home_sms_user_reg_template',
                ],
                [
                    'name'  => '用户密码找回',
                    'key'   => 'home_sms_user_forget_pwd_template',
                ],
                [
                    'name'  => '用户手机绑定',
                    'key'   => 'home_sms_user_mobile_binding_template',
                ],
            ],
        ];
        return array_key_exists($key, $data) ? $data[$key] : null;
    }
}
?>