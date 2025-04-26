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
namespace app\plugins\excellentbuyreturntocash\service;

use think\facade\Db;
use app\service\UserService;
use app\service\GoodsCategoryService;
use app\plugins\excellentbuyreturntocash\service\BaseService;
use app\plugins\coupon\service\BaseService as PluginsBaseService;
use app\plugins\coupon\service\CouponService as PluginsCouponService;

/**
 * 优购返现 - 优惠券服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class CouponService
{
    /**
     * 优惠券释放
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-20
     * @desc    description
     * @param   [int]          $order_id [订单id]
     * @param   [array]        $params   [订单日志数据]
     */
    public static function CouponRelease($order_id, $params)
    {
        $data = Db::name('PluginsExcellentbuyreturntocashCouponLog')->where(['order_id'=>$order_id, 'status'=>0])->select()->toArray();
        if(!empty($data))
        {
            foreach($data as $v)
            {
                Db::name('PluginsCouponUser')->where(['id'=>$v['user_coupon_id'], 'is_valid'=>1, 'is_use'=>0])->update(['is_valid'=>0, 'upd_time'=>time()]);
                Db::name('PluginsExcellentbuyreturntocashCouponLog')->where(['id'=>$v['id']])->update(['status'=>1, 'upd_time'=>time()]);
            }
        }
        return DataReturn('优惠券释放成功', 0);
    }

    /**
     * 优惠券发放
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-20
     * @desc    description
     * @param   [int]          $order_id [订单id]
     * @param   [array]        $params   [订单日志数据]
     */
    public static function CouponSend($order_id, $params)
    {
        // 基础配置信息
        $base = BaseService::BaseConfig();
        if(empty($base['data']) || empty($base['data']['return_category_coupon_ids_all']))
        {
            return DataReturn('未配置、无需处理', 0);
        }

        // 返券类型 0倍数、1订单金额
        $return_coupon_type = empty($base['data']['return_coupon_type']) ? 0 : intval($base['data']['return_coupon_type']);
        if(!array_key_exists($return_coupon_type, BaseService::$return_coupon_type_list))
        {
            return DataReturn('返券类型有误、无需处理', 0);
        }

        // 获取订单信息
        $order = Db::name('Order')->where(['id'=>$order_id])->field('id,user_id,total_price')->find();
        if(empty($order))
        {
            return DataReturn(MyLang('order_info_incorrect_tips'), 0);
        }

        // 获取订单商品
        $detail = Db::name('OrderDetail')->where(['order_id'=>$order_id])->field('id,goods_id,buy_number')->select()->toArray();
        if(empty($detail))
        {
            return DataReturn('无订单相关商品信息，无需处理', 0);
        }

        // 根据返券类型处理不同的逻辑
        $data = [];
        $log_data = [];
        switch($return_coupon_type)
        {
            // 倍数
            case 0 :
                // 订单商品中关联的分类
                $temp_category_ids = [];
                foreach($detail as $k=>$v)
                {
                    // 商品分类, 无记录则查询
                    if(!array_key_exists($v['goods_id'], $temp_category_ids))
                    {
                        $temp_category_ids[$v['goods_id']] = Db::name('GoodsCategoryJoin')->where(['goods_id'=>$v['goods_id']])->column('category_id');
                    }
                    $category_ids = $temp_category_ids[$v['goods_id']];

                    // 循环配置关联
                    foreach($base['data']['return_category_coupon_ids_all'] as $b)
                    {
                        if(!isset($b['order_total_price']) || $b['order_total_price'] <= 0 || $order['total_price'] >= $b['order_total_price'])
                        {
                            $ids = GoodsCategoryService::GoodsCategoryItemsIds([$b['category_id']]);
                            // 循环商品分类
                            foreach($category_ids as $cid)
                            {
                                if(in_array($cid, $ids))
                                {
                                    $v['coupon_id'] = $b['coupon_id'];
                                    $data[] = $v;
                                    break 2;
                                }
                            }
                        }
                    }

                    // 优惠券发放
                    if(!empty($data))
                    {
                        // 循环发放优惠券
                        foreach($data as $v)
                        {
                            // 发放数量倍数
                            $multiple = max(1, empty($base['data']['return_coupon_multiple']) ? 1 : intval($base['data']['return_coupon_multiple']));

                            // 根据购买数量发放数量
                            for($i=0; $i<$multiple*$v['buy_number']; $i++)
                            {
                                $coupon_params = [
                                    'coupon_id' => $v['coupon_id'],
                                    'user_ids'  => [$order['user_id']],
                                ];
                                $ret = PluginsCouponService::CouponSend($coupon_params);
                                if($ret['code'] != 0)
                                {
                                    return $ret;
                                }

                                // 获取用户最新的优惠券 id
                                $user_coupon_id = Db::name('PluginsCouponUser')->where(['user_id'=>$order['user_id']])->order('id desc')->value('id');

                                // 添加日志数据
                                $log_data[] = [
                                    'user_coupon_id'    => $user_coupon_id,
                                    'coupon_id'         => $v['coupon_id'],
                                    'user_id'           => $order['user_id'],
                                    'order_id'          => $order_id,
                                    'order_detail_id'   => $v['id'],
                                    'goods_id'          => $v['goods_id'],
                                    'status'            => 0,
                                    'add_time'          => time(),
                                ];
                            }
                        }
                    }
                }
                break;

            // 订单金额
            case 1 :
                if(!empty($order['total_price']) && $order['total_price'] > 0)
                {
                    // 订单详情对应商品
                    $goods_detail_ids = array_column($detail, 'id', 'goods_id');

                    // 订单中包含商品的所有分类
                    $category_ids = Db::name('GoodsCategoryJoin')->where(['goods_id'=>array_column($detail, 'goods_id')])->column('goods_id', 'category_id');

                    // 排序
                    $category_coupon = array_reverse(ArrayQuickSort($base['data']['return_category_coupon_ids_all'], 'order_total_price'));
                    foreach($category_coupon as $b)
                    {
                        if(isset($b['order_total_price']) && $order['total_price'] > 0 && $order['total_price'] >= $b['order_total_price'])
                        {
                            $ids = GoodsCategoryService::GoodsCategoryItemsIds([$b['category_id']]);
                            foreach($category_ids as $ck=>$cv)
                            {
                                if(in_array($ck, $ids))
                                {
                                    $b['goods_id'] = $cv;
                                    $b['order_detail_id'] = $goods_detail_ids[$cv];
                                    $data[] = $b;
                                    $order['total_price'] -= $b['order_total_price'];
                                    break;
                                }
                            }
                        }
                    }

                    // 优惠券发放
                    if(!empty($data))
                    {
                        // 循环发放优惠券
                        foreach($data as $v)
                        {
                            $coupon_params = [
                                'coupon_id' => $v['coupon_id'],
                                'user_ids'  => [$order['user_id']],
                            ];
                            $ret = PluginsCouponService::CouponSend($coupon_params);
                            if($ret['code'] != 0)
                            {
                                return $ret;
                            }

                            // 获取用户最新的优惠券 id
                            $user_coupon_id = Db::name('PluginsCouponUser')->where(['user_id'=>$order['user_id']])->order('id desc')->value('id');

                            // 添加日志数据
                            $log_data[] = [
                                'user_coupon_id'    => $user_coupon_id,
                                'coupon_id'         => $v['coupon_id'],
                                'user_id'           => $order['user_id'],
                                'order_id'          => $order_id,
                                'order_detail_id'   => $v['order_detail_id'],
                                'goods_id'          => $v['goods_id'],
                                'status'            => 0,
                                'add_time'          => time(),
                            ];
                        }
                    }
                }
                break;
        }


        // 循环发放优惠券
        if(!empty($log_data))
        {
            if(Db::name('PluginsExcellentbuyreturntocashCouponLog')->insertAll($log_data) < count($log_data))
            {
                return DataReturn('优购返现-优惠券日志添加失败', -100);
            }
        }

        return DataReturn('优购返现-优惠券添加成功', 0);
    }

    /**
     * 商品页面优惠券列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     * @param   [array]           $base   [配置信息]
     */
    public static function GoodsCouponList($params = [], $base = [])
    {
        // 基础配置信息
        if(empty($base['data']) || empty($base['data']['return_category_coupon_ids_all']))
        {
            return DataReturn('未配置、无需处理', 0);
        }

        // 获取商品所属分类
        $ids = Db::name('GoodsCategoryJoin')->where(['goods_id'=>$params['goods_id']])->column('category_id');
        if(empty($ids))
        {
            return DataReturn('商品分类为空、无需处理', 0);
        }

        // 优惠券匹配
        $coupon_ids = [];
        foreach($base['data']['return_category_coupon_ids_all'] as $v)
        {
            $base_ids = GoodsCategoryService::GoodsCategoryItemsIds([$v['category_id']]);
            foreach($ids as $cid)
            {
                if(in_array($cid, $base_ids))
                {
                    $coupon_ids[] = $v['coupon_id'];
                    break;
                }
            }
        }

        // 优惠劵列表
        $coupon_params = [
            'where' => [
                'is_enable' => 1,
                'id'        => array_unique($coupon_ids),
            ],
            'm'                 => 0,
            'n'                 => 0,
            'user'              => UserService::LoginUserInfo(),
        ];
        return PluginsCouponService::CouponList($coupon_params);
    }
}
?>