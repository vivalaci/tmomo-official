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
namespace app\plugins\ask\service;

use think\facade\Db;
use app\service\ResourcesService;
use app\service\UserService;
use app\plugins\ask\service\AskIntegralService;

/**
 * 问答 - 问答评论服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class AskCommentsService
{
    /**
     * 问答评论列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-19
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AskCommentsList($params = [])
    {
        $where = [
            ['status', '=', 1],
        ];
        // 问答id
        if(!empty($params['ask_id']))
        {
            $where[] = ['ask_id', '=', intval($params['ask_id'])];
        }
        // 评论id
        if(isset($params['ask_comments_id']))
        {
            $where[] = ['ask_comments_id', '=', intval($params['ask_comments_id'])];
        }

        // 获取总数和列表
        $total = Db::name('PluginsAskComments')->where($where)->count();
        $data = [];
        if($total > 0)
        {
            // 获取评论数据
            $m = empty($params['m']) ? 0 : intval($params['m']);
            $n = empty($params['n']) ? 0 : intval($params['n']);
            $order_by = empty($params['order_by']) ? 'comments_count desc, give_thumbs_count desc, id desc' : $params['order_by'];
            $data = self::AskCommentsListHandle(Db::name('PluginsAskComments')->where($where)->order($order_by)->limit($m, $n)->select()->toArray(), $params);
        }
        return [
            'total' => $total,
            'data'  => $data,
        ];
    }

    /**
     * 评论列表处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-20
     * @desc    description
     * @param   [array]          $data      [评论数据]
     * @param   [array]          $params    [输入参数]
     */
    public static function AskCommentsListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            // 问答
            $ask = [];
            if(isset($params['is_ask_info']) && $params['is_ask_info'] == 1)
            {
                $res = AskService::AskList([
                    'where' => [
                        ['id', 'in', array_column($data, 'ask_id')],
                    ],
                    'field' => 'id,title,content,is_show',
                ]);
                $ask = empty($res['data']) ? [] : array_column($res['data'], null, 'id');
            }

            // 是否读取评论回复
            $is_comments_reply = isset($params['is_comments_reply']) && $params['is_comments_reply'] == 1;

            // 回复评论数据
            $reply_comments = [];
            $reply_comments_ids = array_unique(array_filter(array_column($data, 'reply_comments_id')));
            if(!empty($reply_comments_ids))
            {
                $reply_comments = Db::name('PluginsAskComments')->where(['id'=>$reply_comments_ids, 'status'=>1])->column('id,user_id,content', 'id');
            }

            // 用户点赞
            $user_give_thumbs = empty($params['user']) ? [] : AskCommentsService::AskUserGiveThumbsData($params['user']['id'], array_column($data, 'id'), 'ask_comments_id');

            // 默认读取评论回复数量
            $default_comments_reply_number = (empty($params['plugins_config']) || empty($params['plugins_config']['ask_detail_comments_reply_number'])) ? 3 : intval($params['plugins_config']['ask_detail_comments_reply_number']);

            // 评论用户
            $user = UserService::GetUserViewInfo(array_merge(array_column($data, 'user_id'), array_column($reply_comments, 'user_id')));
            foreach($data as &$v)
            {
                // 问答信息
                if(isset($params['is_ask_info']) && $params['is_ask_info'] == 1)
                {
                    $v['ask_info'] = (empty($ask) || !array_key_exists($v['ask_id'], $ask)) ? [] : $ask[$v['ask_id']];
                }

                // 回复评论
                $v['reply_comments_text'] = '';
                if(!empty($v['reply_comments_id']) && array_key_exists($v['reply_comments_id'], $reply_comments) && !empty($user) && array_key_exists($reply_comments[$v['reply_comments_id']]['user_id'], $user))
                {
                    $temp_reply = $reply_comments[$v['reply_comments_id']];
                    $temp_reply_user = $user[$temp_reply['user_id']];

                    $v['reply_comments_text'] = '“@'.$temp_reply_user['user_name_view'].' '.$temp_reply['content'];
                }

                // 回复数据
                if($is_comments_reply)
                {
                    $res = ($v['comments_count'] > 0) ? self::AskCommentsList(array_merge($params, ['ask_id'=>$v['ask_id'], 'ask_comments_id'=>$v['id'], 'n'=>$default_comments_reply_number, 'is_comments_reply'=>0])) : [];
                    $v['comments_list'] = empty($res['data']) ? [] : $res['data'];
                }

                // 用户
                $v['user'] = empty($user[$v['user_id']]) ? null : $user[$v['user_id']];
                // 是否点赞
                $v['is_give_thumbs'] = (!empty($user_give_thumbs) && in_array($v['id'], $user_give_thumbs)) ? 1 : 0;
                // 时间
                $v['add_time'] = empty($v['add_time']) ? '' : date('m-d H:i', $v['add_time']);
                $v['upd_time'] = empty($v['upd_time']) ? '' : date('m-d H:i', $v['upd_time']);
            }
        }
        return $data;
    }

    /**
     * 用户点赞数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-19
     * @desc    description
     * @param   [int]          $user_id     [用户id]
     * @param   [array]        $data_ids    [问答id]
     * @param   [string]       $data_field  [数据字段]
     */
    public static function AskUserGiveThumbsData($user_id, $data_ids, $data_field = 'ask_id')
    {
        $where = [
            ['user_id', '=', $user_id],
            [$data_field, 'in', $data_ids],
        ];
        if($data_field == 'ask_id')
        {
            $where[] = ['ask_comments_id', '=', 0];
        }
        return Db::name('PluginsAskGiveThumbs')->where($where)->column($data_field);
    }

    /**
     * 问答点赞
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-19
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function AskGiveThumbs($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'plugins_config',
                'error_msg'         => MyLang('plugins_config_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'ask_id',
                'error_msg'         => '问答id有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 是否已开启点赞
        if(!isset($params['plugins_config']['is_ask_give_thumbs']) || $params['plugins_config']['is_ask_give_thumbs'] != 1)
        {
            return DataReturn('未开启点赞', -1);
        }

        // 捕获异常
        Db::startTrans();
        try {
            // 问答或者评论
            $is_active = 0;
            $ask_id = intval($params['ask_id']);
            $ask_comments_id = 0;
            if(empty($params['ask_comments_id']))
            {
                $table = 'PluginsAsk';
                $where = [
                    ['id', '=', $ask_id],
                ];
            } else {
                $ask_comments_id = intval($params['ask_comments_id']);
                $table = 'PluginsAskComments';
                $where = [
                    ['ask_id', '=', $ask_id],
                    ['id', '=', $ask_comments_id],
                ];
            }

            // 是否存在点赞数据
            $temp = Db::name('PluginsAskGiveThumbs')->where([
                ['user_id', '=', $params['user']['id']],
                ['ask_id', '=', $ask_id],
                ['ask_comments_id', '=', $ask_comments_id],
            ])->value('id');
            if(empty($temp))
            {
                // 添加点赞数据
                $data = [
                    'user_id'           => $params['user']['id'],
                    'ask_id'           => $ask_id,
                    'ask_comments_id'  => $ask_comments_id,
                    'add_time'          => time(),
                ];
                if(Db::name('PluginsAskGiveThumbs')->insertGetId($data) <= 0)
                {
                    throw new \Exception('点赞失败');
                }
                $is_active = 1;

                // 扣减点赞数据
                if(!Db::name($table)->where($where)->inc('give_thumbs_count', 1)->update())
                {
                    throw new \Exception('点赞更新失败');
                }

                // 点赞奖励积分
                $ret = AskIntegralService::AskThumbsGiveIntegral($ask_id, $ask_comments_id, $data['user_id'], $params['plugins_config']);
                if($ret['code'] != 0)
                {
                    throw new \Exception($ret['msg']);
                }
            } else {
                // 删除点赞数据
                if(Db::name('PluginsAskGiveThumbs')->where(['id'=>$temp])->delete() === false)
                {
                    throw new \Exception('取消失败');
                }
                $is_active = 0;

                // 扣减点赞数据
                if(Db::name($table)->where($where)->value('give_thumbs_count') > 0)
                {
                    if(!Db::name($table)->where($where)->dec('give_thumbs_count', 1)->update())
                    {
                        throw new \Exception('点赞更新失败');
                    }
                }
            }
            Db::commit();

            // 当前数据总数
            $count = Db::name($table)->where($where)->value('give_thumbs_count');
            return DataReturn(MyLang('operate_success'), 0, [
                'count'     => $count,
                'is_active' => $is_active,
            ]);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 问答评论
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-19
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function AskComments($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'plugins_config',
                'error_msg'         => MyLang('plugins_config_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'ask_id',
                'error_msg'         => '问答id有误',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'content',
                'error_msg'         => '请填写评论内容',
            ],
            [
                'checked_type'      => 'length',
                'checked_data'      => 500,
                'key_name'          => 'content',
                'error_msg'         => '评论内容最多500个字符',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 是否已开启点赞
        if(!isset($params['plugins_config']['is_ask_comments_add']) || $params['plugins_config']['is_ask_comments_add'] != 1)
        {
            return DataReturn('未开启评论', -1);
        }

        // 是否为空
        if($params['content'] == '')
        {
            return DataReturn('请填写评论内容', -1);
        }

        // 状态、是否需要审核
        $status = (isset($params['plugins_config']['is_ask_comments_add_audit']) && $params['plugins_config']['is_ask_comments_add_audit'] == 1) ? 0 : 1;

        // 捕获异常
        Db::startTrans();
        try {
            // 基础参数
            $md5_key = md5($params['content']);
            $ask_id = intval($params['ask_id']);
            $ask_comments_id = empty($params['ask_comments_id']) ? 0 : intval($params['ask_comments_id']);
            $reply_comments_id = empty($params['reply_comments_id']) ? 0 : intval($params['reply_comments_id']);
            // 是否存在重复评论
            $where = [
                ['user_id', '=', $params['user']['id']],
                ['ask_id', '=', $ask_id],
                ['ask_comments_id', '=', $ask_comments_id],
                ['md5_key', '=', $md5_key],
            ];
            if(Db::name('PluginsAskComments')->where($where)->count() > 0)
            {
                return DataReturn('不能重复评论', -1);
            }

            // 评论数据添加
            $data = [
                'user_id'            => $params['user']['id'],
                'ask_id'             => $ask_id,
                'ask_comments_id'    => $ask_comments_id,
                'reply_comments_id'  => $reply_comments_id,
                'status'             => $status,
                'md5_key'            => $md5_key,
                'content'            => $params['content'],
                'comments_count'     => 0,
                'give_thumbs_count'  => 0,
                'add_time'           => time(),
            ];
            $data['id'] = Db::name('PluginsAskComments')->insertGetId($data);
            if($data['id'] <= 0)
            {
                throw new \Exception('评论增加失败');
            }

            // 评论数增加
            if(empty($ask_comments_id))
            {
                $table = 'PluginsAsk';
                $where = ['id'=>$ask_id];
            } else {
                $table = 'PluginsAskComments';
                $where = ['ask_id'=>$ask_id, 'id'=>$ask_comments_id];
            }
            if(!Db::name($table)->where($where)->inc('comments_count', 1)->update())
            {
                throw new \Exception('评论数更新失败');
            }
            // 回复评论数量增加
            if(!empty($reply_comments_id))
            {
                if(!Db::name('PluginsAskComments')->where(['ask_id'=>$ask_id, 'id'=>$reply_comments_id])->inc('comments_count', 1)->update())
                {
                    throw new \Exception('回复评论数更新失败');
                }
            }

            // 评论奖励积分
            $ret = AskIntegralService::AskCommentsGiveIntegral($ask_id, $data['id'], $data['user_id'], $params['plugins_config']);
            if($ret['code'] != 0)
            {
                throw new \Exception($ret['msg']);
            }

            // 问答评论成功钩子
            $hook_name = 'plugins_ask_service_ask_comments_success';
            MyEventTrigger($hook_name, [
                'hook_name'   => $hook_name,
                'is_backend'  => true,
                'data'        => $data,
            ]);

            Db::commit();
            $res = self::AskCommentsListHandle([$data], $params);
            return DataReturn(MyLang('operate_success'), 0, $res[0]);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 评论回复列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-20
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function AskCommentsReplyList($params = [])
    {
        // 返回格式
        $result = [
            'page_total'    => 0,
            'total'         => 0,
            'data'          => [],
        ];

        // 分页
        $result['page'] = max(1, isset($params['page']) ? intval($params['page']) : 1);
        $result['page_size'] = (empty($params['plugins_config']) || empty($params['plugins_config']['ask_detail_comments_more_page_number'])) ? 20 : intval($params['plugins_config']['ask_detail_comments_more_page_number']);
        $result['page_start'] = intval(($result['page']-1)*$result['page_size']);
        
        // 搜索条件
        $where = [
            ['status', '=', 1],
        ];
        // 问答id
        if(!empty($params['ask_id']))
        {
            $where[] = ['ask_id', '=', intval($params['ask_id'])];
        }
        // 问答评论id
        if(!empty($params['ask_comments_id']))
        {
            $where[] = ['ask_comments_id', '=', intval($params['ask_comments_id'])];
        }

        // 排序
        if(!empty($params['order_by_field']) && !empty($params['order_by_type']) && $params['order_by_field'] != 'default')
        {
            $order_by = $params['order_by_field'].' '.$params['order_by_type'];
        } else {
            $order_by = 'id desc';
        }

        // 获取列表
        $params['m'] = $result['page_start'];
        $params['n'] = $result['page_size'];
        $params['order_by'] = $order_by;
        $res = self::AskCommentsList($params);

        // 返回数据
        $result['total'] = $res['total'];
        $result['data'] = $res['data'];
        if($res['total'] > 0)
        {
            $result['page_total'] = ceil($result['total']/$result['page_size']);
        }
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AskCommentsSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'plugins_config',
                'error_msg'         => MyLang('plugins_config_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'content',
                'error_msg'         => '请填写评论内容',
            ],
            [
                'checked_type'      => 'length',
                'checked_data'      => 500,
                'key_name'          => 'content',
                'error_msg'         => '评论内容最多500个字符',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'status',
                'checked_data'      => array_column(BaseService::ConstData('ask_comments_status_list'), 'value'),
                'error_msg'         => '状态值范围不正确',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        if($params['content'] == '')
        {
            return DataReturn('请填写评论内容', -1);
        }

        // 获取数据
        $info = Db::name('PluginsAskComments')->where(['id'=>intval($params['id'])])->find();
        if(empty($info))
        {
            return DataReturn('没有相关评论数据', -1);
        }

        // 捕获异常
        try {
            // 数据
            $data = [
                'status'    => intval($params['status']),
                'content'   => $params['content'],
            ];
            if($data['status'] == 1)
            {
                $data['upd_time'] = time();
            }
            if(Db::name('PluginsAskComments')->where(['id'=>$info['id']])->update($data) === false)
            {
                throw new \Exception(MyLang('operate_fail'));
            }

            // 是否审核模式下、原始状态未审核，新的状态已审核，原始更新时间为空则认为是第一次审核
            if($info['status'] == 0 && $data['status'] == 1 && empty($info['upd_time']))
            {
                // 问答评论审核成功钩子
                $hook_name = 'plugins_ask_service_ask_comments_audit_success';
                MyEventTrigger($hook_name, [
                    'hook_name'   => $hook_name,
                    'is_backend'  => true,
                    'data'        => array_merge($info, $data),
                ]);
            }

            return DataReturn(MyLang('operate_success'), 0);
        } catch(\Exception $e) {
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AskCommentsDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn(MyLang('data_id_error_tips'), -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 获取数据
        $where = [
            ['id', 'in', $params['ids']],
        ];
        $data = Db::name('PluginsAskComments')->where($where)->select()->toArray();
        if(empty($data))
        {
            return DataReturn(MyLang('no_data'), -1);
        }

        // 捕获异常
        Db::startTrans();
        try {
            // 循环扣减数量
            foreach($data as $v)
            {
                $data_id = $v['ask_id'];
                $table_name = 'PluginsAsk';
                if(!empty($v['reply_comments_id']))
                {
                    $data_id = $v['reply_comments_id'];
                    $table_name = 'PluginsAskComments';
                } else {
                    if(!empty($v['ask_comments_id']))
                    {
                        $data_id = $v['ask_comments_id'];
                        $table_name = 'PluginsAskComments';
                    }
                }
                if(Db::name($table_name)->where(['id'=>$data_id])->dec('comments_count', 1)->update() === false)
                {
                    throw new \Exception('评论数量扣减失败');
                }
            }

            // 删除操作
            if(!Db::name('PluginsAskComments')->where(['id'=>array_column($data, 'id')])->delete())
            {
                throw new \Exception(MyLang('delete_fail'));
            }

            Db::commit();
            return DataReturn(MyLang('delete_success'), 0);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }
}
?>