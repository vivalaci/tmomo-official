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
namespace app\plugins\ask\form\admin;

use app\plugins\ask\service\AskCategoryService;

/**
 * 问答管理动态表格-管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-08
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
    }

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-08
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
                'status_field'  => 'is_show',
                'is_search'     => 1,
                'is_delete'     => 1,
                'is_middle'     => 0,
            ],
            // 表单配置
            'form' => [
                [
                    'view_type'         => 'checkbox',
                    'is_checked'        => 0,
                    'checked_text'      => MyLang('reverse_select_title'),
                    'not_checked_text'  => MyLang('select_all_title'),
                    'align'             => 'center',
                    'width'             => 80,
                ],
                [
                    'label'         => $lang['user'],
                    'view_type'     => 'module',
                    'view_key'      => 'lib/module/user',
                    'grid_size'     => 'sm',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => 'user_id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'SystemModuleUserWhereHandle',
                        'placeholder'           => $lang['user_placeholder'],
                    ],
                ],
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
                    'label'         => $lang['name'],
                    'view_type'     => 'field',
                    'view_key'      => 'name',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => $lang['tel'],
                    'view_type'     => 'field',
                    'view_key'      => 'tel',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => $lang['is_show_name'],
                    'view_type'     => 'status',
                    'view_key'      => 'is_show',
                    'post_url'      => PluginsAdminUrl('ask', 'ask', 'statusupdate'),
                    'is_form_su'    => 1,
                    'align'         => 'center',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_is_text_list'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => $lang['is_reply_name'],
                    'view_type'     => 'status',
                    'view_key'      => 'is_reply',
                    'post_url'      => PluginsAdminUrl('ask', 'ask', 'statusupdate'),
                    'align'         => 'center',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_is_text_list'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
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
                    'label'         => $lang['images_count'],
                    'view_type'     => 'field',
                    'view_key'      => 'images_count',
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],
                [
                    'label'         => $lang['access_count'],
                    'view_type'     => 'field',
                    'view_key'      => 'access_count',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],
                [
                    'label'         => $lang['comments_count'],
                    'view_type'     => 'field',
                    'view_key'      => 'comments_count',
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],
                [
                    'label'         => $lang['give_thumbs_count'],
                    'view_type'     => 'field',
                    'view_key'      => 'give_thumbs_count',
                    'search_config' => [
                        'form_type'         => 'section',
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
                    'label'         => $lang['upd_time_time'],
                    'view_type'     => 'field',
                    'view_key'      => 'upd_time_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'upd_time',
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/ask/view/admin/ask/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
            // 数据配置
            'data'  => [
                'table_name'    => 'PluginsAsk',
                'data_handle'   => 'AskService::AskListHandle',
                'detail_action' => ['detail', 'saveinfo', 'replyinfo'],
                'is_page'       => 1,
                'data_params'   => [
                    'is_public'     => 0,
                    'is_goods'      => 1,
                    'user_type'     => 'admin',
                ],
                'is_fixed_name_field'   => 1,
                'fixed_name_data'       => [
                    'category_id'    => [
                        'data'  => $this->category_list,
                        'field' => 'category_name',
                    ]
                ],
            ],
        ];
    }
}
?>