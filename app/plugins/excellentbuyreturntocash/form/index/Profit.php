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
namespace app\plugins\excellentbuyreturntocash\form\index;

use think\facade\Db;
use app\service\UserService;
use app\plugins\excellentbuyreturntocash\service\BaseService;

/**
 * 优购返现动态表格-用户
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-05-16
 * @desc    description
 */
class Profit
{
    // 基础条件
    public $condition_base = [];

    // 用户id
    public $user_id;

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
        // 当前用户
        $user = UserService::LoginUserInfo();
        $this->user_id = empty($user['id']) ? 0 : $user['id'];
        $this->condition_base[] = ['c.user_id', '=', $this->user_id];
    }

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
        // 表格数据
        $data = [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'is_search'     => 1,
                'search_url'    => PluginsHomeUrl('excellentbuyreturntocash', 'profit', 'index'),
                'detail_title'  => MyLang('form_table_base_detail_title'),
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => '订单号',
                    'view_type'     => 'field',
                    'view_key'      => 'order_no',
                    'width'         => 170,
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'o.order_no',
                        'where_type'        => '=',
                    ],
                ],
                [
                    'label'         => '订单金额',
                    'view_type'     => 'field',
                    'view_key'      => 'total_price',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'o.total_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '退款金额',
                    'view_type'     => 'field',
                    'view_key'      => 'refund_price',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'o.refund_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '有效金额',
                    'view_type'     => 'field',
                    'view_key'      => 'valid_price',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'c.valid_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '返现金额',
                    'view_type'     => 'field',
                    'view_key'      => 'profit_price',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'c.profit_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '结算状态',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/excellentbuyreturntocash/view/index/profit/module/status',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'c.status',
                        'where_type'        => 'in',
                        'data'              => BaseService::$profit_status_list,
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '订单状态',
                    'view_type'     => 'field',
                    'view_key'      => 'order_status',
                    'view_data_key' => 'name',
                    'view_data'     => MyConst('common_order_status'),
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'o.status',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_order_status'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '支付状态',
                    'view_type'     => 'field',
                    'view_key'      => 'order_pay_status',
                    'view_data_key' => 'name',
                    'view_data'     => MyConst('common_order_pay_status'),
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'o.pay_status',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_order_pay_status'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '来源',
                    'view_type'     => 'field',
                    'view_key'      => 'order_client_type',
                    'view_data_key' => 'name',
                    'view_data'     => MyConst('common_platform_type'),
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'o.client_type',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_platform_type'),
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '结算时间',
                    'view_type'     => 'fild',
                    'view_key'      => 'success_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'c.success_time',
                    ],
                ],
                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'c.add_time',
                    ],
                ],
                [
                    'label'         => '更新时间',
                    'view_type'     => 'field',
                    'view_key'      => 'upd_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'c.upd_time',
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/excellentbuyreturntocash/view/index/profit/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
        ];

        return $data;
    }
}
?>