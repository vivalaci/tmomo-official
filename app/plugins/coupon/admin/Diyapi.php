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
namespace app\plugins\coupon\admin;

use app\plugins\coupon\admin\Common;
use app\plugins\coupon\service\CouponService;

/**
 * 优惠券 - diy接口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class DiyApi extends Common
{
    /**
     * 优惠券列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function CouponList($params = [])
    {
        $params['control'] = 'coupon';
        $params['action'] = 'index';
        $params['is_enable'] = 1;
        $params['is_user_receive'] = 1;
        return DataReturn('success', 0, FormModuleData($params));
    }

    /**
     * 领取优惠券列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function ReceiveCouponList($params = [])
    {
        $params['user'] = $this->user;
        return DataReturn('success', 0, CouponService::AutoCouponList($params));
    }
}
?>