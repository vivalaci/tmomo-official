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
namespace app\plugins\ask;

use app\service\PluginsAdminService;
use app\plugins\ask\service\BaseService;
use app\plugins\ask\service\AskService;
use app\plugins\ask\service\AskCategoryService;

/**
 * 问答 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
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

    // 用户中心入口名称
    private $user_center_left_name;

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

            // 用户中心入口名称
            $this->user_center_left_name = empty($this->plugins_config['user_center_left_name']) ? '问答/留言' : $this->plugins_config['user_center_left_name'];

            // 商品详情页展示问答和发布问答入口
            $is_goods_user_add_ask = isset($this->plugins_config['is_goods_user_add_ask']) && $this->plugins_config['is_goods_user_add_ask'] == 1;
            $is_goods_user_add_ask_web = $is_goods_user_add_ask && $this->mca == 'indexgoodsindex';

            $ret = [];
            switch($params['hook_name'])
            {
                // 公共css
                case 'plugins_css' :
                    if($is_goods_user_add_ask_web)
                    {
                        $ret[] = 'static/plugins/ask/css/index/public/goods_detail.css';
                    }
                    break;

                // 公共js
                case 'plugins_js' :
                    if($is_goods_user_add_ask_web)
                    {
                        $ret[] = 'static/plugins/ask/js/index/public/goods_detail.js';
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

                // 商品页面tabs内评价底部钩子
                case 'plugins_view_goods_detail_tabs_comments_bottom' :
                    if($is_goods_user_add_ask_web)
                    {
                        $ret = $this->GoodsDetailTabsCommentsBottom($params);
                    }
                    break;

                // 商品接口数据
                case 'plugins_service_base_data_return_api_goods_detail' :
                    if($is_goods_user_add_ask)
                    {
                        $this->GoodsResultHandle($params);
                    }
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
            $ask_ids = [];
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
                                    if(!empty($dmv['data_content']) && isset($dmv['data_content']['data_type']) && $dmv['data_content']['data_type'] == 'custom' && !empty($dmv['data_content']['data_source']) && $dmv['data_content']['data_source'] == 'plugins-ask' && !empty($dmv['data_content']['data_source_content']) && isset($dmv['data_content']['data_source_content']['data_type']) && $dmv['data_content']['data_source_content']['data_type'] == 0 && !empty($dmv['data_content']['data_source_content']['data_ids']))
                                    {
                                        if(!is_array($dmv['data_content']['data_source_content']['data_ids']))
                                        {
                                            $dmv['data_content']['data_source_content']['data_ids'] = explode(',', $dmv['data_content']['data_source_content']['data_ids']);
                                        }
                                        $ask_ids = array_merge($ask_ids, $dmv['data_content']['data_source_content']['data_ids']);
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
                                            if(!empty($content['data_source']) && !empty($content['data_source_content']) && $content['data_source'] == 'plugins-ask' && isset($content['data_source_content']['data_type']) && $content['data_source_content']['data_type'] == 0 && !empty($content['data_source_content']['data_ids']))
                                            {
                                                if(!is_array($content['data_source_content']['data_ids']))
                                                {
                                                    $content['data_source_content']['data_ids'] = explode(',', $content['data_source_content']['data_ids']);
                                                }
                                                $ask_ids = array_merge($ask_ids, $content['data_source_content']['data_ids']);
                                            }
                                        }
                                    }
                                }
                            }
                            break;

                        // 自定义
                        case 'custom' :
                            if(!empty($v['com_data']['content']['data_source']) && $v['com_data']['content']['data_source'] == 'plugins-ask' && !empty($v['com_data']['content']['data_source_content']) && isset($v['com_data']['content']['data_source_content']['data_type']) && $v['com_data']['content']['data_source_content']['data_type'] == 0 && !empty($v['com_data']['content']['data_source_content']['data_ids']))
                            {
                                if(!is_array($v['com_data']['content']['data_source_content']['data_ids']))
                                {
                                    $v['com_data']['content']['data_source_content']['data_ids'] = explode(',', $v['com_data']['content']['data_source_content']['data_ids']);
                                }
                                $ask_ids = array_merge($ask_ids, $v['com_data']['content']['data_source_content']['data_ids']);
                            }
                            break;
                    }
                }
            }

            // 读取指定问答数据
            $ask_data = empty($ask_ids) ? [] : array_column(AskService::AppointAskList(['ask_ids'=>$ask_ids]), null, 'id');

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
                                    if(!empty($dmv['data_content']) && isset($dmv['data_content']['data_type']) && $dmv['data_content']['data_type'] == 'custom' && !empty($dmv['data_content']['data_source']) && $dmv['data_content']['data_source'] == 'plugins-ask')
                                    {
                                        $dmv['data_content']['data_source_content'] = $this->DiyConfigViewAskHandle($dmv['data_content']['data_source_content'], $ask_data);
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
                                        if(!empty($tabs_data_config['content']) && !empty($tabs_data_config['content']['data_source']) && $tabs_data_config['content']['data_source'] == 'plugins-ask' && !empty($tabs_data_config['content']['data_source_content']))
                                        {
                                            $tabs_data_config['content']['data_source_content'] = $this->DiyConfigViewAskHandle($tabs_data_config['content']['data_source_content'], $ask_data);
                                            $dtv[$dtv['tabs_data_type'].'_config'] = $tabs_data_config;
                                        }
                                    }
                                }
                            }
                            break;

                        // 自定义
                        case 'custom' :
                            if(!empty($v['com_data']['content']['data_source']) && $v['com_data']['content']['data_source'] == 'plugins-ask')
                            {
                                $v['com_data']['content']['data_source_content'] = $this->DiyConfigViewAskHandle($v['com_data']['content']['data_source_content'], $ask_data);
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
     * @param   [array]          $ask_data    [指定问答的数据]
     */
    public static function DiyConfigViewAskHandle($config, $ask_data = [])
    {
        $data_type = isset($config['data_type']) ? $config['data_type'] : 0;
        if($data_type == 1)
        {
            $data_params = [
                'ask_category_ids'   => isset($config['ask_category_ids']) ? $config['ask_category_ids'] : (isset($config['category_ids']) ? $config['category_ids'] : ''),
                'ask_keywords'       => isset($config['ask_keywords']) ? $config['ask_keywords'] : (isset($config['keywords']) ? $config['keywords'] : ''),
                'ask_number'         => isset($config['ask_number']) ? $config['ask_number'] : (isset($config['number']) ? $config['number'] : 4),
                'ask_order_by_type'  => isset($config['ask_order_by_type']) ? $config['ask_order_by_type'] : (isset($config['order_by_type']) ? $config['order_by_type'] : 0),
                'ask_order_by_rule'  => isset($config['ask_order_by_rule']) ? $config['ask_order_by_rule'] : (isset($config['order_by_rule']) ? $config['order_by_rule'] : 0),
                'ask_is_reply'       => isset($config['ask_is_reply']) ? $config['ask_is_reply'] : (isset($config['is_reply']) ? $config['is_reply'] : 0),
            ];
            $config['data_auto_list'] = AskService::AutoAskList($data_params);
        } else {
            if(!empty($config['data_list']) && !empty($ask_data))
            {
                $index = 0;
                foreach($config['data_list'] as $dk=>$dv)
                {
                    if(!empty($dv['data_id']) && array_key_exists($dv['data_id'], $ask_data))
                    {
                        $config['data_list'][$dk]['data'] = $ask_data[$dv['data_id']];
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
            $category = AskCategoryService::AskCategoryAll(['field'=>'id,name']);
            $params['data']['data_source'][] = [
                'name'  => '问答',
                'type'  => 'plugins-ask',
                'data'  => [
                    ['name'=>'数据索引', 'field'=>'data_index', 'type'=>'text'],
                    ['name'=>'问答详情', 'field' =>'url', 'type'=>'link'],
                    ['name'=>'数据ID', 'field'=>'id', 'type'=>'text'],
                    ['name'=>'联系人', 'field'=>'name', 'type'=>'text'],
                    ['name'=>'联系电话', 'field'=>'tel', 'type'=>'text'],
                    ['name'=>'标题', 'field'=>'title', 'type'=>'text'],
                    ['name'=>'详细内容', 'field'=>'content', 'type'=>'text'],
                    ['name'=>'问答分类', 'field'=>'category_name', 'type'=>'text'],
                    ['name'=>'访问总数', 'field'=>'access_count', 'type'=>'text'],
                    ['name'=>'评论总数', 'field'=>'comments_count', 'type'=>'text'],
                    ['name'=>'点赞总数', 'field'=>'give_thumbs_count', 'type'=>'text'],
                    ['name'=>'回复内容', 'field'=>'reply', 'type'=>'text'],
                    ['name'=>'回复日期', 'field'=>'reply_time_date', 'type'=>'text'],
                    ['name'=>'回复时间', 'field'=>'reply_time_time', 'type'=>'text'],
                    ['name'=>'添加日期', 'field'=>'add_time_date', 'type'=>'text'],
                    ['name'=>'添加时间', 'field'=>'add_time_time', 'type'=>'text'],
                    ['name'=>'更新日期', 'field'=>'upd_time_date', 'type'=>'text'],
                    ['name'=>'更新时间', 'field'=>'upd_time_time', 'type'=>'text'],
                ],
                'custom_config' => [
                    'appoint_config' => [
                        'data_url'     => PluginsApiUrl('ask', 'diyask', 'index'),
                        'is_multiple'  => 1,
                        'show_data'    => [
                            'data_key'   => 'id',
                            'data_name'  => 'content',
                        ],
                        'popup_title'   => '问答选择',
                        'header' => [
                            [
                                'field'  => 'id',
                                'name'   => '数据ID',
                                'width'  => 120,
                            ],
                            [
                                'field'  => 'content',
                                'name'   => '内容',
                            ],
                            [
                                'field'  => 'category_name',
                                'name'   => '分类',
                            ],
                        ],
                        'search_filter_form_config' => [
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'placeholder'  => '请选择问答分类',
                                    'is_multiple'  => 1,
                                    'children'     => 'items',
                                ],
                                'title'      => '问答分类',
                                'form_name'  => 'category_ids',
                                'data'       => $category,
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
                        'data_url' => PluginsApiUrl('ask', 'diyask', 'autoasklist'),
                        'filter_form_config' => [
                            [
                                'type'       => 'select',
                                'config'     => [
                                    'is_multiple'  => 1,
                                ],
                                'title'      => '问答分类',
                                'form_name'  => 'ask_category_ids',
                                'data'       => $category,
                            ],
                            [
                                'type'    => 'input',
                                'config'  => [
                                    'placeholder'  => '请输入关键字',
                                    'type'         => 'text',
                                ],
                                'title'      => '关键字',
                                'form_name'  => 'ask_keywords',
                            ],
                            [
                                'type'    => 'input',
                                'config'  => [
                                    'default'  => 4,
                                    'type'     => 'number',
                                ],
                                'title'      => '显示数量',
                                'form_name'  => 'ask_number',
                            ],
                            [
                                'type'       => 'radio',
                                'title'      => '排序类型',
                                'form_name'  => 'ask_order_by_type',
                                'data'       => BaseService::ConstData('ask_order_by_type_list'),
                                'data_key'   => 'index',
                                'data_name'  => 'name',
                                'config'     => [
                                    'default'      => 0,
                                ]
                            ],
                            [
                                'type'       => 'radio',
                                'title'      => '排序规则',
                                'form_name'  => 'ask_order_by_rule',
                                'const_key'  => 'data_order_by_rule_list',
                                'data_key'   => 'index',
                                'data_name'  => 'name',
                                'config'     => [
                                    'default'      => 0,
                                ]
                            ],
                            [
                                'type'       => 'switch',
                                'title'      => '是否回复',
                                'form_name'  => 'ask_is_reply',
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
                        'name'  => '问答',
                        'type'  => 'ask',
                        'data'  => [
                            ['name'=>'问答首页', 'page'=>'/pages/plugins/ask/index/index'],
                            ['name'=>'问答添加', 'page'=>'/pages/plugins/ask/form/form'],
                            ['name'=>'我的问答', 'page'=>'/pages/plugins/ask/user-list/user-list'],
                        ],
                    ];
                    break;
                }
            }
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
            // 获取2条问答数据
            $ask = BaseService::AskList([
                'm'               => 0,
                'n'               => 2,
                'where'           => BaseService::AskListWhere(['goods_id'=>$params['data']['goods']['id']]),
                'is_comments'     => 1,
                'plugins_config'  => $this->plugins_config,
            ]);
            $params['data']['plugins_ask_data'] = [
                'is_ask_add'  => 1,
                'ask_data'  => empty($ask['data']) ? null : $ask['data'],

            ];
        }
    }

    /**
     * 商品页面tabs内评价底部问答数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-10-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsDetailTabsCommentsBottom($params = [])
    {
        if(!empty($params['goods_id']))
        {
            // 获取商品问答数据url地址
            $goods_ask_url = PluginsHomeUrl('ask', 'goods', 'index', ['goods_id'=>$params['goods_id']]);
            return MyView('../../../plugins/ask/view/index/public/goods_detail', ['goods_ask_url'=>$goods_ask_url, 'goods_id'=>$params['goods_id']]);
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
        if(isset($this->plugins_config['is_user_menu']) && $this->plugins_config['is_user_menu'] == 1)
        {
            $params['data']['base']['item'][] = [
                'name'      => $this->user_center_left_name,
                'url'       => PluginsHomeUrl('ask', 'ask', 'index'),
                'contains'  => ['askaskindex', 'askasksaveinfo'],
                'is_show'   => 1,
                'icon'      => '',
            ];
        }
        
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
            if(!empty($this->plugins_config['application_name']))
            {
                $nav = [
                    'id'                    => 0,
                    'pid'                   => 0,
                    'name'                  => $this->plugins_config['application_name'],
                    'url'                   => PluginsAdminService::PluginsSecondDomainUrl('ask', true),
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