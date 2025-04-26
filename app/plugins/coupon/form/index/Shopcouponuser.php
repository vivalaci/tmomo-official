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

use think\facade\Db;
use app\service\UserService;
use app\plugins\coupon\service\BaseService;

/**
 * 多商户用户优惠券动态表格-管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-05-16
 * @desc    description
 */
class ShopCouponUser
{
    // 基础条件
    public $condition_base = [];

    /**
     * 构造方法
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function __construct($params = [])
    {
        // 当前店铺优惠券、空则赋值0
        $shop_id = BaseService::ShopID();
        if(empty($shop_id))
        {
            $this->condition_base[] = ['coupon_id', '=', 0];
        } else {
            $where = [
                ['shop_id', '=', $shop_id],
                ['already_send_count', '>', 0],
            ];
            $coupon_ids = Db::name('PluginsCoupon')->where($where)->column('id');
            $this->condition_base[] = ['coupon_id', 'in', empty($coupon_ids) ? [0] : $coupon_ids];
        }
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
        $lang = MyLang('form');
        $cid_map = empty($params['cid']) ? [] : ['cid'=>$params['cid']];
        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'status_field'  => 'is_valid',
                'is_search'     => 1,
                'search_url'    => PluginsHomeUrl('coupon', 'shopcouponuser', 'index', $cid_map),
                'is_middle'     => 0,
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => $lang['user_info_text'],
                    'view_type'     => 'module',
                    'view_key'      => 'lib/module/user',
                    'grid_size'     => 'sm',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => 'user_id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'WhereValueUserInfo',
                        'placeholder'           => $lang['input_user_name_phone_email_placeholder'],
                    ],
                ],
                [
                    'label'             => $lang['coupon_info_text'],
                    'view_type'         => 'module',
                    'view_key'          => '../../../plugins/coupon/view/index/shopcouponuser/module/coupon',
                    'params_where_name' => 'cid',
                    'search_config'     => [
                        'form_type'         => 'select',
                        'form_name'         => 'coupon_id',
                        'where_type'        => 'in',
                        'data'              => $this->WhereValueCouponList(),
                        'placeholder'       => $lang['select_coupon_placeholder'],
                        'is_multiple'       => 1,
                        'is_disabled'       => empty($cid_map) ? 0 : 1,
                    ],
                ],
                [
                    'label'         => $lang['is_effective_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'is_valid',
                    'view_data'     => MyConst('common_is_text_list'),
                    'view_data_key' => 'name',
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
                    'label'         => $lang['is_use_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'is_use',
                    'view_data'     => MyConst('common_is_text_list'),
                    'view_data_key' => 'name',
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
                    'label'         => $lang['is_expire_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'is_expire',
                    'view_data'     => MyConst('common_is_text_list'),
                    'view_data_key' => 'name',
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
                    'label'         => $lang['available_time_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'time_start',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => $lang['end_time_text'],
                    'view_type'     => 'field',
                    'view_key'      => 'time_end',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
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
                    'view_key'      => '../../../plugins/coupon/view/index/shopcouponuser/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
            // 数据配置
            'data'  => [
                'table_name'    => 'PluginsCouponUser',
                'data_handle'   => 'UserCouponService::CouponUserListHandle',
                'order_by'      => 'id desc',
                'is_page'       => 1,
                'data_params'   => [
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
     * 优惠券信息条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-26
     * @desc    description
     */
    public function WhereValueCouponList()
    {
        return Db::name('PluginsCoupon')->order('is_enable desc')->column('name', 'id');
    }
}
?>