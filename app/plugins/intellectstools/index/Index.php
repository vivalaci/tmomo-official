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
namespace app\plugins\intellectstools\index;

use app\service\GoodsCartService;
use app\plugins\intellectstools\index\Common;
use app\plugins\intellectstools\service\AddressDiscernService;

/**
 * 智能工具箱 - 首页
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-11-18
 * @desc    description
 */
class Index extends Common
{
    /**
     * 地址识别
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Address($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        return AddressDiscernService::Run($params);
    }

    /**
     * 购物车
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-07-08
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Cart($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 购物车
        $cart_list = GoodsCartService::GoodsCartList(['user'=>$this->user]);
        $assign = [
            'cart_list' => $cart_list['data'],
        ];

        // 基础
        $assign['cart_base'] = [
            'total_price'   => empty($cart_list['data']) ? '0.00' : PriceNumberFormat(array_sum(array_column($cart_list['data'], 'total_price'))),
            'cart_count'    => empty($cart_list['data']) ? 0 : array_sum(array_column($cart_list['data'], 'stock')),
            'ids'           => empty($cart_list['data']) ? '' : implode(',', array_column($cart_list['data'], 'id')),
        ];
        MyViewAssign($assign);
        return DataReturn(MyLang('operate_success'), 0, [
            'html'  => MyView('../../../plugins/intellectstools/view/index/public/search_right_cart_content'),
            'count' => $assign['cart_base']['cart_count'],
        ]);
    }
}
?>