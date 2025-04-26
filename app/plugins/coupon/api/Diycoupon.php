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
namespace app\plugins\coupon\api;

use app\plugins\coupon\api\Common;
use app\plugins\coupon\service\CouponService;

/**
 * 优惠券 - DIY优惠券
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class DiyCoupon extends Common
{
    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        return DataReturn('success', 0, FormModuleData($params));
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-03-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        $result = FormModuleData($params);
        if(empty($result) || empty($result['data']))
        {
            return DataReturn(MyLang('no_data'), -1);
        }
        return DataReturn('success', 0, $result);
    }

    /**
     * 指定读取优惠券列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-03-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function AppointCouponList($params = [])
    {
        return DataReturn('success', 0, CouponService::AppointCouponList($params));
    }

    /**
     * 自动读取优惠券列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-03-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function AutoCouponList($params = [])
    {
        return DataReturn('success', 0, CouponService::AutoCouponList($params));
    }
}
?>