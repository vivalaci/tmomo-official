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
use app\plugins\shop\service\BaseService as ShopBaseService;
use app\plugins\shop\service\ShopService;

/**
 * 优惠券
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
{
    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-10-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 优惠券列表
        $coupon_list = CouponService::UserReceiveCouponList(array_merge($params, ['user'=>$this->user]));

        // 返回数据
        $result = [
            'base'  => $this->plugins_config,
            'data'  => $coupon_list,
        ];
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-10-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Detail($params = [])
    {
        // 优惠券
        $coupon_data = CouponService::UserReceiveCouponData(array_merge($params, ['user'=>$this->user]));
        if($coupon_data['code'] == 0)
        {
            $result = [
                'base'  => $this->plugins_config,
                'data'  => $coupon_data['data'],
            ];
            return DataReturn(MyLang('handle_success'), 0, $result);
        }
        return $coupon_data;
    }

    /**
     * 多商户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-10-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Shop($params = [])
    {
        // 店铺基础配置
        $shop_id = empty($params['id']) ? 0 : intval($params['id']);
        $shop_base = ShopBaseService::BaseConfig();

        // 店铺信息
        $shop = ShopService::ShopValidInfo($shop_id, $shop_base['data']);
        if(empty($shop))
        {
            return DataReturn(MyLang('shop_not_have_or_close_tips'), -1);
        }
        unset($shop['layout_config']);

        // 优惠券列表
        $coupon_list = [];
        if(!empty($shop_id))
        {
            $coupon_list = CouponService::UserReceiveCouponList(['user'=>$this->user, 'where'=>[['shop_id', '=', $shop_id]]]);
        }

        // 返回数据
        $result = [
            'base'  => $this->plugins_config,
            'data'  => $coupon_list,
            'shop'  => $shop,
        ];
        return DataReturn(MyLang('handle_success'), 0, $result);
    }
}
?>