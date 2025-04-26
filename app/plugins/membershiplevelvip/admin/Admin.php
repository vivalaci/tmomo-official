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
namespace app\plugins\membershiplevelvip\admin;

use app\service\PaymentService;
use app\plugins\membershiplevelvip\admin\Common;
use app\plugins\membershiplevelvip\service\BaseService;
use app\plugins\membershiplevelvip\service\CrontabService;
use app\plugins\membershiplevelvip\service\StatisticalService;
use app\plugins\membershiplevelvip\service\BusinessService;

/**
 * 会员等级增强版插件 - 管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Admin extends Common
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
        // 定时脚本自动运行一次
        CrontabService::OrderClose();
        CrontabService::ProfitCreate();
        CrontabService::ProfitSettlement();

        // 推广用户总数
        $user_total = StatisticalService::UserExtensionTotal();
        MyViewAssign('user_total', $user_total);

        // 收益汇总
        $user_profit_stay_price = StatisticalService::UserProfitPriceTotal(0);
        $user_profit_already_price = StatisticalService::UserProfitPriceTotal(1);
        MyViewAssign('user_profit_stay_price', PriceNumberFormat($user_profit_stay_price));
        MyViewAssign('user_profit_already_price', PriceNumberFormat($user_profit_already_price));
        MyViewAssign('user_profit_total_price', PriceNumberFormat($user_profit_stay_price+$user_profit_already_price));

        // 图表-收益
        $profit = StatisticalService::UserProfitFifteenTodayTotal();
        MyViewAssign('profit_chart', $profit['data']);

        // 图表-推广用户
        $user = StatisticalService::UserExtensionFifteenTodayTotal();
        MyViewAssign('user_chart', $user['data']);

        // 支付方式
        MyViewAssign('payment_list', PaymentService::PaymentList(['is_enable'=>1, 'is_open_user'=>1]));

        // 等级规则
        MyViewAssign('members_level_rules_list', BaseService::$members_level_rules_list);
        MyViewAssign('data', $this->plugins_config);

        // 加载图表组件
        MyViewAssign('is_load_echarts', 1);
        return MyView('../../../plugins/membershiplevelvip/view/admin/admin/index');
    }

    /**
     * 编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        // 支付方式
        MyViewAssign('payment_list', PaymentService::PaymentList(['is_enable'=>1, 'is_open_user'=>1]));

        // 等级规则
        MyViewAssign('members_level_rules_list', BaseService::$members_level_rules_list);
        MyViewAssign('data', $this->plugins_config);
        return MyView('../../../plugins/membershiplevelvip/view/admin/admin/saveinfo');
    }

    /**
     * 数据保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        return BaseService::BaseConfigSave($params);
    }

    /**
     * 二维码清空
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-12T20:34:52+0800
     * @param    [array]          $params [输入参数]
     */
    public function QrcodeDelete($params = [])
    {
        return BaseService::QrcodeDelete($params);
    }

    /**
     * 商品搜索
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-10-16
     * @desc    description
     * @param   [array]           $params [商品搜索]
     */
    public function GoodsSearch($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 搜索数据
        $ret = BaseService::LimitGoodsSearchList($params);
        if($ret['code'] == 0)
        {
            $ret['data']['data'] = MyView('../../../plugins/membershiplevelvip/view/admin/public/goodssearch', ['data'=>$ret['data']['data']]);
        }
        return $ret;
    }
}
?>