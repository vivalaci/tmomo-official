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
namespace app\plugins\membershiplevelvip\service;

use think\facade\Db;
use app\service\SystemBaseService;
use app\service\ResourcesService;

/**
 * 会员等级服务层 - 数据统计
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-06-12T01:13:44+0800
 */
class StatisticalService
{
    // 近15天日期
    private static $nearly_fifteen_days;

    /**
     * 初始化
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-12T01:13:44+0800
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function Init($params = [])
    {
        static $object = null;
        if(!is_object($object))
        {
            // 初始化标记对象，避免重复初始化
            $object = (object) [];

            // 近15天
            $nearly_all = [
                15  => 'nearly_fifteen_days',
            ];
            foreach($nearly_all as $day=>$name)
            {
                $date = [];
                $time = time();
                for($i=0; $i<$day; $i++)
                {
                    $date[] = [
                        'start_time'    => strtotime(date('Y-m-d 00:00:00', time()-$i*3600*24)),
                        'end_time'      => strtotime(date('Y-m-d 23:59:59', time()-$i*3600*24)),
                        'name'          => date('Y-m-d', time()-$i*3600*24),
                    ];
                }
                
                self::${$name} = array_reverse($date);
            }
        }
    }

    /**
     * 收益趋势, 15天数据
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2019-06-12T01:13:44+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserProfitFifteenTodayTotal($params = [])
    {
        // 初始化
        self::Init($params);

        // 循环获取统计数据
        $data = [];
        $name_arr = [];
        foreach(self::$nearly_fifteen_days as $day)
        {
            // 当前日期名称
            $name_arr[] = $day['name'];

            // 获取收益金额
            $where = [
                ['add_time', '>=', $day['start_time']],
                ['add_time', '<=', $day['end_time']],
            ];
            if(!empty($params['user']))
            {
                $where[] = ['user_id', '=', $params['user']['id']];
            }
            $data[] = PriceNumberFormat(Db::name('PluginsMembershiplevelvipUserProfit')->where($where)->sum('profit_price'));
        }

        // 数据组装
        $result = [
            'name_arr'  => $name_arr,
            'data'      => $data,
        ];
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 推广用户趋势, 15天数据
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2019-06-12T01:13:44+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserExtensionFifteenTodayTotal($params = [])
    {
        // 初始化
        self::Init($params);

        // 循环获取统计数据
        $data = [];
        $name_arr = [];
        foreach(self::$nearly_fifteen_days as $day)
        {
            // 当前日期名称
            $name_arr[] = $day['name'];

            // 获取用户总数
            $where = [
                ['add_time', '>=', $day['start_time']],
                ['add_time', '<=', $day['end_time']],
            ];
            if(!empty($params['user']))
            {
                $where[] = ['referrer', '=', $params['user']['id']];
            } else {
                $where[] = ['referrer', '>', 0];
            }
            $data[] = Db::name('User')->where($where)->count('id');
        }

        // 数据组装
        $result = [
            'name_arr'  => $name_arr,
            'data'      => $data,
        ];
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 用户收益总额
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-05T22:08:34+0800
     * @param    [int]                   $status [状态（0待结算, 1已结算, 2已失效）]
     * @param    [int]                   $user_id [用户id]
     */
    public static function UserProfitPriceTotal($status = 0, $user_id = null, $field = 'profit_price')
    {
        $where = [
            'status'    => $status,
        ];
        if(!empty($user_id))
        {
            $where['user_id'] = intval($user_id);
        }
        return PriceNumberFormat(Db::name('PluginsMembershiplevelvipUserProfit')->where($where)->sum($field));
    }

    /**
     * 获取推广用户数量
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-12-08T00:52:25+0800
     * @desc     description
     * @param    [array]                  $params [输入参数]
     */
    public static function UserExtensionTotal($params = 0)
    {
        // 用户总数
        $where = [];
        if(!empty($params['user']))
        {
            $where[] = ['referrer', '=', $params['user']['id']];
        } else {
            $where[] = ['referrer', '>', 0];
        }
        $user_count = (int) Db::name('User')->where($where)->count('id');

        // 已消费用户总数
        if(!empty($params['user']))
        {
            $where = [
                ['u.referrer', '=', $params['user']['id']],
            ];
            $valid_user_count = (int) Db::name('PluginsMembershiplevelvipUserProfit')->alias('p')->join('user u', 'u.id=p.payment_user_order_user_id')->where($where)->count('distinct u.id');
        } else {
            $valid_user_count = (int) Db::name('PluginsMembershiplevelvipUserProfit')->count('distinct payment_user_order_user_id');
        }
        return [
            'user_count'        => $user_count,
            'valid_user_count'  => $valid_user_count,
        ];
    }

    /**
     * 用户统计数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-09-21
     * @desc    description
     * @param   [array]          $config [插件基础配置]
     * @param   [array]          $user   [用户信息]
     */
    public static function UserStatisticsData($config, $user)
    {
        // 是否开启用户返佣功能
        if(empty($config) || !isset($config['is_commission']) || $config['is_commission'] != 1)
        {
            return null;
        }

        // 推广用户总数
        $user_total = self::UserExtensionTotal(['user'=>$user]);

        // 收益汇总
        $user_profit_stay_price = self::UserProfitPriceTotal(0, $user['id']);
        $user_profit_already_price = self::UserProfitPriceTotal(1, $user['id']);

        // 返回数据
        $host = SystemBaseService::AttachmentHost().'/static/plugins/membershiplevelvip/images/app/statistics/';
        $currency_symbol = ResourcesService::CurrencyDataSymbol();
        return [
            // 推广用户
            'user_total'    => [
                [
                    'name'   => '已推广用户总数',
                    'value'  => $user_total['user_count'],
                    'type'   => 'user_count',
                    'icon'   => $host.'user-count.png',
                ],
                [
                    'name'   => '已消费用户总数',
                    'value'  => $user_total['valid_user_count'],
                    'type'   => 'valid_user_count',
                    'icon'   => $host.'valid-user-count.png',
                ],
            ],

            // 返佣
            'user_profit' => [
                [
                    'name'   => '返佣总额',
                    'value'  => $user_profit_stay_price+$user_profit_already_price,
                    'first'  => $currency_symbol,
                ],
                [
                    'name'   => '待结算',
                    'value'  => $user_profit_stay_price,
                    'first'  => $currency_symbol,
                ],
                [
                    'name'   => '已结算',
                    'value'  => $user_profit_already_price,
                    'first'  => $currency_symbol,
                ],
            ],
        ];
    }
}
?>