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
namespace app\plugins\distribution\form\index;

use think\facade\Db;
use app\service\UserService;
use app\plugins\distribution\service\BusinessService;

/**
 * 我的团队动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-28
 * @desc    description
 */
class Team
{
    // 基础条件
    public $condition_base = [];

    // 当前用户id
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
        // 用户信息
        $user = UserService::LoginUserInfo();
        $this->user_id = empty($user['id']) ? 0 : $user['id'];
        $this->condition_base[] = ['referrer', '=', $this->user_id];
        $this->condition_base[] = ['is_delete_time', '=', 0];

        // 额外条件
        $order_where = [];
        $ext_where = BusinessService::UserTeamExtWhere($params);
        if(!empty($ext_where))
        {
            $this->condition_base = array_merge($this->condition_base, $ext_where);
        }
    }

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
        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'is_search'     => 1,
                'search_url'    => PluginsHomeUrl('distribution', 'team', 'index'),
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
                        'form_name'             => 'id',
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
                    'view_key'      => 'add_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/distribution/view/index/team/module/operate',
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