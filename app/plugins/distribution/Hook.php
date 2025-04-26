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
namespace app\plugins\distribution;

use think\facade\Db;
use app\service\UserService;
use app\service\SystemBaseService;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\ProfitService;
use app\plugins\distribution\service\ExtractionService;
use app\plugins\distribution\service\LevelService;

/**
 * 分销 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 模块、控制器、方法
    private $module_name;
    private $controller_name;
    private $action_name;
    private $mca;

    private $pluginsname;
    private $pluginscontrol;
    private $pluginsaction;
    private $pca;

    // 插件配置信息
    private $plugins_config;

    // 自提地址切换入口名称、是否开启自提地址切换
    private $extraction_switch_title = '自提地址';
    private $is_user_extraction_switch;

    // 是否开启用户中心菜单入口
    private $is_user_menu;

    // 用户首单购买形成邀请关系缓存key
    private $first_order_bind_invite_relation_key = 'plugins_distribution_order_first_order_bind_invite_relation_value_';

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
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();
            $this->mca = $this->module_name.$this->controller_name.$this->action_name;

            // 插件
            $this->pluginsname = strtolower(MyInput('pluginsname'));
            $this->pluginscontrol = strtolower(MyInput('pluginscontrol', 'index'));
            $this->pluginsaction = strtolower(MyInput('pluginsaction', 'index'));
            $this->pca = $this->pluginsname.$this->pluginscontrol.$this->pluginsaction;

            // 用户中心菜单入口
            $this->is_user_menu = isset($this->plugins_config['is_user_menu']) && $this->plugins_config['is_user_menu'] == 1;
            // 是否必须有分销权限
            if($this->is_user_menu && isset($this->plugins_config['is_user_menu_must_power']) && $this->plugins_config['is_user_menu_must_power'] == 1)
            {
                $user = UserService::LoginUserInfo();
                if(empty($user))
                {
                    $this->is_user_menu = false;
                } else {
                    // 获取用户分销权限
                    $user_level = BaseService::UserDistributionLevel($user['id'], $this->plugins_config);
                    if($user_level['code'] != 0)
                    {
                        $this->is_user_menu = false;
                    }
                }
            }

            // 开启自提地址切换
            $this->is_user_extraction_switch = (isset($this->plugins_config['is_user_extraction_switch']) && $this->plugins_config['is_user_extraction_switch'] == 1) ? 1 : 0;

            // 强制受邀首单形成上下级关系
            $is_first_order_bind_invite_relation = (isset($this->plugins_config['is_first_order_bind_invite_relation']) && $this->plugins_config['is_first_order_bind_invite_relation'] == 1) ? 1 : 0;

            // 是否引入多商户样式
            $is_shop_style = $this->module_name == 'index' && in_array($this->pluginsname.$this->pluginscontrol, ['distributionshopprofit', 'distributionshoplevel']);

            // 走条件判断
            $ret = '';
            switch($params['hook_name'])
            {
                case 'plugins_css' :
                    $ret = [];
                    // 分销公共基础样式
                    $ret[] = 'static/plugins/distribution/css/index/style.css';
                    // 引入多商户样式
                    if($is_shop_style)
                    {
                        $ret[] = 'static/plugins/shop/css/index/public/shop_admin.css';
                    }
                    break;

                // 公共js
                case 'plugins_js' :
                    $ret = [];
                    if($this->module_name == 'index')
                    {
                        // 分销公共基础js
                        $ret[] = 'static/plugins/distribution/js/index/style.js';
                    }
                    // 引入多商户js
                    if($is_shop_style)
                    {
                        $ret[] = 'static/plugins/shop/js/index/common.js';
                    }
                    break;

                // 用户中心左侧导航
                case 'plugins_service_users_center_left_menu_handle' :
                    if($this->is_user_menu)
                    {
                        $ret = $this->UserCenterLeftMenuHandle($params);
                    }
                    break;

                // 顶部小导航右侧-我的商城
                case 'plugins_service_header_navigation_top_right_handle' :
                    if($this->is_user_menu)
                    {
                        $ret = $this->CommonTopNavRightMenuHandle($params);
                    }
                    break;

                // APP端用户中心接口
                case 'plugins_service_base_data_return_api_user_center' :
                    if(!$this->is_user_menu && !empty($params['data']) && !empty($params['data']['navigation']) && is_array($params['data']['navigation']))
                    {
                        foreach($params['data']['navigation'] as $k=>$v)
                        {
                            if(!empty($v['event_value']) && stripos($v['event_value'], 'pages/plugins/distribution') !== false)
                            {
                                unset($params['data']['navigation'][$k]);
                            }
                        }
                        $params['data']['navigation'] = array_values($params['data']['navigation']);
                    }
                    break;

                // 订单状态
                case 'plugins_service_order_status_change_history_success_handle' :
                    if(!empty($params['data']) && !empty($params['order_id']) && isset($params['data']['new_status']))
                    {
                        switch($params['data']['new_status'])
                        {
                            // 支付订单状态更新
                            case 2 :
                                // 返佣订单处理
                                $ret = ProfitService::OrderProfitValid($params['order_id'], $params['data'], $this->plugins_config);

                                // 强制受邀首单形成上下级关系
                                if($is_first_order_bind_invite_relation == 1)
                                {
                                    $this->FirstOrderBindInviteRelationSuccess($params['order_id']);
                                }
                                break;

                            // 取货订单状态更新
                            case 3 :
                                $ret = ExtractionService::OrderExtractionSuccessHandle($params);
                                break;

                            // 订单售后，处理待生效返佣订单
                            case 4 :
                                $ret = ProfitService::OrderProfitStatusHandle($params['order_id'], $params['data'], $this->plugins_config);
                                break;

                            // 订单取消/关闭
                            case 5 :
                            case 6 :
                                $ret = ProfitService::OrderProfitClose($params['order_id'], $params['data']);
                                break;
                        }
                    }
                    break;

                // 订单售后审核成功
                case 'plugins_service_order_aftersale_audit_handle_end' :
                    $ret = ProfitService::OrderChange($params, $this->plugins_config);
                    break;

                // 自提地址
                case 'plugins_service_site_extraction_address_list' :
                    if($this->module_name.$this->controller_name.$this->action_name != 'adminsiteindex')
                    {
                        $ret = $this->ExtractionAddressHandle($params);
                    }
                    break;

                // 订单提交-佣金订单添加
                // 自提模式-订单提交后钩子
                case 'plugins_service_buy_order_insert_end' :
                    $ret = $this->BuyOrderInsertHandle($params);
                    break;

                // 后台商品编辑规格分销等级
                case 'plugins_service_goods_spec_extends_handle' :
                    $ret = $this->GoodsSpecExtendsHandle($params);
                    break;

                // 后台用户保存页面
                case 'plugins_view_admin_user_save' :
                    $ret = $this->AdminUserSaveHandle($params);
                    break;

                // 后台用户动态列表分销等级
                case 'plugins_module_form_admin_user_index' :
                case 'plugins_module_form_admin_user_detail' :
                case 'plugins_module_form_admin_user_excelexport' :
                    if(isset($this->plugins_config['is_admin_user_level_show']) && $this->plugins_config['is_admin_user_level_show'] == 1)
                    {
                        $ret = $this->AdminFormUserHandle($params);
                    }
                    break;

                // 用户数据列表处理
                case 'plugins_service_user_list_handle_end' :
                    if(isset($this->plugins_config['is_admin_user_level_show']) && $this->plugins_config['is_admin_user_level_show'] == 1)
                    {
                        $ret = $this->UserDataListHandle($params);
                    }
                    break;

                // 后台订单动态列表邀请用户
                case 'plugins_module_form_admin_order_index' :
                case 'plugins_module_form_admin_order_detail' :
                    if(isset($this->plugins_config['is_admin_order_user_referrer_show']) && $this->plugins_config['is_admin_order_user_referrer_show'] == 1)
                    {
                        $ret = $this->AdminFormOrderHandle($params);
                    }
                    break;

                // 订单数据处理后
                case 'plugins_service_order_handle_end' :
                    if(in_array($this->module_name.$this->controller_name.$this->action_name, ['adminorderindex', 'adminorderdetail']) && isset($this->plugins_config['is_admin_order_user_referrer_show']) && $this->plugins_config['is_admin_order_user_referrer_show'] == 1)
                    {
                        $this->OrderDataHandleEnd($params);
                    }
                    break;

                // 用户保存处理
                case 'plugins_service_user_save_handle' :
                    $ret = $this->UserSaveServiceHandle($params);
                    break;

                // 商品数据处理后
                case 'plugins_service_goods_list_handle_end' :
                    if($this->module_name != 'admin')
                    {
                        $this->GoodsHandleEnd($params);
                    }
                    break;

                // 商品规格基础数据
                case 'plugins_service_goods_spec_base' :
                    // 是否支持优惠
                    if(SystemBaseService::IsGoodsDiscount($params))
                    {
                        $this->GoodsSpecBase($params);
                    }
                    break;

                // 商品价格上面钩子
                case 'plugins_view_goods_detail_panel_price_top' :
                    if(APPLICATION == 'web' && $this->module_name.$this->controller_name.$this->action_name == 'indexgoodsindex')
                    {
                        // 是否已支持优惠
                        if(!empty($params['goods']) && !empty($params['goods']['id']) && SystemBaseService::IsGoodsDiscountRecord($params['goods']['id'], 'distribution'))
                        {
                            $ret = $this->GoodsDetailViewPriceTop($params);
                        }
                    }
                    break;

                // web端快捷导航操作按钮
                case 'plugins_service_quick_navigation_pc' :
                    if($this->is_user_extraction_switch == 1)
                    {
                        $this->WebQuickNavigationHandle($params);
                    }
                    break;

                // 小程序/APP端快捷导航操作按钮
                case 'plugins_service_quick_navigation_h5' :
                case 'plugins_service_quick_navigation_weixin' :
                case 'plugins_service_quick_navigation_alipay' :
                case 'plugins_service_quick_navigation_baidu' :
                case 'plugins_service_quick_navigation_qq' :
                case 'plugins_service_quick_navigation_toutiao' :
                    if($this->is_user_extraction_switch == 1)
                    {
                        $this->MiniQuickNavigationHandle($params);
                    }
                    break;

                // 多商户商家中心菜单-扩展模块
                case 'plugins_shop_service_base_user_center_nav' :
                    if(isset($this->plugins_config['is_profit_shop']) && $this->plugins_config['is_profit_shop'] == 1)
                    {
                        $this->ShopUserCenterNav($params);
                    }
                    break;

                // 拖拽可视化-页面地址
                case 'plugins_layout_service_url_value_begin' :
                    $this->LayoutServiceUrlValueBegin($params);
                    break;
                // 拖拽可视化-页面名称
                case 'plugins_layout_service_pages_list' :
                    $this->LayoutServicePagesList($params);
                    break;

                // 强制受邀首单形成上下级关系-取消用户注册邀请关系
                case 'plugins_service_user_register_begin' :
                    if($is_first_order_bind_invite_relation == 1 && !empty($params['user_base']) && isset($params['user_base']['referrer']))
                    {
                        // 移除用户邀请信息
                        unset($params['user_base']['referrer']);
                    }
                    break;
                // 强制受邀首单形成上下级关系-记录订单发起邀请关系
                case 'plugins_service_order_pay_launch_handle' :
                    if($is_first_order_bind_invite_relation == 1)
                    {
                        $this->FirstOrderBindInviteRelationBegin($params);
                    }
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
                        'name'  => '分销',
                        'type'  => 'distribution',
                        'data'  => [
                            ['name'=>'分销中心', 'page'=>'/pages/plugins/distribution/user/user'],
                            ['name'=>'客户订单', 'page'=>'/pages/plugins/distribution/order/order'],
                            ['name'=>'我的团队', 'page'=>'/pages/plugins/distribution/team/team'],
                            ['name'=>'客户分布', 'page'=>'/pages/plugins/distribution/map/map'],
                            ['name'=>'客户拜访', 'page'=>'/pages/plugins/distribution/visit-list/visit-list'],
                            ['name'=>'推荐宝', 'page'=>'/pages/plugins/distribution/recommend-list/recommend-list'],
                            ['name'=>'推广奖励', 'page'=>'/pages/plugins/distribution/poster/poster'],
                            ['name'=>'收益明细', 'page'=>'/pages/plugins/distribution/profit/profit'],
                            ['name'=>'等级介绍', 'page'=>'/pages/plugins/distribution/introduce/introduce'],
                        ],
                    ];
                    break;
                }
            }
        }
    }

    /**
     * 强制受邀首单形成上下级关系-订单支付提交、邀请信息存储
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [int]           $order_id [订单id]
     */
    public function FirstOrderBindInviteRelationSuccess($order_id)
    {
        $user_id = Db::name('Order')->where(['id'=>$order_id])->value('user_id');
        if(!empty($user_id))
        {
            $referrer = MyCache($this->first_order_bind_invite_relation_key.$user_id);
            if(!empty($referrer))
            {
                Db::name('User')->where(['id'=>$user_id, 'referrer'=>0])->update(['referrer'=>$referrer, 'upd_time'=>time()]);
                MyCache($this->first_order_bind_invite_relation_key.$user_id, null);
            }
        }
    }

    /**
     * 强制受邀首单形成上下级关系-订单支付处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function FirstOrderBindInviteRelationBegin($params = [])
    {
        if(!empty($params['order_ids']) && !empty($params['order_ids'][0]))
        {
            $referrer = UserService::UserReferrerDecrypt($params['params']);
            if(!empty($referrer))
            {
                $user_id = Db::name('Order')->where(['id'=>$params['order_ids'][0]])->value('user_id');
                if(!empty($user_id))
                {
                    $user_referrer = Db::name('User')->where(['id'=>$user_id])->value('referrer');
                    if(empty($user_referrer))
                    {
                        MyCache($this->first_order_bind_invite_relation_key.$user_id, $referrer, 10080);
                    }
                }
            }
        }
    }

    /**
     * 拖拽可视化-页面名称
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function LayoutServicePagesList($params = [])
    {
        if(isset($params['data']['plugins']['data']) && is_array($params['data']['plugins']['data']))
        {
            $params['data']['plugins']['data'][] = [
                'name'  => '分销',
                'value' => 'distribution',
                'data'  => [
                    [ 'value' => 'user-center', 'name' => '分销中心'],
                    [ 'value' => 'user-poster', 'name' => '推广海报'],
                ],
            ];
        }
    }

    /**
     * 拖拽可视化-页面地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function LayoutServiceUrlValueBegin($params = [])
    {
        $params['static_url_web_arr']['plugins-distribution-user-center'] = PluginsHomeUrl('distribution', 'index', 'index');
        $params['static_url_web_arr']['plugins-distribution-user-poster'] = PluginsHomeUrl('distribution', 'poster', 'index');
        $params['static_url_app_arr']['plugins-distribution-user-center'] = '/pages/plugins/distribution/user/user';
        $params['static_url_app_arr']['plugins-distribution-user-poster'] = '/pages/plugins/distribution/poster/poster';
    }

    /**
     * 多商户用户中心菜单
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-05-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function ShopUserCenterNav($params = [])
    {
        if(isset($params['data']['extends']) && is_array($params['data']['extends']))
        {
            $params['data']['extends'][] = [
                'name'          => '分销',
                'desc'          => '分销佣金订单列表、分销等级返佣配置',
                'url'           => PluginsHomeUrl('distribution', 'shopprofit', 'index'),
                'icon'          => SystemBaseService::AttachmentHost().'/static/plugins/distribution/images/shop-distribution.png',
                'business'      => 'distribution',
                'is_popup'      => 1,
                'is_full'       => 1,
            ];
        }
    }

    /**
     * web端快捷导航操作导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function WebQuickNavigationHandle($params = [])
    {
        if(isset($params['data']) && is_array($params['data']))
        {
            $nav = [
                'event_type'    => 0,
                'event_value'   => 0,
                'name'          => $this->extraction_switch_title,
                'images_url'    => SystemBaseService::AttachmentHost().'/static/plugins/distribution/images/extraction-switch-quick-nav-icon.png',
                'bg_color'      => '#ff6a80',
                'class_name'    => 'login-event plugins-distribution-quick-event',
                'data_value'    => PluginsHomeUrl('distribution', 'extraction', 'switchinfo'),
            ];
            array_push($params['data'], $nav);
        }
    }

    /**
     * 小程序端快捷导航操作导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function MiniQuickNavigationHandle($params = [])
    {
        if(isset($params['data']) && is_array($params['data']))
        {
            $nav = [
                'event_type'    => 1,
                'event_value'   => '/pages/plugins/distribution/extraction-switch/extraction-switch',
                'name'          => $this->extraction_switch_title,
                'images_url'    => SystemBaseService::AttachmentHost().'/static/plugins/distribution/images/extraction-switch-quick-nav-icon.png',
                'bg_color'      => '#ff6a80',
            ];
            array_push($params['data'], $nav);
        }
    }

    /**
     * 商品价格上面处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-25
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function GoodsDetailViewPriceTop($params = [])
    {
        if(!empty($this->plugins_config['is_appoint_goods']) && !empty($this->plugins_config['appoint_goods_repurchase_discount']) && !empty($this->plugins_config['appoint_repurchase_goods_ids']) && in_array($params['goods']['id'], $this->plugins_config['appoint_repurchase_goods_ids']))
        {
            // 是否复购
            $repurchase = BaseService::IsUserRepurchaseGoods([$params['goods']['id']]);
            if(!empty($repurchase))
            {
                return $this->GoodsDetailPrice($params['goods']['price_container']['price'], $params['goods']['price_container']['price']);
            }
        }
    }

    /**
     * 商品规格基础数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-26
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function GoodsSpecBase($params = [])
    {
        if(!empty($this->plugins_config['is_appoint_goods']) && !empty($this->plugins_config['appoint_goods_repurchase_discount']) && !empty($this->plugins_config['appoint_repurchase_goods_ids']) && in_array($params['data']['spec_base']['goods_id'], $this->plugins_config['appoint_repurchase_goods_ids']))
        {
            // 是否复购
            $repurchase = BaseService::IsUserRepurchaseGoods([$params['data']['spec_base']['goods_id']]);
            if(!empty($repurchase))
            {
                $original_price = ($params['data']['spec_base']['price'] <= 0) ? $params['data']['spec_base']['original_price'] : $params['data']['spec_base']['price'];
                $params['data']['extends_element'][] = [
                    'element'   => '.plugins-distribution-goods-price-top',
                    'content'   => $this->GoodsDetailPrice($params['data']['spec_base']['price'], $original_price),
                ];
                $params['data']['spec_base']['price'] = BaseService::PriceCalculate($params['data']['spec_base']['price'], $this->plugins_config['appoint_goods_repurchase_discount']);
            }
        }
    }

    /**
     * 商品详情价格
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-25
     * @desc    description
     * @param   [float]          $goods_price           [商品销售价格]
     * @param   [float]          $goods_original_price  [商品原价价格]
     */
    private function GoodsDetailPrice($goods_price, $goods_original_price)
    {
        if(APPLICATION == 'web')
        {
            MyViewAssign('goods_original_price', $goods_original_price);
            MyViewAssign('goods_price', $goods_price);
            return MyView('../../../plugins/distribution/view/index/public/detail_goods_price');
        }
        return '';
    }

    /**
     * 商品处理结束钩子
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-26
     * @desc    description
     * @param   [array]              $params [输入参数]
     */
    private function GoodsHandleEnd($params = [])
    {
        if(!empty($this->plugins_config['is_appoint_goods']) && !empty($this->plugins_config['appoint_goods_repurchase_discount']) && !empty($this->plugins_config['appoint_repurchase_goods_ids']) && !empty($params['data']))
        {
            // key字段
            $key_field = empty($params['params']['data_key_field']) ? 'id' : $params['params']['data_key_field'];
            $goods_ids = array_column($params['data'], $key_field);
            $res = array_diff($this->plugins_config['appoint_repurchase_goods_ids'], $goods_ids);
            if(empty($res) || count($res) < count($this->plugins_config['appoint_repurchase_goods_ids']))
            {
                // 是否复购
                $repurchase = BaseService::IsUserRepurchaseGoods($goods_ids);
                if(!empty($repurchase))
                {
                    // 是否还可以使用优惠
                    $discount = SystemBaseService::IsGoodsDiscount($params);
                    if(!empty($discount) && ($discount == 1 || (is_array($discount) && array_sum($discount) > 0)))
                    {
                        $price_title = empty($this->plugins_config['goods_detail_icon']) ? '复购优惠' : $this->plugins_config['goods_detail_icon'];
                        foreach($params['data'] as &$goods)
                        {
                            if(in_array($goods[$key_field], $repurchase))
                            {
                                // 展示销售价格
                                if(isset($goods['price']))
                                {
                                    $goods['price'] = BaseService::PriceCalculate($goods['price'], $this->plugins_config['appoint_goods_repurchase_discount']);
                                }
                                // 最低价最高价
                                if(isset($goods['min_price']))
                                {
                                    $goods['min_price'] = BaseService::PriceCalculate($goods['min_price'], $this->plugins_config['appoint_goods_repurchase_discount']);
                                }
                                if(isset($goods['max_price']))
                                {
                                    $goods['max_price'] = BaseService::PriceCalculate($goods['max_price'], $this->plugins_config['appoint_goods_repurchase_discount']);
                                }

                                // icon title
                                $goods['show_field_price_text'] = '<span class="am-badge am-badge-warning am-padding-xs plugins-distribution-goods-price-icon" title="'.$price_title.'">'.$price_title.'</span>';

                                // 使用优惠处理
                                SystemBaseService::GoodsDiscountRecord($goods[$key_field], 'distribution', 1);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 用户信息保存处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function UserSaveServiceHandle($params = [])
    {
        $params['data']['plugins_distribution_level'] = isset($params['params']['plugins_distribution_level']) ? $params['params']['plugins_distribution_level'] : '';
        return DataReturn(MyLang('handle_success'), 0);
    }

    /**
     * 用户信息保存页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function AdminUserSaveHandle($params = [])
    {
        $ret = LevelService::DataList();
        if($ret['code'] == 0 && !empty($ret['data']))
        {
            MyViewAssign('user_data', $params['data']);
            MyViewAssign('level_list_data', $ret['data']);
            return MyView('../../../plugins/distribution/view/admin/public/user');
        }
    }

    /**
     * 订单数据处理结束
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function OrderDataHandleEnd($params = [])
    {
        if(!empty($params['order']) && !empty($params['order']['user_id']))
        {
            $referrer = Db::name('User')->where(['id'=>$params['order']['user_id']])->value('referrer');
            if(!empty($referrer))
            {
                $params['order']['referrer_info'] = UserService::GetUserViewInfo($referrer);
            }
        }
    }

    /**
     * 后台订单动态列表邀请用户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function AdminFormOrderHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['form']))
        {
            array_splice($params['data']['form'], 2, 0, [[
                'label'         => '邀请用户',
                'view_type'     => 'module',
                'view_key'      => '../../../plugins/distribution/view/admin/public/order_referrer_module',
                'grid_size'     => 'sm',
                'search_config' => [
                    'form_type'             => 'input',
                    'form_name'             => 'user_id',
                    'where_type_custom'     => 'in',
                    'where_value_custom'    => 'ModuleWhereValueUserInfo',
                    'where_object_custom'   => $this,
                    'placeholder'           => '请输入邀请用户名/昵称/手机/邮箱',
                ],
            ]]);
        }
    }

    /**
     * 动态数据订单列表条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-26
     * @desc    description
     * @param   [string]          $value    [条件值]
     * @param   [array]           $params   [输入参数]
     */
    public function ModuleWhereValueUserInfo($value, $params = [])
    {
        if(!empty($value))
        {
            // 获取id
            $ids = Db::name('User')->where('number_code|username|nickname|mobile|email', 'like', '%'.$value.'%')->column('id');
            if(!empty($ids))
            {
                $ids = Db::name('User')->where(['referrer'=>$ids])->column('id');
            }

            // 避免空条件造成无效的错觉
            return empty($ids) ? [0] : $ids;
        }
        return $value;
    }

    /**
     * 用户数据列表处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-23
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function UserDataListHandle($params = [])
    {
        if(!empty($params['data']))
        {
            foreach($params['data'] as &$v)
            {
                $res = BaseService::UserDistributionLevel($v['id'], $this->plugins_config);
                $v['distribution_auto_level_data'] = $res['data'];
            }
        }
    }

    /**
     * 后台用户动态列表分销等级
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function AdminFormUserHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['form']))
        {
            $ret = LevelService::DataList(['where'=>['is_enable'=>1]]);
            if($ret['code'] == 0 && !empty($ret['data']))
            {
                $level_data = array_column($ret['data'], 'name', 'id');
                array_splice($params['data']['form'], -3, 0, [
                    [
                        'label'         => '分销等级(手动)',
                        'view_type'     => 'field',
                        'view_key'      => 'plugins_distribution_level',
                        'view_data'     => $level_data,
                        'is_sort'       => 1,
                        'search_config' => [
                            'form_type'         => 'select',
                            'where_type'        => 'in',
                            'data'              => $level_data,
                            'is_multiple'       => 1,
                        ],
                    ],
                    [
                        'label'         => '分销等级(自动)',
                        'view_type'     => 'module',
                        'view_key'      => '../../../plugins/distribution/view/admin/public/user_level_view',
                        'is_sort'       => 1,
                        'search_config' => [
                            'form_type'         => 'select',
                            'form_name'         => 'plugins_distribution_auto_level',
                            'where_type'        => 'in',
                            'data'              => $level_data,
                            'is_multiple'       => 1,
                        ],
                    ]
                ]);
            }
        }
    }

    /**
     * 商品规格扩展数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-04
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function GoodsSpecExtendsHandle($params = [])
    {
        $ret = LevelService::DataList();
        if($ret['code'] == 0 && !empty($ret['data']))
        {
            $is_profit_shop = (isset($this->plugins_config['is_profit_shop']) && $this->plugins_config['is_profit_shop'] == 1) ? 1 : 0;
            $element = [];
            foreach($ret['data'] as $v)
            {
                $element[] = [
                    'element'       => 'textarea',
                    'rows'          => '3',
                    'name'          => 'plugins_distribution_rules_'.$v['id'],
                    'placeholder'   => '类型|规则值（如 r|10 或 s|5）',
                    'title'         => $v['name'].'向上返佣',
                    'is_required'   => 0,
                    'message'       => '请填写分销向上返佣值',
                ];
                // 后台或开启店铺返佣
                if($this->module_name == 'admin' || ($this->module_name == 'index' && $is_profit_shop == 1 && isset($this->plugins_config['is_profit_down_return_shop']) && $this->plugins_config['is_profit_down_return_shop'] == 1))
                {
                    $element[] = [
                        'element'       => 'input',
                        'name'          => 'plugins_distribution_down_rules_'.$v['id'],
                        'placeholder'   => '类型|规则值（如 r|10 或 s|5）',
                        'title'         => $v['name'].'向下返佣',
                        'type'          => 'text',
                        'is_required'   => 0,
                        'message'       => '请填写分销向下返佣值',
                    ];
                }
                // 后台或开启店铺返佣
                if($this->module_name == 'admin' || ($this->module_name == 'index' && $is_profit_shop == 1 && isset($this->plugins_config['is_profit_self_buy_shop']) && $this->plugins_config['is_profit_self_buy_shop'] == 1))
                {
                    $element[] = [
                        'element'       => 'input',
                        'name'          => 'plugins_distribution_self_buy_rules_'.$v['id'],
                        'placeholder'   => '类型|规则值（如 r|10 或 s|5）',
                        'title'         => $v['name'].'内购返佣',
                        'type'          => 'text',
                        'is_required'   => 0,
                        'message'       => '请填写内购返佣值',
                    ];
                }
                if($this->module_name == 'admin')
                {
                    $element[] = [
                        'element'       => 'textarea',
                        'rows'          => '3',
                        'name'          => 'plugins_distribution_force_current_user_rules_'.$v['id'],
                        'placeholder'   => '类型|规则值（如 r|10 或 s|5）',
                        'title'         => $v['name'].'取货点返佣',
                        'is_required'   => 0,
                        'message'       => '请填写取货点返佣值',
                    ];
                }
            }

            // 配置信息
            if(count($element) > 0)
            {
                $plugins = [
                    'name'      => '分销插件返佣配置',
                    'desc'      => '请按照规则填写',
                    'tips'      => '1: r 代表按照比例, s 代表固定金额。<br />2: 换行区分[表示1~3级，超出3行则超出的行视为无效]。<br />3: 跳级换行为空即可。<br /><span class="am-margin-left-xs">4: 列子：</span><br /><span class="am-margin-left-sm">4.1: 一级返佣5%, 二级返佣3%, 三级返佣2元</span><br /><span class="am-margin-left-lg">r|5</span><br /><span class="am-margin-left-lg">r|3</span><br /><span class="am-margin-left-lg">s|2</span> <br /><span class="am-margin-left-sm">4.2: 一级返佣10%, 二级不返佣, 三级返佣5元</span><br /><span class="am-margin-left-lg">r|10</span><br /><span class="am-margin-left-lg"></span><br /><span class="am-margin-left-lg">s|5</span>',
                    'element'   => $element,
                ];
                $params['data'][] = $plugins;
            }
        }
    }

    /**
     * 订单提交成功后处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function BuyOrderInsertHandle($params = [])
    {
        if(!empty($params['order_id']))
        {
            // 取货点处理
            if(!empty($params['address']) && isset($params['address']['is_plugins_distribution_extraction']) && $params['address']['is_plugins_distribution_extraction'] == 1 && !empty($params['address']['id_old']))
            {
                $data = [
                    'order_id'              => $params['order_id'],
                    'user_id'               => $params['address']['user_id'],
                    'self_extraction_id'    => $params['address']['id_old']
                ];
                $ret = ExtractionService::ExtractionInsert($data);
                if(isset($ret['code']) && $ret['code'] != 0)
                {
                    return $ret;
                }
            }

            // 是否门店下单不进行返佣
            $status = true;
            if(isset($this->plugins_config['is_realstore_order_not_profit']) && $this->plugins_config['is_realstore_order_not_profit'] == 1)
            {
                // 门店来源订单参数
                if(!empty($params['params']['realstore_id']) && isset($params['params']['buy_use_type_index']))
                {
                    $status = false;
                }
            }

            // 有效时间
            if(!empty($this->plugins_config['profit_time_start']))
            {
                // 是否已开始
                if(strtotime($this->plugins_config['profit_time_start']) > time())
                {
                    $status = false;
                }
            }
            if(!empty($this->plugins_config['profit_time_end']))
            {
                // 是否已结束
                if(strtotime($this->plugins_config['profit_time_end']) < time())
                {
                    $status = false;
                }
            }

            // 佣金订单生成
            if($status)
            {
                $ret = ProfitService::ProfitOrderInsert($params, $this->plugins_config);
                if(isset($ret['code']) && $ret['code'] != 0)
                {
                    return $ret;
                }
            }
        }
        return DataReturn(MyLang('handle_noneed'), 0);
    }

    /**
     * 自提地址处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ExtractionAddressHandle($params = [])
    {
        // 取货点列表
        $ret = ExtractionService::ExtractionList();

        // 当前用户信息
        $user = UserService::LoginUserInfo();

        // 默认地址标记
        $status = false;

        // 获取上级用户的取货点信息
        $is_default_address_status = (isset($this->plugins_config['is_buy_default_extraction_address']) && $this->plugins_config['is_buy_default_extraction_address'] == 1) ? 1 : 0;
        if($is_default_address_status == 1)
        {
            // 当前用户上一级的用户取货点地址
            if(!empty($user['referrer']))
            {
                $address = ExtractionService::ExtractionData($user['referrer']);
                if(!empty($address) && !empty($address['data']) && !empty($address['data']['id']))
                {
                    $index = array_search($address['data']['id'], array_column($ret['data'], 'id'));
                    if($index !== false)
                    {
                        $ret['data'][$index]['is_default'] = 1;
                        $status = true;
                    }
                }
            }
        }

        // 数据处理
        if(!empty($ret['data']))
        {
            if(empty($params['data']))
            {
                $params['data'] = [];
                $index = 0;
            } else {
                $index = count($params['data']);

                // 如果当前已经匹配到默认地址，则去除主数据的默认地址
                if($status)
                {
                    foreach($params['data'] as $k=>$v)
                    {
                        if(isset($v['is_default']))
                        {
                            $params['data'][$k]['is_default'] = 0;
                        }
                    }
                }
            }
            foreach($ret['data'] as $v)
            {
                $v['id_old'] = $v['id'];
                $v['id'] = $index;
                $v['is_plugins_distribution_extraction'] = 1;
                $params['data'][$index] = $v;
                $index++;
            }
        }

        // 用户是否自定义切换自提地址
        if($this->is_user_extraction_switch == 1 && !empty($params['data']) && !empty($user['id']))
        {
            $address = ExtractionService::UserCustomExtractionAddress($user['id']);
            if(!empty($address) && array_key_exists($address['address_key'], $params['data']))
            {
                $temp = $params['data'][$address['address_key']];
                if(empty($temp['address_oldid']) || (!empty($temp['address_oldid']) && !empty($temp['id_old']) && $temp['id_old'] == $temp['address_oldid']))
                {
                    // 去除原有的默认地址
                    foreach($params['data'] as $k=>$v)
                    {
                        if(array_key_exists('is_default', $v))
                        {
                            $params['data'][$k]['is_default'] = 0;
                        }
                    }

                    // 设置用户设定的地址
                    $params['data'][$address['address_key']]['is_default'] = 1;
                }
            }
        }
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
        $params['data']['business']['item'][] = [
            'name'      =>  '我的分销',
            'url'       =>  PluginsHomeUrl('distribution', 'index', 'index'),
            'contains'  =>  ['distributionindexindex', 'distributionprofitindex', 'distributionorderindex', 'distributionteamindex', 'distributionposterindex', 'distributionintroduceindex', 'distributionextractionindex', 'distributionvisitindex', 'distributionvisitsaveinfo', 'distributionrecommendindex', 'distributionrecommendsaveinfo'],
            'is_show'   =>  1,
            'icon'      =>  'am-icon-share-alt',
        ];
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
        array_push($params['data'][1]['items'], [
            'name'  => '我的分销',
            'url'   => PluginsHomeUrl('distribution', 'index', 'index'),
        ]);
    }
}
?>