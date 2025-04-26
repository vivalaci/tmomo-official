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
namespace app\plugins\excellentbuyreturntocash;

use app\plugins\excellentbuyreturntocash\service\BaseService;
use app\plugins\excellentbuyreturntocash\service\ProfitService;
use app\plugins\excellentbuyreturntocash\service\OrderService;
use app\plugins\excellentbuyreturntocash\service\CouponService;

/**
 * 优购返现 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 插件配置信息
    private $plugins_config;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-09T14:25:44+0800
     * @param    [array]       $params [输入参数]
     */
    public function handle($params = [])
    {
        if(!empty($params['hook_name']))
        {
            // 插件配置信息
            $base = BaseService::BaseConfig();
            $this->plugins_config = $base['data'];

            // 当前模块/控制器/方法
            $module_name = RequestModule();
            $controller_name = RequestController();
            $action_name = RequestAction();

            $ret = '';
            switch($params['hook_name'])
            {
                // 公共css
                case 'plugins_css' :
                    $coupon_style = ['indexgoodsindex'];
                    if(in_array($module_name.$controller_name.$action_name, $coupon_style))
                    {
                        $ret = 'static/plugins/excellentbuyreturntocash/css/index/style.css';
                    }
                    break;

                // 用户中心左侧导航
                case 'plugins_service_users_center_left_menu_handle' :
                    $ret = $this->UserCenterLeftMenuHandle($params);
                    break;

                // 顶部小导航右侧-我的商城
                case 'plugins_service_header_navigation_top_right_handle' :
                    $ret = $this->CommonTopNavRightMenuHandle($params);
                    break;

                // 订单状态
                case 'plugins_service_order_status_change_history_success_handle' :
                    if(!empty($params['data']) && !empty($params['order_id']) && isset($params['data']['new_status']))
                    {
                        switch($params['data']['new_status'])
                        {
                            // 支付订单状态更新
                            case 2 :
                                // 返现订单处理
                                $ret = ProfitService::OrderProfitValid($params['order_id'], $params['data']);
                                if($ret['code'] == 0)
                                {
                                    // 发放优惠券
                                    $ret = CouponService::CouponSend($params['order_id'], $params['data']);
                                }
                                break;

                            // 订单收货完成
                            case 4 :
                                // 返现订单处理
                                $ret = ProfitService::OrderProfitConfirm($params['order_id'], $params['data']);
                                break;

                            // 订单取消/关闭
                            case 5 :
                            case 6 :
                                $ret = ProfitService::OrderProfitClose($params['order_id'], $params['data']);
                                if($ret['code'] == 0)
                                {
                                    // 释放优惠券
                                    $ret = CouponService::CouponRelease($params['order_id'], $params['data']);
                                }
                                break;
                        }
                    }
                    break;

                // 订单提交前
                case 'plugins_service_buy_order_insert_begin' :
                    if(!empty($params['goods']))
                    {
                        // 订单提交校验
                        $ret = OrderService::OrderInsertBeginCheck($params);
                    }
                    break;

                //  订单提交成功
                case 'plugins_service_buy_order_insert_end' :
                    if(!empty($params['order_id']))
                    {
                        // 返现订单生成
                        $ret = ProfitService::ProfitOrderInsert($params, $this->plugins_config);
                    }
                    break;

                // 订单售后审核成功
                case 'plugins_service_order_aftersale_audit_handle_end' :
                    $ret = ProfitService::OrderChange($params, $this->plugins_config);
                    break;

                // 商品详情
                case 'plugins_view_goods_detail_panel_bottom' :
                    $ret = $this->GoodsDetailCoupinView($params);
                    break;

                // 商品标题icon
                case 'plugins_view_goods_detail_title' :
                    $ret = $this->GoodsDetailTitleIcon($params);
                    break;

                // diyapi初始化
                case 'plugins_service_diyapi_init_data' :
                    $this->DiyApiInitDataHandle($params);
                    break;
            }
            return $ret;
        }
    }

    /**
     * diyapi初始化
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function DiyApiInitDataHandle($params = [])
    {
        // 页面链接
        if(isset($params['data']['page_link_list']) && is_array($params['data']['page_link_list']))
        {
            foreach($params['data']['page_link_list'] as &$lv)
            {
                if(isset($lv['data']) && isset($lv['type']) && $lv['type'] == 'plugins')
                {
                    $lv['data'][] = [
                        'name'  => '优购返现',
                        'type'  => 'excellentbuyreturntocash',
                        'data'  => [
                            ['name'=>'返现列表', 'page'=>'/pages/plugins/excellentbuyreturntocash/profit/profit'],
                        ],
                    ];
                    break;
                }
            }
        }
    }

    /**
     * 商品标题icon
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-26
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function GoodsDetailTitleIcon($params = [])
    {
        // 基础配置信息
        $html = '';

        // 返现
        $ret = BaseService::CashGoodsDetailTitleIcon($params, $this->plugins_config);
        if($ret['code'] == 0)
        {
            $html .= '<span class="am-badge am-radius plugins-excellentbuyreturntocash-goods-title-cash-icon am-margin-right-xs">'.$this->plugins_config['goods_detail_title_cash_icon'].'</span>';
        }

        // 返现
        $ret = BaseService::CouponGoodsDetailTitleIcon($params, $this->plugins_config);
        if($ret['code'] == 0)
        {
            $html .= '<span class="am-badge am-radius plugins-excellentbuyreturntocash-goods-title-coupon-icon am-margin-right-xs">'.$this->plugins_config['goods_detail_title_coupon_icon'].'</span>';
        }
        return $html;
    }

    /**
     * 商品详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function GoodsDetailCoupinView($params = [])
    {
        $ret = CouponService::GoodsCouponList($params, $this->plugins_config);
        MyViewAssign('coupon_list', $ret['data']);
        MyViewAssign('plugins_base', $this->plugins_config);
        return MyView('../../../plugins/excellentbuyreturntocash/view/index/public/goods_detail_panel');
    }

    /**
     * 用户中心左侧菜单处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function UserCenterLeftMenuHandle($params = [])
    {
        if(isset($this->plugins_config['is_enable_cach_order']) && $this->plugins_config['is_enable_cach_order'] == 1)
        {
            $params['data']['business']['item'][] = [
                'name'      =>  '优购返现',
                'url'       =>  PluginsHomeUrl('excellentbuyreturntocash', 'profit', 'index'),
                'contains'  =>  ['excellentbuyreturntocashprofitindex'],
                'is_show'   =>  1,
                'icon'      =>  'am-icon-random',
            ];
        }
    }

    /**
     * 顶部小导航右侧-我的商城
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommonTopNavRightMenuHandle($params = [])
    {
        if(isset($this->plugins_config['is_enable_cach_order']) && $this->plugins_config['is_enable_cach_order'] == 1)
        {
            array_push($params['data'][1]['items'], [
                'name'  => '优购返现',
                'url'   => PluginsHomeUrl('excellentbuyreturntocash', 'profit', 'index'),
            ]);
        }
    }
}
?>