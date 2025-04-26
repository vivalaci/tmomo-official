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
namespace app\plugins\activity\form\index;

use think\facade\Db;
use app\plugins\activity\service\ActivityService;
use app\plugins\activity\service\CategoryService;

/**
 * 活动管理动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-18
 * @desc    description
 */
class DiyActivity
{
    // 基础条件
    public $condition_base = [
        ['is_enable', '=', 1],
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
                'status_field'  => 'is_enable',
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
                    'label'              => '标题',
                    'view_type'          => 'field',
                    'view_key'           => 'title',
                    'grid_size'          => 'lg',
                    'params_where_name'  => 'keywords',
                    'search_config'      => [
                        'form_type'         => 'input',
                        'form_name'         => 'title|vice_title|describe',
                        'where_type'        => 'like',
                        'placeholder'       => '请输入标题/副标题'
                    ],
                ],
                [
                    'label'              => '分类',
                    'view_type'          => 'field',
                    'view_key'           => 'activity_category_name',
                    'params_where_name'  => 'category_ids',
                    'search_config'      => [
                        'form_type'         => 'select',
                        'form_name'         => 'activity_category_id',
                        'where_type'        => 'in',
                        'data'              => $this->ActivityCategoryList(),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '排序',
                    'view_type'     => 'field',
                    'view_key'      => 'sort',
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
                'table_obj'     => Db::name('PluginsActivity')->whereRaw(ActivityService::ActivityTimeWhere()),
                'data_handle'   => 'ActivityService::DataHandle',
                'order_by'      => 'sort asc, id desc',
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
    public function ActivityCategoryList()
    {
        $res = CategoryService::CategoryList(['field'=>'id,name']);
        return $res['data'];
    }
}
?>