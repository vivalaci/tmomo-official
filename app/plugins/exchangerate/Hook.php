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
namespace app\plugins\exchangerate;

use app\service\PluginsService;
use app\service\PaymentService;
use app\service\ResourcesService;
use app\plugins\exchangerate\service\BaseService;

/**
 * 汇率 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 配置信息
    private $plugins_config;

    // 当前模块/控制器/方法
    private $module_name;
    private $controller_name;
    private $action_name;

    // 插件
    private $pluginsname;
    private $pluginscontrol;
    private $pluginsaction;

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
            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();

            // 插件
            $this->pluginsname = strtolower(MyInput('pluginsname'));
            $this->pluginscontrol = strtolower(MyInput('pluginscontrol', 'index'));
            $this->pluginsaction = strtolower(MyInput('pluginsaction', 'index'));

            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            // 是否开启选择
            $is_user_quick_select = isset($this->plugins_config['is_user_quick_select']) ? intval($this->plugins_config['is_user_quick_select']) : 0;
            $is_user_header_top_right_select = isset($this->plugins_config['is_user_header_top_right_select']) ? intval($this->plugins_config['is_user_header_top_right_select']) : 0;
   
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 系统初始化
                case 'plugins_service_system_begin' :
                    if(!empty($params['params']) && !empty($params['params']['currency']))
                    {
                        BaseService::SetUserCurrencyCacheValue($params['params']['currency']);
                    }
                    break;

                // 小程序/APP端快捷导航操作按钮
                case 'plugins_service_quick_navigation_h5' :
                case 'plugins_service_quick_navigation_weixin' :
                case 'plugins_service_quick_navigation_alipay' :
                case 'plugins_service_quick_navigation_baidu' :
                case 'plugins_service_quick_navigation_qq' :
                case 'plugins_service_quick_navigation_toutiao' :
                case 'plugins_service_quick_navigation_kuaishou' :
                case 'plugins_service_quick_navigation_ios' :
                case 'plugins_service_quick_navigation_android' :
                    if($is_user_quick_select == 1)
                    {
                        $this->MiniQuickNavigationHandle($params);
                    }
                    break;

                // pc端顶部左侧菜单
                case 'plugins_service_header_navigation_top_right_handle' :
                    if($is_user_header_top_right_select == 1)
                    {
                        $this->HeaderNavigationTopRightHandle($params);
                    }
                    break;

                // 货币信息处理
                case 'plugins_service_currency_data' :
                    if($this->module_name != 'admin')
                    {
                        $this->CurrencyDataHandle($params);
                    }
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
                    $this->GoodsSpecBase($params);
                    break;

                // 下单金额计算
                case 'plugins_service_buy_group_goods_handle' :
                    $this->BuyGroupGoodsHandle($params);
                    break;

                // 下单接口数据
                case 'plugins_service_base_data_return_api_buy_index' :
                    $this->BuyResultHandle($params);
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
                        'name'  => '汇率',
                        'type'  => 'exchangerate',
                        'data'  => [
                            ['name'=>'汇率切换', 'page'=>'/pages/plugins/exchangerate/currency/currency'],
                        ],
                    ];
                    break;
                }
            }
        }
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
        if(!empty($params['data']))
        {
            // 下单页面返回货币符号
            $params['data']['currency_symbol'] = ResourcesService::CurrencyDataSymbol();
        }
    }

    /**
     * 购买商品处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-05-25
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function BuyGroupGoodsHandle($params = [])
    {
        if(!empty($params['data']) && isset($this->plugins_config['is_use_default_currency_buy']) && $this->plugins_config['is_use_default_currency_buy'] == 1 && isset($this->plugins_config['is_only_buy_order_use_currency']) && $this->plugins_config['is_only_buy_order_use_currency'] == 1)
        {
            // 转换汇率值
            $currency_value = $this->TransformCurrencyValue($params);

            // 获取货币列表
            $currency = BaseService::UserCurrencyData($currency_value);
            if(!empty($currency['default']) && !empty($currency['default']['rate']) && $currency['default']['rate'] > 0)
            {
                foreach($params['data'] as &$v)
                {
                    // 商品处理
                    foreach($v['goods_items'] as &$g)
                    {
                        $g['price'] = PriceNumberFormat($g['price']*$currency['default']['rate']);
                        $g['total_price'] = PriceNumberFormat($g['price']*$g['stock']);
                    }

                    // 总价处理
                    $inc_ev_price = 0;
                    $dec_ev_price = 0;
                    if(!empty($v['order_base']['extension_data']))
                    {
                        foreach($v['order_base']['extension_data'] as $ev)
                        {
                            if(isset($ev['price']) && isset($ev['type']))
                            {
                                if($ev['type'] == 0)
                                {
                                    $dec_ev_price += $ev['price'];
                                } else {
                                    $inc_ev_price += $ev['price'];
                                }
                            }
                        }
                    }
                    $temp_order_price = ($v['order_base']['total_price']-$dec_ev_price)+$inc_ev_price;
                    if($temp_order_price > 0)
                    {
                        $price = PriceNumberFormat($temp_order_price-($temp_order_price*$currency['default']['rate']));
                        $v['order_base']['extension_data'][] = [
                            'name'      => '汇率转换('.$currency['default']['name'].')',
                            'price'     => $price,
                            'type'      => 0,
                            'business'  => 'plugins-exchangerate',
                            'tips'      => '当前汇率 '.$currency['default']['rate'],
                            'ext'       => $currency['default']['rate'],
                        ];
                    }
                }
            }
        }
    }

    /**
     * 获取当前选中的货币
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-25
     * @desc    description
     */
    public function GetUserCurrencyCacheValue($params = [])
    {
        // 是否仅使用默认货币下单
        if(isset($this->plugins_config['is_use_default_currency_buy']) && $this->plugins_config['is_use_default_currency_buy'] == 1)
        {
            // 当前访问模块
            if(in_array($this->module_name.$this->controller_name, ['indexbuy', 'apibuy']))
            {
                return '';
            }
        }

        // 指定的货币值
        return BaseService::GetUserCurrencyCacheValue($this->plugins_config);
    }

    /**
     * 货币信息处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CurrencyDataHandle($params = [])
    {
        // 转换汇率值
        $currency_value = $this->TransformCurrencyValue($params);

        // 获取货币列表
        $data = BaseService::UserCurrencyData($currency_value);
        if(!empty($data['default']))
        {
            $params['data']['currency_symbol']  = $data['default']['symbol'];
            $params['data']['currency_code']    = $data['default']['code'];
            $params['data']['currency_rate']    = $data['default']['rate'];
            $params['data']['currency_name']    = $data['default']['name'];
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
            // 获取货币列表
            $data = BaseService::UserCurrencyData();
            if(!empty($data['data']))
            {
                $nav = [
                    'event_type'    => 1,
                    'event_value'   => '/pages/plugins/exchangerate/currency/currency',
                    'name'          => '货币切换',
                    'images_url'    => StaticAttachmentUrl('quick-nav-icon.png'),
                    'bg_color'      => '#18277f',
                ];
                array_push($params['data'], $nav);
            }
        }
    }

    /**
     * web端顶部右侧小导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function HeaderNavigationTopRightHandle($params = [])
    {
        if(isset($params['data']) && is_array($params['data']))
        {
            // 转换汇率值
            $currency_value = $this->TransformCurrencyValue($params);

            // 获取货币列表
            $data = BaseService::UserCurrencyData($currency_value);
            if(!empty($data['default']) && !empty($data['data']))
            {
                // 当前url地址
                $my_url = $this->CurrentViewUrl();

                // 货币选择列表
                $select = [];
                foreach($data['data'] as $v)
                {
                    $select[] = [
                        'icon'  => empty($v['icon']) ? '' : $v['icon'],
                        'name'  => $v['name'],
                        'url'   => $my_url.$v['id'],
                    ];
                }

                // 加入导航尾部
                $nav = [
                    'name'      => $data['default']['name'],
                    'is_login'  => 0,
                    'badge'     => null,
                    'icon'      => empty($data['default']['icon']) ? 'icon-exchange-nav-top' : $data['default']['icon'],
                    'url'       => '',
                    'items'     => $select,
                ];
                array_push($params['data'], $nav);
            }
        }
    }

    /**
     * 当前页面url地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-11
     * @desc    description
     */
    public function CurrentViewUrl()
    {
        // 去除当前存在的参数
        $url = __MY_VIEW_URL__;
        if(stripos($url, BaseService::$request_currency_key) !== false)
        {
            $arr1 = explode('?', $url);
            if(!empty($arr1[1]))
            {
                $arr2 = explode('&', $arr1[1]);
                foreach($arr2 as $k=>$v)
                {
                    if(stripos($v, BaseService::$request_currency_key) !== false)
                    {
                        unset($arr2[$k]);
                    }
                }
                $url = '?'.implode('&', $arr2);
            }
        }

        // 当前页面地址
        $join = (stripos($url, '?') === false) ? '?' : '&';
        return $url.$join.BaseService::$request_currency_key.'=';
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
    public function GoodsHandleEnd($params = [])
    {
        // 开启转换
        if(!empty($params['data']) && $this->IsGoodsToRate())
        {
            // 转换汇率值
            $currency_value = $this->TransformCurrencyValue($params);

            // 获取货币列表
            $currency = BaseService::UserCurrencyData($currency_value);
            if(!empty($currency['default']) && !empty($currency['default']['rate']) && $currency['default']['rate'] > 0)
            {
                // 汇率
                $rate = $currency['default']['rate'];
                // 开启原始价格处理
                $is_goods_od_to_rate = isset($this->plugins_config['is_goods_od_to_rate']) && $this->plugins_config['is_goods_od_to_rate'] == 1;
                foreach($params['data'] as &$goods)
                {
                    // 原始价格处理、正常是不需要改变系统保留的原始价格，这里汇率插件需要将商品所有价格进行转换，避免有某些插件使用了原始价格而造成页面价格不一致的情况
                    if($is_goods_od_to_rate && !empty($goods['price_container']))
                    {
                        $goods['price_container'] = $this->PriceRateHandle($goods['price_container'], $rate);
                    }

                    // 使用价格处理
                    $goods = $this->PriceRateHandle($goods, $rate);
                }
            }
        }
    }

    /**
     * 转换汇率值
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-05-25
     * @desc    description
     */
    public function TransformCurrencyValue($params = [])
    {
        // 是否仅读取默认货币
        if(!empty($params['params']) && isset($params['params']['is_only_currency_default']) && $params['params']['is_only_currency_default'] == 1)
        {
            return '';
        }

        // 指定的货币值
        $currency_value = $this->GetUserCurrencyCacheValue($params);

        // 是否支付方式默认货币、则覆盖货币
        if(!empty($this->plugins_config['payment_currency_data']) && in_array($this->module_name.$this->controller_name, ['indexbuy', 'apibuy']))
        {
            $request_params = MyInput();
            $payment_id = PaymentService::BuyDefaultPayment(empty($request_params) ? [] : $request_params);
            if(!empty($payment_id) && !empty($this->plugins_config['payment_currency_data'][$payment_id]))
            {
                $currency_value = $this->plugins_config['payment_currency_data'][$payment_id];
            }
        }

        return $currency_value;
    }

    /**
     * 是否转换商品
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-09-13
     * @desc    description
     */
    public function IsGoodsToRate()
    {
        $status = false;
        // 避开订单管理页面
        if(isset($this->plugins_config['is_goods_to_rate']) && $this->plugins_config['is_goods_to_rate'] == 1 && !in_array($this->module_name.$this->controller_name, [
            'adminorder',
            'adminorderaftersale',
            'apiorder',
            'apiorderaftersale',
            'indexorder',
            'indexorderaftersale',
        ]) && !in_array($this->pluginsname.$this->pluginscontrol, [
            'shoporder',
            'shoporderaftersale',
            'realstoreorder',
            'realstoreorderaftersale',
        ]))
        {
            $status = true;
        }

        // 订单确认页面，如果开启了使用默认货币和仅订单使用货币、则不转换商品
        if(in_array($this->module_name.$this->controller_name, ['indexbuy', 'apibuy']) && isset($this->plugins_config['is_use_default_currency_buy']) && $this->plugins_config['is_use_default_currency_buy'] == 1 && isset($this->plugins_config['is_only_buy_order_use_currency']) && $this->plugins_config['is_only_buy_order_use_currency'] == 1)
        {
            $status = false;
        }
        return $status;
    }

    /**
     * 商品价格转换处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-27
     * @desc    description
     * @param   [array]          $price_data [商品价格数据]
     * @param   [float]          $rate       [汇率]
     */
    public function PriceRateHandle($price_data, $rate)
    {
        // 展示销售价格,原价
        if(isset($price_data['price']))
        {
            if(stripos($price_data['price'], '-') !== false)
            {
                $temp = explode('-', $price_data['price']);
                if(is_array($temp) && count($temp) == 2)
                {
                    $temp[0] = PriceNumberFormat($temp[0]*$rate);
                    $temp[1] = PriceNumberFormat($temp[1]*$rate);
                    $price_data['price'] = implode('-', $temp);
                }
            } else {
                $price_data['price'] = PriceNumberFormat(floatval($price_data['price'])*$rate);
            }
        }
        if(isset($price_data['original_price']))
        {
            if(stripos($price_data['original_price'], '-') !== false)
            {
                $temp = explode('-', $price_data['original_price']);
                if(is_array($temp) && count($temp) == 2)
                {
                    $temp[0] = PriceNumberFormat($temp[0]*$rate);
                    $temp[1] = PriceNumberFormat($temp[1]*$rate);
                    $price_data['original_price'] = implode('-', $temp);
                }
            } else {
                $price_data['original_price'] = PriceNumberFormat(floatval($price_data['original_price'])*$rate);
            }
        }

        // 销售 最低价,最高价
        if(isset($price_data['min_price']))
        {
            $price_data['min_price'] = PriceNumberFormat(floatval($price_data['min_price'])*$rate);
        }
        if(isset($price_data['max_price']))
        {
            $price_data['max_price'] = PriceNumberFormat(floatval($price_data['max_price'])*$rate);
        }
        // 原价 最低价,最高价
        if(isset($price_data['min_original_price']))
        {
            $price_data['min_original_price'] = PriceNumberFormat(floatval($price_data['min_original_price'])*$rate);
        }
        if(isset($price_data['max_original_price']))
        {
            $price_data['max_original_price'] = PriceNumberFormat(floatval($price_data['max_original_price'])*$rate);
        }

        return $price_data;
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
    public function GoodsSpecBase($params = [])
    {
        if($this->IsGoodsToRate())
        {
            // 转换汇率值
            $currency_value = $this->TransformCurrencyValue($params);

            // 获取货币列表
            $data = BaseService::UserCurrencyData($currency_value);
            if(!empty($data['default']) && !empty($data['default']['rate']) && $data['default']['rate'] > 0)
            {
                // 汇率
                $rate = $data['default']['rate'];

                // 商品规格
                $params['data']['spec_base']['price'] = PriceNumberFormat(floatval($params['data']['spec_base']['price'])*$rate);
                if(isset($params['data']['spec_base']['original_price']) && $params['data']['spec_base']['original_price'] > 0)
                {
                    $params['data']['spec_base']['original_price'] = PriceNumberFormat(floatval($params['data']['spec_base']['original_price'])*$rate);
                }
            }
        }
    }
}
?>