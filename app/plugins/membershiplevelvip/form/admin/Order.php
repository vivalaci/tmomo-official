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
namespace app\plugins\membershiplevelvip\form\admin;

use think\facade\Db;
use app\service\PaymentService;
use app\plugins\membershiplevelvip\service\BaseService;

/**
 * 会员订单动态表格-管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-05-16
 * @desc    description
 */
class Order
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
                    'label'              => '用户信息',
                    'view_type'          => 'module',
                    'view_key'           => 'lib/module/user',
                    'grid_size'          => 'sm',
                    'is_sort'            => 1,
                    'params_where_name'  => 'uid',
                    'search_config'      => [
                        'form_type'             => 'input',
                        'form_name'             => 'user_id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'WhereValueUserInfo',
                        'placeholder'           => '请输入用户名/昵称/手机/邮箱',
                    ],
                ],
                [
                    'label'             => '开通单号',
                    'view_type'         => 'field',
                    'view_key'          => 'payment_user_order_no',
                    'is_sort'           => 1,
                    'search_config'     => [
                        'form_type'         => 'input',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '时长',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/membershiplevelvip/view/admin/order/module/period',
                    'search_config'     => [
                        'form_type'         => 'section',
                        'form_name'         => 'number',
                    ],
                ],
                [
                    'label'             => '金额',
                    'view_type'         => 'field',
                    'view_key'          => 'price',
                    'is_sort'           => 1,
                    'search_config'     => [
                        'form_type'         => 'section',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'             => '支付金额',
                    'view_type'         => 'field',
                    'view_key'          => 'pay_price',
                    'is_sort'           => 1,
                    'search_config'     => [
                        'form_type'         => 'section',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'             => '返佣总额',
                    'view_type'         => 'field',
                    'view_key'          => 'profit_price_total',
                    'width'             => 120,
                ],
                [
                    'label'             => '订单状态',
                    'view_type'         => 'field',
                    'view_key'          => 'status_name',
                    'is_round_point'    => 1,
                    'round_point_key'   => 'status',
                    'round_point_style' => [1=>'success', 2=>'warning', 3=>'danger'],
                    'align'             => 'center',
                    'is_sort'           => 1,
                    'width'             => 120,
                    'search_config'     => [
                        'form_type'         => 'select',
                        'form_name'         => 'status',
                        'where_type'        => 'in',
                        'data'              => BaseService::$payment_user_order_status_list,
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'          => '结算状态',
                    'view_type'      => 'field',
                    'view_key'       => 'settlement_status_name',
                    'is_color'       => 1,
                    'color_key'      => 'settlement_status',
                    'color_style'    => [1=>'secondary', 2=>'success'],
                    'align'          => 'center',
                    'is_sort'        => 1,
                    'width'          => 120,
                    'search_config'  => [
                        'form_type'         => 'select',
                        'form_name'         => 'settlement_status',
                        'where_type'        => 'in',
                        'data'              => BaseService::$payment_user_order_settlement_status_list,
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'             => '类型',
                    'view_type'         => 'field',
                    'view_key'          => 'type_name',
                    'is_sort'           => 1,
                    'width'             => 120,
                    'search_config'     => [
                        'form_type'         => 'select',
                        'form_name'         => 'type',
                        'where_type'        => 'in',
                        'data'              => BaseService::$payment_user_order_type_list,
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '支付方式',
                    'view_type'     => 'field',
                    'view_key'      => 'payment_name',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'payment',
                        'where_type'        => 'in',
                        'data'              => PaymentService::PaymentList(['field'=>'payment,name']),
                        'data_key'          => 'payment',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '支付时间',
                    'view_type'     => 'field',
                    'view_key'      => 'pay_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
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
                    'view_key'      => '../../../plugins/membershiplevelvip/view/admin/order/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
        ];
    }

    /**
     * 用户信息条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-26
     * @desc    description
     * @param   [string]          $value    [条件值]
     * @param   [array]           $params   [输入参数]
     */
    public function WhereValueUserInfo($value, $params = [])
    {
        if(!empty($value))
        {
            // 获取用户 id
            $ids = Db::name('User')->where('number_code|username|nickname|mobile|email', 'like', '%'.$value.'%')->column('id');

            // 避免空条件造成无效的错觉
            return empty($ids) ? [0] : $ids;
        }
        return $value;
    }
}
?>