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
namespace app\plugins\ask\index;

use app\service\SeoService;
use app\service\ResourcesService;
use app\plugins\ask\index\Common;
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
     * 问答/留言列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-28
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 浏览器名称
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($this->plugins_user_center_left_name, 1));
        return MyView('../../../plugins/ask/view/index/ask/index');
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-30
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Detail($params = [])
    {
        MyViewAssign([
            'is_header' => 0,
            'is_footer' => 0,
        ]);
        return MyView('../../../plugins/ask/view/index/ask/detail');
    }

    /**
     * 新增编辑页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-30
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        MyViewAssign([
            'ask_category_list'  => AskCategoryService::AskCategoryAll(),
            'is_header'          => 0,
            'is_footer'          => 0,
            // 编辑器文件存放地址
            'editor_path_type'   => ResourcesService::EditorPathTypeValue('plugins_ask-ask-'.$this->user['id']),
        ]);
        return MyView('../../../plugins/ask/view/index/ask/saveinfo');
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