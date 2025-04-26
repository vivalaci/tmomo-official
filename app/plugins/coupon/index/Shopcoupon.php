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

use app\service\GoodsService;
use app\plugins\coupon\index\Common;
use app\plugins\coupon\service\BaseService;
use app\plugins\coupon\service\CouponService;
use app\plugins\shop\service\ShopGoodsCategoryService;

/**
 * 优惠券 - 多商户优惠券管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class ShopCoupon extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // 是否已经登录
        $this->IsShopLogin();

        MyViewAssign([
            // 关闭顶部底部内容
            'is_header'        => 0,
            'is_footer'        => 0,
            // 页面加载层
            'is_page_loading'  => 1,
        ]);
    }

    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        return MyView('../../../plugins/coupon/view/index/shopcoupon/index');
    }

    /**
     * 详情
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Detail($params = [])
    {
        MyViewAssign('data', $this->data_detail);
        return MyView('../../../plugins/coupon/view/index/shopcoupon/detail');
    }

    /**
     * 编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        // 静态数据
        MyViewAssign('coupon_type_list', BaseService::ConstData('coupon_type_list'));
        MyViewAssign('coupon_bg_color_list', BaseService::ConstData('coupon_bg_color_list'));
        MyViewAssign('coupon_expire_type_list', BaseService::ConstData('coupon_expire_type_list'));
        MyViewAssign('coupon_use_limit_type_list', BaseService::ConstData('coupon_use_limit_type_list'));

        // 店铺商品分类
        $shop_goods_category = ShopGoodsCategoryService::GoodsCategoryAll(['user_id'=>$this->user['id']]);
        MyViewAssign('category_list', $shop_goods_category['data']);

        unset($params['id']);
        MyViewAssign('params', $params);
        MyViewAssign('data', $this->data_detail);
        return MyView('../../../plugins/coupon/view/index/shopcoupon/saveinfo');
    }

    /**
     * 发放页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SendInfo($params = [])
    {
        if(!empty($params['id']))
        {
            $data_params = array(
                'm'         => 0,
                'n'         => 1,
                'where'     => ['id'=>intval($params['id'])],
            );
            $ret = CouponService::CouponList($data_params);
            MyViewAssign('data', empty($ret['data'][0]) ? [] : $ret['data'][0]);

            // 会员等级
            $level = CallPluginsServiceMethod('membershiplevelvip', 'LevelService', 'DataList', ['where'=>['is_enable'=>1], 'field'=>'id,name']);
            MyViewAssign('level_list', $level['data']);
        }

        unset($params['id']);
        MyViewAssign('params', $params);
        return MyView('../../../plugins/coupon/view/index/shopcoupon/sendinfo');
    }

    /**
     * 数据保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        // 获取店铺信息
        $shop = $this->UserShopInfo();
        if($shop['code'] != 0)
        {
            return $shop;
        }

        // 优惠券保存
        $params['shop_id'] = $shop['data'];
        return CouponService::CouponSave($params);
    }

    /**
     * 商品搜索
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Search($params = [])
    {
        // 获取店铺信息
        $shop = $this->UserShopInfo();
        if($shop['code'] != 0)
        {
            return $shop;
        }

        // 搜索数据
        $params['shop_id'] = $shop['data'];
        return CouponService::GoodsSearchList($params);
    }

    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-01-12T22:23:06+0800
     * @param    [array]          $params [输入参数]
     */
    public function StatusUpdate($params = [])
    {
        // 获取店铺信息
        $shop = $this->UserShopInfo();
        if($shop['code'] != 0)
        {
            return $shop;
        }

        // 开始处理
        $params['shop_id'] = $shop['data'];
        return CouponService::StatusUpdate($params);
    }

    /**
     * 删除
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-01-12T22:23:06+0800
     * @param    [array]          $params [输入参数]
     */
    public function Delete($params = [])
    {
        // 获取店铺信息
        $shop = $this->UserShopInfo();
        if($shop['code'] != 0)
        {
            return $shop;
        }

        // 开始处理
        $params['shop_id'] = $shop['data'];
        return CouponService::Delete($params);
    }

    /**
     * 用户搜索
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function UserSearch($params = [])
    {
        $ret =  CouponService::UserSearchList($params);
        MyViewAssign('data', $ret['data']);
        return DataReturn(MyLang('get_success'), 0, MyView('../../../plugins/coupon/view/index/shopcoupon/user'));
    }

    /**
     * 优惠券发放
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Send($params = [])
    {
        // 获取店铺信息
        $shop = $this->UserShopInfo();
        if($shop['code'] != 0)
        {
            return $shop;
        }

        // 优惠券保存
        $params['shop_id'] = $shop['data'];
        $params['config'] = $this->plugins_config;
        return CouponService::CouponSend($params);
    }

    /**
     * 获取店铺信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-16
     * @desc    description
     */
    public function UserShopInfo()
    {
        $shop_id = BaseService::ShopID();
        if(empty($shop_id))
        {
            return DataReturn(MyLang('shop_info_error_tips'), -1);
        }
        return DataReturn('success', 0, $shop_id);
    }
}
?>