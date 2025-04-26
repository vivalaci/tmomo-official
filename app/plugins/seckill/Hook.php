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
namespace app\plugins\seckill;

use think\facade\Db;
use app\service\PluginsAdminService;
use app\service\SystemBaseService;
use app\plugins\seckill\service\BaseService;
use app\plugins\seckill\service\SeckillGoodsService;

/**
 * 限时秒杀 - 钩子入口
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

    // 配置信息
    private $plugins_config;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-09T14:25:44+0800
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
            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();
            $mca = $this->module_name.$this->controller_name.$this->action_name;

            // 插件
            $this->pluginsname = strtolower(MyInput('pluginsname'));
            $this->pluginscontrol = strtolower(MyInput('pluginscontrol', 'index'));
            $this->pluginsaction = strtolower(MyInput('pluginsaction', 'index'));
            $this->pca = $this->pluginsname.$this->pluginscontrol.$this->pluginsaction;

            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            // 是否引入多商户样式
            $is_shop_style = $this->module_name == 'index' && in_array($this->pluginsname.$this->pluginscontrol, ['seckillshopgoods']);

            switch($params['hook_name'])
            {
                case 'plugins_css' :
                    $ret = ['static/plugins/seckill/css/index/style.css'];
                    // 引入多商户样式
                    if($is_shop_style)
                    {
                        $ret[] = 'static/plugins/shop/css/index/public/shop_admin.css';
                    }
                    break;

                // js
                case 'plugins_js' :
                    $ret = ['static/plugins/seckill/js/index/style.js'];
                    // 引入多商户js
                    if($is_shop_style)
                    {
                        $ret[] = 'static/plugins/shop/js/index/common.js';
                    }
                    break;

                // 在前面添加导航
                case 'plugins_service_navigation_header_handle' :
                    if(is_array($params['header']))
                    {
                        // 获取应用数据
                        if(!empty($this->plugins_config['application_name']))
                        {
                            $nav = [
                                'id'                    => 0,
                                'pid'                   => 0,
                                'name'                  => $this->plugins_config['application_name'],
                                'url'                   => PluginsAdminService::PluginsSecondDomainUrl('seckill', true),
                                'data_type'             => 'custom',
                                'is_show'               => 1,
                                'is_new_window_open'    => 0,
                                'items'                 => [],
                            ];
                            array_unshift($params['header'], $nav);
                        }
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
                    // 是否支持优惠
                    if(SystemBaseService::IsGoodsDiscount($params))
                    {
                        $this->GoodsSpecBase($params);
                    }
                    break;

                // 商品页面基础信息顶部钩子
                case 'plugins_view_goods_detail_base_top' :
                    if($mca == 'indexgoodsindex')
                    {
                        // 是否已支持优惠
                        if(!empty($params['goods']) && !empty($params['goods']['id']) && SystemBaseService::IsGoodsDiscountRecord($params['goods']['id'], 'seckill'))
                        {
                            $ret = $this->GoodsDetailBaseTopHtml($params);
                        }
                    }
                    break;

                // 楼层数据上面
                case 'plugins_view_home_floor_top' :
                    $ret = $this->HomeFloorTopAdv($params);
                    break;

                // 首页接口数据
                case 'plugins_service_base_data_return_api_index_index' :
                    $ret = $this->IndexResultHandle($params);
                    break;

                // 商品接口数据
                case 'plugins_service_base_data_return_api_goods_detail' :
                    $ret = $this->GoodsResultHandle($params);
                    break;

                // 多商户商家中心菜单-扩展模块
                case 'plugins_shop_service_base_user_center_nav' :
                    if(isset($this->plugins_config['is_shop_seckill']) && $this->plugins_config['is_shop_seckill'] == 1)
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

                // diyapi初始化
                case 'plugins_service_diyapi_init_data' :
                    $this->DiyApiInitDataHandle($params);
                    break;

                // diy数据处理
                case 'plugins_module_diy_view_data_handle' :
                    $this->ModuleDiyViewDataHandle($params);
                    break;
            }
        }
        return $ret;
    }

    /**
     * diy数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ModuleDiyViewDataHandle($params = [])
    {
        if(!empty($params['config']) && !empty($params['config']['diy_data']))
        {
            foreach($params['config']['diy_data'] as &$v)
            {
                if(!empty($v['key']) && $v['key'] == 'seckill')
                {
                    if(!empty($v['com_data']) && !empty($v['com_data']['content']))
                    {
                        $ret = SeckillGoodsService::SeckillData($this->plugins_config);
                        $v['com_data']['content']['data'] = $ret['data'];
                    }
                    break;
                }
            }
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
                        'name'  => '限时秒杀',
                        'type'  => 'seckill',
                        'data'  => [
                            ['name'=>'秒杀首页', 'page'=>'/pages/plugins/seckill/index/index'],
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
                        'key' => 'seckill',
                        'name' => '限时秒杀',
                    ];
                    break;
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
        $params['data']['plugins']['data'][] = [
            'name'  => '限时秒杀',
            'value' => 'seckill',
            'data'  => [
                [ 'value' => 'home', 'name' => MyLang('home_title')],
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
        $params['static_url_web_arr']['plugins-seckill-home'] = PluginsAdminService::PluginsSecondDomainUrl('seckill', true);
        $params['static_url_app_arr']['plugins-seckill-home'] = '/pages/plugins/seckill/index/index';
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
            'name'          => '限时秒杀',
            'desc'          => '店铺商品申请加入限时秒杀活动、设置折扣和立减优惠',
            'url'           => PluginsHomeUrl('seckill', 'shopgoods', 'index'),
            'icon'          => StaticAttachmentUrl('shop-seckill-goods.png'),
            'business'      => 'seckill',
            'is_popup'      => 1,
            'is_full'       => 1,
        ];
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
            $seckill_data = $this->CurrentSeckillData(['goods_ids'=>[$params['data']['goods']['id'], 'is_goods_handle'=>0]]);
            if(!empty($seckill_data['goods']) && !empty($seckill_data['time']) && isset($seckill_data['time']['status']) && $seckill_data['time']['status'] == 1)
            {
                unset($seckill_data['goods']);
                $params['data']['plugins_seckill_data'] = $seckill_data;
            }
        }
    }

    /**
     * 首页接口数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function IndexResultHandle($params = [])
    {
        if(isset($this->plugins_config['is_home_show']) && $this->plugins_config['is_home_show'] == 1)
        {
            $data = $this->CurrentSeckillData(['is_recommend'=>1]);
            if(!empty($data))
            {
                $params['data']['plugins_seckill_data'] = [
                    'data'     => $data,
                    'base'     => $this->plugins_config,
                ];
            }
        }
    }

    /**
     * 首页楼层顶部秒杀推荐
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-06T16:16:34+0800
     * @param    [array]          $params [输入参数]
     */
    public function HomeFloorTopAdv($params = [])
    {
        if(isset($this->plugins_config['is_home_show']) && $this->plugins_config['is_home_show'] == 1)
        {
                $data = $this->CurrentSeckillData(['is_recommend'=>1]);
            if(!empty($data))
            {
                return MyView('../../../plugins/seckill/view/index/public/home', [
                    'plugins_seckill_data'  => $data,
                    'plugins_config'        => $this->plugins_config,
                ]);
            }
        }
    }

    /**
     * 当前秒杀数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-07-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CurrentSeckillData($params = [])
    {
        $data = [];
        $ret = SeckillGoodsService::SeckillData($this->plugins_config, $params);
        if(!empty($ret['data']['current']) && !empty($ret['data']['current']['goods']) && !empty($ret['data']['current']['time']) && isset($ret['data']['current']['time']['status']) and in_array($ret['data']['current']['time']['status'], [0,1]))
        {
            $data = $ret['data']['current'];
        }
        return $data;
    }

    /**
     * 商品规格基础数据
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-26
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function GoodsSpecBase($params = [])
    {
        if(!empty($params['data']['spec_base']['goods_id']) && isset($params['data']['spec_base']['price']))
        {
            // 秒杀商品
            $seckill_data = $this->CurrentSeckillData(['goods_ids'=>[$params['data']['spec_base']['goods_id']], 'is_goods_handle'=>0]);
            if(!empty($seckill_data['goods']) && !empty($seckill_data['time']) && isset($seckill_data['time']['status']) && $seckill_data['time']['status'] == 1)
            {
                // 使用销售价作为原价
                if(isset($params['data']['spec_base']['original_price']) && isset($this->plugins_config['is_actas_price_original']) && $this->plugins_config['is_actas_price_original'] == 1)
                {
                    $params['data']['spec_base']['original_price'] = $params['data']['spec_base']['price'];
                }

                // 价格处理
                $params['data']['spec_base']['price'] = $this->PriceCalculate($params['data']['spec_base']['goods_id'], $params['data']['spec_base']['price'], $seckill_data['goods'][0]);
            }
        }
    }

    /**
     * 商品处理结束钩子
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-26
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function GoodsHandleEnd($params = [])
    {
        if(!empty($params['data']))
        {
            // 是否还可以使用优惠
            $discount = SystemBaseService::IsGoodsDiscount($params);
            if(!empty($discount) && ($discount == 1 || (is_array($discount) && array_sum($discount) > 0)))
            {
                // key字段
                $key_field = empty($params['params']['data_key_field']) ? 'id' : $params['params']['data_key_field'];
                // 秒杀商品
                $seckill_data = $this->CurrentSeckillData(['goods_ids'=>array_column($params['data'], $key_field), 'is_goods_handle'=>0]);
                if(!empty($seckill_data['goods']) && !empty($seckill_data['time']) && isset($seckill_data['time']['status']) && $seckill_data['time']['status'] == 1)
                {
                    // 以商品id为索引
                    $seckill_data['goods'] = array_column($seckill_data['goods'], null, 'goods_id');
                    // 使用销售价作为原价
                    $is_actas_price_original = isset($this->plugins_config['is_actas_price_original']) && $this->plugins_config['is_actas_price_original'] == 1;
                    // 详情icon
                    $goods_detail_icon = empty($this->plugins_config['goods_detail_icon']) ? '秒杀价' : $this->plugins_config['goods_detail_icon'];
                    foreach($params['data'] as &$goods)
                    {
                        if(!empty($goods[$key_field]) && array_key_exists($goods[$key_field], $seckill_data['goods']))
                        {
                            // 当前秒杀商品
                            $item_seckill_goods = $seckill_data['goods'][$goods[$key_field]];

                            // 无价格字段则不处理
                            if(isset($goods['price']) && floatval($goods['price']) > 0)
                            {
                                // 使用销售价作为原价
                                if(isset($goods['original_price']) && $is_actas_price_original)
                                {
                                    $goods['original_price'] = $goods['price'];
                                }
                                
                                // 价格处理
                                $goods['price'] = $this->PriceCalculate($goods[$key_field], $goods['price'], $item_seckill_goods);
                                $goods['show_field_price_text'] = '<a href="'.PluginsAdminService::PluginsSecondDomainUrl('seckill', true).'" class="plugins-seckill-goods-price-icon">'.$goods_detail_icon.'</a>';
                            }

                            // 最低价最高价
                            if(isset($goods['min_price']) && floatval($goods['min_price']) > 0)
                            {
                                // 使用销售价作为原价
                                if(isset($goods['min_original_price']) && $is_actas_price_original)
                                {
                                    $goods['min_original_price'] = $goods['min_price'];
                                }

                                // 价格处理
                                $goods['min_price'] = $this->PriceCalculate($goods[$key_field], $goods['min_price'], $item_seckill_goods);
                            }
                            if(isset($goods['max_price']) && floatval($goods['max_price']) > 0)
                            {
                                // 使用销售价作为原价
                                if(isset($goods['max_original_price']) && $is_actas_price_original)
                                {
                                    $goods['max_original_price'] = $goods['max_price'];
                                }

                                // 价格处理
                                $goods['max_price'] = $this->PriceCalculate($goods[$key_field], $goods['max_price'], $item_seckill_goods);
                            }

                            // 使用优惠处理
                            SystemBaseService::GoodsDiscountRecord($goods[$key_field], 'seckill', 1);
                        }
                    }
                } else {
                    foreach($params['data'] as $goods)
                    {
                        if(!empty($goods[$key_field]))
                        {
                            // 使用优惠处理
                            SystemBaseService::GoodsDiscountRecord($goods[$key_field], 'seckill', 0);
                        }
                    }
                }
            }
        }
    }

    /**
     * 商品页面html
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-26
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function GoodsDetailBaseTopHtml($params = [])
    {
        if(!empty($params['goods_id']))
        {
            $seckill_data = $this->CurrentSeckillData(['goods_ids'=>[$params['goods_id'], 'is_goods_handle'=>0]]);
            if(!empty($seckill_data['goods']))
            {
                return MyView('../../../plugins/seckill/view/index/public/countdown', [
                    'plugins_seckill_data'  => $seckill_data,
                    'plugins_config'        => $this->plugins_config,
                ]);
            }
        }
        return '';
    }

    /**
     * 价格处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-26
     * @desc    description
     * @param   [int]            $goods_id      [商品id]
     * @param   [float]          $price         [商品价格]
     * @param   [array]          $seckill_goods [秒杀商品信息]
     */
    private function PriceCalculate($goods_id, $price, $seckill_goods = [])
    {
        if(empty($seckill_goods))
        {
            $where = [
                ['goods_id', '=', $goods_id],
                ['status', '=', 1],
                ['time_start', '<=', time()],
                ['time_end', '>=', time()],
            ];
            $seckill_goods = Db::name('PluginsSeckillGoods')->where($where)->find();
        }
        if(!empty($seckill_goods))
        {
            $price = BaseService::PriceCalculate($price, $seckill_goods['discount_rate'], $seckill_goods['dec_price']);
        }
        return $price;
    }
}
?>