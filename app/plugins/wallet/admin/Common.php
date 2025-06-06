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
namespace app\plugins\wallet\admin;

use app\service\PluginsDataConfigService;
use app\plugins\wallet\service\BaseService;

/**
 * 钱包插件 - 公共
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Common
{
    // 公共属性参数数据
    protected $props_params;

    // 插件配置信息
    protected $plugins_config;

    // 数据配置
    protected $data_config;

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
        // 公共属性参数数据
        $this->props_params = $params;

        // 视图初始化
        $this->ViewInit();
    }

    /**
     * 属性读取处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-04-23
     * @desc    description
     * @param   [string]          $name [属性名称]
     * @return  [mixed]                 [属性的数据]
     */
    public function __get($name)
    {
        return (!empty($this->props_params) && is_array($this->props_params) && isset($this->props_params[$name])) ? $this->props_params[$name] : null;
    }

    /**
     * 视图初始化
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-25
     * @desc    description
     */
    public function ViewInit()
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();
        $this->plugins_config = $base['data'];
        MyViewAssign('plugins_config', $this->plugins_config);

        // 数据配置
        $this->data_config = PluginsDataConfigService::DataConfigData('wallet');
        MyViewAssign('data_config', $this->data_config);

        // 主操作菜单
        MyViewAssign('plugins_nav_menu_list', BaseService::AdminNavMenuList($this->plugins_config));
    }
}
?>