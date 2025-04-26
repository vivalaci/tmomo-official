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
namespace app\plugins\coupon\index;

use app\service\UserService;
use app\service\SeoService;
use app\plugins\coupon\index\Common;
use app\plugins\coupon\service\BaseService;
use app\plugins\coupon\service\CouponService;
use app\plugins\coupon\service\UserCouponService;
use app\plugins\shop\service\BaseService as ShopBaseService;
use app\plugins\shop\service\ShopService;
use app\plugins\shop\service\ShopNavigationService;
use app\plugins\shop\service\ShopGoodsCategoryService;
use app\plugins\shop\service\ShopFavorService;

/**
 * 优惠券 - 优惠券首页
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
{
    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-11T15:36:19+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 优惠券列表
        $coupon_list = CouponService::UserReceiveCouponList(['user'=>$this->user]);
        MyViewAssign('coupon_list', $coupon_list);

        // 浏览器名称
        if(!empty($this->plugins_config['application_name']))
        {
            MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($this->plugins_config['application_name'], 1));
        }

        MyViewAssign('params', $params);
        MyViewAssign('plugins_base', $this->plugins_config);
        return MyView('../../../plugins/coupon/view/index/index/index');
    }

    /**
     * 详情
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-11T15:36:19+0800
     * @param    [array]          $params [输入参数]
     */
    public function Detail($params = [])
    {
        // 优惠券列表
        $coupon_data = CouponService::UserReceiveCouponData(array_merge($params, ['user'=>$this->user]));
        if($coupon_data['code'] != 0)
        {
            return MyView('public/tips_error', ['msg'=>$coupon_data['msg']]);
        }

        // 浏览器名称
        if(!empty($coupon_data['data']['name']))
        {
            MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($coupon_data['data']['name'], 1));
        }

        MyViewAssign('coupon_list', [$coupon_data['data']]);
        MyViewAssign('params', $params);
        MyViewAssign('plugins_base', $this->plugins_config);
        return MyView('../../../plugins/coupon/view/index/index/detail');
    }

    /**
     * 店铺优惠券
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-11T15:36:19+0800
     * @param    [array]          $params [输入参数]
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
            MyViewAssign('msg', MyLang('shop_not_have_or_close_tips'));
            return MyView('public/tips_error');
        }
        MyViewAssign('shop', $shop);

        // 导航
        $navigation = ShopNavigationService::Nav($shop_base['data'], $shop);
        MyViewAssign('shop_navigation', $navigation);

        // 店铺商品分类
        $category = ShopGoodsCategoryService::GoodsCategoryAll(['user_id'=>$shop['user_id']]);
        MyViewAssign('shop_goods_category', $category['data']);

        // 用户收藏的店铺
        $shop_favor_user = empty($this->user['id']) ? [] : ShopFavorService::UserShopFavorData($this->user['id']);
        MyViewAssign('shop_favor_user', $shop_favor_user);

        // 在线客服地址
        $chat = ShopBaseService::ChatUrl($shop_base['data'], $shop['user_id']);
        MyViewAssign('chat', $chat);

        // 优惠券列表
        $coupon_list = [];
        if(!empty($shop_id))
        {
            $coupon_list = CouponService::UserReceiveCouponList(['user'=>$this->user, 'where'=>[['shop_id', '=', $shop_id]]]);
        }
        MyViewAssign('coupon_list', $coupon_list);

        // seo
        $title = empty($this->plugins_config['shop_application_name']) ? '优惠券' : $this->plugins_config['shop_application_name'];
        $seo_title = $title.' - '.$shop['name'];
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($seo_title, 2));
        if(!empty($shop['seo_keywords']))
        {
            MyViewAssign('home_seo_site_keywords', $shop['seo_keywords']);
        }
        if(!empty($shop['seo_desc']))
        {
            MyViewAssign('home_seo_site_description', $shop['seo_desc']);
        }

        MyViewAssign('params', $params);
        MyViewAssign('base_config', $shop_base['data']);
        return MyView('../../../plugins/coupon/view/index/index/shop');
    }

    /**
     * 领取优惠券
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-11T15:36:19+0800
     * @param    [array]          $params [输入参数]
     */
    public function Receive($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 领取优惠券
        return CouponService::UserReceiveCoupon($params);
    }

    /**
     * 优惠券过期处理
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-18T16:57:38+0800
     * @param    [array]          $params [输入参数]
     */
    public function Expire($params = [])
    {
        $ret = UserCouponService::CouponUserExpireHandle();
        return 'success:'.$ret['data'];
    }
}
?>