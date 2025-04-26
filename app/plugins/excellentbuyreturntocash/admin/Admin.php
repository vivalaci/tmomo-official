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
namespace app\plugins\excellentbuyreturntocash\admin;

use app\service\GoodsCategoryService;
use app\plugins\excellentbuyreturntocash\admin\Common;
use app\plugins\excellentbuyreturntocash\service\BaseService;
use app\plugins\excellentbuyreturntocash\service\CrontabService;
use app\plugins\excellentbuyreturntocash\service\StatisticalService;

/**
 * 优购返现 - 基础配置
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class Admin extends Common
{
    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function index($params = [])
    {
        // 佣金结算脚本自动运行一次
        CrontabService::ProfitSettlement();

        // 收益汇总
        $profit_price0 = StatisticalService::ProfitPriceTotal(0);
        $profit_price1 = StatisticalService::ProfitPriceTotal(1);
        $profit_price2 = StatisticalService::ProfitPriceTotal(2);
        $profit_price3 = StatisticalService::ProfitPriceTotal(3);
        $profit_price4 = StatisticalService::ProfitPriceTotal(4);
        MyViewAssign('profit_price0', PriceNumberFormat($profit_price0));
        MyViewAssign('profit_price1', PriceNumberFormat($profit_price1));
        MyViewAssign('profit_price2', PriceNumberFormat($profit_price2));
        MyViewAssign('profit_price3', PriceNumberFormat($profit_price3));
        MyViewAssign('profit_price4', PriceNumberFormat($profit_price4));
        MyViewAssign('profit_total_price', PriceNumberFormat($profit_price0+$profit_price1+$profit_price2+$profit_price3));

        // 图表-返现
        $profit = StatisticalService::ProfitFifteenTodayTotal();
        MyViewAssign('profit_chart', $profit['data']);

        // 加载图表组件
        MyViewAssign('is_load_echarts', 1);

        // 首页信息
        $ret = BaseService::BaseConfig();
        if($ret['code'] == 0)
        {
            MyViewAssign('return_coupon_type_list', BaseService::$return_coupon_type_list);
            MyViewAssign('data', $ret['data']);
            return MyView('../../../plugins/excellentbuyreturntocash/view/admin/admin/index');
        } else {
            return $ret['msg'];
        }
    }

    /**
     * 编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function saveinfo($params = [])
    {
        $ret = BaseService::BaseConfig();
        if($ret['code'] == 0)
        {
            // 优惠券
            $data_params = [
                'm'         => 0,
                'n'         => 0,
                'where'     => ['is_enable' => 1],
            ];
            $coupon = CallPluginsServiceMethod('coupon', 'CouponService', 'CouponList', $data_params);
            MyViewAssign('coupon_list', $coupon['data']);

            // 商品分类
            MyViewAssign('goods_category_list', GoodsCategoryService::GoodsCategoryAll());

            // 静态数据
            MyViewAssign('return_coupon_type_list', BaseService::$return_coupon_type_list);

            MyViewAssign('data', $ret['data']);
            return MyView('../../../plugins/excellentbuyreturntocash/view/admin/admin/saveinfo');
        } else {
            return $ret['msg'];
        }
    }

    /**
     * 数据保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function save($params = [])
    {
        return BaseService::BaseConfigSave($params);
    }
}
?>