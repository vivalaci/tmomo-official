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
namespace app\plugins\sms\admin;

use app\plugins\sms\admin\Common;
use app\plugins\sms\service\BaseService;

/**
 * 短信 - 管理
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
        MyViewAssign('sms_platform_list', array_column(BaseService::ConstData('sms_platform_list'), 'name', 'value'));
        MyViewAssign('data', $this->plugins_config);
        return MyView('../../../plugins/sms/view/admin/admin/index');
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
        MyViewAssign('sms_platform_list', BaseService::ConstData('sms_platform_list'));
        MyViewAssign('sms_template_list', BaseService::ConstData('sms_template_list'));
        MyViewAssign('data', $this->plugins_config);
        return MyView('../../../plugins/sms/view/admin/admin/saveinfo');
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