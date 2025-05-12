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
namespace app\plugins\commonrightnavigation\index;

use app\service\UserService;
use app\service\GoodsCartService;
use app\plugins\ask\service\AskService;

/**
 * 右侧快捷导航 - 前端
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index
{
    /**
     * 留言
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Ask($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        $params['user'] = UserService::LoginUserInfo();
        return AskService::AskSave($params);
    }

    /**
     * 购物车
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Cart($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 当前模块/控制器/方法
        $module_name = RequestModule();
        $controller_name = RequestController();
        $action_name = RequestAction();

        // 购物车
        $cart_list = GoodsCartService::GoodsCartList(['user'=>UserService::LoginUserInfo()]);
        $assign = [
            'cart_list'                 => $cart_list['data'],
            'module_controller_action'  => $module_name.$controller_name.$action_name,
        ];

        // 基础
        $assign['cart_base'] = [
            'total_price'   => empty($cart_list['data']) ? '0.00' : PriceNumberFormat(array_sum(array_column($cart_list['data'], 'total_price'))),
            'cart_count'    => empty($cart_list['data']) ? 0 : array_sum(array_column($cart_list['data'], 'stock')),
            'ids'           => empty($cart_list['data']) ? '' : implode(',', array_column($cart_list['data'], 'id')),
        ];
        MyViewAssign($assign);
        return DataReturn(MyLang('operate_success'), 0, MyView('../../../plugins/commonrightnavigation/view/index/public/cart', ['is_animation'=>0]));
    }
    
    /**
     * 错误提示
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-07-15
     * @desc    description
     * @param   [string]      $msg [提示信息、默认（操作失败）]
     */
    public function Error($msg)
    {
        if(IS_AJAX)
        {
            return DataReturn($msg, -1);
        } else {
            MyViewAssign('msg', $msg);
            return MyView('public/jump_error');
        }
    }
}
?>