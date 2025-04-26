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
namespace app\plugins\themestyle\admin;

use app\service\SystemService;
use app\plugins\themestyle\admin\Common;
use app\plugins\themestyle\service\BaseService;
use app\plugins\themestyle\service\ThemeStyleConfigService;

/**
 * 默认主题样式 - 数据配置管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class Config extends Common
{
    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        return MyView('../../../plugins/themestyle/view/admin/config/index');
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        return MyView('../../../plugins/themestyle/view/admin/config/detail');
    }

    /**
     * 添加编辑页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        // 主要颜色列表
        MyViewAssign('main_color_list', BaseService::MainColorList());

        // 没有数据则取默认
        $data = $this->data_detail;
        if(empty($data['config']))
        {
            $data['config'] = SystemService::ThemeStyleDefaultData();
        }
        MyViewAssign('data', $data);

        // 参数去除自增id
        unset($params['id']);
        MyViewAssign('params', $params);
        return MyView('../../../plugins/themestyle/view/admin/config/saveinfo');
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-11-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Save($params = [])
    {
        return ThemeStyleConfigService::ConfigSave($params);
    }

    /**
     * 状态更新
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-11-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function StatusUpdate($params = [])
    {
        return ThemeStyleConfigService::ConfigStatusUpdate($params);
    }

    /**
     * 删除
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-11-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Delete($params = [])
    {
        return ThemeStyleConfigService::ConfigDelete($params);
    }
}
?>