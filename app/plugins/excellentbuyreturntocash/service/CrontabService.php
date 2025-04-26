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
use app\service\MessageService;
use app\service\GoodsCategoryService;
use app\plugins\wallet\service\WalletService;
use app\plugins\excellentbuyreturntocash\service\BaseService;

/**
 * 优购返现 - 脚本服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class CrontabService
{
    /**
     * 订单返现脚本，将返现金额增加到用户钱包
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ProfitSettlement($params = [])
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();

        // 结算周期时间
        $return_to_cash_time = (empty($base['data']) || empty($base['data']['return_to_cash_time'])) ? 43200 : intval($base['data']['return_to_cash_time']);
        $time = time()-($return_to_cash_time*60);

        // 获取需要结算的订单
        $where = [
            ['o.collect_time', '<', $time],
            ['o.status', '=', 4],
            ['p.status', '=', 2],
        ];
        $data = Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->alias('p')->join('order o', 'o.id=p.order_id')->where($where)->field('p.*')->limit(50)->select()->toArray();

        // 状态
        $sucs = 0;
        $fail = 0;
        if(!empty($data))
        {
            foreach($data as $v)
            {
                $ret = self::SettlementHandle($v);
                if($ret['code'] == 0)
                {
                    $sucs++;
                } else {
                    $fail++;
                }
            }
        }
        return DataReturn(MyLang('operate_success'), 0, ['sucs'=>$sucs, 'fail'=>$fail]);
    }

    /**
     * 自动返现脚本，将返现金额增加到用户钱包
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-05
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function AutoSettlement($params = [])
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();

        // 是否配置了自动返现比例
        if(empty($base['data']['return_auto_cash_number']))
        {
            return DataReturn('自动返现基数未配置', 0);
        }
        if(empty($base['data']['return_auto_cash_rate']))
        {
            return DataReturn('自动返现比例未配置', 0);
        }
        if(empty($base['data']['return_auto_cash_category_ids_all']))
        {
            return DataReturn('自动返现指定分类未配置', 0);
        }
        $category_ids = $base['data']['return_auto_cash_category_ids_all'];
        $number = intval($base['data']['return_auto_cash_number']);
        $rate = intval($base['data']['return_auto_cash_rate']);

        // 获取需要结算的订单
        $where = [
            ['o.status', '=', 4],
            ['p.status', '=', 2],
        ];
        // 是否指定订单
        if(!empty($params['id']))
        {
            $where[] = ['p.id', '=', intval($params['id'])];
        }
        $data = Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->alias('p')->join('order o', 'o.id=p.order_id')->where($where)->field('p.*, o.collect_time as order_collect_time')->limit(50)->select()->toArray();

        // 状态
        $sucs = 0;
        $fail = 0;
        if(!empty($data))
        {
            foreach($data as $v)
            {
                // 获取订单详情商品 id
                $goods_ids = Db::name('OrderDetail')->where(['order_id'=>$v['order_id']])->column('goods_id');
                if(!empty($goods_ids))
                {
                    // 所有分类,包含子分类
                    $base_ids = GoodsCategoryService::GoodsCategoryItemsIds(json_decode($v['category_ids']));

                    // 判断商品销量
                    // 销量必须大于等于设置的基准数值
                    // 并且必须每个商品都存在自动返现分类中
                    // 按照当前用户的订单售后时间 大于等于条件，订单必须是已完成
                    $sales_where = [
                        ['od.goods_id', 'in', $goods_ids],
                        ['o.collect_time', '>=', $v['order_collect_time']],
                        ['o.status', '=', 4],
                    ];
                    $sales = Db::name('Order')->alias('o')->join('order_detail od', 'o.id=od.order_id')->where($sales_where)->field('sum(od.buy_number) as buy_number, od.goods_id')->group('od.goods_id')->select()->toArray();
                    if(!empty($sales) && min(array_column($sales, 'buy_number')) > $number)
                    {
                        // 状态值
                        $count = 0;

                        // 循环商品 id 匹配分类是否存在自动返现分类中
                        foreach($goods_ids as $goods_id)
                        {
                            // 获取商品所属分类
                            $ids = Db::name('GoodsCategoryJoin')->where(['goods_id'=>$goods_id])->column('category_id');
                            if(!empty($ids))
                            {
                                // 循环匹配是否存在分类中
                                foreach($ids as $cid)
                                {
                                    if(in_array($cid, $base_ids))
                                    {
                                        $count++;
                                        break;
                                    }
                                }
                            }
                        }

                        // 所有商品满足则处理返现
                        if($count >= count($goods_ids))
                        {
                            // 重新计算结算金额
                            $profit_price = PriceNumberFormat($v['valid_price']*($rate/100));

                            // 描述数据处理
                            $v['log'] = empty($v['log']) ? [] : json_decode($v['log'], true);
                            $v['log'][] = [
                                'msg'   => '自动结算、重新计算返现金额, 原返现金额'.$v['profit_price'].', 最新返现金额'.$profit_price,
                                'time'  => time(),
                            ];

                            // 更新
                            $v['log'] = json_encode($v['log']);
                            if(Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where(['id'=>$v['id'], 'status'=>2])->update(['profit_price'=>$profit_price, 'rate'=>$rate, 'log'=>$v['log'], 'upd_time'=>time()]))
                            {
                                // 发放佣金
                                $v['profit_price'] = $profit_price;
                                $ret = self::SettlementHandle($v);
                                if($ret['code'] == 0)
                                {
                                    $sucs++;
                                } else {
                                    $fail++;
                                }
                            }
                        }
                    }
                }
            }
        }

        if($sucs == 0 && $fail == 0)
        {
            return DataReturn(BaseService::$user_share_msg, -1);
        }
        return DataReturn(MyLang('operate_success'), 0, ['sucs'=>$sucs, 'fail'=>$fail]);
    }

    /**
     * 结算处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-06
     * @desc    description
     * @param   [array]          $data [返现订单数据]
     */
    private static function SettlementHandle($data)
    {
        // 更新状态
        $upd_data = [
            'status'        => 3,
            'success_time'  => time(),
            'upd_time'      => time(),
        ];

        // 描述
        $title = '优购返现结算';
        $msg = '返现结算, 订单金额'.$data['total_price'].', 有效金额'.$data['valid_price'].', 返现金额'.$data['profit_price'].', 已发放至钱包';

        // 日志
        $upd_data['log'] = empty($data['log']) ? [] : json_decode($data['log'], true);
        $upd_data['log'][] = ['msg'=>$msg, 'time'=>time()];
        $upd_data['log'] = json_encode($upd_data['log']);

        // 开启事务
        Db::startTrans();

        // 更新数据库
        if(Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where(['id'=>$data['id'], 'status'=>2])->update($upd_data))
        {
            // 钱包变更
            $ret = WalletService::UserWalletMoneyUpdate($data['user_id'], $data['profit_price'], 1, 'normal_money', 0, $title);
            if($ret['code'] == 0)
            {
                // 消息通知
                MessageService::MessageAdd($data['user_id'], $title, $msg, BaseService::$message_business_type, $data['id']);

                // 提交事务
                Db::commit();
                return DataReturn('返现成功', 0);
            }
        }

        // 事务回滚
        Db::rollback();
        return DataReturn('返现失败', -100);
    }
}
?>