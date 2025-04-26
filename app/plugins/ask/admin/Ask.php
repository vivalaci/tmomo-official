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
namespace app\plugins\ask\admin;

use app\service\ResourcesService;
use app\plugins\ask\admin\Common;
use app\plugins\ask\service\AskService;
use app\plugins\ask\service\AskCategoryService;

/**
 * 问答管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Ask extends Common
{
    /**
     * 列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        return MyView('../../../plugins/ask/view/admin/ask/index');
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-30
     * @desc    description
     * @param    [array]          $params [输入参数]
     */
    public function Detail($params = [])
    {
        return MyView('../../../plugins/ask/view/admin/ask/detail');
    }

    /**
     * 添加/编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-14T21:37:02+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        $data = $this->data_detail;
        $assign = [
            'data'                 => $data,
            // 静态数据
            'common_is_show_list'  => MyConst('common_is_show_list'),
            'common_is_text_list'  => MyConst('common_is_text_list'),
            // 问答分类
            'ask_category_list'    => AskCategoryService::AskCategoryAll(),
            // 编辑器文件存放地址
            'editor_path_type'     => ResourcesService::EditorPathTypeValue('plugins_ask'.((empty($data) || empty($data['user_id'])) ? '' : '-ask-'.$data['user_id'])),
        ];

        // 参数
        unset($params['id']);
        $assign['params'] = $params;

        // 数据赋值
        MyViewAssign($assign);
        return MyView('../../../plugins/ask/view/admin/ask/saveinfo');
    }

    /**
     * 回复页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-14T21:37:02+0800
     * @param    [array]          $params [输入参数]
     */
    public function ReplyInfo($params = [])
    {
        $data = $this->data_detail;
        MyViewAssign([
            // 数据
            'data'               => $data,
            // 问答分类
            'ask_category_list'  => AskCategoryService::AskCategoryAll(),
            // 编辑器文件存放地址
            'editor_path_type'   => ResourcesService::EditorPathTypeValue('plugins_ask'.((empty($data) || empty($data['user_id'])) ? '' : '-ask-'.$data['user_id'])),
        ]);
        return MyView('../../../plugins/ask/view/admin/ask/replyinfo');
    }

    /**
     * 保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-25T22:36:12+0800
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        $params['user_type'] = 'admin';
        $params['plugins_config'] = $this->plugins_config;
        return AskService::AskSave($params);
    }

    /**
     * 问答删除
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     * @param    [array]          $params [输入参数]
     */
    public function Delete($params = [])
    {
        $params['user_type'] = 'admin';
        return AskService::AskDelete($params);
    }

    /**
     * 问答回复处理
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-03-28T15:07:17+0800
     * @param    [array]          $params [输入参数]
     */
    public function Reply($params = [])
    {
        return AskService::AskReply($params);
    }

    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-01-12T22:23:06+0800
     * @param    [array]          $params [输入参数]
     */
    public function StatusUpdate($params = [])
    {
        return AskService::AskStatusUpdate($params);
    }
}
?>