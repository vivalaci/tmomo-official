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
namespace app\plugins\coupon\form\admin;

use app\plugins\coupon\service\BaseService;

/**
 * 优惠券动态表格-管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-05-16
 * @desc    description
 */
class Coupon
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
        $lang = MyLang('form');
        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'status_field'  => 'is_enable',
                'is_search'     => 1,
                'is_middle'     => 0,
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => $lang['id'],
                    'view_type'     => 'field',
                    'view_key'      => 'id',
                    'width'         => 110,
                    'is_sort'       => 1,
                    'is_copy'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                        'where_type'        => '=',
                    ],
                ],
                [
                    'label'              => $lang['name_desc_text'],
                    'view_type'          => 'module',
                    'view_key'           => '../../../plugins/coupon/view/admin/coupon/module/info',
                    'params_where_name'  => 'keywords',
                    'search_config'      => [
                        'form_type'         => 'input',
                        'form_name'         => 'name|desc',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => $lang['background_color_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'bg_color_name',
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'bg_color',
                        'where_type'        => 'in',
                        'data'              => BaseService::ConstData('coupon_bg_color_list'),
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'              => $lang['coupon_type_text'],
                    'view_type'          => 'field',
                    'view_key'           => 'type_name',
                    'params_where_name'  => 'type',
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
                    'label'         => MyLang('offer_info_text'),
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/coupon/view/admin/coupon/module/discount_value',
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'discount_value',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'              => $lang['is_open_text'],
                    'view_type'          => 'status',
                    'view_key'           => 'is_enable',
                    'post_url'           => PluginsAdminUrl('coupon', 'coupon', 'statusupdate'),
                    'is_form_su'         => 1,
                    'align'              => 'center',
                    'params_where_name'  => 'is_enable',
                    'search_config'      => [
                        'form_type'         => 'select',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_is_text_list'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => MyLang('open_user_claim_text'),
                    'view_type'     => 'status',
                    'view_key'      => 'is_user_receive',
                    'post_url'      => PluginsAdminUrl('coupon', 'coupon', 'statusupdate'),
                    'align'         => 'center',
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
                    'label'         => MyLang('repeat_receive_text'),
                    'view_type'     => 'status',
                    'view_key'      => 'is_repeat_receive',
                    'post_url'      => PluginsAdminUrl('coupon', 'coupon', 'statusupdate'),
                    'align'         => 'center',
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
                    'label'         => MyLang('register_grant_text'),
                    'view_type'     => 'status',
                    'view_key'      => 'is_regster_send',
                    'post_url'      => PluginsAdminUrl('coupon', 'coupon', 'statusupdate'),
                    'align'         => 'center',
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
                    'label'         => MyLang('expire_type_text'),
                    'view_type'     => 'field',
                    'view_key'      => 'expire_type_name',
                    'search_config' => [
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
                    'label'         => MyLang('effective_hour_text'),
                    'view_type'     => 'field',
                    'view_key'      => 'expire_hour',
                    'search_config' => [
                        'form_type'         => 'section',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => $lang['limit_time_start_time_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'fixed_time_start',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => $lang['limit_time_end_time_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'fixed_time_end',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => MyLang('use_limit_text'),
                    'view_type'     => 'field',
                    'view_key'      => 'use_limit_type_name',
                    'search_config' => [
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
                    'label'         => MyLang('minimum_order_amount_text'),
                    'view_type'     => 'field',
                    'view_key'      => 'where_order_price',
                    'search_config' => [
                        'form_type'         => 'section',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => $lang['limit_all_num_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'limit_send_count',
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],
                [
                    'label'         => $lang['grant_all_num_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'already_send_count',
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],
                [
                    'label'         => $lang['order_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'sort',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],
                [
                    'label'         => $lang['create_time_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => $lang['update_time_text'],
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
                    'view_key'      => '../../../plugins/coupon/view/admin/coupon/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
            // 数据配置
            'data'  => [
                'table_name'    => 'PluginsCoupon',
                'data_handle'   => 'CouponService::CouponListHandle',
                'order_by'      => 'sort asc,id desc',
                'is_page'       => 1,
            ],
        ];
    }
}
?>