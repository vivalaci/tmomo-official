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
namespace app\plugins\distribution\form\admin;

use think\facade\Db;

/**
 * 推广用户动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-28
 * @desc    description
 */
class PromotionUser
{
    // 基础条件
    public $condition_base = [];

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-28
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Run($params = [])
    {
        $uid = empty($params['uid']) ? 0 : intval($params['uid']);
        $type = empty($params['type']) ? 0 : intval($params['type']);
        $start = empty($params['start']) ? '' : $params['start'];
        $end = empty($params['end']) ? '' : $params['end'];
        $user_field_first = in_array($type, [0]) ? '' : 'u.';
        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'is_search'     => 1,
                'search_url'    => PluginsAdminUrl('distribution', 'promotionuser', 'index', ['uid'=>$uid, 'type'=>$type, 'start'=>$start, 'end'=>$end]),
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => '用户信息',
                    'view_type'     => 'module',
                    'view_key'      => 'lib/module/user',
                    'grid_size'     => 'sm',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => $user_field_first.'id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'WhereValueUserInfo',
                        'placeholder'           => '请输入用户名/昵称/手机/邮箱',
                    ],
                ],
                [
                    'label'         => '消费订单总数',
                    'view_type'     => 'field',
                    'view_key'      => 'order_count',
                ],
                [
                    'label'         => '消费金额',
                    'view_type'     => 'field',
                    'view_key'      => 'order_total',
                ],
                [
                    'label'         => '最后下单时间',
                    'view_type'     => 'field',
                    'view_key'      => 'order_last_time',
                ],
                [
                    'label'         => '下级订单',
                    'view_type'     => 'field',
                    'view_key'      => 'find_order_count',
                ],
                [
                    'label'         => '下级消费金额',
                    'view_type'     => 'field',
                    'view_key'      => 'find_order_total',
                ],
                [
                    'label'         => '下级最后下单时间',
                    'view_type'     => 'field',
                    'view_key'      => 'find_order_last_time',
                ],
                [
                    'label'         => '下级用户总数',
                    'view_type'     => 'field',
                    'view_key'      => 'referrer_count',
                ],
                [
                    'label'         => '注册时间',
                    'view_type'     => 'field',
                    'view_key'      => $user_field_first.'add_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/distribution/view/admin/promotionuser/module/operate',
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