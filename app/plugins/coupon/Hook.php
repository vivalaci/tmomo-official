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
namespace app\plugins\coupon;

use think\facade\Db;
use app\service\UserService;
use app\service\ResourcesService;
use app\service\SystemBaseService;
use app\service\PluginsAdminService;
use app\plugins\coupon\service\BaseService;
use app\plugins\coupon\service\CouponService;
use app\plugins\coupon\service\UserCouponService;

/**
 * 优惠券 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-11T21:51:08+0800
 */
class Hook
{
    // 配置信息
    private $plugins_config;

    // 模块、控制器、方法
    private $module_name;
    private $controller_name;
    private $action_name;
    private $mca;

    private $pluginsname;
    private $pluginscontrol;
    private $pluginsaction;
    private $pca;

    // 排除限时秒杀
    private $is_exclude_seckill_goods_order;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-11T14:25:44+0800
     * @param    [array]          $params [输入参数]
     */
    public function handle($params = [])
    {
        // 后端访问不处理
        if(isset($params['params']['is_admin_access']) && $params['params']['is_admin_access'] == 1)
        {
            return DataReturn(MyLang('handle_noneed'), 0);
        }

        // 钩子名称
        $ret = '';
        if(!empty($params['hook_name']))
        {
            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

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

            // 优惠券基础
            $is_coupon_style = in_array($this->mca, ['indexgoodsindex', 'indexbuyindex']);

            // 是否引入多商户样式
            $is_shop_admin_style = $this->module_name == 'index' && in_array($this->pluginsname.$this->pluginscontrol, ['couponshopcoupon', 'couponshopcouponuser']);

            // 多商户公共样式
            $is_shop_style = $this->module_name == 'index' && in_array($this->pca, ['couponindexshop']);

            // 是否排除限时秒杀商品订单
            $this->is_exclude_seckill_goods_order = isset($this->plugins_config['is_exclude_seckill_goods_order']) && $this->plugins_config['is_exclude_seckill_goods_order'] == 1;

            switch($params['hook_name'])
            {
                // 公共css
                case 'plugins_css' :
                    $ret = [];
                    // 优惠券基础
                    if($is_coupon_style)
                    {
                        $ret[] = 'static/plugins/coupon/css/index/common.css';
                    }
                    // 引入多商户样式
                    if($is_shop_admin_style)
                    {
                        $ret[] = 'static/plugins/shop/css/index/public/shop_admin.css';
                    }
                    // 多商户优惠券页面头
                    if($is_shop_style)
                    {
                        $ret[] = 'static/plugins/shop/css/index/common.css';
                    }
                    break;

                // 公共js
                case 'plugins_js' :
                    $ret = [];
                    // 优惠券基础
                    if($is_coupon_style)
                    {
                        $ret[] = 'static/plugins/coupon/js/index/common.js';
                    }
                    // 引入多商户js
                    if($is_shop_admin_style || $is_shop_style)
                    {
                        $ret[] = 'static/plugins/shop/js/index/common.js';
                    }
                    break;

                // 在前面添加导航
                case 'plugins_service_navigation_header_handle' :
                    $ret = $this->NavigationHeaderHandle($params);
                    break;

                // 用户中心左侧导航
                case 'plugins_service_users_center_left_menu_handle' :
                    $ret = $this->UserCenterLeftMenuHandle($params);
                    break;

                // 顶部小导航右侧-我的商城
                case 'plugins_service_header_navigation_top_right_handle' :
                    $ret = $this->CommonTopNavRightMenuHandle($params);
                    break;

                // 商品详情面板底部
                case 'plugins_view_goods_detail_panel_bottom' :
                    $ret = $this->GoodsDetailCoupinView($params);
                    break;

                // 购买确认页面优惠券选择
                case 'plugins_view_buy_group_goods_inside_extension_top' :
                    // 排除秒杀商品订单
                    if($this->is_exclude_seckill_goods_order && !empty($params['data']) && !empty($params['data']['goods_items']))
                    {
                        if(BaseService::GoodsIsExistValidSeckill(array_column($params['data']['goods_items'], 'goods_id')))
                        {
                            return '';
                        }
                    }
                    // 页面优惠券选择
                    $ret = $this->BuyCoupinView($params);
                    break;

                // 购买订单优惠处理
                case 'plugins_service_buy_group_goods_handle' :
                    $ret = $this->BuyDiscountCalculate($params);
                    break;

                // 购买提交订单页面隐藏域html
                case 'plugins_view_buy_form_inside' :
                    $ret = $this->BuyFormInsideInput($params);
                    break;

                // 订单添加成功处理
                case 'plugins_service_buy_order_insert_success' :
                    $ret = $this->OrderInsertSuccessHandle($params);
                    break;

                // 订单状态改变处理
                case 'plugins_service_order_status_change_history_success_handle' :
                    $ret = $this->OrderStatusUpdateHandle($params);
                    break;

                // 注册送优惠券
                case 'plugins_service_user_register_end' :
                    $ret = $this->UserRegisterGiveHandle($params);
                    break;

                // 商品接口数据
                case 'plugins_service_base_data_return_api_goods_detail' :
                    $this->GoodsResultHandle($params);
                    break;

                // 下单接口数据
                case 'plugins_service_base_data_return_api_buy_index' :
                    $this->BuyResultHandle($params);
                    break;

                // 多商户商家中心菜单-扩展模块
                case 'plugins_shop_service_base_user_center_nav' :
                    if(isset($this->plugins_config['is_shop_coupon']) && $this->plugins_config['is_shop_coupon'] == 1)
                    {
                        $this->ShopUserCenterNav($params);
                    }
                    break;

                // 多商户页面主导航
                case 'plugins_shop_service_shopnavigation_nav' :
                    if(isset($this->plugins_config['is_shop_coupon']) && $this->plugins_config['is_shop_coupon'] == 1)
                    {
                        $this->ShopNavigationNav($params);
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

                // diyapi初始化
                case 'plugins_service_diyapi_init_data' :
                    $this->DiyApiInitDataHandle($params);
                    break;

                // diy自定义初始化
                case 'plugins_service_diyapi_custom_init' :
                    $this->DiyCustomInitHandle($params);
                    break;

                // diy展示数据处理
                case 'plugins_module_diy_view_data_handle' :
                    $this->DiyViewDataHandle($params);
                    break;
            }
        }
        return $ret;
    }

    /**
     * DIY展示数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function DiyViewDataHandle($params = [])
    {
        if(!empty($params['config']['diy_data']) && is_array($params['config']['diy_data']))
        {
            // 指定id获取
            $coupon_ids = [];
            foreach($params['config']['diy_data'] as $v)
            {
                if(!empty($v['com_data']) && !empty($v['com_data']['content']))
                {
                    switch($v['key'])
                    {
                        // 数据魔方
                        case 'data-magic' :
                            if(!empty($v['com_data']['content']['data_magic_list']))
                            {
                                foreach($v['com_data']['content']['data_magic_list'] as &$dmv)
                                {
                                    if(!empty($dmv['data_content']) && isset($dmv['data_content']['data_type']) && $dmv['data_content']['data_type'] == 'custom' && !empty($dmv['data_content']['data_source']) && $dmv['data_content']['data_source'] == 'plugins-coupon' && !empty($dmv['data_content']['data_source_content']) && isset($dmv['data_content']['data_source_content']['data_type']) && $dmv['data_content']['data_source_content']['data_type'] == 0 && !empty($dmv['data_content']['data_source_content']['data_ids']))
                                    {
                                        if(!is_array($dmv['data_content']['data_source_content']['data_ids']))
                                        {
                                            $dmv['data_content']['data_source_content']['data_ids'] = explode(',', $dmv['data_content']['data_source_content']['data_ids']);
                                        }
                                        $coupon_ids = array_merge($coupon_ids, $dmv['data_content']['data_source_content']['data_ids']);
                                    }
                                }
                            }
                            break;

                        // 数据选项卡
                        case 'data-tabs' :
                            if(!empty($v['com_data']['content']['tabs_list']))
                            {
                                foreach($v['com_data']['content']['tabs_list'] as $dtv)
                                {
                                    if(!empty($dtv['tabs_data_type']) && $dtv['tabs_data_type'] == 'custom' && !empty($dtv[$dtv['tabs_data_type'].'_config']))
                                    {
                                        $tabs_data_config = $dtv[$dtv['tabs_data_type'].'_config'];
                                        if(!empty($tabs_data_config['content']))
                                        {
                                            $content = $tabs_data_config['content'];
                                            if(!empty($content['data_source']) && !empty($content['data_source_content']) && $content['data_source'] == 'plugins-coupon' && isset($content['data_source_content']['data_type']) && $content['data_source_content']['data_type'] == 0 && !empty($content['data_source_content']['data_ids']))
                                            {
                                                if(!is_array($content['data_source_content']['data_ids']))
                                                {
                                                    $content['data_source_content']['data_ids'] = explode(',', $content['data_source_content']['data_ids']);
                                                }
                                                $coupon_ids = array_merge($coupon_ids, $content['data_source_content']['data_ids']);
                                            }
                                        }
                                    }
                                }
                            }
                            break;

                        // 自定义
                        case 'custom' :
                            if(!empty($v['com_data']['content']['data_source']) && $v['com_data']['content']['data_source'] == 'plugins-coupon' && !empty($v['com_data']['content']['data_source_content']) && isset($v['com_data']['content']['data_source_content']['data_type']) && $v['com_data']['content']['data_source_content']['data_type'] == 0 && !empty($v['com_data']['content']['data_source_content']['data_ids']))
                            {
                                if(!is_array($v['com_data']['content']['data_source_content']['data_ids']))
                                {
                                    $v['com_data']['content']['data_source_content']['data_ids'] = explode(',', $v['com_data']['content']['data_source_content']['data_ids']);
                                }
                                $coupon_ids = array_merge($coupon_ids, $v['com_data']['content']['data_source_content']['data_ids']);
                            }
                            break;
                    }
                }
            }

            // 读取指定优惠券数据
            $coupon_data = empty($coupon_ids) ? [] : array_column(CouponService::AppointCouponList(['coupon_ids'=>$coupon_ids]), null, 'id');

            // 数据获取
            foreach($params['config']['diy_data'] as &$v)
            {
                if(!empty($v['com_data']) && !empty($v['com_data']['content']))
                {
                    switch($v['key'])
                    {
                        // 优惠券
                        case 'coupon' :
                            if(!empty($v['com_data']) && !empty($v['com_data']['content']) && isset($v['com_data']['content']['data_type']))
                            {
                                // 手动模式
                                if($v['com_data']['content']['data_type'] == 1)
                                {
                                    if(!empty($v['com_data']['content']['data_ids']))
                                    {
                                        $v['com_data']['content']['data_list'] = CouponService::AppointCouponList(['coupon_ids'=>$v['com_data']['content']['data_ids']]);
                                    }
                                } else {
                                    // 自动读取
                                    $v['com_data']['content']['data_auto_list'] = CouponService::AutoCouponList($v['com_data']['content']);
                                }
                            }
                            break;

                        // 数据魔方
                        case 'data-magic' :
                            if(!empty($v['com_data']['content']['data_magic_list']))
                            {
                                foreach($v['com_data']['content']['data_magic_list'] as &$dmv)
                                {
                                    if(!empty($dmv['data_content']) && isset($dmv['data_content']['data_type']) && $dmv['data_content']['data_type'] == 'custom' && !empty($dmv['data_content']['data_source']) && $dmv['data_content']['data_source'] == 'plugins-coupon')
                                    {
                                        $dmv['data_content']['data_source_content'] = $this->DiyConfigViewCouponHandle($dmv['data_content']['data_source_content'], $coupon_data);
                                    }
                                }
                            }
                            break;

                        // 数据选项卡
                        case 'data-tabs' :
                            if(!empty($v['com_data']['content']['tabs_list']))
                            {
                                foreach($v['com_data']['content']['tabs_list'] as &$dtv)
                                {
                                    if(!empty($dtv['tabs_data_type']) && $dtv['tabs_data_type'] == 'custom' && !empty($dtv[$dtv['tabs_data_type'].'_config']))
                                    {
                                        $tabs_data_config = $dtv[$dtv['tabs_data_type'].'_config'];
                                        if(!empty($tabs_data_config['content']) && !empty($tabs_data_config['content']['data_source']) && $tabs_data_config['content']['data_source'] == 'plugins-coupon' && !empty($tabs_data_config['content']['data_source_content']))
                                        {
                                            $tabs_data_config['content']['data_source_content'] = $this->DiyConfigViewCouponHandle($tabs_data_config['content']['data_source_content'], $coupon_data);
                                            $dtv[$dtv['tabs_data_type'].'_config'] = $tabs_data_config;
                                        }
                                    }
                                }
                            }
                            break;

                        // 自定义
                        case 'custom' :
                            if(!empty($v['com_data']['content']['data_source']) && $v['com_data']['content']['data_source'] == 'plugins-coupon')
                            {
                                $v['com_data']['content']['data_source_content'] = $this->DiyConfigViewCouponHandle($v['com_data']['content']['data_source_content'], $coupon_data);
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * DIY配置显示数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-14
     * @desc    description
     * @param   [array]          $config      [配置数据]
     * @param   [array]          $coupon_data [指定优惠券的数据]
     */
    public static function DiyConfigViewCouponHandle($config, $coupon_data = [])
    {
        $data_type = isset($config['data_type']) ? $config['data_type'] : 0;
        if($data_type == 1)
        {
            $data_params = [
                'coupon_type_ids'            => isset($config['coupon_type_ids']) ? $config['coupon_type_ids'] : (isset($config['type_ids']) ? $config['type_ids'] : ''),
                'coupon_expire_type_ids'     => isset($config['coupon_expire_type_ids']) ? $config['coupon_expire_type_ids'] : (isset($config['expire_type_ids']) ? $config['expire_type_ids'] : ''),
                'coupon_use_limit_type_ids'  => isset($config['coupon_use_limit_type_ids']) ? $config['coupon_use_limit_type_ids'] : (isset($config['use_limit_type_ids']) ? $config['use_limit_type_ids'] : ''),
                'coupon_keywords'            => isset($config['coupon_keywords']) ? $config['coupon_keywords'] : (isset($config['keywords']) ? $config['keywords'] : ''),
                'coupon_number'              => isset($config['coupon_number']) ? $config['coupon_number'] : (isset($config['number']) ? $config['number'] : 4),
                'coupon_order_by_type'       => isset($config['coupon_order_by_type']) ? $config['coupon_order_by_type'] : (isset($config['order_by_type']) ? $config['order_by_type'] : 0),
                'coupon_order_by_rule'       => isset($config['coupon_order_by_rule']) ? $config['coupon_order_by_rule'] : (isset($config['order_by_rule']) ? $config['order_by_rule'] : 0),
                'coupon_is_repeat_receive'   => isset($config['coupon_is_repeat_receive']) ? $config['coupon_is_repeat_receive'] : (isset($config['is_repeat_receive']) ? $config['is_repeat_receive'] : 0),
            ];
            $config['data_auto_list'] = CouponService::AutoCouponList($data_params);
        } else {
            if(!empty($config['data_list']) && !empty($coupon_data))
            {
                $index = 0;
                foreach($config['data_list'] as $dk=>$dv)
                {
                    if(!empty($dv['data_id']) && array_key_exists($dv['data_id'], $coupon_data))
                    {
                        $config['data_list'][$dk]['data'] = $coupon_data[$dv['data_id']];
                        $config['data_list'][$dk]['data']['data_index'] = $index+1;
                        $index++;
                    }
                }
            }
        }
        return $config;
    }

    /**
     * DIY自定义配置初始化
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function DiyCustomInitHandle($params = [])
    {
        if(!empty($params['data']) && isset($params['data']['data_source']) && is_array($params['data']['data_source']))
        {
            $params['data']['data_source'][] = [
                'name'  => MyLang('coupon_name'),
                'type'  => 'plugins-coupon',
                'data'  => [
                    ['name'=>'数据索引', 'field'=>'data_index', 'type'=>'text'],
                    ['name'=>'优惠券详情', 'field' =>'url', 'type'=>'link'],
                    ['name'=>'数据ID', 'field'=>'id', 'type'=>'text'],
                    ['name'=>'名称', 'field'=>'name', 'type'=>'text'],
                    ['name'=>'描述', 'field'=>'desc', 'type'=>'text'],
                    ['name'=>'类型', 'field'=>'type', 'type'=>'text'],
                    ['name'=>'类型名称', 'field'=>'type_name', 'type'=>'text'],
                    ['name'=>'名称前置符号', 'field'=>'type_first', 'type'=>'text'],
                    ['name'=>'名称后置单位', 'field'=>'type_unit', 'type'=>'text'],
                    ['name'=>'优惠/折扣值', 'field'=>'discount_value', 'type'=>'text'],
                    ['name'=>'背景色', 'field'=>'bg_color', 'type'=>'text', 'tips'=>'优惠券颜色（0红色, 1紫色, 2黄色, 3蓝色, 4橙色, 5绿色, 6咖啡色）'],
                    ['name'=>'背景色名称', 'field'=>'bg_color_name', 'type'=>'text'],
                    ['name'=>'背景色值', 'field'=>'bg_color_value', 'type'=>'text'],
                    ['name'=>'操作状态名称', 'field'=>'status_operable_name', 'type'=>'text'],
                    ['name'=>'操作状态值', 'field'=>'status_type', 'type'=>'text', 'tips'=>'0可领取，1已领取，2已抢完，3去使用，4已使用，5已过期'],
                    ['name'=>'限时开始时间', 'field'=>'fixed_time_start', 'type'=>'text'],
                    ['name'=>'限时结束时间', 'field'=>'fixed_time_end', 'type'=>'text'],
                    ['name'=>'有效小时', 'field'=>'expire_hour', 'type'=>'text'],
                    ['name'=>'过期类型', 'field'=>'expire_type', 'type'=>'text'],
                    ['name'=>'过期类型名称', 'field'=>'expire_type_name', 'type'=>'text'],
                    ['name'=>'使用限制', 'field'=>'use_limit_type', 'type'=>'text'],
                    ['name'=>'使用限制名称', 'field'=>'use_limit_type_name', 'type'=>'text'],
                    ['name'=>'订单最低金额', 'field'=>'where_order_price', 'type'=>'text'],
                    ['name'=>'已发放总数量', 'field'=>'already_send_count', 'type'=>'text'],
                    ['name'=>'添加时间', 'field'=>'add_time', 'type'=>'text'],
                    ['name'=>'更新时间', 'field'=>'upd_time', 'type'=>'text'],
                    ['name'=>'商品列表', 'field'=>'goods_items', 'type'=>'custom-data-list', 'data' => [
                        ['name'=>'数据索引', 'field'=>'data_index', 'type'=>'text'],
                        ['name'=>'商品URL', 'field' =>'goods_url', 'type'=>'link'],
                        ['name'=>'商品ID', 'field' =>'id', 'type'=>'text'],
                        ['name'=>'标题', 'field' =>'title', 'type'=>'text'],
                        ['name'=>'标题颜色', 'field' =>'title_color', 'type'=>'text'],
                        ['name'=>'简述', 'field' =>'simple_desc', 'type'=>'text'],
                        ['name'=>'型号', 'field' =>'model', 'type'=>'text'],
                        ['name'=>'品牌', 'field' =>'brand_name', 'type'=>'text'],
                        ['name'=>'生产地', 'field' =>'place_origin_name', 'type'=>'text'],
                        ['name'=>'库存', 'field' =>'inventory', 'type'=>'text'],
                        ['name'=>'库存单位', 'field' =>'inventory_unit', 'type'=>'text'],
                        ['name'=>'封面图片', 'field' =>'images', 'type'=>'images'],
                        ['name'=>'原价', 'field' =>'original_price', 'type'=>'text'],
                        ['name'=>'最低原价', 'field' =>'min_original_price', 'type'=>'text'],
                        ['name'=>'最高原价', 'field' =>'max_original_price', 'type'=>'text'],
                        ['name'=>'售价', 'field' =>'price', 'type'=>'text'],
                        ['name'=>'最低售价', 'field' =>'min_price', 'type'=>'text'],
                        ['name'=>'最高售价', 'field' =>'max_price', 'type'=>'text'],
                        ['name'=>'起购数', 'field' =>'buy_min_number', 'type'=>'text'],
                        ['name'=>'限购数', 'field' =>'buy_max_number', 'type'=>'text'],
                        ['name'=>'详情内容', 'field' =>'content_web', 'type'=>'text'],
                        ['name'=>'销量', 'field' =>'sales_count', 'type'=>'text'],
                        ['name'=>'访问量', 'field' =>'access_count', 'type'=>'text'],
                        ['name'=>'原价标题', 'field' =>'show_field_original_price_text', 'type'=>'text'],
                        ['name'=>'原价符号', 'field' =>'show_original_price_symbol', 'type'=>'text'],
                        ['name'=>'原价单位', 'field' =>'show_original_price_unit', 'type'=>'text'],
                        ['name'=>'售价标题', 'field' =>'show_field_price_text', 'type'=>'text'],
                        ['name'=>'售价符号', 'field' =>'show_price_symbol', 'type'=>'text'],
                        ['name'=>'售价单位', 'field' =>'show_price_unit', 'type'=>'text'],
                        ['name'=>'添加时间', 'field' =>'add_time', 'type'=>'text'],
                        ['name'=>'更新时间', 'field' =>'upd_time', 'type'=>'text'],
                    ]],
                ],
                'custom_config' => [
                    'appoint_config' => [
                        'data_url'     => PluginsApiUrl('coupon', 'diycoupon', 'index'),
                        'is_multiple'  => 1,
                        'show_data'    => [
                            'data_key'   => 'id',
                            'data_name'  => 'name',
                        ],
                        'popup_title'   => '优惠券选择',
                        'header' => [
                            [
                                'field'  => 'id',
                                'name'   => '数据ID',
                                'width'  => 120,
                            ],
                            [
                                'field'  => 'name',
                                'name'   => '名称',
                            ],
                            [
                                'field'  => 'desc',
                                'name'   => '描述',
                            ],
                            [
                                'field'  => 'type_name',
                                'name'   => '类型',
                            ],
                            [
                                'field'  => 'expire_type_name',
                                'name'   => '到期类型',
                            ],
                            [
                                'field'  => 'use_limit_type_name',
                                'name'   => '使用限制',
                            ],
                        ],
                        'search_filter_form_config' => [
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'placeholder'  => '请选择优惠券类型',
                                    'is_multiple'  => 1,
                                ],
                                'title'      => '类型',
                                'form_name'  => 'type_ids',
                                'data'       => BaseService::ConstData('coupon_type_list'),
                                'data_key'   => 'value',
                            ],
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'placeholder'  => '请选择到期类型',
                                    'is_multiple'  => 1,
                                ],
                                'title'      => '到期类型',
                                'form_name'  => 'expire_type_ids',
                                'data'       => BaseService::ConstData('coupon_expire_type_list'),
                                'data_key'   => 'value',
                            ],
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'placeholder'  => '请选择使用限制',
                                    'is_multiple'  => 1,
                                ],
                                'title'      => '使用限制',
                                'form_name'  => 'use_limit_type_ids',
                                'data'       => BaseService::ConstData('coupon_use_limit_type_list'),
                                'data_key'   => 'value',
                            ],
                            [
                                'type'    => 'input',
                                'config'  => [
                                    'placeholder'  => '请输入关键字',
                                    'type'         => 'text',
                                ],
                                'title'      => '关键字',
                                'form_name'  => 'keywords',
                            ]
                        ],
                    ],
                    'filter_config' => [
                        'data_url' => PluginsApiUrl('coupon', 'diycoupon', 'autocouponlist'),
                        'filter_form_config' => [
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'is_multiple'  => 1,
                                ],
                                'title'      => '类型',
                                'form_name'  => 'coupon_type_ids',
                                'data'       => BaseService::ConstData('coupon_type_list'),
                                'data_key'   => 'value',
                            ],
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'is_multiple'  => 1,
                                ],
                                'title'      => '到期类型',
                                'form_name'  => 'coupon_expire_type_ids',
                                'data'       => BaseService::ConstData('coupon_expire_type_list'),
                                'data_key'   => 'value',
                            ],
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'is_multiple'  => 1,
                                ],
                                'title'      => '使用限制',
                                'form_name'  => 'coupon_use_limit_type_ids',
                                'data'       => BaseService::ConstData('coupon_use_limit_type_list'),
                                'data_key'   => 'value',
                            ],
                            [
                                'type'    => 'input',
                                'config'  => [
                                    'placeholder'  => '请输入关键字',
                                    'type'         => 'text',
                                ],
                                'title'      => '关键字',
                                'form_name'  => 'coupon_keywords',
                            ],
                            [
                                'type'    => 'input',
                                'config'  => [
                                    'default'  => 4,
                                    'type'     => 'number',
                                ],
                                'title'      => '显示数量',
                                'form_name'  => 'coupon_number',
                            ],
                            [
                                'type'       => 'radio',
                                'title'      => '排序类型',
                                'form_name'  => 'coupon_order_by_type',
                                'data'       => BaseService::ConstData('coupon_order_by_type_list'),
                                'data_key'   => 'index',
                                'data_name'  => 'name',
                                'config'     => [
                                    'default'      => 0,
                                ]
                            ],
                            [
                                'type'       => 'radio',
                                'title'      => '排序规则',
                                'form_name'  => 'coupon_order_by_rule',
                                'const_key'  => 'data_order_by_rule_list',
                                'data_key'   => 'index',
                                'data_name'  => 'name',
                                'config'     => [
                                    'default'      => 0,
                                ]
                            ],
                            [
                                'type'       => 'switch',
                                'title'      => '重复领取',
                                'form_name'  => 'coupon_is_repeat_receive',
                            ],
                        ],
                    ],
                ],
            ];
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
                        'name'  => MyLang('coupon_name'),
                        'type'  => 'coupon',
                        'data'  => [
                            ['name'=>'优惠券首页', 'page'=>'/pages/plugins/coupon/index/index'],
                            ['name'=>'我的优惠券', 'page'=>'/pages/plugins/coupon/user/user'],
                        ],
                    ];
                    break;
                }
            }
        }

        // 模块
        if(isset($params['data']['module_list']) && is_array($params['data']['module_list']))
        {
            foreach($params['data']['module_list'] as &$mv)
            {
                if(isset($mv['data']) && isset($mv['key']) && $mv['key'] == 'plugins')
                {
                    $mv['data'][] = [
                        'key' => 'coupon',
                        'name' => MyLang('coupon_name'),
                    ];
                    break;
                }
            }
        }

        // 静态数据
        if(isset($params['data']['plugins']) && is_array($params['data']['plugins']))
        {
            $params['data']['plugins']['coupon'] = [
                // 优惠券类型
                'coupon_type_list' => BaseService::ConstData('coupon_type_list')
            ];
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
        $params['data']['plugins']['data'][] = [
            'name'  => '优惠券',
            'value' => 'coupon',
            'data'  => [
                [ 'value' => 'home', 'name' => '领券中心'],
                [ 'value' => 'user', 'name' => '我的优惠券'],
            ],
        ];
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
        $params['static_url_web_arr']['plugins-coupon-home'] = PluginsAdminService::PluginsSecondDomainUrl('coupon', true);
        $params['static_url_web_arr']['plugins-coupon-user'] = PluginsHomeUrl('coupon', 'coupon', 'index');
        $params['static_url_app_arr']['plugins-coupon-home'] = '/pages/plugins/coupon/index/index';
        $params['static_url_app_arr']['plugins-coupon-user'] = '/pages/plugins/coupon/user/user';
    }

    /**
     * 多商户页面主导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-05-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function ShopNavigationNav($params = [])
    {
        if(!empty($params['shop']) && isset($this->plugins_config['is_shop_nav_coupon']) && $this->plugins_config['is_shop_nav_coupon'] == 1)
        {
            // 是否有有效优惠券
            $where = [
                ['is_enable', '=', 1],
                ['is_user_receive', '=', 1],
                ['shop_id', '=', $params['shop']['id']],
            ];
            $count = CouponService::CouponTotal($where);
            if($count > 0)
            {
                if(APPLICATION == 'app')
                {
                    $url = '/pages/plugins/coupon/shop/shop?id='.$params['shop']['id'];
                } else {
                    $url = PluginsHomeUrl('coupon', 'index', 'shop', ['id'=>$params['shop']['id']]);
                }
                $title = empty($this->plugins_config['shop_application_name']) ? '优惠券' : $this->plugins_config['shop_application_name'];
                $params['data'][] = [
                    'id'                    => 0,
                    'pid'                   => 0,
                    'name'                  => $title,
                    'url'                   => $url,
                    'data_type'             => 'system',
                    'is_show'               => 1,
                    'is_new_window_open'    => 0,
                ];
            }
        }
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
        $params['data']['extends'][] = [
            'name'          => '优惠券',
            'desc'          => '创建店铺优惠券、可给指定用户发放优惠券',
            'url'           => PluginsHomeUrl('coupon', 'shopcoupon', 'index'),
            'icon'          => SystemBaseService::AttachmentHost().'/static/plugins/coupon/images/shop-coupon.png',
            'business'      => 'coupon',
            'is_popup'      => 1,
            'is_full'       => 1,
        ];
    }

    /**
     * 下单接口数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function BuyResultHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['goods_list']))
        {
            $result = [];
            foreach($params['data']['goods_list'] as $v)
            {
                // 排除秒杀商品订单
                if($this->is_exclude_seckill_goods_order && !empty($v['goods_items']))
                {
                    if(BaseService::GoodsIsExistValidSeckill(array_column($v['goods_items'], 'goods_id')))
                    {
                        continue;
                    }
                }

                // 获取优惠券
                $ret = BaseService::BuyUserCouponDataHandle($this->plugins_config, $v['id'], $v['goods_items'], $params['params']);
                $result[] = [
                    'warehouse_id'      => $v['id'],
                    'warehouse_name'    => $v['name'],
                    'coupon_data'       => $ret,
                ];
            }
            $params['data']['plugins_coupon_data'] = $result;
        }
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
            $data = $this->GoodsDetailCoupinData($params['data']['goods']['id']);
            if(!empty($data))
            {
                $params['data']['plugins_coupon_data'] = [
                    'base'  => $this->plugins_config,
                    'data'  => $data,
                ];
            }
        }
    }

    /**
     * 注册送劵
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-15
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function UserRegisterGiveHandle($params = [])
    {
        if(!empty($params['user_id']))
        {
            UserCouponService::UserRegisterGive($params['user_id']);
        }
    }

    /**
     * 订单状态改变处理,状态为取消|关闭时释放优惠券
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-15
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function OrderStatusUpdateHandle($params = [])
    {
        if(!empty($params['data']) && isset($params['data']['new_status']) && in_array($params['data']['new_status'], [5,6]) && !empty($params['order_id']))
        {
            // 释放用户优惠券
            UserCouponService::UserCouponUseStatusUpdate(Db::name('Order')->where(['id'=>intval($params['order_id'])])->value('extension_data'), 0, 0);
        }
    }

    /**
     * 订单添加成功处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function OrderInsertSuccessHandle($params = [])
    {
        if(!empty($params['order_ids']))
        {
            $order = Db::name('Order')->where(['id'=>$params['order_ids']])->field('id,extension_data')->select()->toArray();
            if(!empty($order))
            {
                // 更新优惠券使用状态
                foreach($order as $v)
                {
                    UserCouponService::UserCouponUseStatusUpdate($v['extension_data'], 1, $v['id']);
                }
            }
        }
    }

    /**
     * 满减计算
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function BuyDiscountCalculate($params = [])
    {
        $currency_symbol = ResourcesService::CurrencyDataSymbol();
        foreach($params['data'] as &$v)
        {
            // 排除秒杀商品订单
            if($this->is_exclude_seckill_goods_order && !empty($v['goods_items']))
            {
                if(BaseService::GoodsIsExistValidSeckill(array_column($v['goods_items'], 'goods_id')))
                {
                    continue;
                }
            }

            // 获取优惠券
            $ret = BaseService::BuyUserCouponDataHandle($this->plugins_config, $v['id'], $v['goods_items'], $params['params']['params']);
            if(!empty($ret['coupon_choice']) && !empty($ret['coupon_choice']['buy_goods_ids']))
            {
                // 优惠券是否限定, 则读取优惠券可用商品id重新计算
                $order_price = 0.00;
                if($ret['coupon_choice']['coupon']['use_limit_type'] > 0)
                {
                    foreach($v['goods_items'] as $goods)
                    {
                        if(in_array($goods['goods_id'], $ret['coupon_choice']['buy_goods_ids']))
                        {
                            $order_price += $goods['total_price'];
                        }
                    }
                } else {
                    $order_price = $v['order_base']['total_price'];
                }
                if($order_price > 0)
                {
                    $discount_price = BaseService::PriceCalculate($order_price, $ret['coupon_choice']['coupon']['type'], $ret['coupon_choice']['coupon']['where_order_price'], $ret['coupon_choice']['coupon']['discount_value']);

                    if($discount_price > 0)
                    {
                        // 扩展展示数据
                        $title = ($ret['coupon_choice']['coupon']['type'] == 0) ? MyLang('coupon_name') : MyLang('discount_coupon_text');
                        $v['order_base']['extension_data'][] = [
                            'name'      => $title.(empty($ret['coupon_choice']['coupon']['desc']) ? '' : '-'.$ret['coupon_choice']['coupon']['desc']),
                            'price'     => $discount_price,
                            'type'      => 0,
                            'tips'      => '-'.$currency_symbol.$discount_price,
                            'business'  => 'plugins-coupon',
                            'ext'       => $ret['coupon_choice'],
                        ];
                    }
                }
            }
        }
        return DataReturn(MyLang('handle_success'), 0);
    }

    /**
     * 购买确认页面优惠券选择
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function BuyCoupinView($params = [])
    {
        // 当前仓库id
        $warehouse_id = $params['data']['id'];

        // 获取用户优惠券
        $ret = BaseService::BuyUserCouponDataHandle($this->plugins_config, $warehouse_id, $params['data']['goods_items'], $params['params']);
        return MyView('../../../plugins/coupon/view/index/public/buy', [
            'coupon_choice'  => $ret['coupon_choice'],
            'coupon_list'    => $ret['coupon_list'],
            'warehouse_id'   => $warehouse_id,
            'params'         => $params['params'],
        ]);
    }

    /**
     * 购买提交订单页面隐藏域html
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function BuyFormInsideInput($params = [])
    {
        $ret = '';
        if(!empty($params['params']) && is_array($params['params']))
        {
            $ids = [];
            $key_field_first = 'coupon_id_';
            foreach($params['params'] as $k=>$v)
            {
                if(substr($k, 0, strlen($key_field_first)) == $key_field_first)
                {
                    $key = str_replace($key_field_first, '', $k);
                    $ids[$key] = $v;
                }
            }
            if(!empty($ids))
            {
                foreach($ids as $k=>$v)
                {
                    $ret .= '<input type="hidden" name="'.$key_field_first.$k.'" value="'.$v.'" />';
                }
            }
        }
        return $ret;
    }

    /**
     * 商品详情面板底部
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function GoodsDetailCoupinView($params = [])
    {
        if(!empty($params['goods_id']))
        {
            MyViewAssign('coupon_list', $this->GoodsDetailCoupinData($params['goods_id']));
            return MyView('../../../plugins/coupon/view/index/public/goods_detail_panel');
        }        
    }

    /**
     * 商品页面优惠券
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-07
     * @desc    description
     * @param   [int]          $goods_id [商品id]
     */
    private function GoodsDetailCoupinData($goods_id)
    {
        $coupon_params = [
            'where'             => [
                'is_enable'         => 1,
                'is_user_receive'   => 1,
            ],
            'm'                 => 0,
            'n'                 => 0,
            'is_sure_receive'   => 1,
            'user'              => UserService::LoginUserInfo(),
        ];
        $ret = CouponService::CouponList($coupon_params);
        // 排除商品不支持的活动
        if(!empty($ret['data']))
        {
            $ret['data'] = BaseService::CouponListGoodsExclude(['data'=>$ret['data'], 'goods_id'=>intval($goods_id)]);
        }
        return empty($ret['data']) ? [] : $ret['data'];
    }

    /**
     * 中间大导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function NavigationHeaderHandle($params = [])
    {
        if(is_array($params['header']) && !empty($this->plugins_config['application_name']))
        {
            $nav = [
                'id'                    => 0,
                'pid'                   => 0,
                'name'                  => $this->plugins_config['application_name'],
                'url'                   => PluginsAdminService::PluginsSecondDomainUrl('coupon', true),
                'data_type'             => 'custom',
                'is_show'               => 1,
                'is_new_window_open'    => 0,
                'items'                 => [],
            ];
            array_unshift($params['header'], $nav);
        }
    }

    /**
     * 用户中心左侧菜单处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function UserCenterLeftMenuHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['property']) && isset($params['data']['property']['item']) && is_array($params['data']['property']['item']))
        {
            $params['data']['property']['item'][] = [
                'name'      =>  MyLang('user_center_coupon_title', null, null, 'coupon'),
                'url'       =>  PluginsHomeUrl('coupon', 'coupon', 'index'),
                'contains'  =>  ['couponcouponindex'],
                'is_show'   =>  1,
                'icon'      =>  'am-icon-gift',
            ];
        }
    }

    /**
     * 顶部小导航右侧-我的商城
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommonTopNavRightMenuHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data'][1]) && isset($params['data'][1]['items']) && is_array($params['data'][1]['items']))
        {
            array_push($params['data'][1]['items'], [
                'name'  => MyLang('user_center_coupon_title', null, null, 'coupon'),
                'url'   => PluginsHomeUrl('coupon', 'coupon', 'index'),
            ]);
        }
    }
}
?>