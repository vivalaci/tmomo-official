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
namespace app\plugins\seckill\service;

use think\facade\Db;
use app\service\PluginsService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\plugins\seckill\service\SeckillGoodsService;

/**
 * 限时秒杀服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [
        'header_logo',
        'header_bg',
        'home_title_icon',
        'home_bg',
    ];

    // 商品审核状态（0待取货, 1已取货, 2异常）
    public static $plugins_goods_audit_status_list = [
        0 => ['value'=>0, 'name'=>'待审核'],
        1 => ['value'=>1, 'name'=>'已审核'],
        2 => ['value'=>2, 'name'=>'已拒绝'],
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
        return PluginsService::PluginsDataSave(['plugins'=>'seckill', 'data'=>$params], self::$base_config_attachment_field);
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
        $ret = PluginsService::PluginsData('seckill', self::$base_config_attachment_field, $is_cache);
        if(empty($ret['data']))
        {
            $ret['data'] = [];
        }

        // 描述
        $ret['data']['content_notice'] = empty($ret['data']['content_notice']) ? [] : explode("\n", $ret['data']['content_notice']);

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
    public static function AdminNavMenuList()
    {
        $lang = MyLang('admin_nav_menu_list_data');
        return [
            [
                'name'      => $lang['admin'],
                'control'   => 'admin',
                'action'    => 'index',
            ],
            [
                'name'      => $lang['goods'],
                'control'   => 'goods',
                'action'    => 'index',
            ],
            [
                'name'      => $lang['periods'],
                'control'   => 'periods',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 商品搜索
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $config [插件配置]
     * @param    [array]          $params [输入参数]
     */
    public static function GoodsSearchList($config, $params = [])
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

        // 是否多商户
        if(empty($params['shop_id']))
        {
            // 系统分类id
            if(!empty($params['category_id']))
            {
                $category_ids = GoodsCategoryService::GoodsCategoryItemsIds([$params['category_id']], 1);
                $category_ids[] = $params['category_id'];
                $where[] = ['gci.category_id', 'in', $category_ids];
            }
        } else {
            // 店铺id
            $where[] = ['g.shop_id', '=', intval($params['shop_id'])];

            // 店铺分类id
            if(!empty($params['category_id']))
            {
                $where[] = ['g.shop_category_id', '=', intval($params['category_id'])];
            }
        }

        // 读取字段
        $field = 'g.id,g.title,g.images,g.price,g.original_price,g.min_price,g.min_original_price,g.images';
        // 是否开启多商户支持
        if(isset($config['is_shop_seckill']) && $config['is_shop_seckill'] == 1)
        {
            $field .= ',g.shop_id';
        }

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
                $min_price = $text[0]-$dec_price;
                $max_price = $text[1]-$dec_price;
                $price = ($min_price <= 0) ? '0.00' : PriceNumberFormat($min_price);
                $price .= '-'.(($max_price <= 0) ? '0.00' : PriceNumberFormat($max_price));
            } else {
                $price = (float) $price-$dec_price;
                $price = ($price <= 0) ? '0.00' : PriceNumberFormat($price);
            }

        // 折扣
        } else if($discount_rate > 0)
        {
            if(stripos($price, '-') !== false)
            {
                $text = explode('-', $price);
                $min_price = $text[0]*$discount_rate;
                $max_price = $text[1]*$discount_rate;
                $price = ($min_price <= 0) ? '0.00' : PriceNumberFormat($min_price);
                $price .= '-'.(($max_price <= 0) ? '0.00' : PriceNumberFormat($max_price));
            } else {
                $price = (float) $price *$discount_rate;
                $price = ($price <= 0) ? '0.00' : PriceNumberFormat($price);
            }
        }
        
        return $price;
    }

    /**
     * 剩余时间计算
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-26
     * @desc    description
     * @param   [int]          $time_start [开始时间]
     * @param   [int]          $time_end   [结束时间]
     */
    public static function TimeCalculate($time_start, $time_end)
    {
        // 默认
        // status 0未开始, 1进行中(距离结束还有), 2已结束, 3异常错误
        $result = ['hours'=>'00', 'minutes'=>'00', 'seconds'=>'00', 'status'=>1, 'msg'=>'进行中', 'buy_text'=>'抢购中', 'time_first_text'=>'距结束'];

        // 配置有误
        $time = $time_end-$time_start;
        if($time <= 0)
        {
            $result['status'] = 3;
            $result['msg'] = '配置有误';
            $result['buy_text'] = '';
            $result['time_first_text'] = '';
            return $result;
        }

        // 已结束
        if($time_end < time())
        {
            $result['status'] = 2;
            $result['msg'] = '已结束';
            $result['buy_text'] = '已结束';
            $result['time_first_text'] = '';
            return $result;
        }

        // 还没开始
        if($time_start > time())
        {
            $result['status'] = 0;
            $result['msg'] = '即将开始';
            $result['buy_text'] = '即将开始';
            $result['time_first_text'] = '距开始';
            $time = $time_start-time();
        }

        // 活动正常，结束时间减去当前时间
        if($result['status'] == 1)
        {
            $time = $time_end-time();
        }

        // 计算时分秒
        $hours = intval($time/3600);
        $modulus = $time%3600;
        $minutes = intval($modulus/60);
        $seconds = $modulus%60;

        // 组合
        $result['hours'] = ($hours < 10) ? '0'.$hours : $hours;
        $result['minutes'] = ($minutes < 10) ? '0'.$minutes : $minutes;
        $result['seconds'] = ($seconds < 10) ? '0'.$seconds : $seconds;
        return $result;
    }

    /**
     * 获取店铺id
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-08
     * @desc    description
     */
    public static function ShopID()
    {
        return CallPluginsServiceMethod('shop', 'ShopService', 'CurrentUserShopID', true);
    }

    /**
     * 商品是否存在有效秒杀中（提供给外部插件使用）
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-05-19
     * @desc    description
     * @param   [array]          $goods_ids [商品id]
     */
    public static function GoodsIsExistValidSeckill($goods_ids)
    {
        $count = 0;
        $base = self::BaseConfig();
        if(!empty($base['data']))
        {
            $ret = SeckillGoodsService::SeckillData($base['data'], ['goods_ids'=>$goods_ids, 'is_goods_handle'=>0]);
            if(!empty($ret['data']['current']) && !empty($ret['data']['current']['goods']) && !empty($ret['data']['current']['time']) && isset($ret['data']['current']['time']['status']) and in_array($ret['data']['current']['time']['status'], [1]))
            {
                $count = count($ret['data']['current']['goods']);
            }
        }
        return $count > 0;
    }
}
?>