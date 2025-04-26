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
namespace app\plugins\activity;

use app\service\SystemBaseService;
use app\service\PluginsAdminService;
use app\plugins\activity\service\BaseService;
use app\plugins\activity\service\ActivityService;
use app\plugins\activity\service\CategoryService;

/**
 * 活动配置 - 钩子入口
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

    // 配置信息
    private $plugins_config;

    /**
     * 应用响应入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-10-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function handle($params = [])
    {
        if(!empty($params['hook_name']))
        {
            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();
            $mca = $this->module_name.$this->controller_name.$this->action_name;

            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            $ret = '';
            switch($params['hook_name'])
            {
                // 公共css
                case 'plugins_css' :
                    $style_arr = ['indexindexindex', 'indexgoodsindex'];
                    if(in_array($mca, $style_arr))
                    {
                        $ret = 'static/plugins/activity/css/index/style.css';
                    }
                    break;

                // 在前面添加导航
                case 'plugins_service_navigation_header_handle' :
                    $ret = $this->NavigationHeaderHandle($params);
                    break;

                // 首页楼层顶部新增活动
                case 'plugins_view_home_floor_top' :
                case 'plugins_view_home_floor_bottom' :
                    $ret = $this->ActivityFloorHandle($params);
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

                // 首页接口数据
                case 'plugins_service_base_data_return_api_index_index' :
                    $this->IndexResultHandle($params);
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
            return $ret;
        }
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
            $activity_ids = [];
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
                                    if(!empty($dmv['data_content']) && isset($dmv['data_content']['data_type']) && $dmv['data_content']['data_type'] == 'custom' && !empty($dmv['data_content']['data_source']) && $dmv['data_content']['data_source'] == 'plugins-activity' && !empty($dmv['data_content']['data_source_content']) && isset($dmv['data_content']['data_source_content']['data_type']) && $dmv['data_content']['data_source_content']['data_type'] == 0 && !empty($dmv['data_content']['data_source_content']['data_ids']))
                                    {
                                        if(!is_array($dmv['data_content']['data_source_content']['data_ids']))
                                        {
                                            $dmv['data_content']['data_source_content']['data_ids'] = explode(',', $dmv['data_content']['data_source_content']['data_ids']);
                                        }
                                        $activity_ids = array_merge($activity_ids, $dmv['data_content']['data_source_content']['data_ids']);
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
                                            if(!empty($content['data_source']) && !empty($content['data_source_content']) && $content['data_source'] == 'plugins-activity' && isset($content['data_source_content']['data_type']) && $content['data_source_content']['data_type'] == 0 && !empty($content['data_source_content']['data_ids']))
                                            {
                                                if(!is_array($content['data_source_content']['data_ids']))
                                                {
                                                    $content['data_source_content']['data_ids'] = explode(',', $content['data_source_content']['data_ids']);
                                                }
                                                $activity_ids = array_merge($activity_ids, $content['data_source_content']['data_ids']);
                                            }
                                        }
                                    }
                                }
                            }
                            break;

                        // 自定义
                        case 'custom' :
                            if(!empty($v['com_data']['content']['data_source']) && $v['com_data']['content']['data_source'] == 'plugins-activity' && !empty($v['com_data']['content']['data_source_content']) && isset($v['com_data']['content']['data_source_content']['data_type']) && $v['com_data']['content']['data_source_content']['data_type'] == 0 && !empty($v['com_data']['content']['data_source_content']['data_ids']))
                            {
                                if(!is_array($v['com_data']['content']['data_source_content']['data_ids']))
                                {
                                    $v['com_data']['content']['data_source_content']['data_ids'] = explode(',', $v['com_data']['content']['data_source_content']['data_ids']);
                                }
                                $activity_ids = array_merge($activity_ids, $v['com_data']['content']['data_source_content']['data_ids']);
                            }
                            break;
                    }
                }
            }

            // 读取指定活动数据
            $activity_data = empty($activity_ids) ? [] : array_column(ActivityService::AppointActivityList(['activity_ids'=>$activity_ids]), null, 'id');

            // 数据获取
            foreach($params['config']['diy_data'] as &$v)
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
                                    if(!empty($dmv['data_content']) && isset($dmv['data_content']['data_type']) && $dmv['data_content']['data_type'] == 'custom' && !empty($dmv['data_content']['data_source']) && $dmv['data_content']['data_source'] == 'plugins-activity')
                                    {
                                        $dmv['data_content']['data_source_content'] = $this->DiyConfigViewActivityHandle($dmv['data_content']['data_source_content'], $activity_data);
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
                                        if(!empty($tabs_data_config['content']) && !empty($tabs_data_config['content']['data_source']) && $tabs_data_config['content']['data_source'] == 'plugins-activity' && !empty($tabs_data_config['content']['data_source_content']))
                                        {
                                            $tabs_data_config['content']['data_source_content'] = $this->DiyConfigViewActivityHandle($tabs_data_config['content']['data_source_content'], $activity_data);
                                            $dtv[$dtv['tabs_data_type'].'_config'] = $tabs_data_config;
                                        }
                                    }
                                }
                            }
                            break;

                        // 自定义
                        case 'custom' :
                            if(!empty($v['com_data']['content']['data_source']) && $v['com_data']['content']['data_source'] == 'plugins-activity')
                            {
                                $v['com_data']['content']['data_source_content'] = $this->DiyConfigViewActivityHandle($v['com_data']['content']['data_source_content'], $activity_data);
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
     * @param   [array]          $config        [配置数据]
     * @param   [array]          $activity_data [指定活动的数据]
     */
    public static function DiyConfigViewActivityHandle($config, $activity_data = [])
    {
        $data_type = isset($config['data_type']) ? $config['data_type'] : 0;
        if($data_type == 1)
        {
            $data_params = [
                'activity_category_ids'   => isset($config['activity_category_ids']) ? $config['activity_category_ids'] : (isset($config['category_ids']) ? $config['category_ids'] : ''),
                'activity_keywords'       => isset($config['activity_keywords']) ? $config['activity_keywords'] : (isset($config['keywords']) ? $config['keywords'] : ''),
                'activity_number'         => isset($config['activity_number']) ? $config['activity_number'] : (isset($config['number']) ? $config['number'] : 4),
                'activity_order_by_type'  => isset($config['activity_order_by_type']) ? $config['activity_order_by_type'] : (isset($config['order_by_type']) ? $config['order_by_type'] : 0),
                'activity_order_by_rule'  => isset($config['activity_order_by_rule']) ? $config['activity_order_by_rule'] : (isset($config['order_by_rule']) ? $config['order_by_rule'] : 0),
                'activity_is_home'        => isset($config['activity_is_home']) ? $config['activity_is_home'] : (isset($config['is_home']) ? $config['is_home'] : 0),
            ];
            $config['data_auto_list'] = ActivityService::AutoActivityList($data_params);
        } else {
            if(!empty($config['data_list']) && !empty($activity_data))
            {
                $index = 0;
                foreach($config['data_list'] as $dk=>$dv)
                {
                    if(!empty($dv['data_id']) && array_key_exists($dv['data_id'], $activity_data))
                    {
                        $config['data_list'][$dk]['data'] = $activity_data[$dv['data_id']];
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
            $category = CategoryService::CategoryList(['field'=>'id,name']);
            $params['data']['data_source'][] = [
                'name'  => '活动配置',
                'type'  => 'plugins-activity',
                'data'  => [
                    ['name'=>'数据索引', 'field'=>'data_index', 'type'=>'text'],
                    ['name'=>'活动详情', 'field' =>'url', 'type'=>'link'],
                    ['name'=>'数据ID', 'field'=>'id', 'type'=>'text'],
                    ['name'=>'banner', 'field'=>'banner', 'type'=>'images'],
                    ['name'=>'封面', 'field'=>'cover', 'type'=>'images'],
                    ['name'=>'标题', 'field'=>'title', 'type'=>'text'],
                    ['name'=>'副标题', 'field'=>'vice_title', 'type'=>'text'],
                    ['name'=>'描述', 'field'=>'describe', 'type'=>'text'],
                    ['name'=>'活动分类', 'field'=>'activity_category_name', 'type'=>'text'],
                    ['name'=>'访问总数', 'field'=>'access_count', 'type'=>'text'],
                    ['name'=>'时间年-日期', 'field'=>'add_time_date_cn', 'type'=>'text'],
                    ['name'=>'时间日期', 'field'=>'add_time_date', 'type'=>'text'],
                    ['name'=>'添加时间', 'field'=>'add_time', 'type'=>'text'],
                    ['name'=>'更新时间', 'field'=>'upd_time', 'type'=>'text'],
                    ['name'=>'商品列表', 'field'=>'goods_list', 'type'=>'custom-data-list', 'data' => [
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
                        'data_url'     => PluginsApiUrl('activity', 'diyactivity', 'index'),
                        'is_multiple'  => 1,
                        'show_data'    => [
                            'data_key'   => 'id',
                            'data_name'  => 'title',
                            'data_logo'  => 'cover',
                        ],
                        'popup_title'   => '活动选择',
                        'header' => [
                            [
                                'field'  => 'id',
                                'name'   => '数据ID',
                                'width'  => 120,
                            ],
                            [
                                'field'  => 'cover',
                                'name'   => '封面',
                                'type'   => 'images',
                                'width'  => 100,
                            ],
                            [
                                'field'  => 'title',
                                'name'   => '标题',
                            ],
                            [
                                'field'  => 'describe',
                                'name'   => '描述',
                            ],
                            [
                                'field'  => 'activity_category_name',
                                'name'   => '分类',
                            ],
                        ],
                        'search_filter_form_config' => [
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'placeholder'  => '请选择活动分类',
                                    'is_multiple'  => 1,
                                ],
                                'title'      => '活动分类',
                                'form_name'  => 'category_ids',
                                'data'       => $category['data'],
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
                        'data_url' => PluginsApiUrl('activity', 'diyactivity', 'autoactivitylist'),
                        'filter_form_config' => [
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'is_multiple'  => 1,
                                ],
                                'title'      => '活动分类',
                                'form_name'  => 'activity_category_ids',
                                'data'       => $category['data'],
                            ],
                            [
                                'type'    => 'input',
                                'config'  => [
                                    'placeholder'  => '请输入关键字',
                                    'type'         => 'text',
                                ],
                                'title'      => '关键字',
                                'form_name'  => 'activity_keywords',
                            ],
                            [
                                'type'    => 'input',
                                'config'  => [
                                    'default'  => 4,
                                    'type'     => 'number',
                                ],
                                'title'      => '显示数量',
                                'form_name'  => 'activity_number',
                            ],
                            [
                                'type'       => 'radio',
                                'title'      => '排序类型',
                                'form_name'  => 'activity_order_by_type',
                                'data'       => BaseService::ConstData('activity_order_by_type_list'),
                                'data_key'   => 'index',
                                'data_name'  => 'name',
                                'config'     => [
                                    'default'      => 0,
                                ]
                            ],
                            [
                                'type'       => 'radio',
                                'title'      => '排序规则',
                                'form_name'  => 'activity_order_by_rule',
                                'const_key'  => 'data_order_by_rule_list',
                                'data_key'   => 'index',
                                'data_name'  => 'name',
                                'config'     => [
                                    'default'      => 0,
                                ]
                            ],
                            [
                                'type'       => 'switch',
                                'title'      => '首页显示',
                                'form_name'  => 'activity_is_home',
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
                        'name'  => '活动配置',
                        'type'  => 'activity',
                        'data'  => [
                            ['name'=>'所有活动', 'page'=>'/pages/plugins/activity/index/index'],
                        ],
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
            'name'  => '活动中心',
            'value' => 'activity',
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
        $params['static_url_web_arr']['plugins-activity-home'] = PluginsAdminService::PluginsSecondDomainUrl('activity', true);
        $params['static_url_app_arr']['plugins-activity-home'] = '/pages/plugins/activity/index/index';
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
    public function IndexResultHandle($params = [])
    {
        $data = ActivityService::ActivityFloorData();
        if(!empty($data))
        {
            $params['data']['plugins_activity_data'] = [
                'base'  => $this->plugins_config,
                'data'  => $data,
            ];
        }
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
    public function GoodsSpecBase($params = [])
    {
        if(!empty($params['data']['spec_base']) && isset($params['data']['spec_base']['price']))
        {
            $goods_id = $params['data']['spec_base']['goods_id'];
            $ag = ActivityService::ActivityGoodsData([$goods_id]);
            if(!empty($ag) && array_key_exists($goods_id, $ag))
            {
                // 使用销售价作为原价
                if(isset($params['data']['spec_base']['original_price']) && isset($this->plugins_config['is_actas_price_original']) && $this->plugins_config['is_actas_price_original'] == 1)
                {
                    $params['data']['spec_base']['original_price'] = $params['data']['spec_base']['price'];
                }

                // 价格处理
                $params['data']['spec_base']['price'] = BaseService::PriceCalculate($params['data']['spec_base']['price'], $ag[$goods_id]['discount_rate'], $ag[$goods_id]['dec_price']);
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
    public function GoodsHandleEnd($params = [])
    {
        if(!empty($params['data']) && is_array($params['data']))
        {
            // 是否还可以使用优惠
            $discount = SystemBaseService::IsGoodsDiscount($params);
            if(!empty($discount) && ($discount == 1 || (is_array($discount) && array_sum($discount) > 0)))
            {
                // key字段
                $key_field = empty($params['params']['data_key_field']) ? 'id' : $params['params']['data_key_field'];

                // 活动信息
                $ag = ActivityService::ActivityGoodsData(array_column($params['data'], $key_field));
                if(!empty($ag))
                {
                    // 使用销售价作为原价
                    $is_actas_price_original = isset($this->plugins_config['is_actas_price_original']) && $this->plugins_config['is_actas_price_original'] == 1;

                    foreach($params['data'] as &$goods)
                    {
                        if(!empty($goods[$key_field]) && array_key_exists($goods[$key_field], $ag) && ($discount == 1 || (is_array($discount) && array_key_exists($goods[$key_field], $discount) && $discount[$goods[$key_field]] == 1)))
                        {
                            // 活动商品
                            $item_ag = $ag[$goods[$key_field]];
                            if($item_ag['discount_rate'] > 0 || $item_ag['dec_price'] > 0)
                            {
                                // 无价格字段则不处理
                                if(isset($goods['price']))
                                {
                                    // 使用销售价作为原价
                                    if(isset($goods['original_price']) && $is_actas_price_original)
                                    {
                                        $goods['original_price'] = $goods['price'];
                                    }

                                    // 价格处理
                                    $goods['price'] = BaseService::PriceCalculate($goods['price'], $item_ag['discount_rate'], $item_ag['dec_price']);
                                    if(!empty($this->plugins_config['goods_detail_icon']))
                                    {
                                        $goods['show_field_price_text'] = '<a href="'.PluginsAdminService::PluginsSecondDomainUrl('activity', true).'" class="plugins-activity-goods-price-icon">'.$this->plugins_config['goods_detail_icon'].'</a>';
                                    }
                                }

                                // 最低价最高价
                                if(isset($goods['min_price']))
                                {
                                    // 使用销售价作为原价
                                    if(isset($goods['min_original_price']) && $is_actas_price_original)
                                    {
                                        $goods['min_original_price'] = $goods['min_price'];
                                    }

                                    // 价格处理
                                    $goods['min_price'] = BaseService::PriceCalculate($goods['min_price'], $item_ag['discount_rate'], $item_ag['dec_price']);
                                }
                                if(isset($goods['max_price']))
                                {
                                    // 使用销售价作为原价
                                    if(isset($goods['max_original_price']) && $is_actas_price_original)
                                    {
                                        $goods['max_original_price'] = $goods['max_price'];
                                    }

                                    // 价格处理
                                    $goods['max_price'] = BaseService::PriceCalculate($goods['max_price'], $item_ag['discount_rate'], $item_ag['dec_price']);
                                }

                                // 使用优惠处理
                                SystemBaseService::GoodsDiscountRecord($goods[$key_field], 'activity', 1);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 楼层活动
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-10-15
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ActivityFloorHandle($params = [])
    {
        // 数据位置
        $floor_location_arr = [
            'plugins_view_home_floor_top'       => 0,
            'plugins_view_home_floor_bottom'    => 1,
        ];
        $home_data_location = array_key_exists($params['hook_name'], $floor_location_arr) ? $floor_location_arr[$params['hook_name']] : 0;
        $data = ActivityService::ActivityFloorData(['where'=>[['home_data_location', '=', $home_data_location]]]);
        MyViewAssign('activity_data_list', $data);
        MyViewAssign('plugins_config', $this->plugins_config);
        return MyView('../../../plugins/activity/view/index/public/home');
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
        if(isset($params['header']) && is_array($params['header']))
        {
            // 获取应用数据
            if(!empty($this->plugins_config['application_name']))
            {
                $nav = [
                    'id'                    => 0,
                    'pid'                   => 0,
                    'name'                  => $this->plugins_config['application_name'],
                    'url'                   => PluginsAdminService::PluginsSecondDomainUrl('activity', true),
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
?>