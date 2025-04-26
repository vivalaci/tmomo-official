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
namespace app\plugins\brand\service;

use app\service\PluginsService;
use app\service\BrandService;
use app\service\BrandCategoryService;

/**
 * 品牌 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [
        'brand_home_category_all_icon'
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
        return PluginsService::PluginsDataSave(['plugins'=>'brand', 'data'=>$params], self::$base_config_attachment_field);
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
        return PluginsService::PluginsData('brand', self::$base_config_attachment_field, $is_cache);
    }

    /**
     * 品牌列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function BrandList($params = [])
    {
        $data_params = [
            'where'         => [
                ['is_enable', '=', 1],
            ],
            'field'         => 'id,name,describe,logo,website_url',
            'm'             => 0,
            'n'             => 0,
        ];
        $ret = BrandService::BrandList($data_params);
        if(!empty($ret['data']))
        {
            $default_logo = StaticAttachmentUrl('default-brand.jpg');
            foreach($ret['data'] as &$v)
            {
                if(empty($v['logo']))
                {
                    $v['logo'] = $default_logo;
                }
            }
        }
        return $ret['data'];
    }

    /**
     * 品牌分类列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-11
     * @desc    description
     * @param   [array]           $config [插件配置]
     */
    public static function BrandCategoryList($config = [])
    {
        $brand_category = BrandCategoryService::BrandCategoryList(['field'=>'id,name,icon']);
        if(!empty($brand_category['data']))
        {
            // 未设置图标则不增加默认图标
            $temp = array_filter(array_column($brand_category['data'], 'icon'));
            if(empty($temp))
            {
                $icon = '';
            } else {
                $icon = empty($config['brand_home_category_all_icon']) ? '' : $config['brand_home_category_all_icon'];
            }
            array_unshift($brand_category['data'], [
                'id'           => 0,
                'name'         => MyLang('all_title'),
                'icon'         => $icon,
            ]);
        }
        return $brand_category['data'];
    }

    /**
     * 根据品牌id获取信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-11
     * @desc    description
     * @param   [int]          $brand_id [品牌id]
     */
    public static function BrandInfo($brand_id)
    {
        $data_params = [
            'field'     => 'id,name,describe,logo',
            'where'     => [
                ['id', '=', intval($brand_id)]
            ],
            'm'         => 0,
            'n'         => 1,
        ];
        $ret = BrandService::BrandList($data_params);
        return (empty($ret['data']) || empty($ret['data'][0])) ? [] : $ret['data'][0];
    }
}
?>