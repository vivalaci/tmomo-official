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
namespace app\plugins\homemiddleadv\form\admin;

/**
 * 数据列表动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-18
 * @desc    description
 */
class Slider
{
    // 基础条件
    public $condition_base = [];

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-18
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
                'is_delete'     => 1,
                'delete_key'    => 'ids',
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
                    'label'         => '名称',
                    'view_type'     => 'field',
                    'view_key'      => 'name',
                ],
                [
                    'label'         => '所属平台',
                    'view_type'     => 'field',
                    'view_key'      => 'platform_name',
                ],
                [
                    'label'         => '图片',
                    'view_type'     => 'images',
                    'view_key'      => 'images',
                    'images_height'        => 50,
                ],
                [
                    'label'         => '链接地址',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/homemiddleadv/view/admin/slider/module/url',
                    'grid_size'     => 'lg',
                ],
                [
                    'label'         => '状态',
                    'view_type'     => 'status',
                    'view_key'      => 'is_enable',
                    'post_url'      => PluginsAdminUrl('homemiddleadv', 'slider', 'statusupdate'),
                    'is_form_su'    => 1,
                    'align'         => 'center',
                ],
                [
                    'label'         => '新窗口打开',
                    'view_type'     => 'status',
                    'view_key'      => 'is_new_window_open',
                    'post_url'      => PluginsAdminUrl('homemiddleadv', 'slider', 'statusupdate'),
                    'is_form_su'    => 0,
                    'align'         => 'center',
                ],
                [
                    'label'         => '操作时间',
                    'view_type'     => 'field',
                    'view_key'      => 'operation_time',
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/homemiddleadv/view/admin/slider/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
        ];
    }
}
?>