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
namespace app\plugins\activity\service;

use think\facade\Db;
use app\service\PluginsService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;

/**
 * 活动配置 - 基础服务层
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
        return PluginsService::PluginsDataSave(['plugins'=>'activity', 'data'=>$params], self::$base_config_attachment_field);
    }
    
    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * 
     * @param   [boolean]          $is_cache [是否缓存中读取]
     */
    public static function BaseConfig($is_cache = true)
    {
        return PluginsService::PluginsData('activity', self::$base_config_attachment_field, $is_cache);
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
                'name'      => '活动管理',
                'control'   => 'activity',
                'action'    => 'index',
            ],
            [
                'name'      => '分类管理',
                'control'   => 'category',
                'action'    => 'index',
            ],
            [
                'name'      => '轮播管理',
                'control'   => 'slider',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 静态数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-10-13
     * @desc    description
     * @param   [string]          $key [数据key]
     */
    public static function ConstData($key)
    {
        $data = [
            // 首页楼层位置
            'home_floor_location_list' => [
                0 => ['value' => 0, 'name' => '楼层上面', 'checked' => true],
                1 => ['value' => 1, 'name' => '楼层下面'],
            ],

            // 推荐样式类型
            'recommend_style_type_list' => [
                0 => ['value' => 0, 'name' => '图文列表', 'checked' => true],
                1 => ['value' => 1, 'name' => '九方格'],
                2 => ['value' => 2, 'name' => '一行滚动'],
            ],

            // 问答排序类型
            'activity_order_by_type_list' => [
                0 => ['index' => 0, 'value' => 'id', 'name' => '最新', 'checked' => true],
                1 => ['index' => 1, 'value' => 'access_count', 'name' => '热度'],
                2 => ['index' => 2, 'value' => 'sort', 'name' => '排序'],
                3 => ['index' => 3, 'value' => 'upd_time', 'name' => '更新'],
            ],
        ];
        return array_key_exists($key, $data) ? $data[$key] : [];
    }

    /**
     * 商品搜索
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function GoodsSearchList($params = [])
    {
        // 条件
        $where = [
            ['g.is_delete_time', '=', 0],
            ['g.is_shelves', '=', 1]
        ];

        // 关键字
        if(!empty($params['keywords']))
        {
            $where[] = ['g.title', 'like', '%'.$params['keywords'].'%'];
        }

        // 分类id
        if(!empty($params['category_id']))
        {
            $category_ids = GoodsCategoryService::GoodsCategoryItemsIds([$params['category_id']], 1);
            $category_ids[] = $params['category_id'];
            $where[] = ['gci.category_id', 'in', $category_ids];
        }

        // 指定字段
        $field = 'g.id,g.title,g.images,g.price';

        // 获取数据
        return GoodsService::CategoryGoodsList(['where'=>$where, 'm'=>0, 'n'=>100, 'field'=>$field, 'is_admin_access'=>1]);
    }

    /**
     * 优惠价格计算
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-26
     * @desc    description
     * @param   [string]          $price            [商品展示金额]
     * @param   [int]             $discount_rate    [折扣系数]
     * @param   [int]             $dec_price        [减金额]
     */
    public static function PriceCalculate($price, $discount_rate = 0, $dec_price = 0)
    {
        if($discount_rate <= 0 && $dec_price <= 0)
        {
            return $price;
        }

        // 减金额
        if($dec_price > 0)
        {
            if(stripos($price, '-') !== false)
            {
                $text = explode('-', $price);
                $min_price = floatval($text[0])-floatval($dec_price);
                $max_price = floatval($text[1])-floatval($dec_price);
                $price = ($min_price <= 0) ? '0.00' : PriceNumberFormat($min_price);
                $price .= '-'.(($max_price <= 0) ? '0.00' : PriceNumberFormat($max_price));
            } else {
                if(floatval($price) > 0)
                {
                    $price = floatval($price)-floatval($dec_price);
                    $price = ($price <= 0) ? 0.00 : PriceNumberFormat($price);
                }
            }

        // 折扣
        } else if($discount_rate > 0)
        {
            if(stripos($price, '-') !== false)
            {
                $text = explode('-', $price);
                $min_price = floatval($text[0])*floatval($discount_rate);
                $max_price = floatval($text[1])*floatval($discount_rate);
                $price = ($min_price <= 0) ? '0.00' : PriceNumberFormat($min_price);
                $price .= '-'.(($max_price <= 0) ? '0.00' : PriceNumberFormat($max_price));
            } else {
                if(floatval($price) > 0)
                {
                    $price = floatval($price)*floatval($discount_rate);
                    $price = ($price <= 0) ? 0.00 : PriceNumberFormat($price);
                }
            }
        }
        
        return $price;
    }
}
?>