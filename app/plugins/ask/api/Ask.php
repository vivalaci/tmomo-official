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
namespace app\plugins\ask\api;

use app\service\ResourcesService;
use app\plugins\ask\api\Common;
use app\plugins\ask\service\AskService;
use app\plugins\ask\service\AskCategoryService;

/**
 * 问答/留言管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Ask extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // 是否已经登录
        IsUserLogin();
    }

    /**
     * 我的留言列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-07-17
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        return DataReturn('success', 0, FormModuleData($params));
    }

    /**
     * 详情
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-07
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Detail($params = [])
    {
        $result = FormModuleData($params);
        if(empty($result) || empty($result['data']))
        {
            return DataReturn(MyLang('no_data'), -1);
        }
        return DataReturn('success', 0, $result);
    }

    /**
     * 添加/编辑页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        $result = FormModuleData($params);
        return DataReturn('success', 0, [
            // 数据
            'data'                   => (empty($result) || empty($result['data'])) ? null : $result['data'],
            // 问答分类
            'ask_category_list'      => AskCategoryService::AskCategoryAll(),
            // 附件上传标识
            'editor_path_type'       => ResourcesService::EditorPathTypeValue('plugins_ask-ask-'.$this->user['id']),
            // 是否展示邮箱通知填写
            'is_show_email_notice'   => isset($this->plugins_config['is_show_email_notice']) ? intval($this->plugins_config['is_show_email_notice']) : 0,
            // 是否展示手机通知填写
            'is_show_mobile_notice'  => isset($this->plugins_config['is_show_mobile_notice']) ? intval($this->plugins_config['is_show_mobile_notice']) : 0,
        ]);
    }

    /**
     * 问答/提问保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-30
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        $params['user'] = $this->user;
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
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return AskService::AskDelete($params);
    }
}
?>