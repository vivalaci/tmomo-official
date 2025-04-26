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
namespace app\plugins\distribution\admin;

use app\service\UserService;
use app\plugins\distribution\admin\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\StatisticalService;

/**
 * 分销 - 分销商统计管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Stats extends Common
{
    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 默认时间
        $default_day = 'this-month';
        MyViewAssign('default_day', $default_day);

        // 时间
        $time_data = StatisticalService::DateTimeList();
        MyViewAssign('time_data', $time_data);

        // 基础数据总计
        $uid = empty($params['uid']) ? 0 : intval($params['uid']);
        if(!empty($uid))
        {
            $params['user'] = UserService::UserHandle(UserService::UserBaseInfo('id', $uid));
        }
        if(!empty($time_data) && !empty($default_day) && isset($time_data[$default_day]))
        {
            // 日期转时间戳
            $params['start'] = strtotime($time_data[$default_day]['start']);
            $params['end'] = strtotime($time_data[$default_day]['end']);
        }

        // 基础统计数据
        $base_data = StatisticalService::BaseData($params);
        MyViewAssign('base_data', $base_data);

        // 推广用户
        $user_promotion_data = StatisticalService::UserPromotionTotalData($params);
        MyViewAssign('user_promotion_data', $user_promotion_data);

        // 收益统计数据
        $profit_data = StatisticalService::ProfitData($params);
        MyViewAssign('profit_data', $profit_data);

        // 加载图表组件
        MyViewAssign('is_load_echarts', 1);
        MyViewAssign('uid', $uid);
        return MyView('../../../plugins/distribution/view/admin/stats/index');
    }

    /**
     * 数据统计
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Data($params = [])
    {
        if(!empty($params['uid']))
        {
            $params['user'] = UserService::UserHandle(UserService::UserBaseInfo('id', intval($params['uid'])));
        }
        return StatisticalService::StatsData($params);
    }
}
?>