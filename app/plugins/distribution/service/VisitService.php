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
namespace app\plugins\distribution\service;

use think\facade\Db;
use app\service\ResourcesService;
use app\service\AttachmentService;
use app\service\UserService;

/**
 * 分销 - 客户拜访服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class VisitService
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
    public static function VisitList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : trim($params['order_by']);
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        $data = Db::name('PluginsDistributionVisit')->field($field)->where($where)->order($order_by)->limit($m, $n)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::VisitListHandle($data, $params));
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
    public static function VisitListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            // 团队用户和客户用户
            $user_ids = array_unique(array_merge(array_column($data, 'team_user_id'), array_column($data, 'custom_user_id')));
            $user = empty($user_ids) ? [] : UserService::GetUserViewInfo($user_ids);
            foreach($data as &$v)
            {
                // 用户
                if(array_key_exists('team_user_id', $v))
                {
                    $v['team_user'] = empty($user[$v['team_user_id']]) ? null : $user[$v['team_user_id']];
                }
                if(array_key_exists('custom_user_id', $v))
                {
                    $v['custom_user'] = empty($user[$v['custom_user_id']]) ? null : $user[$v['custom_user_id']];
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

                // 时间
                if(array_key_exists('add_time', $v))
                {
                    $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                if(array_key_exists('upd_time', $v))
                {
                    $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
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
    public static function VisitTotal($where)
    {
        return (int) Db::name('PluginsDistributionVisit')->where($where)->count();
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
    public static function VisitSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'custom_user_id',
                'error_msg'         => '请选择客户',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'content',
                'checked_data'      => '1,230',
                'error_msg'         => '拜访内容格式1~230个字符',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'images',
                'error_msg'         => '请上传拜访图片',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 是否已开启拜访
        if(!isset($params['plugins_config']['is_enable_visit']) || $params['plugins_config']['is_enable_visit'] != 1)
        {
            return DataReturn('管理员未开启拜访功能', -1);
        }

        // 附件处理
        if(!empty($params['images']))
        {
            if(!is_array($params['images']))
            {
                $params['images'] = json_decode(htmlspecialchars_decode($params['images']), true);
            }
            foreach($params['images'] as &$iv)
            {
                $iv = ResourcesService::AttachmentPathHandle($iv);
            }
            if(count($params['images']) > 30)
            {
                return DataReturn('图片最多上传30张', -1);
            }
        }

        // 图片组
        $data = [
            'team_user_id'    => $params['user']['id'],
            'custom_user_id'  => intval($params['custom_user_id']),
            'content'         => str_replace(['"', "'", '&quot', '&lt;', '&gt;'], '', $params['content']),
            'images'          => empty($params['images']) ? '' : json_encode($params['images']),
        ];

        // 是否编辑的数据
        $info = empty($params['id']) ? null : Db::name('PluginsDistributionVisit')->where(['id'=>intval($params['id']), 'team_user_id'=>$params['user']['id']])->find();

        // 捕获异常
        try {
            if(empty($info))
            {
                $data['add_time'] = time();
                if(Db::name('PluginsDistributionVisit')->insertGetId($data) <= 0)
                {
                    throw new \Exception(MyLang('insert_fail'));
                }
            } else {
                $data['upd_time'] = time();
                if(!Db::name('PluginsDistributionVisit')->where(['id'=>$info['id']])->update($data))
                {
                    throw new \Exception(MyLang('edit_fail'));
                }
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
    public static function VisitDelete($params = [])
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
                $where[] = ['team_user_id', '<', 0];
            } else {
                $where[] = ['team_user_id', '=', $params['user']['id']];
            }
        }

        // 获取附件数据
        $images = Db::name('PluginsDistributionVisit')->where($where)->column('images');

        // 删除操作
        if(Db::name('PluginsDistributionVisit')->where($where)->delete() !== false)
        {
            // 删除附件
            if(!empty($images))
            {
                $delete_images = [];
                foreach($images as $iv)
                {
                    if(!empty($iv))
                    {
                        $iv = json_decode($iv, true);
                        if(!empty($iv))
                        {
                            $delete_images = array_merge($delete_images, $iv);
                        }
                    }
                }
                if(!empty($delete_images))
                {
                    AttachmentService::AttachmentUrlDelete($delete_images);
                }
            }
            return DataReturn(MyLang('delete_success'), 0);
        }
        return DataReturn(MyLang('delete_fail'), -100);
    }
}
?>