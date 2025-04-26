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
namespace app\plugins\intellectstools\form\admin;

use think\facade\Db;
use app\plugins\intellectstools\service\BaseService;

/**
 * 头条支付分账动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-26
 * @desc    description
 */
class ToutiaoSettlement
{
    // 基础条件
    public $condition_base = [
        ['p.payment', '=', 'Toutiao'],
        ['p.status', '=', 1],
    ];

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-26
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
                    'view_type'         => 'checkbox',
                    'is_checked'        => 0,
                    'checked_text'      => MyLang('reverse_select_title'),
                    'not_checked_text'  => MyLang('select_all_title'),
                    'not_show_key'      => 'is_settlement',
                    'not_show_data'     => [0],
                    'align'             => 'center',
                    'width'             => 80,
                ],
                [
                    'label'         => '用户信息',
                    'view_type'     => 'module',
                    'view_key'      => 'lib/module/user',
                    'grid_size'     => 'sm',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => 'p.user_id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'WhereValueUserInfo',
                        'placeholder'           => '请输入用户名/昵称/手机/邮箱',
                    ],
                ],
                [
                    'label'         => '支付单号',
                    'view_type'     => 'field',
                    'view_key'      => 'log_no',
                    'width'         => 170,
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'p.log_no',
                        'where_type'        => '=',
                    ],
                ],
                [
                    'label'         => '业务订单金额',
                    'view_type'     => 'field',
                    'view_key'      => 'total_price',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'p.total_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '支付金额',
                    'view_type'     => 'field',
                    'view_key'      => 'pay_price',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'p.pay_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '分账状态',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/intellectstools/view/admin/toutiaosettlement/module/status',
                    'is_sort'       => 1,
                    'align'         => 'center',
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'ps.status',
                        'where_type'        => 'in',
                        'data'              => BaseService::$toutiaosettlement_status_list,
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '是否推送',
                    'view_type'     => 'field',
                    'view_key'      => 'is_push',
                    'view_data'     => MyConst('common_is_text_list'),
                    'view_data_key' => 'name',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'ps.is_push',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_is_text_list'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '业务类型',
                    'view_type'     => 'field',
                    'view_key'      => 'business_type',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'p.business_type',
                        'where_type'        => 'in',
                        'data'              => $this->PayLogBusinessTypeList(),
                        'data_key'          => 'name',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '业务id/单号',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/intellectstools/view/admin/toutiaosettlement/module/business_list',
                    'width'         => 300,
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => 'p.id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'WhereValueBusinessInfo',
                    ],
                ],
                [
                    'label'         => '支付平台交易号',
                    'view_type'     => 'field',
                    'view_key'      => 'trade_no',
                    'grid_size'     => 'sm',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'p.trade_no',
                        'where_type'        => '=',
                    ],
                ],
                [
                    'label'         => '支付平台用户帐号',
                    'view_type'     => 'field',
                    'view_key'      => 'buyer_user',
                    'grid_size'     => 'sm',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'p.buyer_user',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '订单名称',
                    'view_type'     => 'field',
                    'view_key'      => 'subject',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'p.subject',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '推送时间',
                    'view_type'     => 'field',
                    'view_key'      => 'push_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'ps.push_time',
                    ],
                ],
                [
                    'label'         => '支付时间',
                    'view_type'     => 'field',
                    'view_key'      => 'pay_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'p.pay_time',
                    ],
                ],
                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'p.add_time',
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/intellectstools/view/admin/toutiaosettlement/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
            // 数据配置
            'data'  => [
                'table_obj'     => Db::name('PayLog')->alias('p')->leftJoin('plugins_intellectstools_toutiaosettlement ps', 'p.id=ps.pay_id'),
                'select_field'  => 'p.*,ps.status,ps.is_push,ps.reason,ps.response_data,ps.push_time',
                'detail_dkey'   => 'p.id',
                'order_by'      => 'p.id desc',
                'data_handle'   => 'ToutiaoSettlementService::PayLogListHandle',
                'is_page'       => 1,
                'data_params'   => [
                    'is_public'     => 0,
                    'user_type'     => 'admin',
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

    /**
     * 业务类型
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-26
     * @desc    description
     */
    public function PayLogBusinessTypeList()
    {
        return Db::name('PayLog')->field('business_type as name')->group('business_type')->select()->toArray();
    }

    /**
     * 关联业务条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-30
     * @desc    description
     * @param   [string]          $value    [条件值]
     * @param   [array]           $params   [输入参数]
     */
    public function WhereValueBusinessInfo($value, $params = [])
    {
        if(!empty($value))
        {
            // 获取支持业务支付 id
            $ids = Db::name('PayLogValue')->where('business_id|business_no', '=', $value)->column('pay_log_id');

            // 避免空条件造成无效的错觉
            return empty($ids) ? [0] : $ids;
        }
        return $value;
    }
}
?>