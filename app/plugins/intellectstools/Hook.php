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
namespace app\plugins\intellectstools;

use think\facade\Db;
use app\service\UserService;
use app\service\GoodsCategoryService;
use app\service\GoodsCartService;
use app\plugins\intellectstools\service\BaseService;
use app\plugins\intellectstools\service\OrderBaseService;
use app\plugins\intellectstools\service\OrderNoteService;
use app\plugins\intellectstools\service\GoodsNoteService;
use app\plugins\intellectstools\service\CommentsDataService;
use app\plugins\intellectstools\service\GoodsBeautifyService;
use app\plugins\intellectstools\service\GoodsInventoryService;
use app\plugins\intellectstools\service\UserNoteService;

/**
 * 智能工具箱 - 钩子入口
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class Hook
{
    // 配置信息
    private $plugins_config;

    // 当前模块/控制器/方法
    private $module_name;
    private $controller_name;
    private $action_name;
    private $mc;
    private $mca;

    private $pluginsname;
    private $pluginscontrol;
    private $pluginsaction;
    private $pc;
    private $pca;

    // 下单是否开启快捷选择留言
    private $is_buy_user_note_fast_choice;

    // 用户订单复购，备注
    private $is_index_order_buy_again_web;
    private $is_order_note_user;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date     2021-05-07
     * @param    [array]                    $params [输入参数]
     */
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();
            $this->mc = $this->module_name.$this->controller_name;
            $this->mca = $this->module_name.$this->controller_name.$this->action_name;

            // 插件
            $this->pluginsname = PluginsRequestName();
            $this->pluginscontrol = PluginsRequestController();
            $this->pluginsaction = PluginsRequestAction();
            $this->pc = $this->pluginsname.$this->pluginscontrol;
            $this->pca = $this->pluginsname.$this->pluginscontrol.$this->pluginsaction;

            // 是否引入多商户样式
            $is_shop_style = $this->module_name == 'index' && in_array($this->pc, ['intellectstoolsorder', 'intellectstoolsgoods']);

            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            // 商品想提示信息展示
            $is_goods_msg = !empty($this->plugins_config['goods_detail_content_top_tips_msg']) && in_array($this->mca, ['indexgoodsindex']);

            // 地址自动识别
            $is_user_address_discern = (isset($this->plugins_config['is_user_address_discern']) && $this->plugins_config['is_user_address_discern'] == 1 && $this->mca == 'indexuseraddresssaveinfo');

            // 开启商品分类批量移动
            $is_goods_top_category_move = isset($this->plugins_config['is_goods_top_category_move']) && $this->plugins_config['is_goods_top_category_move'] == 1;

            // 开启商品批量上下架
            $is_goods_top_is_shelves = isset($this->plugins_config['is_goods_top_is_shelves']) && $this->plugins_config['is_goods_top_is_shelves'] == 1;
            $is_goods_top_is_shelves_shop = $is_goods_top_is_shelves && isset($this->plugins_config['is_goods_top_is_shelves_shop']) && $this->plugins_config['is_goods_top_is_shelves_shop'] == 1;

            // 开启后台库存预警
            $is_admin_inventory_early_warning = isset($this->plugins_config['is_admin_inventory_early_warning']) && $this->plugins_config['is_admin_inventory_early_warning'] == 1;

            // 商品详情页面是否默认选中第一个有效规格
            $is_goods_detail_selected_first_spec = (isset($this->plugins_config['is_goods_detail_selected_first_spec']) && $this->plugins_config['is_goods_detail_selected_first_spec'] == 1 && $this->mca == 'indexgoodsindex');

            // 搜索右侧购物车入口
            $is_search_right_cart = (isset($this->plugins_config['is_search_right_cart']) && $this->plugins_config['is_search_right_cart'] == 1 && $this->mca != 'indexcartindex');

            // 商品上方鼠标悬浮操作购物车和收藏
            $is_goods_above_suspension_cart_favor = isset($this->plugins_config['is_goods_above_suspension_cart_favor']) && $this->plugins_config['is_goods_above_suspension_cart_favor'] == 1;

            // 订单售后显示客服
            $is_order_aftersale_service_show = isset($this->plugins_config['is_order_aftersale_service_show']) && $this->plugins_config['is_order_aftersale_service_show'] == 1 && in_array($this->mca, ['indexorderaftersaledetail', 'apiorderaftersaleaftersale']);

            // 用户端展示再次购买
            $is_index_order_buy_again_cart = isset($this->plugins_config['is_index_order_buy_again_cart']) && $this->plugins_config['is_index_order_buy_again_cart'] == 1;
            $is_index_order_buy_again_buy = isset($this->plugins_config['is_index_order_buy_again_buy']) && $this->plugins_config['is_index_order_buy_again_buy'] == 1;
            $is_index_order_buy_again = ($is_index_order_buy_again_cart || $is_index_order_buy_again_buy) && (isset($this->plugins_config['is_index_order_buy_again']) && $this->plugins_config['is_index_order_buy_again'] == 1);
            $this->is_index_order_buy_again_web = $is_index_order_buy_again && in_array($this->mca, ['indexorderindex', 'indexorderdetail']);
            // 用户端订单管理展示备注信息
            $this->is_order_note_user = isset($this->plugins_config['is_order_note_user']) && $this->plugins_config['is_order_note_user'] == 1;

            // 订单列表和详情支付仅可以选择下单时候选择的支付方式
            $is_order_pay_only_can_buy_payment = isset($this->plugins_config['is_order_pay_only_can_buy_payment']) && $this->plugins_config['is_order_pay_only_can_buy_payment'] == 1 && in_array($this->mca, ['indexorderindex', 'indexorderdetail']);

            // 商品页面展示评论入口
            $is_goods_detail_comments_add = isset($this->plugins_config['is_goods_detail_comments_add']) && $this->plugins_config['is_goods_detail_comments_add'] == 1 && in_array($this->mca, ['indexgoodsindex']);

            // 订单确认页面留言快捷选择
            $this->is_buy_user_note_fast_choice = isset($this->plugins_config['is_buy_user_note_fast_choice']) && $this->plugins_config['is_buy_user_note_fast_choice'] == 1 && !empty($this->plugins_config['buy_user_note_fast_choice_data']);
            $is_buy_user_note_fast_choice_web = $this->is_buy_user_note_fast_choice && in_array($this->mca, ['indexbuyindex']);

            // 首页轮播右侧商品
            $is_home_banner_right_goods = isset($this->plugins_config['is_home_banner_right_goods']) && $this->plugins_config['is_home_banner_right_goods'] == 1 && !empty($this->plugins_config['appoint_home_banner_right_goods_list']) && is_array($this->plugins_config['appoint_home_banner_right_goods_list']) && !empty($this->plugins_config['home_banner_right_goods_data']) && is_array($this->plugins_config['home_banner_right_goods_data']) && in_array($this->mca, ['indexindexindex']);

            // 商品页购买按钮跳转链接
            $is_user_goods_detail_show_buy_btn_run = isset($this->plugins_config['is_user_goods_detail_show_buy_btn_run']) && $this->plugins_config['is_user_goods_detail_show_buy_btn_run'] == 1;
   
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 公共css
                case 'plugins_css' :
                    $ret = [];
                    // 商品详情提示信息、地址自动识别、搜索右侧购物车入口、商品页面展示评论入口
                    if($is_goods_msg || $is_user_address_discern || $is_search_right_cart || $is_goods_above_suspension_cart_favor || $is_order_aftersale_service_show || $is_goods_detail_comments_add || $is_buy_user_note_fast_choice_web)
                    {
                        $ret[] = 'static/plugins/intellectstools/css/index/style.css';
                    }
                    // 样式图片处理
                    if(isset($this->plugins_config['is_images_height_fixed']) && $this->plugins_config['is_images_height_fixed'] == 1)
                    {
                        $ret[] = 'static/plugins/intellectstools/css/index/images_style.css';
                    }
                    // 引入多商户样式
                    if($is_shop_style)
                    {
                        $ret[] = 'static/plugins/shop/css/index/public/shop_admin.css';
                    }
                    // 首页轮播右侧商品
                    if($is_home_banner_right_goods)
                    {
                        $ret[] = 'static/plugins/intellectstools/css/index/home_banner_right.css';
                    }
                    break;

                // 公共js
                case 'plugins_js' :
                    $ret = [];
                    // 搜索右侧展示购物车
                    if($is_search_right_cart || $is_goods_above_suspension_cart_favor || $this->is_index_order_buy_again_web || $is_order_pay_only_can_buy_payment || $is_buy_user_note_fast_choice_web)
                    {
                        $ret[] = 'static/plugins/intellectstools/js/index/style.js';
                    }
                    // 地址自动识别
                    if($is_user_address_discern)
                    {
                        $ret[] = 'static/plugins/intellectstools/js/index/address_discern.js';
                    }
                    // 商品详情页面是否默认选中第一个有效规格
                    if($is_goods_detail_selected_first_spec)
                    {
                        $ret[] = 'static/plugins/intellectstools/js/index/goods_spec_selected.js';
                    }
                    // 引入多商户js
                    if($is_shop_style)
                    {
                        $ret[] = 'static/plugins/shop/js/index/common.js';
                    }
                    // 商品批量上下架
                    if($this->pca == 'shopgoodsindex' && $is_goods_top_is_shelves_shop)
                    {
                        $ret[] = 'static/plugins/intellectstools/js/public/goods_is_shelves.js';
                    }
                    // 首页轮播右侧商品
                    if($is_home_banner_right_goods)
                    {
                        $ret[] = 'static/plugins/intellectstools/js/index/home_banner_right.js';
                    }
                    break;

                // 后台公共css
                case 'plugins_admin_css' :
                    $ret = [];
                    if($this->mca == 'admingoodssaveinfo')
                    {
                        $ret[] = 'static/plugins/intellectstools/css/admin/comments.goodssaveinfo.css';
                    }
                    if($this->mca == 'admingoodsindex' && $is_goods_top_category_move)
                    {
                        $ret[] = 'static/plugins/intellectstools/css/admin/public/goods_category_move.css';
                    }
                    if($is_admin_inventory_early_warning && $this->mca == 'adminindexinit')
                    {
                        $ret[] = 'static/plugins/intellectstools/css/admin/public/inventory_early_warning.css';
                    }
                    break;

                // 后台公共js
                case 'plugins_admin_js' :
                    $ret = [];
                    if($this->mca == 'admingoodsindex' && $is_goods_top_category_move)
                    {
                        $ret[] = 'static/plugins/intellectstools/js/admin/public/goods_category_move.js';
                    }
                    if($this->mca == 'admingoodsindex' && $is_goods_top_is_shelves)
                    {
                        $ret[] = 'static/plugins/intellectstools/js/public/goods_is_shelves.js';
                    }
                    break;

                // 公共页面底部内容
                case 'plugins_common_page_bottom' :
                    if($is_user_address_discern || $is_order_pay_only_can_buy_payment)
                    {
                        $ret = $this->ViewPageBottomContent($params);
                    }
                    break;

                // 订单列表数据处理
                case 'plugins_service_order_list_handle_end' :
                    $this->OrderListDataHandle($params);
                    break;

                // 商品列表操作
                case 'plugins_view_admin_goods_list_operate' :
                    $ret = $this->AdminGoodsListOperateButton($params);
                    break;

                // 订单列表操作
                case 'plugins_view_admin_order_list_operate' :
                    $ret = $this->AdminOrderListOperateButton($params);
                    break;

                // 后台商品保存页面
                case 'plugins_view_admin_goods_save' :
                    $ret = $this->AdminGoodsSaveView($params);
                    break;

                // 商品保存前处理
                case 'plugins_service_goods_save_handle' :
                    $ret = $this->GoodsSaveBeginHandle($params);
                    break;

                // 商品保存成功处理
                case 'plugins_service_goods_save_end' :
                    $ret = $this->GoodsSaveEndHandle($params);
                    break;

                // 订单列表商品列数据
                case 'plugins_view_admin_order_grid_goods' :
                    $ret = $this->AdminOrderListGoodsInfo($params);
                    break;

                // 商品列表基础信息列数据
                case 'plugins_view_admin_goods_grid_info' :
                    $ret = $this->AdminGoodsListBaseInfo($params);
                    break;

                // 系统初始化
                case 'plugins_service_system_begin' :
                    $this->SystemInitHandle($params);
                    break;

                // 商品详情右侧基础提示信息
                case 'plugins_view_goods_detail_right_content_inside_bottom' :
                    $ret = $this->GoodsDetailRightInsideButtonContent($params);
                    break;

                // 商品详情内容顶部
                case 'plugins_view_goods_detail_base_bottom' :
                    $ret = $this->GoodsDetailContentTopHandle($params);
                    break;

                // 商品接口数据
                case 'plugins_service_base_data_return_api_goods_detail' :
                    $ret = $this->GoodsResultHandle($params);
                    break;

                // 批量设置商品顶部操作按钮
                case 'plugins_view_admin_goods_top_operate' :
                    if($is_goods_top_category_move || $is_goods_top_is_shelves)
                    {
                        $ret = $this->AdminGoodsViewTopHtml($params);
                    }
                    break;

                // 批量设置商品顶部操作按钮 - 多商户
                case 'plugins_view_index_plugins_shop_goods_top_operate' :
                    if($is_goods_top_is_shelves_shop)
                    {
                        $ret = $this->PluginsShopGoodsViewTopHtml($params);
                    }
                    break;

                // 批量设置商品分类弹窗数据
                case 'plugins_view_admin_goods_content_inside_bottom' :
                    if($is_goods_top_category_move)
                    {
                        $ret = $this->AdminGoodsViewContentBottom($params);
                    }
                    break;

                // 订单列表操作 - 多商户
                case 'plugins_view_index_plugins_shop_order_list_operate' :
                    $ret = $this->ShopOrderListOperateButton($params);
                    break;

                // 订单列表商品列数据 - 多商户
                case 'plugins_view_index_plugins_shop_order_grid_goods' :
                    $ret = $this->ShopOrderListGoodsInfo($params);
                    break;

                // 商品列表操作 - 多商户
                case 'plugins_view_index_plugins_shop_goods_list_operate' :
                    $ret = $this->ShopGoodsListOperateButton($params);
                    break;

                // 商品列表基础信息列数据 - 多商户
                case 'plugins_view_index_plugins_shop_goods_grid_info' :
                    $ret = $this->ShopGoodsListBaseInfo($params);
                    break;

                // 后台首页库存预警 - 后台
                case 'plugins_admin_view_index_init_stats_base_top' :
                    if($is_admin_inventory_early_warning)
                    {
                        $ret = $this->AdminIndexInitInventoryEarlyWarning($params);
                    }
                    break;

                // 搜索右侧
                case 'plugins_view_common_search_right' :
                    // 购物车页面不展示
                    if($is_search_right_cart)
                    {
                        $ret = $this->SearchRight($params);
                    }
                    break;

                // 顶部小导航
                case 'plugins_service_header_navigation_top_right_handle' :
                    $this->NavTopRightHandle($params);
                    break;

                // 用户订单售后详情商品信息底部
                case 'plugins_view_user_orderaftersale_detail_goods_bottom' :
                    if(!empty($params['order']) && $is_order_aftersale_service_show)
                    {
                        $ret = $this->UserOrderAftersaleDetailGoodsBottom($params);
                    }
                    break;

                // 用户订单售后详情商品信息内部底部、申请页面
                case 'plugins_view_user_orderaftersale_detail_goods_inside_base_bottom' :
                    if(!empty($params['order']) && $is_order_aftersale_service_show)
                    {
                        $params['is_create'] = 1;
                        $ret = $this->UserOrderAftersaleDetailGoodsBottom($params);
                    }
                    break;

                // 用户订单售后详情接口数据
                case 'plugins_service_base_data_return_api_orderaftersale_aftersale' :
                    if(!empty($params['data']) && !empty($params['data']['order_data']) && $is_order_aftersale_service_show)
                    {
                        $this->OrderAftersaleCreatedService($params);
                    }
                    break;

                // 后台用户管理关联标签操作
                case 'plugins_view_admin_user_list_operate' :
                    if(in_array($this->mca, ['adminuserindex']) && !empty($params['data']))
                    {
                        $ret = $this->AdminUserListOperate($params);
                    }
                    break;

                // 用户数据列表处理结束
                case 'plugins_service_user_list_handle_end' :
                    $this->UserDataListHandle($params);
                    break;

                // 后台用户动态列表
                case 'plugins_module_form_admin_user_index' :
                case 'plugins_module_form_admin_user_detail' :
                case 'plugins_module_form_admin_user_excelexport' :
                    $this->AdminFormUserHandle($params);
                    break;

                // 商品模块鼠标放上去悬浮操作（加入购物车和收藏）
                case 'plugins_view_module_goods_inside_top' :
                    if(!empty($params['goods']) && !empty($params['goods_id']) && $is_goods_above_suspension_cart_favor)
                    {
                        $ret = $this->ViewModuleGoodsInsideTopHandle($params);
                    }
                    break;

                // 用户端订单管理操作
                case 'plugins_view_index_order_list_operate' :
                    if(($this->is_index_order_buy_again_web || $this->is_order_note_user) && !empty($params['data']))
                    {
                        $ret = $this->IndexOrderListOperate($params);
                    }
                    break;

                // 用户端订单详情操作
                case 'plugins_view_index_order_detail_operate' :
                    if(($this->is_index_order_buy_again_web || $this->is_order_note_user) && !empty($params['data']))
                    {
                        $ret = $this->IndexOrderListOperate($params);
                    }
                    break;

                // 用户订单列表 - api接口
                case 'plugins_service_base_data_return_api_order_index' :
                    if(!empty($params['data']) && !empty($params['data']['data']) && $is_index_order_buy_again)
                    {
                        $this->ApiUserOrderListIndexHandle($params);
                    }
                    break;

                // 商品页面tabs内评价顶部钩子
                case 'plugins_view_goods_detail_tabs_comments_top' :
                    if($is_goods_detail_comments_add)
                    {
                        $ret = $this->GoodsDetailTabsCommentsTop($params);
                    }
                    break;

                // 订单确认页面留言内钩子
                case 'plugins_view_buy_user_note_bottom' :
                    if($is_buy_user_note_fast_choice_web)
                    {
                        $ret = $this->BuyUserNoteBottomHtml($params);
                    }
                    break;

                // 购物车接口数据
                case 'plugins_service_base_data_return_api_cart_index' :
                    $this->ApiCartResultHandle($params);
                    break;

                // 下单接口数据
                case 'plugins_service_base_data_return_api_buy_index' :
                    $this->ApiBuyResultHandle($params);
                    break;

                // 订单确认页面商品内底部钩子
                case 'plugins_view_buy_group_goods_inside_bottom' :
                    $ret = $this->ViewBuyGroupGoodsInsideBottomHtml($params);
                    break;

                // 购物车页面商品里边底部钩子
                case 'plugins_view_cart_list_inside_bottom' :
                    $ret = $this->ViewCartListInsideBottomHtml($params);
                    break;

                // 首页轮播上聚合内容右侧
                case 'plugins_view_home_banner_mixed_bottom' :
                    if($is_home_banner_right_goods)
                    {
                        $ret = $this->ViewHomeBannerMixedRightHtml($params);
                    }
                    break;

                // 商品详情页面导航购买按钮处理
                case 'plugins_service_goods_buy_nav_button_handle' :
                    if($is_user_goods_detail_show_buy_btn_run)
                    {
                        $this->GoodsDetailBuyNavButtonContent($params);
                    }
                    break;
            }
            return $ret;
        }
    }

    /**
     * 商品详情页面导航购买按钮处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-02-19
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsDetailBuyNavButtonContent($params = [])
    {
        if(!empty($params['goods']) && !empty($params['data']) && is_array($params['data']) && !empty($params['goods']) && !empty($params['goods']['plugins_intellectstools_buy_btn_link_name']) && !empty($params['goods']['plugins_intellectstools_buy_btn_link_url']))
        {
            $index = false;
            foreach($params['data'] as $k=>$v)
            {
                if(isset($v['type']) && $v['type'] == 'cart')
                {
                    $index = $k;
                }
            }
            $name = $params['goods']['plugins_intellectstools_buy_btn_link_name'];
            $btn = [
                'color' => 'second',
                'type'  => in_array(APPLICATION_CLIENT_TYPE, ['pc', 'h5', 'ios', 'android']) ? 'url' : 'copy',
                'value' => $params['goods']['plugins_intellectstools_buy_btn_link_url'],
                'title' => $name,
                'name'  => $name,
            ];
            if($index === false)
            {
                $params['data'][] = $btn;
            } else {
                $params['data'][$index] = $btn;
            }
        }
    }

    /**
     * 首页轮播上聚合内容右侧
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ViewHomeBannerMixedRightHtml($params = [])
    {
        $goods_list = BaseService::HomeBannerRightGoodsData($this->plugins_config);
        if(!empty($goods_list))
        {
            return MyView('../../../plugins/intellectstools/view/index/public/home_banner_mixed_right', ['goods_list' => $goods_list]);
        }
    }

    /**
     * 购物车页面商品里边底部钩子
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ViewCartListInsideBottomHtml($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/index/public/cart_list_inside_bottom', ['plugins_config' => $this->plugins_config]);
    }

    /**
     * 订单确认页面商品内底部钩子
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ViewBuyGroupGoodsInsideBottomHtml($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/index/public/buy_goods_bottom', ['plugins_config' => $this->plugins_config]);
    }

    /**
     * 下单接口数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ApiBuyResultHandle($params = [])
    {
        if(!empty($params['data']))
        {
            // 数据容器
            $result = [];

            // 快捷选择留言内容
            if($this->is_buy_user_note_fast_choice)
            {
                $result['note_fast_data'] = $this->plugins_config['buy_user_note_fast_choice_data'];
            }

            // 页面底部说明
            if(!empty($this->plugins_config['buy_view_goods_bottom_desc']))
            {
                $result['bottom_desc'] = $this->plugins_config['buy_view_goods_bottom_desc'];
            }

            // 假如返回数据
            if(!empty($result))
            {
                $params['data']['plugins_intellectstools_data'] = $result;
            }
        }
    }

    /**
     * 购物车接口数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ApiCartResultHandle($params = [])
    {
        if(!empty($params['data']))
        {
            // 页面底部说明
            if(!empty($this->plugins_config['cart_view_bottom_desc']))
            {
                $params['data']['plugins_intellectstools_data'] = [
                    'bottom_desc' => $this->plugins_config['cart_view_bottom_desc'],
                ];
            }
        }
    }

    /**
     * 订单确认页面留言内钩子、快捷选择留言内容
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function BuyUserNoteBottomHtml($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/index/public/buy_user_note', ['plugins_config' => $this->plugins_config]);
    }

    /**
     * 用户订单列表 - api接口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-10-20
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ApiUserOrderListIndexHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['data']) && is_array($params['data']['data']))
        {
            foreach($params['data']['data'] as &$v)
            {
                // 再次购买数据
                $continue_buy = BaseService::OrderBuyAgainData($v['items']);
                if(!empty($continue_buy))
                {
                    $v['plugins_intellectstools_data'] = [
                        'is_buy_again_cart'  => isset($this->plugins_config['is_index_order_buy_again_cart']) && $this->plugins_config['is_index_order_buy_again_cart'] == 1 ? 1 : 0,
                        'is_buy_again_buy'   => isset($this->plugins_config['is_index_order_buy_again_buy']) && $this->plugins_config['is_index_order_buy_again_buy'] == 1 ? 1 : 0,
                        'continue_buy_data'  => $continue_buy,
                    ];
                }
            }
        }
    }

    /**
     * 商品页面tabs内评价顶部评论
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-10-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsDetailTabsCommentsTop($params = [])
    {
        if(!empty($params['goods_id']))
        {
            return MyView('../../../plugins/intellectstools/view/index/public/goods_detail_comments', ['goods_id'=>$params['goods_id']]);
        }
    }

    /**
     * 用户端订单列表操作
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-31
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function IndexOrderListOperate($params = [])
    {
        $html = '';
        if(!empty($params['data']))
        {
            // 按钮类型
            $is_list_btn = $params['hook_name'] == 'plugins_view_index_order_list_operate' ? 1 : 0;

            // 订单复购
            if($this->is_index_order_buy_again_web && !empty($params['data']['items']))
            {
                $data = BaseService::OrderBuyAgainData($params['data']['items']);
                if(!empty($data))
                {
                    $html .= MyView('../../../plugins/intellectstools/view/index/order/user_button', [
                        'plugins_data'    => $data,
                        'plugins_config'  => $this->plugins_config,
                        'is_list_btn'     => $is_list_btn,
                    ]);
                }
            }
            // 订单备注展示
            if($this->is_order_note_user && !empty($params['data']['plugins_intellectstools_note_data']))
            {
                $html .= MyView('../../../plugins/intellectstools/view/index/orderuser/user_button', [
                    'plugins_data'    => $params['data'],
                    'plugins_config'  => $this->plugins_config,
                    'is_list_btn'     => $is_list_btn,
                    'btn_name'        => empty($this->plugins_config['order_note_user_name']) ? MyLang('note_title') : $this->plugins_config['order_note_user_name'],
                ]);
            }
        }
        return $html;
    }

    /**
     * 商品模块鼠标放上去悬浮操作（加入购物车和收藏）
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-05-04
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ViewModuleGoodsInsideTopHandle($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/index/public/module_goods_above_suspension_operate', [
            'plugins_data'    => $params,
            'plugins_config'  => $this->plugins_config,
        ]);
    }

    /**
     * 后台用户动态列表标签信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function AdminFormUserHandle($params = [])
    {
        if(!empty($this->plugins_config) && isset($this->plugins_config['is_user_note']) && $this->plugins_config['is_user_note'] == 1)
        {
            array_splice($params['data']['form'], 3, 0, [
                [
                    'label'         => '备注',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/intellectstools/view/admin/user/module_note',
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => 'id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'ModuleWhereValueUserNoteInfo',
                        'where_object_custom'   => $this,
                    ],
                ]
            ]);
        }
    }

    /**
     * 动态数据用户列表条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-26
     * @desc    description
     * @param   [string]          $value    [条件值]
     * @param   [array]           $params   [输入参数]
     */
    public function ModuleWhereValueUserNoteInfo($value, $params = [])
    {
        if(!empty($value))
        {
            // 获取id
            $ids = Db::name('PluginsIntellectstoolsUserNote')->where('content', 'like', '%'.$value.'%')->column('user_id');

            // 避免空条件造成无效的错觉
            return empty($ids) ? [0] : $ids;
        }
        return $value;
    }

    /**
     * 用户数据列表处理结束
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-06-05
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function UserDataListHandle($params = [])
    {
        if(!empty($params['data']))
        {
            $user_note = UserNoteService::UserNoteDataList(array_column($params['data'], 'id'));
            if(!empty($user_note))
            {
                foreach($params['data'] as &$v)
                {
                    if(array_key_exists($v['id'], $user_note))
                    {
                        $v['plugins_intellectstools_note_data'] = $user_note[$v['id']];
                    }
                }
            }
        }
    }

    /**
     * 用户管理列表操作
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-05-04
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function AdminUserListOperate($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/admin/user/button', [
            'plugins_data'    => $params['data'],
            'plugins_config'  => $this->plugins_config,
        ]);
    }

    /**
     * 订单售后接口展示客服信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-01-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function OrderAftersaleCreatedService($params = [])
    {
        $params['data']['plugins_intellectstools_data'] = BaseService::UserOrderAftersaleDServiceData($params['data']['order_data'], $this->plugins_config);
    }

    /**
     * 订单售后页面提示信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-01-09
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function UserOrderAftersaleDetailGoodsBottom($params = [])
    {
        $data = BaseService::UserOrderAftersaleDServiceData($params['order'], $this->plugins_config);
        $data['params'] = $params;
        return MyView('../../../plugins/intellectstools/view/index/public/orderaftersale_detail_goods_bottom', $data);
    }

    /**
     * 顶部小导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function NavTopRightHandle($params = [])
    {
        // 是否去除购物车导航
        if(isset($this->plugins_config['is_del_nav_top_right_cart']) && $this->plugins_config['is_del_nav_top_right_cart'] == 1)
        {
            if(!empty($params['data']) && is_array($params['data']))
            {
                foreach($params['data'] as $k=>$v)
                {
                    if(isset($v['type']) && $v['type'] == 'cart')
                    {
                        unset($params['data'][$k]);
                    }
                }
                $params['data'] = array_values($params['data']);
            }
        }
    }

    /**
     * 搜索右侧
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function SearchRight($params = [])
    {
        // 列表
        $cart_list = GoodsCartService::GoodsCartList(['user'=>UserService::LoginUserInfo()]);
        MyViewAssign('cart_list', $cart_list['data']);
        // 基础
        $cart_base = [
            'total_price'   => empty($cart_list['data']) ? '0.00' : PriceNumberFormat(array_sum(array_column($cart_list['data'], 'total_price'))),
            'cart_count'    => empty($cart_list['data']) ? 0 : array_sum(array_column($cart_list['data'], 'stock')),
            'ids'           => empty($cart_list['data']) ? '' : implode(',', array_column($cart_list['data'], 'id')),
        ];
        MyViewAssign('cart_base', $cart_base);
        return MyView('../../../plugins/intellectstools/view/index/public/search_right_cart');
    }

    /**
     * 后台首页库存预警
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-28
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function AdminIndexInitInventoryEarlyWarning($params = [])
    {
        $warehouse_data = GoodsInventoryService::WarehouseGoodsInventoryEarlyWarning($this->plugins_config);
        MyViewAssign('warehouse_data', $warehouse_data);
        return MyView('../../../plugins/intellectstools/view/admin/public/inventory_early_warning');
    }

    /**
     * 商品列表基础信息列数据 - 多商户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ShopGoodsListBaseInfo($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['id']))
        {
            // 备注信息
            $note_data = GoodsNoteService::GoodsNoteData($params['data']['id']);
            if(!empty($note_data) && !empty($note_data['content']))
            {
                $note_data['content'] = explode("\n", $note_data['content']);
            }
            MyViewAssign('plugins_goods_data', $params['data']);
            MyViewAssign('plugins_note_data', $note_data);
            MyViewAssign('plugins_config', $this->plugins_config);
            return MyView('../../../plugins/intellectstools/view/index/goods/goods_info_grid');
        }
    }

    /**
     * 商品管理列表操作 - 多商户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function ShopGoodsListOperateButton($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/index/goods/button', [
            'plugins_data'    => empty($params['data']) ? [] : $params['data'],
            'plugins_config'  => $this->plugins_config,
        ]);
    }

    /**
     * 订单列表商品列数据 - 多商户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ShopOrderListGoodsInfo($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['plugins_intellectstools_note_data']))
        {
            // 备注信息
            $note_data = $params['data']['plugins_intellectstools_note_data'];
            if(!empty($note_data['content']))
            {
                $note_data['content'] = explode("\n", $note_data['content']);
            }
            MyViewAssign('plugins_order_data', $params['data']);
            MyViewAssign('plugins_note_data', $note_data);
            MyViewAssign('plugins_config', $this->plugins_config);
            return MyView('../../../plugins/intellectstools/view/index/order/order_goods_grid');
        }
    }

    /**
     * 订单管理列表操作 - 多商户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function ShopOrderListOperateButton($params = [])
    {
        // 订单信息
        $plugins_data = empty($params['data']) ? [] : $params['data'];
        if(!empty($plugins_data['id']) && isset($plugins_data['status']))
        {
            // 订单修改信息
            $operate_edit_button_info = OrderBaseService::OrderOperateEditInfo($this->plugins_config, $plugins_data);
            return MyView('../../../plugins/intellectstools/view/index/order/shop_button', [
                'operate_edit_button_info'  => $operate_edit_button_info,
                'plugins_data'              => $plugins_data,
                'plugins_config'            => $this->plugins_config,
            ]);
        }
    }

    /**
     * 批量设置商品分类弹窗数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function AdminGoodsViewContentBottom($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/admin/goods/content_bottom', [
            'goods_category_list'   => GoodsCategoryService::GoodsCategoryAll(),
        ]);
    }

    /**
     * 批量设置商品顶部操作按钮
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function AdminGoodsViewTopHtml($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/admin/goods/top_operate', [
                'plugins_config' => $this->plugins_config,
            ]);
    }

    /**
     * 批量设置商品顶部操作按钮 - 多商户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function PluginsShopGoodsViewTopHtml($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/index/goods/top_operate', [
                'plugins_config' => $this->plugins_config,
            ]);
    }

    /**
     * 商品接口数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function GoodsResultHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['goods']))
        {
            // 基础提示
            $base_bottom = [];
            if(!empty($this->plugins_config['goods_detail_base_tips_msg']))
            {
                $base_bottom['title'] = empty($this->plugins_config['goods_detail_base_tips_title']) ? null : $this->plugins_config['goods_detail_base_tips_title'];
                $base_bottom['msg'] = $this->plugins_config['goods_detail_base_tips_msg'];
            }

            // 详情顶部内容
            $content_top = [];
            if(!empty($this->plugins_config['goods_detail_content_top_tips_msg']))
            {
                $content_top['title'] = empty($this->plugins_config['goods_detail_content_top_tips_title']) ? null : $this->plugins_config['goods_detail_content_top_tips_title'];
                $content_top['msg'] = $this->plugins_config['goods_detail_content_top_tips_msg'];
            }

            // 是否开启商品评论添加
            $is_comments_add = (isset($this->plugins_config['is_goods_detail_comments_add']) && $this->plugins_config['is_goods_detail_comments_add'] == 1) ? 1 : 0;

            // 存在数据则接口返回
            if(!empty($base_bottom) || !empty($content_top) || $is_comments_add == 1)
            {
                $params['data']['plugins_intellectstools_data'] = [
                    'base_bottom'      => $base_bottom,
                    'content_top'      => $content_top,
                    'is_comments_add'  => $is_comments_add,
                ];
            }
        }
    }

    /**
     * 商品详情内容顶部
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-02-08
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsDetailContentTopHandle($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/index/public/goods_detail_content_top_tips', [
            'plugins_config'    => $this->plugins_config,
        ]);
    }

    /**
     * 商品详情右侧基础提示信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-02-27
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsDetailRightInsideButtonContent($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/index/public/goods_detail_base_bottom_tips', [
            'plugins_config'    => $this->plugins_config,
        ]);
    }

    /**
     * 系统初始化处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-05
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function SystemInitHandle($params = [])
    {
        // 必须存在输入参数
        if(!empty($params['params']))
        {
            // web端和接口请求
            if($this->mca == 'indexgoodsindex')
            {
                $goods_id = empty($params['params']['id']) ? 0 : $params['params']['id'];
            } else if($this->mca == 'apigoodsdetail')
            {
                $goods_id = empty($params['params']['goods_id']) ? (empty($params['params']['id']) ? 0 : $params['params']['id']) : $params['params']['goods_id'];
            }
            if(!empty($goods_id))
            {
                // 自动增加销量
                if(!empty($this->plugins_config['auto_inc_sales_number']))
                {
                    $ret = GoodsBeautifyService::GoodsAutoIncSales($goods_id, $this->plugins_config, $params['params']);
                }

                // 处理用户评论数据
                $ret = CommentsDataService::GoodsCommentsHandle($goods_id, $this->plugins_config, $params['params']);
            }
        }

        // 是否开启强制用户登录、get和post（非ajax）
        if(isset($this->plugins_config['is_user_force_login']) && $this->plugins_config['is_user_force_login'] == 1 && $this->module_name == 'index' && (IS_GET || (IS_POST && !IS_AJAX)))
        {
            // 主页面
            $mp = [
                'indexuserlogininfo',
                'indexuserreginfo',
                'indexuserforgetpwdinfo',
                'indexusermodallogininfo',
                'indexuseruserverifyentry',
                'indexagreementindex',
            ];
            // 插件+控制器
            $plc_arr = [
                'themeswitchindex',
                'thirdpartyloginindex',
                'weixinwebauthorizationauth',
                'weixinwebauthorizationpay',
                'weixinwebauthorizationindex',
                'touristbuyindex',
                'realstorelogin',
                'realstorecenter',
                'shoplogin',
                'shopcenter',
                'erplogin',
                'erpcenter',
                'chatlogin',
                'chatwork',
                'chatindex',
            ];
            if(!in_array($this->mca, $mp) && !in_array($this->mc, $plc_arr))
            {
                $user = UserService::LoginUserInfo();
                if(empty($user))
                {
                    $request_params = [];
                    if(!empty($params['params']) && !empty($params['params']['referrer']))
                    {
                        $request_params['referrer'] = $params['params']['referrer'];
                    }
                    die(header('location:'.MyUrl('index/user/logininfo', $request_params)));
                }
            }
        }
    }

    /**
     * 商品列表基础信息列数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function AdminGoodsListBaseInfo($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['id']))
        {
            // 备注信息
            $note_data = GoodsNoteService::GoodsNoteData($params['data']['id']);
            if(!empty($note_data) && !empty($note_data['content']))
            {
                $note_data['content'] = explode("\n", $note_data['content']);
            }
            return MyView('../../../plugins/intellectstools/view/admin/goods/goods_info_grid', [
                'plugins_goods_data'  => $params['data'],
                'plugins_note_data'   => $note_data,
                'plugins_config'      => $this->plugins_config,
            ]);
        }
    }

    /**
     * 订单列表商品列数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function AdminOrderListGoodsInfo($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['plugins_intellectstools_note_data']))
        {
            // 备注信息
            $note_data = $params['data']['plugins_intellectstools_note_data'];
            if(!empty($note_data['content']))
            {
                $note_data['content'] = explode("\n", $note_data['content']);
            }
            return MyView('../../../plugins/intellectstools/view/admin/order/order_goods_grid', [
                'plugins_order_data'  => $params['data'],
                'plugins_note_data'   => $note_data,
                'plugins_config'      => $this->plugins_config,
            ]);
        }
    }

    /**
     * 商品保存后处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsSaveEndHandle($params = [])
    {
        if(!empty($params['goods_id']) && !empty($params['params']) && isset($this->plugins_config['is_goods_alone_comments_config']) && $this->plugins_config['is_goods_alone_comments_config'] == 1)
        {
            CommentsDataService::CommentsGoodsConfigSave($params['goods_id'], $params['params']);
        }
    }

    /**
     * 商品保存前处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsSaveBeginHandle($params = [])
    {
        if(!empty($params['params']))
        {
            // 数据美化
            if(isset($this->plugins_config['is_goods_beautify']) && $this->plugins_config['is_goods_beautify'] == 1)
            {
                $params['data']['access_count'] = isset($params['params']['access_count']) ? intval($params['params']['access_count']) : 0;
                $params['data']['sales_count'] = isset($params['params']['sales_count']) ? intval($params['params']['sales_count']) : 0;
            }

            // 商品页购买按钮跳转链接
            if(isset($this->plugins_config['is_admin_goods_saveinfo_buy_btn_config']) && $this->plugins_config['is_admin_goods_saveinfo_buy_btn_config'] == 1)
            {
                $params['data']['plugins_intellectstools_buy_btn_link_name'] = empty($params['params']['plugins_intellectstools_buy_btn_link_name']) ? '' : $params['params']['plugins_intellectstools_buy_btn_link_name'];
                $params['data']['plugins_intellectstools_buy_btn_link_url'] = empty($params['params']['plugins_intellectstools_buy_btn_link_url']) ? '' : htmlspecialchars_decode($params['params']['plugins_intellectstools_buy_btn_link_url']);
            }
        }
    }

    /**
     * 商品信息保存页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-23
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function AdminGoodsSaveView($params = [])
    {
        // 商品id
        $goods_id = empty($params['goods_id']) ? 0 : intval($params['goods_id']);

        // 定义空的返回内容
        $ret = '';

        // 商品美化
        if(isset($this->plugins_config['is_goods_beautify']) && $this->plugins_config['is_goods_beautify'] == 1)
        {
            MyViewAssign('plugins_data', empty($params['data']) ? [] : $params['data']);
            $ret .= MyView('../../../plugins/intellectstools/view/admin/goods/goods_edit');
        }

        // 单独为商品配置评价区间值
        if(isset($this->plugins_config['is_goods_alone_comments_config']) && $this->plugins_config['is_goods_alone_comments_config'] == 1)
        {
            MyViewAssign('plugins_data', CommentsDataService::CommentsGoodsConfigData($goods_id));
            $ret .= MyView('../../../plugins/intellectstools/view/admin/comments/goods');
        }

        // 商品页购买按钮跳转链接
        if(isset($this->plugins_config['is_admin_goods_saveinfo_buy_btn_config']) && $this->plugins_config['is_admin_goods_saveinfo_buy_btn_config'] == 1)
        {
            $ret .= MyView('../../../plugins/intellectstools/view/admin/goods/buy_btn_config', [
                'data'    => $params['data'],
                'config'  => $this->plugins_config,
            ]);
        }

        return $ret;
    }

    /**
     * 订单数据列表处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function OrderListDataHandle($params = [])
    {
        if(!empty($params['data']) && is_array($params['data']))
        {
            $order_ids = array_column($params['data'], 'id');
            if(!empty($order_ids))
            {
                $note_data = OrderNoteService::OrderNoteData($order_ids);
                if(!empty($note_data))
                {
                    foreach($params['data'] as $k=>$v)
                    {
                        if(array_key_exists($v['id'], $note_data))
                        {
                            $params['data'][$k]['plugins_intellectstools_note_data'] = $note_data[$v['id']];
                        }
                    }
                }
            }
        }
    }

    /**
     * 订单管理列表操作
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function AdminOrderListOperateButton($params = [])
    {
        // 订单信息
        $plugins_data = empty($params['data']) ? [] : $params['data'];
        if(!empty($plugins_data['id']) && isset($plugins_data['status']))
        {
            // 订单修改信息
            $operate_edit_button_info = OrderBaseService::OrderOperateEditInfo($this->plugins_config, $plugins_data);
            return MyView('../../../plugins/intellectstools/view/admin/order/button', [
                'operate_edit_button_info'  => $operate_edit_button_info,
                'plugins_data'              => $plugins_data,
                'plugins_config'            => $this->plugins_config,
            ]);
        }
    }

    /**
     * 商品管理列表操作
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function AdminGoodsListOperateButton($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/admin/goods/button', [
            'plugins_data'    => empty($params['data']) ? [] : $params['data'],
            'plugins_config'  => $this->plugins_config,
        ]);
    }

    /**
     * 底部公共内容
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-04-11
     * @desc    description
     * @param   array           $params [description]
     */
    private function ViewPageBottomContent($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/index/public/common', [
            'plugins_config'  => $this->plugins_config
        ]);
    }
}
?>