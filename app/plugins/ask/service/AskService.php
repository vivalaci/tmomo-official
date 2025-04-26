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
use app\service\GoodsService;
use app\plugins\ask\service\BaseService;
use app\plugins\ask\service\AskCommentsService;

/**
 * 问答 - 问答服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class AskService
{
    /**
     * 获取列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-08-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AskList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : trim($params['order_by']);
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        $data = Db::name('PluginsAsk')->field($field)->where($where)->order($order_by)->limit($m, $n)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::AskListHandle($data, $params));
    }

    /**
     * 数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]          $data [数据]
     */
    public static function AskListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            // 是否读取评论
            $is_comments = isset($params['is_comments']) && $params['is_comments'] == 1;
            // 是否已开启评论展示
            if($is_comments && (empty($params['plugins_config']) || !isset($params['plugins_config']['is_ask_comments_show']) || $params['plugins_config']['is_ask_comments_show'] != 1))
            {
                $is_comments = false;
            }

            // 用户点赞
            $user_give_thumbs = empty($params['user']) ? [] : AskCommentsService::AskUserGiveThumbsData($params['user']['id'], array_column($data, 'id'));

            // 默认读取评论数量
            $default_comments_number = (empty($params['plugins_config']) || empty($params['plugins_config']['ask_detail_comments_number'])) ? 3 : intval($params['plugins_config']['ask_detail_comments_number']);
            // 是否评论列表信息查询
            if(isset($params['is_comments_list_info']) && $params['is_comments_list_info'] == 1)
            {
                $default_comments_number = (empty($params['plugins_config']) || empty($params['plugins_config']['ask_detail_comments_more_page_number'])) ? 20 : intval($params['plugins_config']['ask_detail_comments_more_page_number']);
            }

            // 分类
            $category_ids = array_unique(array_filter(array_column($data, 'category_id')));
            $category_data = empty($category_ids) ? [] : Db::name('PluginsAskCategory')->where(['id'=>$category_ids])->column('name', 'id');

            // 问答用户
            $user = UserService::GetUserViewInfo(array_column($data, 'user_id'));

            // 商品
            $goods_data = [];
            if(isset($params['is_goods']) && $params['is_goods'] == 1)
            {
                $goods_ids = array_column($data, 'goods_id');
                if(!empty($goods_ids))
                {
                    $goods = GoodsService::GoodsList([
                            'where'   => [
                                ['is_delete_time', '=', 0],
                                ['is_shelves', '=', 1],
                                ['id', 'in', $goods_ids],
                            ],
                            'm'       => 0,
                            'n'       => 0,
                            'is_spec' => 1,
                            'is_cart' => 1,
                        ]);
                    $goods_data = empty($goods['data']) ? [] : array_column($goods['data'], null, 'id');
                }
            }

            // 用户默认头像
            $default_avatar = UserDefaultAvatar();
            // 默认用户名称
            $default_username = MyLang('ask.no_username_name');

            foreach($data as &$v)
            {
                // 用户
                if(array_key_exists('user_id', $v))
                {
                    $v['user'] = empty($user[$v['user_id']]) ? null : $user[$v['user_id']];
                    if(!isset($params['is_public']) || $params['is_public'] == 1)
                    {
                        $v['user'] = [
                            'avatar'            => empty($v['user']['avatar']) ? $default_avatar : $v['user']['avatar'],
                            'user_name_view'    => empty($v['user']['user_name_view']) ? $default_username : $v['user']['user_name_view'],
                        ];
                    }
                }

                // url
                if(array_key_exists('id', $v))
                {
                    $v['url'] = (APPLICATION == 'web') ? PluginsHomeUrl('ask', 'index', 'detail', ['id'=>$v['id']]) : '/pages/plugins/ask/detail/detail?id='.$v['id'];
                }

                // 内容
                if(array_key_exists('content', $v))
                {
                    $v['content'] = ResourcesService::ContentStaticReplace($v['content'], 'get');
                    if(!empty($v['content']) && APPLICATION == 'web')
                    {
                        $v['content'] = str_replace("\n", '<br />', $v['content']);
                    }
                }

                // 标题不存在则截取前部分内容
                if(array_key_exists('title', $v) && !empty($v['content']) && empty($v['title']))
                {
                    $v['title'] = mb_substr(strip_tags($v['content']), 0, 120, 'utf-8');
                }

                // 回复内容
                if(array_key_exists('reply', $v))
                {
                    $v['reply'] = ResourcesService::ContentStaticReplace($v['reply'], 'get');
                    if(!empty($v['reply']) && APPLICATION == 'web')
                    {
                        $v['reply'] = str_replace("\n", '<br />', $v['reply']);
                    }
                }

                // 图片组
                if(array_key_exists('images', $v))
                {
                    if(!empty($v['images']))
                    {
                        $images = json_decode($v['images'], true);
                        foreach($images as &$img)
                        {
                            $img = ResourcesService::AttachmentPathViewHandle($img);
                        }
                        $v['images'] = $images;
                    }
                }

                // 分类
                if(array_key_exists('category_id', $v))
                {
                    $v['category_name'] = (empty($category_data) || !array_key_exists($v['category_id'], $category_data)) ? '' : $category_data[$v['category_id']];
                }

                // 关联商品
                if(array_key_exists('goods_id', $v))
                {
                    $v['goods'] = (!empty($v['goods_id']) && !empty($goods_data) && array_key_exists($v['goods_id'], $goods_data)) ? $goods_data[$v['goods_id']] : null;
                }

                // 评论数据
                if($is_comments)
                {
                    $res = ($v['comments_count'] > 0) ? AskCommentsService::AskCommentsList(array_merge($params, ['ask_id'=>$v['id'], 'ask_comments_id'=>0, 'n'=>$default_comments_number, 'is_comments_reply'=>1])) : [];
                    $v['comments_list'] = empty($res['data']) ? [] : $res['data'];
                }

                // 是否点赞
                $v['is_give_thumbs'] = (!empty($user_give_thumbs) && in_array($v['id'], $user_give_thumbs)) ? 1 : 0;

                // 回复时间
                if(isset($v['reply_time']))
                {
                    $v['reply_time_time'] = empty($v['reply_time']) ? '' : date('Y-m-d H:i:s', $v['reply_time']);
                    $v['reply_time_date'] = empty($v['reply_time']) ? '' : date('Y-m-d', $v['reply_time']);
                }

                // 添加时间
                if(isset($v['add_time']))
                {
                    $v['add_time_time'] = date('Y-m-d H:i:s', $v['add_time']);
                    $v['add_time_date'] = date('Y-m-d', $v['add_time']);
                }

                // 更新时间
                if(isset($v['upd_time']))
                {
                    $v['upd_time_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
                    $v['upd_time_date'] = empty($v['upd_time']) ? '' : date('Y-m-d', $v['upd_time']);
                }
            }
        }
        return $data;
    }

    /**
     * 总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @param    [array]          $where [条件]
     */
    public static function AskTotal($where)
    {
        return (int) Db::name('PluginsAsk')->where($where)->count();
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
    public static function AskSave($params = [])
    {
        // 参数校验
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '30',
                'is_checked'        => 1,
                'error_msg'         => MyLang('ask.form_item_name_message'),
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'tel',
                'is_checked'        => 1,
                'error_msg'         => MyLang('ask.form_item_tel_message'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'title',
                'checked_data'      => '2,120',
                'error_msg'         => MyLang('ask.form_item_title_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 用户类型
        $user_type = empty($params['user_type']) ? 'user' : $params['user_type'];

        // 问答用户
        $user_id = isset($params['user']['id']) ? intval($params['user']['id']) : (isset($params['user_id']) ? intval($params['user_id']) : 0);
        // 用户状态
        if(!empty($user_id))
        {
            $ret = UserService::UserStatusCheck($user_id);
            if($ret['code'] != 0)
            {
                return $ret;
            }
        }

        // 当前数据
        $info = [];
        if(!empty($params['id']))
        {
            $where = [
                ['id', '=', intval($params['id'])],
            ];
            if($user_type == 'user')
            {
                $where[] = ['user_id', '=', $user_id];
            }
            $info = Db::name('PluginsAsk')->where($where)->find();
        }

        // 编辑器内容
        $content = empty($params['content']) ? '' : ResourcesService::ContentStaticReplace(htmlspecialchars_decode($params['content']), 'add');

        // 回复内容
        $reply = empty($params['reply']) ? (empty($info['reply']) ? '' : $info['reply']) : ResourcesService::ContentStaticReplace(htmlspecialchars_decode($params['reply']), 'add');

        // 图片组
        $images = ResourcesService::RichTextMatchContentAttachment($content, '', 'images');

        // 数据
        $data = [
            'user_id'        => $user_id,
            'goods_id'       => empty($params['goods_id']) ? 0 : intval($params['goods_id']),
            'category_id'    => empty($params['category_id']) ? 0 : intval($params['category_id']),
            'name'           => isset($params['name']) ? $params['name'] : '',
            'tel'            => isset($params['tel']) ? $params['tel'] : '',
            'title'          => $params['title'],
            'content'        => $content,
            'images'         => empty($images) ? '' : json_encode($images),
            'images_count'   => count($images),
            'reply'          => $reply,
            'access_count'   => isset($params['access_count']) ? intval($params['access_count']) : 0,
            'is_reply'       => isset($params['is_reply']) ? intval($params['is_reply']) : 0,
            'is_show'        => isset($params['is_show']) ? intval($params['is_show']) : 1,
            'email_notice'   => empty($params['email_notice']) ? '' : $params['email_notice'],
            'mobile_notice'  => empty($params['mobile_notice']) ? '' : $params['mobile_notice'],
        ];

        // 用户处理
        if($user_type == 'user')
        {
            // 没有问答配置则读取
            if(empty($params['plugins_config']))
            {
                $base = BaseService::BaseConfig();
                $params['plugins_config'] = $base['data'];
            }
            // 是否已开启用户添加问答
            if(empty($params['plugins_config']) || !isset($params['plugins_config']['is_user_add_ask']) || $params['plugins_config']['is_user_add_ask'] != 1)
            {
                return DataReturn('管理员未开启问答操作权限', -1);
            }

            // 编辑则待审核
            if(isset($params['plugins_config']) && isset($params['plugins_config']['is_user_add_ask_audit']) && $params['plugins_config']['is_user_add_ask_audit'] == 1)
            {
                $data['is_show'] = 0;
            }
        }

        // 回复时间
        $data['reply_time'] = (empty($data['reply']) || empty($info)) ? 0 : (($info['reply'] != $data['reply']) ? time() : $info['reply_time']);

        // 不存在添加，则更新
        if(empty($info))
        {
            $data['add_time'] = time();
            $data['id'] = Db::name('PluginsAsk')->insertGetId($data);
            if($data['id'] > 0)
            {
                // 问答添加成功钩子
                $hook_name = 'plugins_ask_service_ask_insert_success';
                MyEventTrigger($hook_name, [
                    'hook_name'   => $hook_name,
                    'is_backend'  => true,
                    'data'        => $data,
                ]);

                return DataReturn(MyLang('submit_success'), 0);
            }
            return DataReturn(MyLang('submit_fail'), -100);
        } else {
            $data['upd_time'] = time();
            if(Db::name('PluginsAsk')->where(['id'=>$info['id']])->update($data))
            {
                return DataReturn(MyLang('edit_success'), 0);
            }
            return DataReturn(MyLang('edit_fail'), -100); 
        }
    }

    /**
     * 访问统计加1
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-10-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AskAccessCountInc($params = [])
    {
        if(!empty($params['id']))
        {
            return Db::name('PluginsAsk')->where(['id'=>intval($params['id'])])->inc('access_count')->update();
        }
        return false;
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
    public static function AskDelete($params = [])
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
        // 操作条件
        $where = [
            ['id', 'in', $params['ids']],
        ];
        if(empty($params['user_type']) || $params['user_type'] != 'admin')
        {
            if(empty($params['user']))
            {
                $where[] = ['user_id', '<', 0];
            } else {
                $where[] = ['user_id', '=', $params['user']['id']];
            }
        }
        $ids = Db::name('PluginsAsk')->where($where)->column('id');
        if(empty($ids))
        {
            return DataReturn('没有相关问答', -1);
        }

        // 删除操作
        if(Db::name('PluginsAsk')->where(['id'=>$ids])->delete())
        {
            return DataReturn(MyLang('delete_success'), 0);
        }
        return DataReturn(MyLang('delete_fail'), -100);
    }

    /**
     * 回复
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AskReply($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 条件
        $where = [
            'id'    => intval($params['id']),
        ];

        // 问答是否存在
        $info = Db::name('PluginsAsk')->where($where)->find();
        if(empty($info))
        {
            return DataReturn(MyLang('data_no_exist_or_delete_error_tips'), -2);
        }

        // 回复内容
        $reply = empty($params['reply']) ? '' : ResourcesService::ContentStaticReplace(htmlspecialchars_decode($params['reply']), 'add');

        // 更新问答
        $data = [
            'reply'         => $reply,
            'category_id'   => empty($params['category_id']) ? 0 : intval($params['category_id']),
            'email_notice'  => empty($params['email_notice']) ? '' : $params['email_notice'],
            'mobile_notice' => empty($params['mobile_notice']) ? '' : $params['mobile_notice'],
            'is_reply'      => 1,
            'is_show'       => (isset($params['is_show']) && $params['is_show'] == 1) ? 1 : 0,
            'reply_time'    => time(),
            'upd_time'      => time()
        ];
        if(Db::name('PluginsAsk')->where($where)->update($data))
        {
            // 问答回复成功钩子
            $hook_name = 'plugins_ask_service_ask_reply_success';
            MyEventTrigger($hook_name, [
                'hook_name'   => $hook_name,
                'is_backend'  => true,
                'data'        => array_merge($info, $data),
            ]);

            return DataReturn(MyLang('operate_success'), 0);
        }
        return DataReturn(MyLang('operate_fail'), -100);
    }

    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AskStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => MyLang('operate_field_error_tips'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => MyLang('form_status_range_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据更新
        if(Db::name('PluginsAsk')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
           return DataReturn(MyLang('edit_success'), 0);
        }
        return DataReturn(MyLang('edit_fail'), -100);
    }

    /**
     * 指定读取问答列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]         $params   [输入参数]
     */
    public static function AppointAskList($params = [])
    {
        $result = [];
        if(!empty($params['ask_ids']))
        {
            // 非数组则转为数组
            if(!is_array($params['ask_ids']))
            {
                $params['ask_ids'] = explode(',', $params['ask_ids']);
            }

            // 基础条件
            $where = [
                ['is_show', '=', 1],
                ['id', 'in', array_unique($params['ask_ids'])]
            ];

            // 获取数据
            $ret = self::AskList(['where'=>$where, 'm'=>0, 'n'=>0]);
            if(!empty($ret['data']))
            {
                $temp = array_column($ret['data'], null, 'id');
                foreach($params['ask_ids'] as $id)
                {
                    if(!empty($id) && array_key_exists($id, $temp))
                    {
                        $result[] = $temp[$id];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 自动读取问答列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]         $params [输入参数]
     */
    public static function AutoAskList($params = [])
    {
        // 基础条件
        $where = [
            ['is_show', '=', 1],
        ];

        // 关键字
        if(!empty($params['ask_keywords']))
        {
            $where[] = ['name|content', 'like', '%'.$params['ask_keywords'].'%'];
        }

        // 分类条件
        if(!empty($params['ask_category_ids']))
        {
            if(!is_array($params['ask_category_ids']))
            {
                $params['ask_category_ids'] = explode(',', $params['ask_category_ids']);
            }
            $where[] = ['category_id', 'in', $params['ask_category_ids']];
        }

        // 是否回复
        if(isset($params['ask_is_reply']) && $params['ask_is_reply'] == 1)
        {
            $where[] = ['is_reply', '=', 1];
        }

        // 排序
        $order_by_type_list = BaseService::ConstData('ask_order_by_type_list');
        $order_by_rule_list = MyConst('common_data_order_by_rule_list');
        $order_by_type = !isset($params['ask_order_by_type']) || !array_key_exists($params['ask_order_by_type'], $order_by_type_list) ? $order_by_type_list[0]['value'] : $order_by_type_list[$params['ask_order_by_type']]['value'];
        $order_by_rule = !isset($params['ask_order_by_rule']) || !array_key_exists($params['ask_order_by_rule'], $order_by_rule_list) ? $order_by_rule_list[0]['value'] : $order_by_rule_list[$params['ask_order_by_rule']]['value'];
        $order_by = $order_by_type.' '.$order_by_rule;

        // 获取数据
        $ret = self::AskList([
            'where'     => $where,
            'm'         => 0,
            'n'         => empty($params['ask_number']) ? 10 : intval($params['ask_number']),
            'order_by'  => $order_by,
        ]);
        return empty($ret['data']) ? [] : $ret['data'];
    }
}
?>