<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\plugins\homemiddleadv\service;

use app\service\PluginsService;
use app\plugins\homemiddleadv\service\SliderService;

/**
 * 基础服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [];

    // 基础私有字段
    public static $base_config_private_field = [
        'data_list',
    ];

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
        return PluginsService::PluginsDataSave(['plugins'=>'homemiddleadv', 'data'=>$params], self::$base_config_attachment_field);
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
        return PluginsService::PluginsData('homemiddleadv', self::$base_config_attachment_field, $is_cache);
    }

    /**
     * 后台导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-06
     * @desc    description
     */
    public static function AdminNavMenuList()
    {
        return [
            [
                'name'      => '基础配置',
                'control'   => 'admin',
                'action'    => 'index',
            ],
            [
                'name'      => '数据管理',
                'control'   => 'slider',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 获取首页数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-03
     * @desc    description
     * @param   [array]          $config [插件配置信息]
     */
    public static function HomeDataList($config)
    {
        // 终端
        if(APPLICATION == 'web')
        {
            // 是否开启web端
            if(!isset($config['is_web_show']) || $config['is_web_show'] != 1)
            {
                return [];
            }
        } else {
            // 是否开启app端
            if(!isset($config['is_app_show']) || $config['is_app_show'] != 1)
            {
                return [];
            }
        }

        // 有效时间
        if(!empty($config['time_start']))
        {
            // 是否已开始
            if(strtotime($config['time_start']) > time())
            {
                return [];
            }
        }
        if(!empty($config['time_end']))
        {
            // 是否已结束
            if(strtotime($config['time_end']) < time())
            {
                return [];
            }
        }

        // 获取列表
        $data = [];
        $ret = SliderService::SliderList();
        if(!empty($ret['data']))
        {
            foreach($ret['data'] as $v)
            {
                if(isset($v['is_enable']) && $v['is_enable'] == 1 && (empty($v['platform']) || in_array(APPLICATION_CLIENT_TYPE, $v['platform'])))
                {
                    $data[] = $v;
                }
            }
        }
        return $data;
    }
}
?>