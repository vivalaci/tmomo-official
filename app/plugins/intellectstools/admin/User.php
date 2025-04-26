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
namespace app\plugins\intellectstools\admin;

use app\service\UserService;
use app\plugins\intellectstools\admin\Common;
use app\plugins\intellectstools\service\UserNoteService;

/**
 * 智能工具箱 - 订单
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class User extends Common
{
    /**
     * 登录
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Login($params = [])
    {
        if(empty($params['uid']))
        {
            MyViewAssign('msg', '用户id有误');
            return MyView('public/tips_error');
        }

        // 用户基础信息
        $user = UserService::UserBaseInfo('id', intval($params['uid']));
        if(empty($user))
        {
            MyViewAssign('msg', '用户不存在');
            return MyView('public/tips_error');
        }

        // 用户平台信息、不存在则添加
        $user_platform = UserService::UserPlatformInfo('user_id', $user['id']);
        if(empty($user_platform))
        {
            if(!UserService::UserPlatformInsert(['user_id' => $user['id']], $params))
            {
                MyViewAssign('msg', '用户平台信息添加失败');
                return MyView('public/tips_error');
            }
        }

        // 用户登录处理
        $ret = UserService::UserLoginHandle($user['id']);
        if($ret['code'] != 0)
        {
            MyViewAssign('msg', $ret['msg']);
            return MyView('public/tips_error');
        }

        $ret['data']['msg'] = '登入成功';
        return MyView('../../../plugins/intellectstools/view/admin/user/login', $ret['data']);
    }

    /**
     * 用户备注页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function NoteInfo($params = [])
    {
        $user_data = [];
        if(!empty($params['id']))
        {
            // 用户基础信息
            $user_data = UserService::UserBaseInfo('id', intval($params['id']));
            if(!empty($user_data))
            {
                // 用户信息处理
                $user_data = UserService::UserHandle($user_data);

                // 备注信息
                $note_data = UserNoteService::UserNoteDataList($user_data['id']);
                MyViewAssign('note_data', empty($note_data[$user_data['id']]) ? '' : $note_data[$user_data['id']]);
            }
        }
        MyViewAssign('user_data', $user_data);
        MyViewAssign('module_data', ['user'=>$user_data]);
        return MyView('../../../plugins/intellectstools/view/admin/user/noteinfo');
    }

    /**
     * 用户备注保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function NoteSave($params = [])
    {
        return UserNoteService::UserNoteSave($params);
    }
}
?>