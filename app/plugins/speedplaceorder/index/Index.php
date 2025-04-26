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
namespace app\plugins\speedplaceorder\index;

use app\service\SeoService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\plugins\speedplaceorder\index\Common;
use app\plugins\speedplaceorder\service\BaseService;
use app\plugins\speedplaceorder\service\CartService;

/**
 * 极速下单 - 下单首页
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
        // 商品分类
        MyViewAssign('goods_category_list', GoodsCategoryService::GoodsCategoryAll());

        // 数据列表
        $ret = CartService::CartList(['where'=>$this->form_where, 'user'=>$this->user]);
        MyViewAssign('data_list', $ret['data']);
        $view = MyView('../../../plugins/speedplaceorder/view/index/public/list');
        MyViewAssign('data_html', $view);

        // 获取应用数据
        $base = BaseService::BaseConfig();

        // 浏览器标题
        $seo_name = empty($base['data']['application_name']) ? '极速下单' : $base['data']['application_name'];
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($seo_name, 1));

        MyViewAssign('params', $params);
        return MyView('../../../plugins/speedplaceorder/view/index/index/index');
    }

    /**
     * 商品搜索
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function GoodsSearch($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 搜索数据
        $ret = BaseService::GoodsSearchList($params);
        MyViewAssign('data', $ret['data']);
        $view = MyView('../../../plugins/speedplaceorder/view/index/public/goods');
        return DataReturn('success', 0, $view);
    }

    /**
     * 获取商品规格
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Spec($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 参数校验
        if(empty($params['goods_id']))
        {
            return DataReturn(MyLang('data_id_error_tips'), -1);
        }

        $goods_id = input('id');
        $params = [
            'where' => [
                ['id', '=', $params['goods_id']],
                ['is_shelves', '=', 1],
                ['is_delete_time', '=', 0],
            ],
            'is_spec'   => 1,
        ];
        $ret = GoodsService::GoodsList($params);
        MyViewAssign('goods', empty($ret['data'][0]) ? [] : $ret['data'][0]);
        $view = MyView('../../../plugins/speedplaceorder/view/index/public/spec');
        return DataReturn('success', 0, $view);
    }

    /**
     * 商品规格类型
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function SpecType($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        return GoodsService::GoodsSpecType($params);
    }

    /**
     * 商品规格信息
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function SpecDetail($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        return GoodsService::GoodsSpecDetail($params);
    }

    /**
     * 商品保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        // 是否登录
        IsUserLogin();

        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params['user'] = $this->user;
        $ret = CartService::CartSave($params);
        if($ret['code'] == 0)
        {
            // 数据列表
            $data = CartService::CartList($params);
            MyViewAssign('data_list', $data['data']);
            $ret['data'] = MyView('../../../plugins/speedplaceorder/view/index/public/list');
        }
        return $ret;
    }

    /**
     * 数量保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Stock($params = [])
    {
        // 是否登录
        IsUserLogin();

        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        $params['user'] = $this->user;
        return CartService::CartStock($params);
    }

    /**
     * 删除
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-21
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Delete($params = [])
    {
        // 是否登录
        IsUserLogin();

        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params['user'] = $this->user;
        return CartService::CartDelete($params);
    }

    /**
     * 加入购物车
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-22
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Cart($params = [])
    {
        // 是否登录
        IsUserLogin();
        
        // 开始处理
        $params['user'] = $this->user;
        $ret = CartService::CartCopy($params);
        if($ret['code'] == 0)
        {
            return MyRedirect(MyUrl('index/cart/index'));
        } else {
            MyViewAssign('msg', $ret['msg']);
            return MyView('public/tips_error');
        }
    }
}
?>