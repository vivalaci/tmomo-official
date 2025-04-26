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
namespace app\plugins\popupscreen\service;

use app\service\PluginsService;

/**
 * 弹屏广告 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = ['images'];

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
        return PluginsService::PluginsDataSave(['plugins'=>'popupscreen', 'data'=>$params]);
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
        $ret = PluginsService::PluginsData('popupscreen', self::$base_config_attachment_field, $is_cache);
        $ret['data'] = self::BaseConfigHandle($ret['data']);
        return $ret;
    }

    /**
     * 配置数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2025-01-10
     * @desc    description
     * @param   [array]           $data [配置数据]
     */
    public static function BaseConfigHandle($data = [])
    {
        // 赋值空数组
        if(empty($data))
        {
            $data = [];
        }

        // url地址处理
        $module = RequestModule();
        $data['images_url'] = empty($data['images_url']) ? [] : (is_array($data['images_url']) ? $data['images_url'] : json_decode($data['images_url'], true));
        if($module != 'admin')
        {
            // url
            if(!empty($data['images_url']) && is_array($data['images_url']) && array_key_exists(APPLICATION_CLIENT_TYPE, $data['images_url']))
            {
                $data['images_url'] = $data['images_url'][APPLICATION_CLIENT_TYPE];
            } else {
                $data['images_url'] = '';
            }
        }
        // 是否有效
        $is_valid = 1;
        // 有效时间
        if(!empty($data['time_start']) && strtotime($data['time_start']) > time())
        {
            $is_valid = 0;
        }
        // 是否已结束
        if(!empty($data['time_end']) && strtotime($data['time_end']) < time())
        {
            $is_valid = 0;
        }
        $data['is_valid'] = $is_valid;

        return $data;
    }
}
?>