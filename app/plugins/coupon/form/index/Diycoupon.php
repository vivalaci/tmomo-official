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
namespace app\plugins\coupon\form\index;

use app\plugins\coupon\service\BaseService;

/**
 * DIY优惠券动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-18
 * @desc    description
 */
class DiyCoupon
{
    // 基础条件
    public $condition_base = [
        ['is_enable', '=', 1],
        ['is_user_receive', '=', 1],
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
                    'label'         => '优惠券ID',
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
                    'label'              => '名称',
                    'view_type'          => 'field',
                    'view_key'           => 'name',
                    'is_sort'            => 1,
                    'params_where_name'  => 'keywords',
                    'search_config'      => [
                        'form_type'         => 'input',
                        'form_name'         => 'name|desc',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'              => '类型',
                    'view_type'          => 'field',
                    'view_key'           => 'type_name',
                    'params_where_name'  => 'type_ids',
                    'search_config'      => [
                        'form_type'         => 'select',
                        'form_name'         => 'type',
                        'where_type'        => 'in',
                        'data'              => BaseService::ConstData('coupon_type_list'),
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'              => '到期类型',
                    'view_type'          => 'field',
                    'view_key'           => 'expire_type_name',
                    'params_where_name'  => 'expire_type_ids',
                    'search_config'      => [
                        'form_type'         => 'select',
                        'form_name'         => 'expire_type',
                        'where_type'        => 'in',
                        'data'              => BaseService::ConstData('coupon_expire_type_list'),
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'              => '使用限制',
                    'view_type'          => 'field',
                    'view_key'           => 'use_limit_type_name',
                    'params_where_name'  => 'use_limit_type_ids',
                    'search_config'      => [
                        'form_type'         => 'select',
                        'form_name'         => 'use_limit_type',
                        'where_type'        => 'in',
                        'data'              => BaseService::ConstData('coupon_use_limit_type_list'),
                        'data_key'          => 'value',
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
                'table_name'   => 'PluginsCoupon',
                'data_handle'  => 'CouponService::CouponListHandle',
            ],
        ];
    }
}
?>