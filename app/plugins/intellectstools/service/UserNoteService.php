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
namespace app\plugins\intellectstools\service;

use think\facade\Db;

/**
 * 智能工具箱 - 用户备注服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class UserNoteService
{
    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UserNoteSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('user_id_error_tips'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 是否自定义条件
        $where = empty($params['where']) ? [] : $params['where'];

        // 条件
        $where = array_merge($where, [
            ['is_delete_time', '=', 0],
            ['id', '=', intval($params['id'])],
        ]);
        $user = Db::name('User')->where($where)->field('id')->find();
        if(empty($user))
        {
            return DataReturn(MyLang('user_info_incorrect_tips'), -1);
        }

        // 数据
        $data = [
            'user_id'  => intval($params['id']),
            'content'  => empty($params['content']) ? '' : $params['content'],
        ];

        // 获取数据
        $info = Db::name('PluginsIntellectstoolsUserNote')->where(['user_id'=>$data['user_id']])->find();
        if(empty($info))
        {
            $data['add_time'] = time();
            if(Db::name('PluginsIntellectstoolsUserNote')->insertGetId($data) <= 0)
            {
                return DataReturn(MyLang('operate_fail'), -1);
            }
        } else {
            $data['upd_time'] = time();
            if(Db::name('PluginsIntellectstoolsUserNote')->where(['user_id'=>$data['user_id']])->update($data) === false)
            {
                return DataReturn(MyLang('operate_fail'), -1);
            }
        }
        return DataReturn(MyLang('operate_success'), 0);
    }

    /**
     * 用户备注信息列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-06-05
     * @desc    description
     * @param   [array]          $user_ids [用户id]
     */
    public static function UserNoteDataList($user_ids)
    {
        $data = Db::name('PluginsIntellectstoolsUserNote')->where(['user_id'=>$user_ids])->column('*', 'user_id');
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                if(array_key_exists('content', $v))
                {
                    $v['content'] = empty($v['content']) ? '' : explode("\n", $v['content']);
                }
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
}
?>