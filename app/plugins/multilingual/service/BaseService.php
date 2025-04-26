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
namespace app\plugins\multilingual\service;

use think\facade\Db;
use app\service\PluginsService;
use app\service\UserService;
use app\plugins\multilingual\service\MultilingualService;

/**
 * 多语言 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class BaseService
{
    // 多语言数据缓存key
    public static $multilingual_data_cache_key = 'plugins_multilingual_data_';

    // 基础数据附件字段
    public static $base_config_attachment_field = [];

    // 基础私有字段
    public static $base_config_private_field = [
        'appid',
        'appkey',
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
        return PluginsService::PluginsDataSave(['plugins'=>'multilingual', 'data'=>$params]);
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
        $ret = PluginsService::PluginsData('multilingual', self::$base_config_attachment_field, $is_cache);
        if(!empty($ret['data']))
        {
            $ret['data']['can_choice_lang_arr'] = empty($ret['data']['can_choice_lang']) ? [] : explode(',', $ret['data']['can_choice_lang']);
        }
        return $ret;
    }

    /**
     * 后台导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-06
     * @desc    description
     */
    public static function AdminNavList()
    {
        $lang = MyLang('admin_nav_list');
        return [
            [
                'name'      => $lang['admin'],
                'control'   => 'admin',
                'action'    => 'index',
            ],
            [
                'name'      => $lang['trdata'],
                'control'   => 'trdata',
                'action'    => 'index',
            ],
        ];
    }
}
?>