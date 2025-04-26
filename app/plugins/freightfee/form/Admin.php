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
namespace app\plugins\freightfee\form;

use think\facade\Db;
use app\service\GoodsService;
use app\service\RegionService;
use app\service\BrandService;
use app\plugins\freightfee\service\BaseService;

/**
 * 运费动态表单
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-07-21
 * @desc    description
 */
class Admin
{
    // 基础条件
    public $condition_base = [
        ['w.is_delete_time', '=', 0],
    ];

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-21
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
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => '名称/别名',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/freightfee/view/admin/admin/module/info',
                    'grid_size'     => 'sm',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'w.name|w.alias',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '运费展示名称',
                    'view_type'     => 'field',
                    'view_key'      => 'show_name',
                    'search_config' => [
                        'form_type'         => 'input',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '是否启用',
                    'view_type'     => 'status',
                    'view_key'      => 'is_enable',
                    'post_url'      => PluginsAdminUrl('freightfee', 'admin', 'statusupdate'),
                    'is_form_su'    => 1,
                    'align'         => 'center',
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'ft.is_enable',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_is_text_list'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '计价方式',
                    'view_type'     => 'field',
                    'view_key'      => 'valuation',
                    'view_data'     => BaseService::$is_whether_list,
                    'view_data_key' => 'name',
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'ft.valuation',
                        'where_type'        => 'in',
                        'data'              => BaseService::$is_whether_list,
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '续费计算方式',
                    'view_type'     => 'field',
                    'view_key'      => 'is_continue_type',
                    'view_data'     => BaseService::$is_continue_type_list,
                    'view_data_key' => 'name',
                    'grid_size'     => 'xs',
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'ft.is_continue_type',
                        'where_type'        => 'in',
                        'data'              => BaseService::$is_continue_type_list,
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '首费不足按首费',
                    'view_type'     => 'field',
                    'view_key'      => 'is_insufficient_first_price',
                    'view_data'     => MyConst('common_is_text_list'),
                    'view_data_key' => 'name',
                    'align'         => 'center',
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'ft.is_insufficient_first_price',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_is_text_list'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/freightfee/view/admin/admin/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
        ];
    }
}
?>