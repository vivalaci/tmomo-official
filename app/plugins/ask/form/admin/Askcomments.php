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
namespace app\plugins\ask\form\admin;

use think\facade\Db;
use app\plugins\ask\service\BaseService;

/**
 * 问答评论动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-18
 * @desc    description
 */
class AskComments
{
    // 基础条件
    public $condition_base = [];

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
                    'label'         => '用户信息',
                    'view_type'     => 'module',
                    'view_key'      => 'lib/module/user',
                    'width'         => 200,
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => 'user_id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'WhereValueUserInfo',
                        'placeholder'           => '请输入用户名/昵称/手机/邮箱',
                    ],
                ],
                [
                    'label'         => '问答信息',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/ask/view/admin/askcomments/module/ask',
                    'grid_size'     => 'lg',
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => 'ask_id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'WhereBaseAskInfo',
                        'placeholder'           => '请输入问答名称',
                    ],
                ],
                [
                    'label'             => '状态',
                    'view_type'         => 'field',
                    'view_key'          => 'status_name',
                    'is_first_tips'     => 1,
                    'first_tips_key'    => 'reason',
                    'first_tips_style'  => 'danger',
                    'is_round_point'    => 1,
                    'round_point_key'   => 'status',
                    'round_point_style' => [1=>'success', 2=>'danger'],
                    'align'             => 'center',
                    'is_sort'           => 1,
                    'search_config'     => [
                        'form_type'         => 'select',
                        'form_name'         => 'status',
                        'where_type'        => 'in',
                        'data'              => BaseService::ConstData('ask_comments_status_list'),
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '评论内容',
                    'view_type'     => 'field',
                    'view_key'      => 'content',
                    'text_truncate' => 2,
                    'is_popover'    => 1,
                    'grid_size'     => 'sm',
                    'search_config' => [
                        'form_type'         => 'input',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '上级评论',
                    'view_type'     => 'field',
                    'view_key'      => 'reply_comments_text',
                    'text_truncate' => 2,
                    'is_popover'    => 1,
                    'grid_size'     => 'sm',
                    'search_config' => [
                        'form_type'             => 'input',
                        'form_name'             => 'reply_comments_id',
                        'where_type_custom'     => 'in',
                        'where_value_custom'    => 'WhereBaseAskReplyCommentsInfo',
                        'placeholder'           => '请输入评论内容',
                    ],
                ],
                [
                    'label'         => '评论总数',
                    'view_type'     => 'field',
                    'view_key'      => 'comments_count',
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],
                [
                    'label'         => '点赞总数',
                    'view_type'     => 'field',
                    'view_key'      => 'give_thumbs_count',
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],
                [
                    'label'         => '访问次数',
                    'view_type'     => 'field',
                    'view_key'      => 'access_count',
                    'search_config' => [
                        'form_type'         => 'section',
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
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/ask/view/admin/askcomments/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
            // 数据配置
            'data'  => [
                'table_name'            => 'PluginsAskComments',
                'data_handle'           => 'AskCommentsService::AskCommentsListHandle',
                'is_fixed_name_field'   => 1,
                'fixed_name_data'       => [
                    'status'            => [
                        'data'  => BaseService::ConstData('ask_comments_status_list'),
                    ],
                ],
                'data_params'           => [
                    'is_ask_info'  => 1,
                ],
            ],
        ];
    }

    /**
     * 上级评论基础条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-08
     * @desc    description
     * @param   [string]          $value    [条件值]
     * @param   [array]           $params   [输入参数]
     */
    public function WhereBaseAskReplyCommentsInfo($value, $params = [])
    {
        if(!empty($value))
        {
            // 问答搜索
            $ids = Db::name('PluginsAskComments')->where('content', 'like', '%'.$value.'%')->column('id');

            // 避免空条件造成无效的错觉
            return empty($ids) ? [0] : $ids;
        }
        return $value;
    }

    /**
     * 问答基础条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-08
     * @desc    description
     * @param   [string]          $value    [条件值]
     * @param   [array]           $params   [输入参数]
     */
    public function WhereBaseAskInfo($value, $params = [])
    {
        if(!empty($value))
        {
            // 问答搜索
            $ids = Db::name('PluginsAsk')->where('title', 'like', '%'.$value.'%')->column('id');

            // 避免空条件造成无效的错觉
            return empty($ids) ? [0] : $ids;
        }
        return $value;
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