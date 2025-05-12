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
namespace app\plugins\distribution\service;

use think\facade\Db;
use app\service\UserService;
use app\service\MessageService;
use app\service\IntegralService;
use app\plugins\wallet\service\WalletService;
use app\plugins\distribution\service\BaseService;

/**
 * 分销 - 脚本服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class CrontabService
{
    /**
     * 订单收益脚本，将收益增加到用户钱包
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
        $profit_settlement_limit_time = (empty($base['data']) || empty($base['data']['profit_settlement_limit_time'])) ? 43200 : intval($base['data']['profit_settlement_limit_time']);
        $time = time()-($profit_settlement_limit_time*60);

        // 获取需要结算的订单
        $where = [
            ['o.collect_time', '<', $time],
            ['o.status', '=', 4],
            ['pp.status', '=', 1],
        ];
        $data = Db::name('PluginsDistributionProfitLog')->alias('pp')->join('order o', 'o.id=pp.order_id')->where($where)->field('o.*,pp.id,pp.user_id,pp.order_user_id,pp.profit_type,profit_price,pp.total_price')->limit(50)->select()->toArray();

        // 状态
        $sucs = 0;
        $fail = 0;
        if(!empty($data))
        {
            // 是否开启多商户
            $is_profit_shop = (!empty($base['data']) && isset($base['data']['is_profit_shop'])) && $base['data']['is_profit_shop'] == 1;

            // 更新状态
            $upd_data = [
                'status'    => 2,
                'upd_time'  => time(),
            ];
            foreach($data as $v)
            {
                // 开启事务
                Db::startTrans();
                if(Db::name('PluginsDistributionProfitLog')->where(['id'=>$v['id'], 'status'=>1])->update($upd_data))
                {
                     // 获取订单用户昵称
                    $user = UserService::GetUserViewInfo($v['order_user_id']);
                    $user_name_view = (empty($user) || empty($user['user_name_view'])) ? '' : $user['user_name_view'];

                    // 发放值
                    $profit_price = ($v['profit_type'] == 1) ? intval($v['profit_price']) : $v['profit_price'];

                    // 消息通知
                    $profit_type_name = ($v['profit_type'] == 1) ? '积分' : '钱包';
                    $msg = $user_name_view.'用户订单佣金结算'.$v['total_price'].', 收益'.$profit_price.', 已发放至'.$profit_type_name;
                    MessageService::MessageAdd($v['user_id'], '分销收益新增', $msg, BaseService::$message_business_type, $v['id']);

                    // 用户佣金发放
                    if($v['profit_type'] == 1)
                    {
                        // 积分变更
                        IntegralService::UserIntegralUpdate($v['user_id'], null, $profit_price, '分销收益新增', 1);
                    } else {
                        // 钱包变更
                        WalletService::UserWalletMoneyUpdate($v['user_id'], $profit_price, 1, 'normal_money', 0, '分销收益新增');
                    }

                    // 多商户订单则扣除店主钱包金额
                    if(!empty($v['shop_id']) && $is_profit_shop)
                    {
                        $shop = BaseService::ShopInfo($v['shop_id']);
                        if(!empty($shop))
                        {
                            // 消息通知
                            $msg = $user_name_view.'用户订单佣金结算'.$v['total_price'].', 发放'.$profit_price.', 已从'.$profit_type_name.'扣除';
                            MessageService::MessageAdd($shop['user_id'], '分销佣金扣除', $msg, BaseService::$message_business_type, $v['id']);

                            if($v['profit_type'] == 1)
                            {
                                // 积分变更
                                IntegralService::UserIntegralUpdate($shop['user_id'], null, intval($profit_price), '分销佣金扣除', 0);
                            } else {
                                // 钱包变更
                                WalletService::UserWalletMoneyUpdate($shop['user_id'], $profit_price, 0, 'normal_money', 0, '分销佣金扣除');
                            }
                        }
                    }

                    // 提交事务
                    Db::commit();
                    $sucs++;
                    continue;
                }
                // 事务回滚
                Db::rollback();
                $fail++;
            }
        }
        return DataReturn(MyLang('operate_success'), 0, ['sucs'=>$sucs, 'fail'=>$fail]);
    }

    /**
     * 订单地址坐标解析
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function OrderAddressLocationGeocoder($params = [])
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();
        if(empty($base['data']))
        {
            return DataReturn('插件未配置', -1);
        }
        if(!isset($base['data']['is_order_address_location_geocoder']) || $base['data']['is_order_address_location_geocoder'] != 1)
        {
            return DataReturn('未开启解析订单地址坐标', -1);
        }

        // 获取订单地址进行解析
        $where = [
            ['o.status', '<=', 4],
            ['oa.lng', '<=', 0],
        ];
        $data = Db::name('Order')->alias('o')->join('order_address oa', 'o.id=oa.order_id')->where($where)->field('oa.*')->order('o.id desc')->limit(30)->select()->toArray();

        // 处理数据
        $sucs = 0;
        $fail = 0;
        $msg = [];
        if(!empty($data))
        {
            $obj = new \base\Map();
            foreach($data as $v)
            {
                // 解析地址
                $ret = $obj->Geocoder($v['province_name'].$v['city_name'].$v['county_name'].$v['address']);
                if($ret['code'] == 0)
                {
                    if(Db::name('OrderAddress')->where(['id'=>$v['id']])->update(['lng'=>$ret['data']['lng'], 'lat'=>$ret['data']['lat']]))
                    {
                        $sucs++;
                    }
                } else {
                    $msg[] = '('.$v['order_id'].')'.$ret['msg'];
                }
                $fail++;
            }
        }
        return DataReturn(MyLang('operate_success'), 0, ['sucs'=>$sucs, 'fail'=>$fail, 'msg'=>$msg]);
    }
}
?>