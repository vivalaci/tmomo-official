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
namespace app\plugins\messagenotice\admin;

use app\plugins\messagenotice\admin\Common;
use app\plugins\messagenotice\service\BaseService;

/**
 * 消息通知 - 管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-11-18
 * @desc    description
 */
class Admin extends Common
{
    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        MyViewAssign('data', $this->base_config);
        return MyView('../../../plugins/messagenotice/view/admin/admin/index');
    }

   /**
     * 编辑页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        MyViewAssign([
            'data'                           => $this->base_config,
            'order_email_var_fields'         => BaseService::$order_email_var_fields,
            'user_audit_email_var_fields'    => BaseService::$user_audit_email_var_fields,
            'ask_email_var_fields'           => BaseService::$ask_email_var_fields,
            'ask_comments_email_var_fields'  => BaseService::$ask_comments_email_var_fields,
        ]);
        return MyView('../../../plugins/messagenotice/view/admin/admin/saveinfo');
    }

    /**
     * 数据保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        return BaseService::BaseConfigSave($params);
    }
}
?>