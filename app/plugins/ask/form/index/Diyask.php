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

use app\plugins\ask\service\AskCategoryService;

/**
 * DIY问答动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-18
 * @desc    description
 */
class DiyAsk
{
    // 基础条件
    public $condition_base = [
        ['is_show', '=', 1],
    ];

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-16
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Run($params = [])
    {
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
                    'label'         => '问答ID',
                    'view_type'     => 'field',
                    'view_key'      => 'id',
                    'width'         => 110,
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'id',
                        'where_type'        => '=',
                    ],
                ],
                [
                    'label'              => '标题/内容',
                    'view_type'          => 'field',
                    'view_key'           => 'title',
                    'is_sort'            => 1,
                    'params_where_name'  => 'keywords',
                    'search_config'      => [
                        'form_type'         => 'input',
                        'form_name'         => 'title|content',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'              => '问答分类',
                    'view_type'          => 'field',
                    'view_key'           => 'category_name',
                    'params_where_name'  => 'category_ids',
                    'search_config'      => [
                        'form_type'         => 'select',
                        'form_name'         => 'category_id',
                        'where_type'        => 'in',
                        'data'              => $this->AskCategoryList(),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => '更新时间',
                    'view_type'     => 'field',
                    'view_key'      => 'upd_time',
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
            ],
            // 数据配置
            'data'  => [
                'table_name'   => 'PluginsAsk',
                'data_handle'  => 'AskService::AskListHandle',
            ],
        ];
    }

    /**
     * 获取分类列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-11
     * @desc    description
     */
    public function AskCategoryList()
    {
        return AskCategoryService::AskCategoryAll(['field'=>'id,name']);
    }
}
?>