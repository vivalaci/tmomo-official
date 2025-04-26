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
use app\plugins\excellentbuyreturntocash\service\BaseService;

/**
 * 优购返现 - 业务服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class BusinessService
{
    /**
     * 获取返现明细列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function ProfitList($params = [])
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();

        // 结算周期时间
        $return_to_cash_time = (empty($base['data']) || empty($base['data']['return_to_cash_time'])) ? 43200 : intval($base['data']['return_to_cash_time']);
        $time = time()-($return_to_cash_time*60);

        // 列表数据获取
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'c.*, o.order_no, o.status as order_status, o.pay_status as order_pay_status, o.client_type as order_client_type, o.refund_price, o.collect_time as order_collect_time' : $params['field'];
        $order_by = empty($params['order_by']) ? 'c.id desc' : $params['order_by'];
        $user_type = isset($params['user_type']) ? $params['user_type'] : 'user';

        $data = Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->alias('c')->join('order o', 'c.order_id=o.id')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        if(!empty($data))
        {
            $common_platform_type = MyConst('common_platform_type');
            $order_status_list = MyConst('common_order_status');
            $order_pay_status = MyConst('common_order_pay_status');
            $profit_status_list = BaseService::$profit_status_list;
            foreach($data as &$v)
            {
                // 用户信息
                $v['user'] = ($user_type == 'admin') ? UserService::GetUserViewInfo($v['user_id']) : [];

                // 佣金状态
                $v['status_name'] = $profit_status_list[$v['status']]['name'];

                // 订单状态
                $v['order_status_name'] = $order_status_list[$v['order_status']]['name'];

                // 支付状态
                $v['order_pay_status_name'] = $order_pay_status[$v['order_pay_status']]['name'];

                // 客户端
                $v['order_client_type_name'] = isset($common_platform_type[$v['order_client_type']]) ? $common_platform_type[$v['order_client_type']]['name'] : '';

                // 日志
                if(!empty($v['log']))
                {
                    $log = json_decode($v['log'], true);
                    foreach($log as $lk=>$lv)
                    {
                        $log[$lk]['time'] = date('Y-m-d H:i:s', $lv['time']);
                    }
                    $v['log'] = $log;
                }

                // 时间
                $v['success_time'] = ($v['success_time'] > 0) ? date('Y-m-d H:i:s', $v['success_time']) : '';
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['upd_time'] = ($v['upd_time'] > 0) ? date('Y-m-d H:i:s', $v['upd_time']) : '';

                // 结算时间未完成状态下计算剩余时间
                if($v['status'] == 2)
                {
                    $v['success_estimate_icon'] = '预计';
                    $v['success_time'] = date('Y-m-d H:i:s', $v['order_collect_time']+($return_to_cash_time*60));
                }
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 返现明细列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function ProfitWhere($params = [])
    {
        $where = [];

        // 关键字根据订单筛选
        if(!empty($params['keywords']))
        {
            $order_ids = Db::name('Order')->where('order_no', '=', $params['keywords'])->column('id');
            if(!empty($order_ids))
            {
                $where[] = ['o.id', 'in', $order_ids];
            } else {
                $user_ids = Db::name('User')->where('number_code|username|nickname|mobile|email', '=', $params['keywords'])->column('id');
                if(!empty($user_ids))
                {
                    $where[] = ['c.user_id', 'in', $user_ids];
                } else {
                    // 无数据条件，避免搜索条件没有数据造成的错觉
                    $where[] = ['c.id', '=', 0];
                }
            }
        }

        // 用户
        if(!empty($params['user']))
        {
            $where[] = ['c.user_id', '=', $params['user']['id']];
        }

        // 指定用户id
        if(!empty($params['uid']))
        {
            $where[] = ['c.user_id', '=', intval($params['uid'])];
        }

        // id
        if(!empty($params['id']))
        {
            $where[] = ['c.id', '=', intval($params['id'])];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 佣金状态
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['c.status', '=', intval($params['status'])];
            }

            // 订单状态
            if(isset($params['order_status']) && $params['order_status'] > -1)
            {
                $where[] = ['o.status', '=', intval($params['order_status'])];
            }

            // 来源平台
            if(!empty($params['platform_type']))
            {
                $where[] = ['o.client_type', '=', $params['platform_type']];
            }

            // 是否有退款
            if(isset($params['is_refund']) && $params['is_refund'] > -1)
            {
                if($params['is_refund'] == 0)
                {
                    $where[] = ['o.refund_price', '<=', 0];
                } else {
                    $where[] = ['o.refund_price', '>', 0];
                }
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['c.add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['c.add_time', '<', strtotime($params['time_end'])];
            }
        }
        return $where;
    }

    /**
     * 返现明细总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function ProfitTotal($where)
    {
        return (int) Db::name('PluginsExcellentbuyreturntocashReturnCashOrder')->alias('c')->join('order o', 'c.order_id=o.id')->where($where)->count();
    }
}
?>