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
use app\service\MessageService;
use app\service\ResourcesService;
use app\service\GoodsCategoryService;
use app\plugins\excellentbuyreturntocash\service\BaseService;

/**
 * 优购返现 - 收益服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class ProfitService
{
    /**
     * 订单关闭
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [int]          $order_id [订单id]
     * @param   [array]        $params   [订单日志数据]
     */
    public static function OrderProfitClose($order_id, $params = [])
    {
        // 获取返现订单
        $where = ['order_id'=>$order_id];
        $order = Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where($where)->find();
        if(!empty($order))
        {
            // 更新数据
            $data = [
                'status'    => 4,
                'upd_time'  => time(),
            ];

            // 订单关闭则情况金额
            if($params['new_status'] == 6)
            {
                $data['valid_price'] = 0;
                $data['profit_price'] = 0;
            }

            // 描述信息
            $msg = ($params['new_status'] == 5) ? '订单取消' : '订单关闭';

            // 描述数据处理
            $data['log'] = empty($order['log']) ? [] : json_decode($order['log'], true);
            $data['log'][] = ['msg'=>$msg, 'time'=>time()];

            // 更新
            $data['log'] = json_encode($data['log']);
            if(Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where($where)->update($data))
            {
                // 消息通知
                MessageService::MessageAdd($order['user_id'], '优购返现变更', $msg, BaseService::$message_business_type, $order['id']);

                return DataReturn('优购返现订单关闭成功', 0);
            }
            return DataReturn('优购返现订单关闭失败', -1);
        }
        return DataReturn('无优购返现订单-无需处理', 0);
    }

    /**
     * 订单生效
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [int]          $order_id [订单id]
     * @param   [array]        $params   [订单日志数据]
     */
    public static function OrderProfitValid($order_id, $params = [])
    {
        // 重新计算订单佣金
        $ret = self::OrderChange(['order_id'=>$order_id]);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 更新返现订单状态
        $data = [
            'status'    => 1,
            'upd_time'  => time(),
        ];
        if(Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where(['order_id'=>$order_id])->update($data) !== false)
        {
            return DataReturn('优购返现订单生效成功', 0);
        }
        return DataReturn('优购返现订单生效失败', -1);
    }

    /**
     * 订单确认
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [int]          $order_id [订单id]
     * @param   [array]        $params   [订单日志数据]
     */
    public static function OrderProfitConfirm($order_id, $params = [])
    {
        // 重新计算订单佣金
        $ret = self::OrderChange(['order_id'=>$order_id]);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 更新返现订单状态
        $data = [
            'status'    => 2,
            'upd_time'  => time(),
        ];
        if(Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where(['order_id'=>$order_id])->update($data) !== false)
        {
            return DataReturn('优购返现订单确认成功', 0);
        }
        return DataReturn('优购返现订单确认失败', -1);
    }

    /**
     * 返现订单添加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [array]           $params [输入参数]
     * @param   [array]           $config [插件配置]
     */
    public static function ProfitOrderInsert($params = [], $config = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order_id',
                'error_msg'         => '订单id为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order',
                'error_msg'         => '订单信息为空',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 状态校验
        if(!in_array($params['order']['status'], [0,1,2]))
        {
            return DataReturn('返现订单创建仅支持状态[待确认/待支付]', -1);
        }

        // 插件配置信息
        if(empty($config))
        {
            return DataReturn('插件未配置', 0);
        }

        // 是否配置了返现比例
        if(empty($config['return_to_cash_rate']))
        {
            return DataReturn('返现比例未配置', 0);
        }
        if(empty($config['return_to_cash_category_ids_all']))
        {
            return DataReturn('返现指定分类未配置', 0);
        }
        $category_ids = $config['return_to_cash_category_ids_all'];
        $rate = intval($config['return_to_cash_rate']);

        // 订单货币
        $currency = Db::name('OrderCurrency')->where(['order_id'=>$params['order_id']])->find();
        if(empty($currency))
        {
            return DataReturn('订单汇率信息不存在', 0);
        }

        // 是否必须使用指定优惠券才进行返现
        // 必须已配置关联优惠券
        $is_appoint_coupon_cach = isset($config['is_appoint_coupon_cach']) ? intval($config['is_appoint_coupon_cach']) : 0;
        if($is_appoint_coupon_cach == 1)
        {
            // 订单扩展信息
            $status = false;
            if(!empty($params['order']['extension_data']))
            {
                // 查询优惠券发放记录
                $coupon_ids = Db::name('PluginsExcellentbuyreturntocashCouponLog')->where(['user_id'=>$params['order']['user_id']])->column('coupon_id');
                if(!empty($coupon_ids))
                {
                    // 开始匹配
                    $extension_data = is_array($params['order']['extension_data']) ? $params['order']['extension_data'] : json_decode($params['order']['extension_data'], true);
                    foreach($extension_data as $v)
                    {
                        if(isset($v['business']) && $v['business'] == 'plugins-coupon' && !empty($v['ext']) && !empty($v['ext']['coupon_id']))
                        {
                            if(in_array($v['ext']['coupon_id'], $coupon_ids))
                            {
                                $status = true;
                                break;
                            }
                        }
                    }
                }
            }

            if($status === false)
            {
                return DataReturn('未使用指定优惠券不返现', 0);
            }
        }

        // 返现金额计算
        // 返现金额大于0则有效
        $profit = self::ProfitPriceCalculation($params['order_id'], $rate, $category_ids, $currency, $config);
        if($profit['code'] != 0 && isset($profit['data']['profit_price']) && $profit['data']['profit_price'] > 0)
        {
            return $profit;
        }

        // 返现订单数据
        $data = [
            'user_id'           => $params['order']['user_id'],
            'order_id'          => $params['order_id'],
            'total_price'       => $params['order']['total_price'],
            'valid_price'       => $profit['data']['valid_price'],
            'profit_price'      => $profit['data']['profit_price'],
            'rate'              => $rate,
            'category_ids'      => json_encode($category_ids),
            'status'            => 0,
            'add_time'          => time(),
        ];

        // 货币符号
        $currency_symbol = ResourcesService::CurrencyDataSymbol(['is_only_currency_default'=>1]);

        // 获取订单用户昵称
        $user = UserService::GetUserViewInfo($params['order']['user_id'], '', true);

        // 消息通知
        $msg = $user['user_name_view'].'用户下单, 订单金额'.$currency['currency_symbol'].$params['order']['total_price'].', 增加金额'.$currency['currency_symbol'].$params['order']['increase_price'].', 优惠金额'.$currency['currency_symbol'].$params['order']['preferential_price'].', 有效金额'.$currency['currency_symbol'].$data['valid_price'].', 预计返现'.$currency_symbol.$data['profit_price'];

        // 描述内容
        $data['log'] = json_encode([['msg'=>$msg, 'time'=>time()]]);

        // 写入数据
        $cash_id = Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->insertGetId($data);
        if($cash_id > 0)
        {
            MessageService::MessageAdd($params['order']['user_id'], '优购返现新增', $msg, BaseService::$message_business_type, $cash_id);
            return DataReturn('优购返现订单添加成功', 0);
        }
        return DataReturn('优购返现订单添加失败', -1);
    }

    /**
     * 返现金额计算
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [int]           $order_id       [订单id]
     * @param   [int]           $rate           [比例]
     * @param   [array]         $category_ids   [指定分类id]
     * @param   [array]         $currency       [订单汇率]
     * @param   [array]         $config         [插件配置]
     * @return  [array]                         [有效金额|收益金额]
     */
    private static function ProfitPriceCalculation($order_id, $rate, $category_ids, $currency, $config)
    {
        // 获取订单信息
        $order = Db::name('Order')->find($order_id);
        if(empty($order))
        {
            return DataReturn('优购返现-无订单信息', -1);
        }

        // 获取订单详情
        $detail = Db::name('OrderDetail')->where(['order_id'=>$order_id])->select()->toArray();
        if(empty($detail))
        {
            return DataReturn('优购返现-无订单详情信息', -1);
        }

        // 获取配置指定分类所有子分类
        $base_ids = GoodsCategoryService::GoodsCategoryItemsIds($category_ids);

        // 循环商品匹配
        $data = [];
        foreach($detail as $v)
        {
            // 排除已完全退货完成数据
            if($v['returned_quantity'] < $v['buy_number'])
            {
                // 获取商品所属分类
                $ids = Db::name('GoodsCategoryJoin')->where(['goods_id'=>$v['goods_id']])->column('category_id');
                if(!empty($ids))
                {
                    // 循环匹配是否存在分类中
                    foreach($ids as $cid)
                    {
                        if(in_array($cid, $base_ids))
                        {
                            $data[] = $v;
                            break;
                        }
                    }
                }
            }
        }

        // 是否匹配到了商品
        $valid_price = 0;
        $profit_price = 0;
        if(!empty($data))
        {
            // 有效金额
            $valid_price = array_sum(array_column($data, 'total_price'))-array_sum(array_column($data, 'refund_price'));

            // 金额不一样则重新计算
            // 计算的单价是否等于订单单价
            if($valid_price >= $order['price'])
            {
                $valid_price = $order['total_price'];
            } else {
                // 比例计算
                $p = $valid_price/$order['price'];

                // 增加金额/优惠金额处理
                if(isset($order['increase_price']) && $order['increase_price'] > 0)
                {
                    $valid_price += $p*$order['increase_price'];
                }
                if(isset($order['preferential_price']) && $order['preferential_price'] > 0)
                {
                    $valid_price -= $p*$order['preferential_price'];
                }
            }
            $valid_price = PriceNumberFormat($valid_price);

            // 计算收益
            $profit_price = $valid_price*($rate/100);
        }

        // 汇率转换
        if(isset($config['is_profit_transform_currency_rate']) && $config['is_profit_transform_currency_rate'] == 1 && $profit_price > 0 && !empty($currency) && isset($currency['currency_rate']) && $currency['currency_rate'] > 0)
        {
            $profit_price /= $currency['currency_rate'];
        }

        $data = [
            'valid_price'   => $valid_price,
            'profit_price'  => PriceNumberFormat($profit_price),
        ];
        return DataReturn('优购返现-计算成功', 0, $data);
    }

    /**
     * 订单变更重新计算收益
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     * @param   [array]           $config [插件配置]
     */
    public static function OrderChange($params = [], $config = [])
    {
        // 参数
        if(empty($params['order_id']))
        {
            return DataReturn(MyLang('order_id_error_tips'), -1);
        }

        // 获取返现订单数据
        $where = [
            ['order_id', '=', $params['order_id']],
            ['status', '<=', 1],
        ];
        $cash_order = Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where($where)->select()->toArray();
        if(!empty($cash_order))
        {
            // 获取订单数据
            $order = Db::name('Order')->field('id,total_price,price,increase_price,preferential_price,refund_price')->find(intval($params['order_id']));
            if(empty($order))
            {
                return DataReturn('订单不存在', -1);
            }

            // 订单货币
            $currency = Db::name('OrderCurrency')->where(['order_id'=>$order['id']])->find();
            if(empty($currency))
            {
                return DataReturn('订单汇率信息不存在', 0);
            }

            // 货币符号
            $currency_symbol = ResourcesService::CurrencyDataSymbol(['is_only_currency_default'=>1]);
            
            // 循环处理
            foreach($cash_order as $v)
            {
                // 退款金额大于0或者订单金额不一致
                if($order['refund_price'] > 0 || $order['total_price'] != $v['total_price'])
                {
                    // 计算返现金额
                    $profit = self::ProfitPriceCalculation($v['order_id'], $v['rate'], json_decode($v['category_ids'], true), $currency, $config);
                    if($profit['code'] != 0)
                    {
                        return $profit;
                    }

                    // 更新数据
                    $data = [
                        'total_price'   => $order['total_price'],
                        'valid_price'   => $profit['data']['valid_price'],
                        'profit_price'  => $profit['data']['profit_price'],
                        'upd_time'      => time(),
                    ];

                    // 返现金额为0则关闭返现订单
                    if($data['profit_price'] <= 0)
                    {
                        $data['status'] = 4;
                    }

                    // 描述信息
                    $msg = '订单发生变更, 订单金额'.$currency['currency_symbol'].$order['total_price'].', 增加金额'.$currency['currency_symbol'].$order['increase_price'].', 优惠金额'.$currency['currency_symbol'].$order['preferential_price'].', 退款金额'.$currency['currency_symbol'].$order['refund_price'].', 有效金额'.$currency['currency_symbol'].$data['valid_price'].', 原收益'.$currency_symbol.$v['profit_price'].' / 变更后收益'.$currency_symbol.$data['profit_price'];

                    // 描述数据处理
                    $data['log'] = empty($v['log']) ? [] : json_decode($v['log'], true);
                    $data['log'][] = ['msg'=>$msg, 'time'=>time()];

                    // 更新
                    $data['log'] = json_encode($data['log']);
                    if(Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where(['id'=>$v['id']])->update($data))
                    {
                        // 收益金额不一致的时候变更
                        if($v['profit_price'] != $data['profit_price'])
                        {
                            // 消息通知
                            MessageService::MessageAdd($v['user_id'], '优购返现变更', $msg, BaseService::$message_business_type, $v['id']);
                        }
                    }
                }
            }
            return DataReturn(MyLang('operate_success'), 0);
        }
        return DataReturn(MyLang('handle_noneed'), 0);
    }
}
?>