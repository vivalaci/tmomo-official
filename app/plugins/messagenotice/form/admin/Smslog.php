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
namespace app\plugins\messagenotice\form\admin;

use app\plugins\messagenotice\service\BaseService;

/**
 * 短信日志动态表格-管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-05-16
 * @desc    description
 */
class SmsLog
{
    // 基础条件
    public $condition_base = [];

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-05-16
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Run($params = [])
    {
        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'is_search'     => 1,
                'is_middle'     => 0,
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => '手机',
                    'view_type'     => 'field',
                    'view_key'      => 'mobile',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                    ],
                ],
                [
                    'label'         => '短信模板ID',
                    'view_type'     => 'field',
                    'view_key'      => 'template',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                    ],
                ],
                [
                    'label'         => '短信签名',
                    'view_type'     => 'field',
                    'view_key'      => 'sms_sign',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                    ],
                ],
                [
                    'label'             => '状态',
                    'view_type'         => 'field',
                    'view_key'          => 'status_name',
                    'is_round_point'    => 1,
                    'round_point_key'   => 'status',
                    'round_point_style' => [1=>'success', 2=>'danger'],
                    'align'             => 'center',
                    'is_sort'           => 1,
                    'search_config'     => [
                        'form_type'         => 'select',
                        'form_name'         => 'status',
                        'where_type'        => 'in',
                        'data'              => BaseService::$log_status_list,
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '失败原因',
                    'view_type'     => 'field',
                    'view_key'      => 'reason',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                    ],
                ],
                [
                    'label'         => '耗时(秒)',
                    'view_type'     => 'field',
                    'view_key'      => 'tsc',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => '更新时间',
                    'view_type'     => 'field',
                    'view_key'      => 'upd_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/messagenotice/view/admin/smslog/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
            // 数据配置
            'data'  => [
                'table_name'            => 'PluginsMessagenoticeSmsLog',
                'is_handle_time_field'  => 1,
                'is_fixed_name_field'   => 1,
                'fixed_name_data'       => [
                    'status'            => [
                        'data'  => BaseService::$log_status_list,
                    ],
                ],
            ],
        ];
    }
}
?>