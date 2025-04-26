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
namespace app\plugins\coupon\service;

use think\facade\Db;
use app\service\PluginsService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\service\UserService;

/**
 * 基础服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [
        'banner_images',
        'app_banner_images'
    ];

    // 类型名称
    public static $business_type_name = 'coupon';

    /**
     * 基础配置信息保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseConfigSave($params = [])
    {
        return PluginsService::PluginsDataSave(['plugins'=>'coupon', 'data'=>$params], self::$base_config_attachment_field);
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
        $ret = PluginsService::PluginsData('coupon', self::$base_config_attachment_field, $is_cache);
        if(empty($ret['data']))
        {
            $ret['data'] = [];
        }

        // 当前平台的链接地址
        if(!empty($ret['data']['url_rules']) && is_array($ret['data']['url_rules']) && array_key_exists(APPLICATION_CLIENT_TYPE, $ret['data']['url_rules']))
        {
            $ret['data']['url'] = $ret['data']['url_rules'][APPLICATION_CLIENT_TYPE];
        } else {
            $ret['data']['url'] = '';
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
    public static function AdminNavMenuList()
    {
        return [
            [
                'name'      => MyLang('base_config_text'),
                'control'   => 'admin',
                'action'    => 'index',
            ],
            [
                'name'      => MyLang('couopon_manage_text'),
                'control'   => 'coupon',
                'action'    => 'index',
            ],
            [
                'name'      => MyLang('user_coupon_text'),
                'control'   => 'couponuser',
                'action'    => 'index',
            ],
        ];
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
        $coupon_order_by_type_list = MyLang('coupon_order_by_type_list');
        $data = [
            // 优惠券类型
            'coupon_type_list' =>  [
                0 => ['value' => 0, 'name' => MyLang('full_discount_coupon_text'), 'checked' => true],
                1 => ['value' => 1, 'name' => MyLang('discount_coupon_text')],
            ],

            // 优惠券背景色
            'coupon_bg_color_list' =>  [
                0 => ['value' => 0, 'name' => MyLang('red_color'), 'color' => '#D2364C', 'checked' => true],
                1 => ['value' => 1, 'name' => MyLang('purple_color'), 'color' => '#9C27B0',],
                2 => ['value' => 2, 'name' => MyLang('yellow_color'), 'color' => '#FFC107',],
                3 => ['value' => 3, 'name' => MyLang('blue_color'), 'color' => '#03A9F4',],
                4 => ['value' => 4, 'name' => MyLang('orange_color'), 'color' => '#F44336',],
                5 => ['value' => 5, 'name' => MyLang('green_color'), 'color' => '#4CAF50',],
                6 => ['value' => 6, 'name' => MyLang('coffee_color'), 'color' => '#795548',],
            ],

            // 到期类型
            'coupon_expire_type_list' =>  [
                0 => ['value' => 0, 'name' => MyLang('effective_collection_text'), 'checked' => true],
                1 => ['value' => 1, 'name' => MyLang('fixed_date_text')],
            ],

            // 使用限制类型
            'coupon_use_limit_type_list' =>  [
                0 => ['value' => 0, 'name' => MyLang('all_use_text'), 'checked' => true],
                1 => ['value' => 1, 'name' => MyLang('limit_goods_category_use_text')],
                2 => ['value' => 2, 'name' => MyLang('limit_goods_use_text')],
            ],

            // 优惠券排序类型
            'coupon_order_by_type_list' => [
                0 => ['index' => 0, 'value' => 'id', 'name' => $coupon_order_by_type_list[0], 'checked' => true],
                1 => ['index' => 1, 'value' => 'sort', 'name' => $coupon_order_by_type_list[1]],
                2 => ['index' => 2, 'value' => 'type', 'name' => $coupon_order_by_type_list[2]],
                3 => ['index' => 3, 'value' => 'discount_value', 'name' => $coupon_order_by_type_list[3]],
                4 => ['index' => 4, 'value' => 'expire_type', 'name' => $coupon_order_by_type_list[4]],
                5 => ['index' => 5, 'value' => 'use_limit_type', 'name' => $coupon_order_by_type_list[5]],
                6 => ['index' => 6, 'value' => 'upd_time', 'name' => $coupon_order_by_type_list[6]],
            ],
        ];
        return array_key_exists($key, $data) ? $data[$key] : null;
    }

    /**
     * 优惠列表排除商品未在享受范围的优惠
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-13
     * @desc    description
     * @param   [array]     $params['data']          [优惠列表]
     * @param   [int]       $params['goods_id']      [商品id]
     * @param   [string]    $params['type']          [类型（coupon优惠券, user用户优惠券）]
     */
    public static function CouponListGoodsExclude($params = [])
    {
        if(!empty($params['data']) && !empty($params['goods_id']))
        {
            $type = empty($params['type']) ? 'coupon' : $params['type'];
            $data = $params['data'];
            foreach($data as $k=>$v)
            {
                // 使用限制条件
                $shop_id = 0;
                $use_limit_type = 0;
                $use_value_ids = [];
                switch($type)
                {
                    // 优惠券
                    case 'coupon' :
                        $shop_id = isset($v['shop_id']) ? $v['shop_id'] : 0;
                        $use_limit_type = isset($v['use_limit_type']) ? $v['use_limit_type'] : 0;
                        $use_value_ids = isset($v['use_value_ids_all']) ? $v['use_value_ids_all'] : [];
                        break;

                    // 用户优惠券
                    case 'user' :
                        if(isset($v['coupon']))
                        {
                            $shop_id = isset($v['coupon']['shop_id']) ? $v['coupon']['shop_id'] : 0;
                            $use_limit_type = isset($v['coupon']['use_limit_type']) ? $v['coupon']['use_limit_type'] : 0;
                            $use_value_ids = isset($v['coupon']['use_value_ids_all']) ? $v['coupon']['use_value_ids_all'] : [];
                        }
                        break;
                }
                if($use_limit_type > 0 && !empty($use_value_ids))
                {
                    switch($use_limit_type)
                    {
                        // 限定商品分类
                        case 1 :
                            $category_status = false;
                            // 是否店铺优惠券
                            if(empty($shop_id))
                            {
                                $goods_categosy_ids = Db::name('GoodsCategoryJoin')->where(['goods_id'=>$params['goods_id']])->column('category_id');
                                if(!empty($goods_categosy_ids))
                                {
                                    foreach($use_value_ids as $value)
                                    {
                                        $category_ids = GoodsCategoryService::GoodsCategoryItemsIds([$value], 1);
                                        if(!empty($category_ids))
                                        {
                                            foreach($goods_categosy_ids as $gcid)
                                            {
                                                if(in_array($gcid, $category_ids))
                                                {
                                                    $category_status = true;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $shop_category_id = Db::name('Goods')->where(['id'=>$params['goods_id'], 'shop_id'=>$shop_id])->value('shop_category_id');
                                if(!empty($shop_category_id))
                                {
                                    if(in_array($shop_category_id, $use_value_ids))
                                    {
                                        $category_status = true;
                                    }
                                }
                            }
                            if($category_status == false)
                            {
                                unset($data[$k]);
                                break;
                            }
                            break;

                        // 限定商品
                        case 2 :
                            if(!in_array($params['goods_id'], $use_value_ids))
                            {
                                unset($data[$k]);
                            }
                            break;
                    }
                } else {
                    // 全场通用、是否店铺优惠券
                    if(!empty($shop_id))
                    {
                        // 商品不存店铺id或不等于当前优惠券所属店铺id则移除优惠券
                        $goods_shop_id = Db::name('Goods')->where(['id'=>$params['goods_id']])->value('shop_id');
                        if(empty($goods_shop_id) || $goods_shop_id != $shop_id)
                        {
                            unset($data[$k]);
                        }
                    }
                }
            }
            sort($data);
        }
        return $data;
    }

    /**
     * 提交订单页面优惠券排除
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-14
     * @desc    description
     * @param   [int]               $warehouse_id       [仓库id]
     * @param   [array]             $data               [优惠列表]
     * @param   [array]             $goods_ids          [商品id]
     * @param   [array]             $goods              [购买的商品信息]
     */
    public static function BuyCouponExclude($warehouse_id, $data, $goods_ids, $goods)
    {
        $coupon = [];
        if(!empty($data) && !empty($goods_ids))
        {
            foreach($goods_ids as $goods_id)
            {
                $temp_coupon = self::CouponListGoodsExclude(['data'=>$data, 'goods_id'=>$goods_id, 'type'=>'user']);
                if(!empty($temp_coupon))
                {
                    // 合并优惠券
                    foreach($temp_coupon as $v)
                    {
                        if(!isset($coupon[$v['id']]))
                        {
                            $coupon[$v['id']] = $v;
                        }
                        $coupon[$v['id']]['buy_goods_ids'][] = $goods_id;
                    }
                }
            }

            // 是否有优惠
            // 根据当前订单商品排除不满足的优惠券
            if(!empty($coupon))
            {
                $order_total_price = array_sum(array_column($goods, 'total_price'));
                foreach($coupon as $k=>$v)
                {
                    // 整个订单总额是否满足当前优惠券条件
                    if($order_total_price < $v['coupon']['where_order_price'])
                    {
                        unset($coupon[$k]);
                        continue;
                    }

                    // 排除有效期
                    if(stripos($v['time_start'], '-') !== false)
                    {
                        $v['time_start'] = strtotime($v['time_start']);
                    }
                    if(stripos($v['time_end'], '-') !== false)
                    {
                        $v['time_end'] = strtotime($v['time_end']);
                    }
                    if(time() < $v['time_start'] || time() > $v['time_end'])
                    {
                        unset($coupon[$k]);
                        continue;
                    }

                    // 是否店铺的优惠券
                    if(!empty($v['coupon']['shop_id']))
                    {
                        // 获取店铺仓库
                        $where = [
                            ['shop_id', '=', $v['coupon']['shop_id']],
                            ['is_enable', '=', 1],
                            ['is_delete_time', '=', 0],
                        ];
                        $swid = Db::name('Warehouse')->where($where)->value('id');
                        if($swid != $warehouse_id)
                        {
                            unset($coupon[$k]);
                            continue;
                        }
                    }

                    // 是否有使用限制，根据使用限制关联的商品总额重新计算满足条件
                    if($v['coupon']['use_limit_type'] > 0)
                    {
                        if(!empty($v['buy_goods_ids']))
                        {
                            $inside_goods_price_total = 0.00;
                            foreach($goods as $g)
                            {
                                if(in_array($g['goods_id'], $v['buy_goods_ids']))
                                {
                                    $inside_goods_price_total += $g['total_price'];
                                }
                            }
                            if($inside_goods_price_total < $v['coupon']['where_order_price'])
                            {
                                unset($coupon[$k]);
                            }
                        } else {
                            unset($coupon[$k]);
                        }
                    }
                }
                sort($coupon);
            }
        }
        return $coupon;
    }

    /**
     * 优惠价格计算
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-14
     * @desc    description
     * @param   [float]           $order_price          [订单金额]
     * @param   [int]             $type                 [类型（0满减, 1折扣）]
     * @param   [float]           $where_order_price    [订单满优惠条件]
     * @param   [float]           $discount_value       [满减金额|折扣系数]
     */
    public static function PriceCalculate($order_price, $type = 0, $where_order_price = 0, $discount_value = 0)
    {
        if($order_price <= 0 || $discount_value <= 0 || $order_price < $where_order_price)
        {
            return 0;
        }

        // 默认 减金额
        $discount_price = $discount_value;
        switch($type)
        {
            // 折扣
            case 1 :
                $discount_price = $order_price-($order_price*($discount_value/10));
                break;
        }
        return PriceNumberFormat($discount_price);
    }

    /**
     * 下订单用户优惠券信息获取处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $config           [插件配置]
     * @param   [int]             $warehouse_id     [输入参当前仓库id数]
     * @param   [array]           $order_goods      [订单商品]
     * @param   [array]           $params           [输入参数]
     */
    public static function BuyUserCouponDataHandle($config, $warehouse_id, $order_goods, $params = [])
    {
        $coupon_list = [];
        $coupon_choice = null;
        if(!empty($order_goods))
        {
            // 当前登录用户
            $user = UserService::LoginUserInfo();
            if(!empty($user['id']))
            {
                // 优惠券列表
                $coupon_params = [
                    'user'      => $user,
                    'where'     => [
                        'user_id'   => $user['id'],
                        'is_valid'  => 1,
                        'is_use'    => 0,
                        'is_expire' => 0,
                    ],
                    'is_group'  => 1,
                ];
                $ret = UserCouponService::CouponUserList($coupon_params);
                if(!empty($ret['data']['not_use']))
                {
                    // 排除商品不支持的活动
                    $coupon_list = self::BuyCouponExclude($warehouse_id, $ret['data']['not_use'], array_column($order_goods, 'goods_id'), $order_goods);
                    if(!empty($coupon_list))
                    {
                        // 获取选择的优惠券或者默认使用的优惠券id
                        $total_price = array_sum(array_column($order_goods, 'total_price'));
                        $coupon_id = self::UseChoiceCouponID($config, $total_price, $coupon_list, $warehouse_id, $params);
                        if(!empty($coupon_id))
                        {
                            $temp = array_column($coupon_list, null, 'id');
                            if(array_key_exists($coupon_id, $temp))
                            {
                                $coupon_choice = $temp[$coupon_id];
                            }
                        }
                    }
                }
            }
        }
        return ['coupon_list'=>$coupon_list, 'coupon_choice'=>$coupon_choice];
    }

    /**
     * 用户优惠券选择和默认优惠券计算
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-01-05
     * @desc    description
     * @param   [array]          $config       [插件配置]
     * @param   [float]          $total_price  [订单总额]
     * @param   [array]          $coupon_list  [优惠券列表]
     * @param   [int]            $warehouse_id [仓库id]
     * @param   [array]          $params       [输入参数]
     */
    public static function UseChoiceCouponID($config, $total_price, $coupon_list, $warehouse_id, $params = [])
    {
        // 当前选择的优惠券id
        $key = 'coupon_id_'.$warehouse_id;
        if(!empty($params) && array_key_exists($key, $params))
        {
            return empty($params[$key]) ? 0 : intval($params[$key]);
        }

        // 未传参则匹配是否自动使用优惠券
        if(isset($config['is_default_use_coupon']) && $config['is_default_use_coupon'] == 1)
        {
            $is_default_use_coupon_type = isset($config['is_default_use_coupon_type']) ? intval($config['is_default_use_coupon_type']) : 0;
            $coupon = [];
            $temp_value = 0;
            foreach($coupon_list as $v)
            {
                // 0最优惠、1快过期
                if($is_default_use_coupon_type == 1)
                {
                    $time_end = strtotime($v['time_end']);
                    if(empty($temp_value) || $time_end < $temp_value)
                    {
                        $coupon = $v;
                        $temp_value = $time_end;
                    }
                } else {
                    // 折扣券、或满减券
                    if($v['coupon']['type'] == 1)
                    {
                        $discount_price = $total_price-($total_price*($v['coupon']['discount_value']/10));
                    } else {
                        $discount_price = $v['coupon']['discount_value'];
                    }
                    if(empty($temp_value) || $discount_price > $temp_value)
                    {
                        $coupon = $v;
                        $temp_value = $discount_price;
                    }
                }
            }
            return empty($coupon) ? 0 : $coupon['id'];
        }

        return 0;
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
     * 判断商品是否存在秒杀插件中
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-11
     * @desc    description
     * @param   [array]          $goods_ids [商品id]
     */
    public static function GoodsIsExistValidSeckill($goods_ids)
    {
        $ret = CallPluginsServiceMethod('seckill', 'BaseService', 'GoodsIsExistValidSeckill', $goods_ids);
        // 避免不存在秒杀插件、则直接返回false
        if(is_array($ret) && isset($ret['code']) && $ret['code'] != 0)
        {
            return false;
        }
        return $ret;
    }
}
?>