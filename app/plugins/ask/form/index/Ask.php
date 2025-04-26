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
namespace app\plugins\ask\form\index;

use app\service\UserService;
use app\plugins\ask\service\BaseService;
use app\plugins\ask\service\AskCategoryService;

/**
 * 我的问答动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-30
 * @desc    description
 */
class Ask
{
    // 基础条件
    public $condition_base = [];

    // 问答分类
    public $category_list;

    /**
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function __construct($params = [])
    {
        // 问答分类
        $this->category_list = array_column(AskCategoryService::AskCategoryAll(), 'name', 'id');

        // 当前用户
        $user = UserService::LoginUserInfo();
        $user_id = empty($user['id']) ? 0 : $user['id'];
        $this->condition_base[] = ['user_id', '=', $user_id];
    }

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Run($params = [])
    {
        $lang = MyLang('form_table.ask');
        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'is_search'     => 1,
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => $lang['goods'],
                    'view_type'     => 'module',
                    'view_key'      => 'lib/module/goods',
                    'grid_size'     => 'lg',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => 'goods_id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'SystemModuleGoodsWhereHandle',
                        'placeholder'           => $lang['goods_placeholder'],
                    ],
                ],
                [
                    'label'         => $lang['title'],
                    'view_type'     => 'field',
                    'view_key'      => 'title',
                    'grid_size'     => 'lg',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'title|content',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => $lang['category_name'],
                    'view_type'     => 'field',
                    'view_key'      => 'category_name',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'category_id',
                        'where_type'        => 'in',
                        'data'              => $this->category_list,
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => $lang['is_show_name'],
                    'view_type'     => 'field',
                    'view_key'      => 'is_show_name',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'is_show',
                        'where_type'        => 'in',
                        'data'              => BaseService::ConstData('ask_is_show_list'),
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => $lang['is_reply_name'],
                    'view_type'     => 'field',
                    'view_key'      => 'is_reply_name',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'is_reply',
                        'where_type'        => 'in',
                        'data'              => BaseService::ConstData('ask_is_reply_list'),
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => $lang['images_count'],
                    'view_type'     => 'field',
                    'view_key'      => 'images_count',
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],
                [
                    'label'         => $lang['reply_time_time'],
                    'view_type'     => 'field',
                    'view_key'      => 'reply_time_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'reply_time',
                    ],
                ],
                [
                    'label'         => $lang['email_notice'],
                    'view_type'     => 'field',
                    'view_key'      => 'email_notice',
                    'search_config' => [
                        'form_type'         => 'input',
                    ],
                ],
                [
                    'label'         => $lang['mobile_notice'],
                    'view_type'     => 'field',
                    'view_key'      => 'mobile_notice',
                    'search_config' => [
                        'form_type'         => 'input',
                    ],
                ],
                [
                    'label'         => $lang['add_time_time'],
                    'view_type'     => 'field',
                    'view_key'      => 'add_time_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'add_time',
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/ask/view/index/ask/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
            // 数据配置
            'data'  => [
                'table_name'    => 'PluginsAsk',
                'data_handle'   => 'AskService::AskListHandle',
                'is_page'       => 1,
                'data_params'   => [
                    'is_goods'      => 1,
                ],
                'is_fixed_name_field'   => 1,
                'fixed_name_data'       => [
                    'category_id'    => [
                        'data'  => $this->category_list,
                        'field' => 'category_name',
                    ],
                    'is_show'    => [
                        'data'  => BaseService::ConstData('ask_is_show_list'),
                    ],
                    'is_reply'    => [
                        'data'  => BaseService::ConstData('ask_is_reply_list'),
                    ]
                ],
            ],
        ];
    }
}
?>