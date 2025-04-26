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
namespace app\plugins\membershiplevel\admin;

use app\service\PluginsService;
use app\plugins\membershiplevel\service\Service;

/**
 * 会员等级插件 - 管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Admin
{
    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        $ret = PluginsService::PluginsData('membershiplevel', Service::$base_config_attachment_field, false);
        if($ret['code'] == 0)
        {
            // 等级规则
            MyViewAssign('members_level_rules_list', Service::$members_level_rules_list);
            
            MyViewAssign('data', $ret['data']);
            return MyView('../../../plugins/membershiplevel/view/admin/admin/index');
        } else {
            return $ret['msg'];
        }
    }

    /**
     * 编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        $ret = PluginsService::PluginsData('membershiplevel', Service::$base_config_attachment_field, false);
        if($ret['code'] == 0)
        {
            // 等级规则
            MyViewAssign('members_level_rules_list', Service::$members_level_rules_list);

            MyViewAssign('data', $ret['data']);
            return MyView('../../../plugins/membershiplevel/view/admin/admin/saveinfo');
        } else {
            return $ret['msg'];
        }
    }

    /**
     * 数据保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        // 会员等级数据
        $level = Service::LevelDataList();
        $params['level_list'] = $level['data'];
        return PluginsService::PluginsDataSave(['plugins'=>'membershiplevel', 'data'=>$params]);
    }
}
?>