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

/**
 * 优购返现 - 数据统计服务层
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
     * 返现趋势, 15天数据
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2019-06-12T01:13:44+0800
     * @param    [array]          $params [输入参数]
     */
    public static function ProfitFifteenTodayTotal($params = [])
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
                ['status', '<=', 3],
            ];
            if(!empty($params['user']))
            {
                $where[] = ['user_id', '=', $params['user']['id']];
            }
            $data[] = PriceNumberFormat(Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where($where)->sum('profit_price'));
        }

        // 数据组装
        $result = [
            'name_arr'  => $name_arr,
            'data'      => $data,
        ];
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 用户返现总额
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-05T22:08:34+0800
     * @param    [int]                   $status [结算状态（0待生效, 1生效中, 2待结算, 3已结算, 4已失效）]
     * @param    [int]                   $user_id [用户id]
     */
    public static function ProfitPriceTotal($status = 0, $user_id = null, $field = 'profit_price')
    {
        $where = [
            'status'    => $status,
        ];
        if(!empty($user_id))
        {
            $where['user_id'] = intval($user_id);
        }
        return PriceNumberFormat(Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->where($where)->sum($field));
    }
}
?>