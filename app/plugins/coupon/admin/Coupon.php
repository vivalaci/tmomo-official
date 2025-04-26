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

use app\service\GoodsCategoryService;
use app\plugins\coupon\admin\Common;
use app\plugins\coupon\service\BaseService;
use app\plugins\coupon\service\CouponService;
use app\plugins\shop\service\ShopGoodsCategoryService;

/**
 * 优惠券 - 优惠券管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class Coupon extends Common
{
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
        return MyView('../../../plugins/coupon/view/admin/coupon/index');
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
        return MyView('../../../plugins/coupon/view/admin/coupon/detail');
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

        // 商品分类
        if(!empty($this->data_detail['id']) && !empty($this->data_detail['shop_id']))
        {
            // 店铺商品分类
            $shop_goods_category = ShopGoodsCategoryService::GoodsCategoryAll(['shop_id'=>$this->data_detail['shop_id']]);
            MyViewAssign('category_list', $shop_goods_category['data']);
        } else {
            // 系统商品分类
            MyViewAssign('category_list', GoodsCategoryService::GoodsCategoryAll());
        }

        unset($params['id']);
        MyViewAssign('params', $params);
        MyViewAssign('data', $this->data_detail);
        return MyView('../../../plugins/coupon/view/admin/coupon/saveinfo');
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
        return MyView('../../../plugins/coupon/view/admin/coupon/sendinfo');
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
        return DataReturn(MyLang('get_success'), 0, MyView('../../../plugins/coupon/view/admin/coupon/user'));
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
        $params['config'] = $this->plugins_config;
        return CouponService::CouponSend($params);
    }
}
?>