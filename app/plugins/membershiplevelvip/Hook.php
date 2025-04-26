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
namespace app\plugins\membershiplevelvip;

use think\facade\Db;
use app\service\PluginsService;
use app\service\GoodsService;
use app\service\ResourcesService;
use app\service\SystemBaseService;
use app\service\PluginsAdminService;
use app\plugins\membershiplevelvip\service\BaseService;
use app\plugins\membershiplevelvip\service\BusinessService;
use app\plugins\membershiplevelvip\service\LevelService;

/**
 * 会员等级增强版插件 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 插件配置信息
    private $base_config;

    // 模块、控制器、方法
    private $module_name;
    private $controller_name;
    private $action_name;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-09T14:25:44+0800
     * @param    [array]                    $params [输入参数]
     */
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 插件配置信息
            $base = BaseService::BaseConfig();
            $this->base_config = $base['data'];

            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();

            // 是否开启用户中心菜单
            $is_user_menu = isset($this->base_config['is_user_menu']) && $this->base_config['is_user_menu'] == 1;

            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 公共css
                case 'plugins_css' :
                    $ret = 'static/plugins/membershiplevelvip/css/index/style.css';
                    break;

                // 在前面添加导航
                case 'plugins_service_navigation_header_handle' :
                    $ret = $this->NavigationHeaderHandle($params);
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

                // 满减优惠、免运费
                case 'plugins_service_buy_group_goods_handle' :
                    $ret = $this->FullReductionCalculate($params);
                    break;

                // 用户登录成功信息纪录钩子 icon处理
                case 'plugins_service_user_login_success_record' :
                    $ret = $this->UserLoginSuccessIconHandle($params);
                    break;

                // 后台商品编辑规格会员等级
                case 'plugins_service_goods_spec_extends_handle' :
                    $ret = $this->GoodsSpecExtendsHandle($params);
                    break;

                // 后台用户保存页面
                case 'plugins_view_admin_user_save' :
                    $ret = $this->AdminUserSaveHandle($params);
                    break;

                // 后台用户动态列表会员等级
                case 'plugins_module_form_admin_user_index' :
                case 'plugins_module_form_admin_user_detail' :
                case 'plugins_module_form_admin_user_excelexport' :
                    if(isset($this->base_config['is_admin_user_level_show']) && $this->base_config['is_admin_user_level_show'] == 1)
                    {
                        $ret = $this->AdminFormUserHandle($params);
                    }
                    break;

                // 用户数据列表处理
                case 'plugins_service_user_list_handle_end' :
                    if(isset($this->base_config['is_admin_user_level_show']) && $this->base_config['is_admin_user_level_show'] == 1)
                    {
                        $ret = $this->UserDataListHandle($params);
                    }
                    break;

                // 用户保存处理
                case 'plugins_service_user_save_handle' :
                    $ret = $this->UserSaveServiceHandle($params);
                    break;

                // 商品保存处理
                case 'plugins_service_goods_save_handle' :
                    $ret = $this->GoodsSaveServiceHandle($params);
                    break;

                // 商品基础数据更新、新版本替代上面商品保存的方案
                case 'plugins_service_goods_base_update' :
                    $ret = $this->GoodsBaseUpdateHandle($params);
                    break;

                // 商品价格上面钩子
                case 'plugins_view_goods_detail_panel_price_top' :
                    if(APPLICATION == 'web' && $this->module_name.$this->controller_name.$this->action_name == 'indexgoodsindex' && !empty($params['goods']) && !empty($params['goods']['id']) && MyC('common_goods_original_price_status', 0, true) == 1 && MyC('common_goods_sales_price_status', 0, true) == 1)
                    {
                        // 是否已支持优惠
                        if(SystemBaseService::IsGoodsDiscountRecord($params['goods']['id'], 'membershiplevelvip'))
                        {
                            $ret = $this->GoodsDetailViewPriceTop($params);
                        }
                    }
                    break;

                // 商品列表弹层价格钩子
                case 'plugins_view_module_goods_inside_bottom' :
                    if(APPLICATION == 'web' && isset($params['module']) && $params['module'] == 'grid-base' && isset($this->base_config['is_user_goods_hover_price']) && $this->base_config['is_user_goods_hover_price'] == 1)
                    {
                        $ret = $this->GoodsListViewPriceContent($params);
                    }
                    break;

                // 商品详情获取规格类型处理
                case 'plugins_service_goods_spec_type' :
                    $this->GoodsSpecType($params);
                    break;

                // 用户中心左侧导航
                case 'plugins_service_users_center_left_menu_handle' :
                    if($is_user_menu)
                    {
                        $ret = $this->UserCenterLeftMenuHandle($params);
                    }
                    break;

                // 顶部小导航右侧-我的商城
                case 'plugins_service_header_navigation_top_right_handle' :
                    if($is_user_menu)
                    {
                        $ret = $this->CommonTopNavRightMenuHandle($params);
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

                // 静态数据处理
                case 'plugins_service_const_data' :
                    $this->ConstData($params);
                    break;

                // diyapi初始化
                case 'plugins_service_diyapi_init_data' :
                    $this->DiyApiInitDataHandle($params);
                    break;
            }
        }
        return $ret;
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
                        'name'  => '会员等级增强版',
                        'type'  => 'membershiplevelvip',
                        'data'  => [
                            ['name'=>'会员首页', 'page'=>'/pages/plugins/membershiplevelvip/index/index'],
                            ['name'=>'会员中心', 'page'=>'/pages/plugins/membershiplevelvip/user/user'],
                            ['name'=>'开通订单', 'page'=>'/pages/plugins/membershiplevelvip/order/order'],
                            ['name'=>'收益明细', 'page'=>'/pages/plugins/membershiplevelvip/profit/profit'],
                            ['name'=>'我的团队', 'page'=>'/pages/plugins/membershiplevelvip/team/team'],
                            ['name'=>'推广奖励', 'page'=>'/pages/plugins/membershiplevelvip/poster/poster'],
                            ['name'=>'会员码', 'page'=>'/pages/plugins/membershiplevelvip/member-code/member-code'],
                        ],
                    ];
                    break;
                }
            }
        }
    }

    /**
     * 常量数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ConstData($params = [])
    {
        // 新增订单状态
        if(!empty($params['key']))
        {
            switch($params['key'])
            {
                // 支付日志业务类型
                case 'common_pay_log_business_type_list' :
                    $value = BaseService::$business_type_name;
                    $params['data'][$value] = ['value' => $value, 'name' => '会员等级'];
                    break;
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
        $params['data']['plugins']['data'][] = [
            'name'  => '会员等级增强版',
            'value' => 'membershiplevelvip',
            'data'  => [
                [ 'value' => 'home', 'name' => '会员首页'],
                [ 'value' => 'user-center', 'name' => '会员中心'],
                [ 'value' => 'user-poster', 'name' => '推广奖励'],
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
        $params['static_url_web_arr']['plugins-membershiplevelvip-home'] = PluginsAdminService::PluginsSecondDomainUrl('membershiplevelvip', true);
        $params['static_url_web_arr']['plugins-membershiplevelvip-user-center'] = PluginsHomeUrl('membershiplevelvip', 'vip', 'index');
        $params['static_url_web_arr']['plugins-membershiplevelvip-user-poster'] = PluginsHomeUrl('membershiplevelvip', 'poster', 'index');
        $params['static_url_app_arr']['plugins-membershiplevelvip-home'] = '/pages/plugins/membershiplevelvip/index/index';
        $params['static_url_app_arr']['plugins-membershiplevelvip-user-center'] = '/pages/plugins/membershiplevelvip/user/user';
        $params['static_url_app_arr']['plugins-membershiplevelvip-user-poster'] = '/pages/plugins/membershiplevelvip/poster/poster';
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
        // 是否开启会员购买
        if(BusinessService::IsUserPay())
        {
            if(is_array($params['header']))
            {
                // 获取应用数据
                if(!empty($this->base_config['application_name']))
                {
                    $nav = [
                        'id'                    => 0,
                        'pid'                   => 0,
                        'name'                  => $this->base_config['application_name'],
                        'url'                   => PluginsAdminService::PluginsSecondDomainUrl('membershiplevelvip', true),
                        'data_type'             => 'custom',
                        'is_show'               => 1,
                        'is_new_window_open'    => 0,
                        'items'                 => [],
                    ];
                    array_unshift($params['header'], $nav);
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
     * @param   array           $params [description]
     */
    public function UserCenterLeftMenuHandle($params = [])
    {
        $params['data']['business']['item'][] = [
            'name'      =>  '我的会员',
            'url'       =>  PluginsHomeUrl('membershiplevelvip', 'vip', 'index'),
            'group'     =>  'membershiplevelvip',
            'contains'  =>  ['membershiplevelvipvipindex', 'membershiplevelvipposterindex', 'membershiplevelvipprofitindex', 'membershiplevelviporderindex', 'membershiplevelvipteamindex'],
            'is_show'   =>  1,
            'icon'      =>  'am-icon-coffee',
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
            'name'  => '我的会员',
            'url'   => PluginsHomeUrl('membershiplevelvip', 'vip', 'index'),
        ]);
    }

    /**
     * 商品列表金额内容
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-07-30T22:02:44+0800
     * @param   [array]          $params [输入参数]
     */
    private function GoodsListViewPriceContent($params = [])
    {
        if(!empty($params['goods']))
        {
            $is_membershiplevelvip = false;

            // 是否已支持优惠
            if(!empty($params['goods']['id']) && SystemBaseService::IsGoodsDiscountRecord($params['goods']['id'], 'membershiplevelvip'))
            {
                $level = BusinessService::UserLevelMatching(0, $params);
                if(!empty($level))
                {
                    // 指定商品售价
                    if(!empty($params['goods']['plugins_membershiplevelvip_price_extends']))
                    {
                        $extends = json_decode($params['goods']['plugins_membershiplevelvip_price_extends'], true);
                        if(!empty($extends[$level['id']]) && isset($extends[$level['id']]['price']) && $extends[$level['id']]['price'] !== '')
                        {
                            $is_membershiplevelvip = true;
                        }
                    }
                    
                    // 自动折扣售价
                    if($is_membershiplevelvip == false && $level['discount_rate'] > 0 && BaseService::IsGoodsDiscountConfig($params['goods']['id'], $this->base_config))
                    {
                        $is_membershiplevelvip = true;
                    }
                }
            }

            MyViewAssign('is_membershiplevelvip', $is_membershiplevelvip);
            MyViewAssign('goods_data', $params['goods']);
            return MyView('../../../plugins/membershiplevelvip/view/index/public/items_goods_price');
        }
    }

    /**
     * 商品详情获取规格类型处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-25
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function GoodsSpecType($params = [])
    {
        if(!empty($params['goods_id']))
        {
            $level = BusinessService::UserLevelMatching(0, $params);
            if(!empty($level))
            {
                $price = $this->GoodsSalePrice($params['goods_id'], $level['id']);
                if(!empty($price))
                {
                    $params['data']['extends_element'][] = [
                        'element'   => '.plugins-membershiplevelvip-goods-price-top',
                        'content'   => $this->GoodsDetailPrice($params['goods_id'], $price, $price),
                    ];
                }
            }
        }
    }

    /**
     * 根据商品id获取会员等级扩展数据价格
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-25
     * @desc    description
     * @param   [int]          $goods_id            [商品id]
     * @param   [int]          $level_id            [会员等级id]
     * @param   [boolean]      $is_extends_check    [是否需要校验是否存在会员扩展数据]
     */
    private function GoodsSalePrice($goods_id, $level_id, $is_extends_check = true)
    {
        $where = [
            ['id', '=', $goods_id],
            ['is_shelves', '=', 1],
            ['is_delete_time', '=', 0],
        ];
        $price = 0.00;
        $goods = Db::name('Goods')->where($where)->field('id,price,plugins_membershiplevelvip_price_extends')->find();
        if(!empty($goods))
        {
            // 扩展数据是否存在会员等级自定义售价
            if($is_extends_check === true)
            {
                if(!empty($goods['plugins_membershiplevelvip_price_extends']))
                {
                    $extedns = json_decode($goods['plugins_membershiplevelvip_price_extends'], true);
                    if(!empty($extedns[$level_id]) && isset($extedns[$level_id]['price']) && $extedns[$level_id]['price'] !== '')
                    {
                        $price = $extedns[$level_id]['price'];
                    }
                }
            } else {
                // 商品售价
                $price = $goods['price'];
            }
        }
        return $price;
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
        $level = BusinessService::UserLevelMatching(0, $params);
        if(!empty($level))
        {
            // 指定商品售价
            if(!empty($params['goods']['plugins_membershiplevelvip_price_extends']))
            {
                $extends = json_decode($params['goods']['plugins_membershiplevelvip_price_extends'], true);
                if(!empty($extends[$level['id']]) && isset($extends[$level['id']]['price']) && $extends[$level['id']]['price'] !== '')
                {
                    return $this->GoodsDetailPrice($params['goods']['id'], $params['goods']['price_container']['price'], $params['goods']['price_container']['price']);
                }
            }
        
            // 自动折扣商品售价
            if($level['discount_rate'] > 0 && BaseService::IsGoodsDiscountConfig($params['goods']['id'], $this->base_config))
            {
                return $this->GoodsDetailPrice($params['goods']['id'], $params['goods']['price_container']['price'], $params['goods']['price_container']['price']);
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
     * @param   [int]          $goods_id              [商品id]
     * @param   [float]        $goods_price           [商品销售价格]
     * @param   [float]        $goods_original_price  [商品原价价格]
     */
    private function GoodsDetailPrice($goods_id, $goods_price, $goods_original_price)
    {
        if(APPLICATION == 'web' && $this->module_name == 'index')
        {
            if(!isset($this->base_config['is_user_hide_sales_price']) || $this->base_config['is_user_hide_sales_price'] != 1)
            {
                return MyView('../../../plugins/membershiplevelvip/view/index/public/detail_goods_price', [
                    'currency_symbol'       => ResourcesService::CurrencyDataSymbol(),
                    'goods_original_price'  => $goods_original_price,
                    'goods_price'           => $goods_price,
                    'inventory_unit'        => (MyC('common_goods_sales_price_unit_status') == 1) ? Db::name('Goods')->where(['id'=>$goods_id])->value('inventory_unit') : '',
                ]);
            }
        }
        return '';
    }

    /**
     * 商品基础数据更新
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-23
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function GoodsBaseUpdateHandle($params = [])
    {
        $spec_all = [];
        $spec_extends = [];
        if(!empty($params['goods_id']))
        {
            $goods_spec = Db::name('GoodsSpecBase')->where(['goods_id'=>$params['goods_id']])->field('price,extends')->select()->toArray();
            foreach($goods_spec as $k=>$v)
            {
                if(!empty($v['extends']))
                {
                    $temp = json_decode($v['extends'], true);
                    if(!empty($temp))
                    {
                        foreach($temp as $ks=>$vs)
                        {
                            if($vs !== '' && substr($ks, 0, 33) == 'plugins_membershiplevelvip_price_')
                            {
                                $key = str_replace('plugins_membershiplevelvip_price_', '', $ks);
                                if(!array_key_exists($key, $spec_extends))
                                {
                                    $spec_extends[$key] = [];
                                }
                                $spec_extends[$key][$k] = PriceNumberFormat($vs);
                            }
                        }
                    }
                }
                $spec_all[] = $v['price'];
            }
        }

        // 扩展数据处理
        $result = [];
        if(!empty($spec_extends))
        {
            foreach($spec_extends as $k=>$v)
            {
                // 防止会员价未全部设置，将原始数据未设置的加入列表防止出现价格差异
                foreach($spec_all as $ks=>$vs)
                {
                    if(!array_key_exists($ks, $v))
                    {
                        $v[$ks] = $vs;
                    }
                }

                // 价格处理
                $min_price = min($v);
                $max_price = max($v);

                $data = [
                    'min_price' => $min_price,
                    'max_price' => $max_price,
                ];
                $data['price'] = (!empty($data['max_price']) && $data['min_price'] != $data['max_price']) ? (empty($data['min_price']) ? $data['max_price'] : $data['min_price'].'-'.$data['max_price']) : $data['min_price'];
                $result[$k] = $data;
            }
        }

        // 商品数据更新
        $update_data = [
            'plugins_membershiplevelvip_price_extends'  => empty($result) ? '' : json_encode($result),
            'upd_time'                                  => time(),
        ];
        if(Db::name('Goods')->where(['id'=>$params['goods_id']])->update($update_data) === false)
        {
            return DataReturn('会员等级数据更新失败', -1);
        }

        return DataReturn(MyLang('handle_success'), 0);
    }

    /**
     * 商品信息保存处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-23
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function GoodsSaveServiceHandle($params = [])
    {
        $spec_all = [];
        $spec_extends = [];
        if(!empty($params['spec']['data']))
        {
            $spec_count = 6;
            foreach($params['spec']['data'] as $k=>$v)
            {
                $count = count($v);
                if(!empty($v[$count-1]))
                {
                    $temp = json_decode(htmlspecialchars_decode($v[$count-1]), true);
                    if(!empty($temp))
                    {
                        foreach($temp as $ks=>$vs)
                        {
                            if($vs !== '' && substr($ks, 0, 33) == 'plugins_membershiplevelvip_price_')
                            {
                                $key = str_replace('plugins_membershiplevelvip_price_', '', $ks);
                                if(!array_key_exists($key, $spec_extends))
                                {
                                    $spec_extends[$key] = [];
                                }
                                $spec_extends[$key][$k] = PriceNumberFormat($vs);
                            }
                        }
                    }
                }
                if(isset($v[$count-$spec_count]))
                {
                    $spec_all[] = $v[$count-$spec_count];
                }
            }
        }

        // 扩展数据处理
        if(!empty($spec_extends))
        {
            $result = [];
            foreach($spec_extends as $k=>$v)
            {
                // 防止会员价未全部设置，将原始数据未设置的加入列表防止出现价格差异
                foreach($spec_all as $ks=>$vs)
                {
                    if(!array_key_exists($ks, $v))
                    {
                        $v[$ks] = $vs;
                    }
                }

                // 价格处理
                $min_price = min($v);
                $max_price = max($v);

                $data = [
                    'min_price' => $min_price,
                    'max_price' => $max_price,
                ];
                $data['price'] = (!empty($data['max_price']) && $data['min_price'] != $data['max_price']) ? (empty($data['min_price']) ? $data['max_price'] : $data['min_price'].'-'.$data['max_price']) : $data['min_price'];
                $result[$k] = $data;
            }
            $params['data']['plugins_membershiplevelvip_price_extends'] = json_encode($result);
        } else {
            $params['data']['plugins_membershiplevelvip_price_extends'] = '';
        }
        return DataReturn(MyLang('handle_success'), 0);
    }

    /**
     * 用户信息保存处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-23
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function UserSaveServiceHandle($params = [])
    {
        $params['data']['plugins_user_level'] = isset($params['params']['plugins_user_level']) ? $params['params']['plugins_user_level'] : '';
        return DataReturn(MyLang('handle_success'), 0);
    }

    /**
     * 用户信息保存页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-07-23
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function AdminUserSaveHandle($params = [])
    {
        $ret = LevelService::DataList(['where'=>['is_enable'=>1]]);
        if(!empty($ret['data']))
        {
            MyViewAssign('user_data', $params['data']);
            MyViewAssign('level_list', $ret['data']);
            return MyView('../../../plugins/membershiplevelvip/view/admin/public/user');
        }
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
                $v['membershiplevelvip_auto_level_data'] = BusinessService::UserVip($v['id']);
            }
        }
    }

    /**
     * 后台用户动态列表会员等级
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
                        'label'         => '会员等级(手动)',
                        'view_type'     => 'field',
                        'view_key'      => 'plugins_user_level',
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
                        'label'         => '会员等级(自动)',
                        'view_type'     => 'module',
                        'view_key'      => '../../../plugins/membershiplevelvip/view/admin/public/user_level_view',
                        'is_sort'       => 1,
                        'search_config' => [
                            'form_type'         => 'select',
                            'form_name'         => 'plugins_user_auto_level',
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
     * @date    2019-07-23
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function GoodsSpecExtendsHandle($params = [])
    {
        $ret = LevelService::DataList(['where'=>['is_enable'=>1]]);
        if($ret['code'] == 0 && !empty($ret['data']))
        {
            $element = [];
            foreach($ret['data'] as $v)
            {
                $element[] = [
                    'element'       => 'input',
                    'type'          => 'text',
                    'name'          => 'plugins_membershiplevelvip_price_'.$v['id'],
                    'placeholder'   => $v['name'].'销售价',
                    'title'         => $v['name'].'销售价',
                    'is_required'   => 0,
                    'message'       => '请填写会员销售价',
                    'desc'          => '会员等级对应销售金额',
                ];
            }

            // 配置信息
            if(count($element) > 0)
            {
                $plugins = [
                    'name'      => '会员等级增强版',
                    'desc'      => '按照会员等级设定不同金额',
                    'element'   => $element,
                ];
                $params['data'][] = $plugins;
            }
        }
    }

    /**
     * 用户icon处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-28
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function UserLoginSuccessIconHandle($params = [])
    {
        if(!empty($params['user']))
        {
            // 用户等级
            $vip = BusinessService::UserLevelMatching($params['user']['id']);
            if(!empty($vip) && !empty($vip['icon']))
            {
                $params['user']['icon'] = $vip['icon'];
            }
        }
        return DataReturn(MyLang('handle_success'), 0);
    }

    /**
     * 满减、免运费计算
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-21
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function FullReductionCalculate($params = [])
    {
        if(!empty($params['data']))
        {
            // 用户等级
            $vip = BusinessService::UserLevelMatching(0, $params);
            if(!empty($vip))
            {
                // 满减
                $order_price = isset($vip['order_price']) ? $vip['order_price'] : 0;
                $full_reduction_price = isset($vip['full_reduction_price']) ? $vip['full_reduction_price'] : 0;
                if($order_price > 0 && $full_reduction_price)
                {
                    $currency_symbol = ResourcesService::CurrencyDataSymbol();
                    $show_name = $vip['name'].'-满减';
                    foreach($params['data'] as &$mv)
                    {
                        if($mv['order_base']['total_price'] >= $order_price)
                        {
                            // 扩展展示数据
                            $mv['order_base']['extension_data'][] = [
                                'name'      => $show_name,
                                'price'     => $full_reduction_price,
                                'type'      => 0,
                                'business'  => 'plugins-membershiplevelvip',
                                'tips'      => '-'.$currency_symbol.$full_reduction_price,
                            ];
                        }
                    }
                }

                // 免运费
                if(!empty($vip['free_shipping_price']) && $vip['free_shipping_price'] > 0)
                {
                    // 是否跨订单
                    $is_span_free_shipping_price = isset($vip['is_span_free_shipping_price']) && $vip['is_span_free_shipping_price'] == 1;
                    if($is_span_free_shipping_price)
                    {
                        $order_total = array_sum(array_map(function($item)
                        {
                            return $item['total_price'];
                        }, array_column($params['data'], 'order_base')));
                        if($order_total < $vip['free_shipping_price'])
                        {
                            $is_span_free_shipping_price = false;
                        }
                    }

                    // 循环处理多个订单
                    foreach($params['data'] as &$fv)
                    {
                        // 仅处理订单快递模式
                        if(isset($fv['order_base']['site_model']) && $fv['order_base']['site_model'] == 0 && !empty($fv['order_base']['extension_data']))
                        {
                            // 满足跨订单或者单个订单满足
                            if($is_span_free_shipping_price || $fv['order_base']['total_price'] >= $vip['free_shipping_price'])
                            {
                                foreach($fv['order_base']['extension_data'] as $ek=>$ev)
                                {
                                    if(isset($ev['business']) && $ev['business'] == 'plugins-freightfee' && (empty($ev['ext']) || $ev['ext'] != 'special'))
                                    {
                                        unset($fv['order_base']['extension_data'][$ek]);
                                        break;
                                    }
                                }
                                $fv['order_base']['extension_data'] = array_values($fv['order_base']['extension_data']);
                            }
                        }
                    }
                }
            }
        }
        return DataReturn(MyLang('handle_success'), 0);
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
        if(!empty($params['data']) && is_array($params['data']))
        {
            // 用户等级
            $vip = BusinessService::UserLevelMatching(0, $params);
            if(!empty($vip))
            {
                // 是否还可以使用优惠
                $discount = SystemBaseService::IsGoodsDiscount($params);
                if(!empty($discount) && ($discount == 1 || (is_array($discount) && array_sum($discount) > 0)))
                {
                    // 商品扩展数据类似存储
                    static $plugins_membershiplevelvip_price_extends_static_data = [];

                    // 未开启原价显示  或  手机端  则pc端展示销售价，手机端原价显示为售价
                    $goods_original_price_status = MyC('common_goods_original_price_status', 0, true) != 1 || APPLICATION == 'app';

                    // key字段
                    $key_field = empty($params['params']['data_key_field']) ? 'id' : $params['params']['data_key_field'];
                    foreach($params['data'] as &$goods)
                    {
                        if(!empty($goods[$key_field]) && ($discount == 1 || (is_array($discount) && array_key_exists($goods[$key_field], $discount) && $discount[$goods[$key_field]] == 1)))
                        {
                            $status = false;
                            // 自定义商品售价、字段不存在则读取
                            if(array_key_exists('plugins_membershiplevelvip_price_extends', $goods))
                            {
                                $price_extends = $goods['plugins_membershiplevelvip_price_extends'];
                            } else {
                                if(array_key_exists($goods[$key_field], $plugins_membershiplevelvip_price_extends_static_data))
                                {
                                    $price_extends = $plugins_membershiplevelvip_price_extends_static_data[$goods[$key_field]];
                                } else {
                                    $price_extends = Db::name('Goods')->where(['id'=>$goods[$key_field]])->value('plugins_membershiplevelvip_price_extends');
                                    $plugins_membershiplevelvip_price_extends_static_data[$goods[$key_field]] = $price_extends;
                                }
                            }
                            if(!empty($price_extends))
                            {
                                $extends = json_decode($price_extends, true);
                                if(!empty($extends[$vip['id']]) && isset($extends[$vip['id']]['price']) && $extends[$vip['id']]['price'] !== '')
                                {
                                    $status = true;
                                    // 展示销售价格
                                    if(isset($goods['price']))
                                    {
                                        if($goods_original_price_status)
                                        {
                                            $goods['original_price'] = $goods['price'];
                                        }
                                        $goods['price'] = $extends[$vip['id']]['price'];
                                    }

                                    // 最低价最高价
                                    if(isset($goods['min_price']) && isset($extends[$vip['id']]['min_price']))
                                    {
                                        if($goods_original_price_status)
                                        {
                                            $goods['min_original_price'] = $goods['min_price'];
                                        }
                                        $goods['min_price'] = $extends[$vip['id']]['min_price'];
                                    }
                                    if(isset($goods['max_price']) && isset($extends[$vip['id']]['max_price']))
                                    {
                                        if($goods_original_price_status)
                                        {
                                            $goods['max_original_price'] = $goods['max_price'];
                                        }
                                        $goods['max_price'] = $extends[$vip['id']]['max_price'];
                                    }
                                }
                            }

                            // 统一折扣
                            if($status == false && $vip['discount_rate'] > 0 && BaseService::IsGoodsDiscountConfig($goods[$key_field], $this->base_config))
                            {
                                $status = true;
                                // 展示销售价格
                                if(isset($goods['price']))
                                {
                                    if($goods_original_price_status)
                                    {
                                        $goods['original_price'] = $goods['price'];
                                    }
                                    $goods['price'] = BusinessService::PriceCalculate($goods['price'], $vip['discount_rate'], 0);
                                }
                                // 最低价最高价
                                if(isset($goods['min_price']))
                                {
                                    if($goods_original_price_status)
                                    {
                                        $goods['min_original_price'] = $goods['min_price'];
                                    }
                                    $goods['min_price'] = BusinessService::PriceCalculate($goods['min_price'], $vip['discount_rate'], 0);
                                }
                                if(isset($goods['max_price']))
                                {
                                    if($goods_original_price_status)
                                    {
                                        $goods['max_original_price'] = $goods['max_price'];
                                    }
                                    $goods['max_price'] = BusinessService::PriceCalculate($goods['max_price'], $vip['discount_rate'], 0);
                                }
                            }

                            // 价格icon处理
                            if($status === true)
                            {
                                // 存在会员价格则 展示原价，并把原价名称改成销售价(上面已做了售价价格覆盖了原价)
                                if($goods_original_price_status)
                                {
                                    $goods['show_field_original_price_status'] = 1;
                                    $goods['show_field_original_price_text'] = MyLang('goods_sales_price_title');
                                }

                                // icon title
                                $price_title = empty($vip['name']) ? '会员价' : $vip['name'];

                                // 开启会员则点击icon可进入会员首页
                                $price_style = 'background: #271c0a;color: #e4cb96 !important;';
                                if(isset($this->base_config['is_user_buy']) && $this->base_config['is_user_buy'] == 1)
                                {
                                    $goods['show_field_price_text'] = '<a href="'.PluginsAdminService::PluginsSecondDomainUrl('membershiplevelvip', true).'" class="price-icon flash" style="'.$price_style.'">'.$price_title.'</a>';
                                } else {
                                    $goods['show_field_price_text'] = '<span class="price-icon flash" style="'.$price_style.'">'.$price_title.'</span>';
                                }

                                // 使用优惠标记
                                SystemBaseService::GoodsDiscountRecord($goods[$key_field], 'membershiplevelvip', 1);

                                // 是否需要隐藏原价
                                if(isset($this->base_config['is_user_hide_original_price']) && $this->base_config['is_user_hide_original_price'] == 1)
                                {
                                    $goods['show_field_original_price_status'] = 0;
                                }
                            }
                        }
                    }
                }
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
        // 用户等级
        $vip = BusinessService::UserLevelMatching(0, $params);
        if(!empty($vip))
        {
            $status = false;
            // 自定义等级售价
            $goods_sale_price = $this->GoodsSalePrice($params['goods_id'], $vip['id']);
            $price = BusinessService::PriceCalculateManual($params['data']['spec_base']['extends'], $vip);
            if(!empty($goods_sale_price) && $price !== false)
            {
                $status = true;
                $params['data']['extends_element'][] = [
                    'element'   => '.plugins-membershiplevelvip-goods-price-top',
                    'content'   => $this->GoodsDetailPrice($params['goods_id'], $params['data']['spec_base']['price'], $goods_sale_price),
                ];
                $params['data']['spec_base']['price'] = $price;
            }

            // 统一折扣
            if($status == false && $vip['discount_rate'] > 0 && isset($params['data']['spec_base']['price']) && BaseService::IsGoodsDiscountConfig($params['goods_id'], $this->base_config))
            {
                $status = true;
                $params['data']['extends_element'][] = [
                    'element'   => '.plugins-membershiplevelvip-goods-price-top',
                    'content'   => $this->GoodsDetailPrice($params['goods_id'], $params['data']['spec_base']['price'], $this->GoodsSalePrice($params['goods_id'], $vip['id'], false)),
                ];
                $params['data']['spec_base']['price'] = BusinessService::PriceCalculate($params['data']['spec_base']['price'], $vip['discount_rate'], 0);
            }

            // 未匹配到则使用默认价格、避免仅配置了部分价格导致销售价格无法展示正常对应数据
            if($status == false)
            {
                $params['data']['extends_element'][] = [
                    'element'   => '.plugins-membershiplevelvip-goods-price-top',
                    'content'   => $this->GoodsDetailPrice($params['goods_id'], $params['data']['spec_base']['price'], $this->GoodsSalePrice($params['goods_id'], 0, false)),
                ];
            }
        }
    }
}
?>