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
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\LevelService;
use app\plugins\distribution\service\BusinessService;

/**
 * 分销商动态表格
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

    // 推荐id
    public $referrer_id;

    // 额外条件
    public $ext_where = [];

    // 分销等级
    public $level_data = [];

    // 一级是否仅展示手动数据
    public $is_admin_team_appoint_model;

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
        // 邀请用户id
        $this->referrer_id = empty($params['referrer_id']) ? 0 : intval($params['referrer_id']);
        if(!empty($this->referrer_id))
        {
            // 邀请用户id
            $this->condition_base[] = ['referrer', '=', $this->referrer_id];

            // 额外条件
            if(!empty($this->referrer_id))
            {
                $this->ext_where['referrer_id'] = $this->referrer_id;
            }
        }

        // 插件配置
        $base = BaseService::BaseConfig();
        $config = $base['data'];

        // 一级是否仅展示手动数据
        $this->is_admin_team_appoint_model = isset($config['is_admin_team_appoint_model']) && $config['is_admin_team_appoint_model'] == 1;
        if($this->is_admin_team_appoint_model && empty($this->referrer_id))
        {
            // 仅读取设定的分销用户
            $this->condition_base[] = ['plugins_distribution_level', '>', 0];
        }

        // 额外条件
        $order_where = [];
        $ext_where = BusinessService::UserTeamExtWhere($params);
        if(!empty($ext_where))
        {
            $this->condition_base = array_merge($this->condition_base, $ext_where);
        }

        // 分销等级
        $level = LevelService::DataList(['where'=>['is_enable'=>1]]);
        $this->level_data = array_column($level['data'], 'name', 'id');
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
                'is_middle'     => 0,
                'search_url'    => PluginsAdminUrl('distribution', 'team', 'index', $this->ext_where),
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
                    'label'         => '分销等级(手动)',
                    'view_type'     => 'field',
                    'view_key'      => 'plugins_distribution_level',
                    'view_data'     => $this->level_data,
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'where_type'        => 'in',
                        'data'              => $this->level_data,
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '分销等级(自动)',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/distribution/view/admin/public/user_level_view',
                    'view_data'     => $this->level_data,
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'plugins_distribution_auto_level',
                        'where_type'        => 'in',
                        'data'              => $this->level_data,
                        'is_multiple'       => 1,
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
                    'label'         => '下级订单总数',
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
                    'label'         => '分享码',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/distribution/view/admin/team/module/qrcode',
                    'grid_size'     => 'lg',
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
                    'view_key'      => '../../../plugins/distribution/view/admin/team/module/operate',
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