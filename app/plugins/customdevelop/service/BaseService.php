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
namespace app\plugins\customdevelop\service;

use app\service\PluginsService;

/**
 * 自定义开发 - 基础服务层
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
        $fields = ['html_content', 'css_content', 'js_content'];
        foreach($fields as $v)
        {
            $params[$v] = empty($params[$v]) ? '' : htmlspecialchars_decode($params[$v]);
        }
        return PluginsService::PluginsDataSave(['plugins'=>'customdevelop', 'data'=>$params], self::$plugins_config_attachment_field);
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
        return PluginsService::PluginsData('customdevelop', self::$plugins_config_attachment_field, $is_cache);
    }
}
?>