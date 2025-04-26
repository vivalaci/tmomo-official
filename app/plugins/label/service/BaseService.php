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
namespace app\plugins\label\service;

use think\facade\Db;
use app\service\PluginsService;
use app\service\ResourcesService;

/**
 * 标签 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [];

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
        return PluginsService::PluginsDataSave(['plugins'=>'label', 'data'=>$params], self::$base_config_attachment_field);
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
        return PluginsService::PluginsData('label', self::$base_config_attachment_field, $is_cache);
    }

    /**
     * 静态数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-12-07
     * @desc    description
     * @param   [string]          $key [数据key]
     */
    public static function ConstData($key)
    {
        $data = [
            // 标签展示位置
            'plugins_label_style_list' => [
                'top-left'      => '上居左',
                'top-center'    => '上居中',
                'top-right'     => '上居右',
                'bottom-left'   => '下居左',
                'bottom-center' => '下居中',
                'bottom-right'  => '下居右',
            ],
        ];
        return array_key_exists($key, $data) ? $data[$key] : null;
    }

    /**
     * 后台导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-03-07
     * @desc    description
     * @param   [array]           $plugins_config [插件配置]
     */
    public static function AdminNavList($plugins_config = [])
    {
        $lang = MyLang('admin_nav_list');
        return [
            [
                'name'      => '基础配置',
                'control'   => 'admin',
                'action'    => 'index',
            ],
            [
                'name'      => '标签管理',
                'control'   => 'label',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 用户关联的标签信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-05
     * @desc    description
     * @param   [array]           $user_ids [用户id]
     */
    public static function LabelUserData($user_ids = [])
    {
        $result = [];
        if(!empty($user_ids))
        {
            // 非数组则使用逗号转数组
            if(!is_array($user_ids))
            {
                $user_ids = explode(',', $user_ids);
            }

            // 用户标签数据
            static $plugins_label_user_data = [];
            $temp_user_ids = [];
            foreach($user_ids as $uid)
            {
                if(!array_key_exists($uid, $plugins_label_user_data))
                {
                    $temp_user_ids[] = $uid;
                }
            }
            if(!empty($temp_user_ids))
            {
                // 获取标签关联信息
                $where = [
                    ['plv.user_id', 'in', $temp_user_ids],
                    ['pl.is_enable', '=', 1],
                ];
                $data = Db::name('PluginsLabel')->alias('pl')->join('plugins_label_user plv', 'pl.id=plv.label_id')->where($where)->field('pl.id,pl.name,pl.text_color,pl.bg_color,pl.icon,plv.user_id')->select()->toArray();
                if(!empty($data))
                {
                    foreach($data as $v)
                    {
                        $v['url'] = self::LabelUrl($v['id']);
                        $v['icon'] = ResourcesService::AttachmentPathViewHandle($v['icon']);
                        $plugins_label_user_data[$v['user_id']][] = $v;
                    }
                }
                // 不存在则记录空，避免重复查询
                foreach($temp_user_ids as $uid)
                {
                    if(!array_key_exists($uid, $plugins_label_user_data))
                    {
                        $plugins_label_user_data[$uid] = [];
                    }
                }
            }

            // 返回组合数据
            foreach($user_ids as $uid)
            {
                if(array_key_exists($uid, $plugins_label_user_data))
                {
                    $result[$uid] = $plugins_label_user_data[$uid];
                }
            }
        }
        return $result;
    }

    /**
     * 标签 url 地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-09
     * @desc    description
     * @param   [int]          $id [标签id]
     */
    public static function LabelUrl($id)
    {
        return (APPLICATION == 'web') ? PluginsHomeUrl('label', 'index', 'detail', ['id'=>$id]) : '/pages/plugins/label/detail/detail?id='.$id;
    }

    /**
     * 用户管理列表搜索条件数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function LabelListUser($params = [])
    {
        $where = [
            ['pl.is_enable', '=', 1],
        ];
        $data = Db::name('PluginsLabel')->alias('pl')->join('plugins_label_user plv', 'pl.id=plv.label_id')->where($where)->field('pl.id,pl.name')->group('pl.id')->select()->toArray();
        return empty($data) ? [] : $data;
    }

    /**
     * 商品管理列表搜索条件数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function LabelListGoods($params = [])
    {
        $where = [
            ['pl.is_enable', '=', 1],
        ];
        $data = Db::name('PluginsLabel')->alias('pl')->join('plugins_label_goods plv', 'pl.id=plv.label_id')->where($where)->field('pl.id,pl.name')->group('pl.id')->select()->toArray();
        return empty($data) ? [] : $data;
    }
}
?>