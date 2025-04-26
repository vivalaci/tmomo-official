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

use app\plugins\messagenotice\service\BaseService;

/**
 * 消息通知 - 公共
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2019-08-12
 * @desc    description
 */
class Common
{
    // 公共属性参数数据
    protected $props_params;

    // 插件配置信息
    protected $base_config;

    /**
     * 构造方法
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     */
    public function __construct($params = [])
    {
        // 公共属性参数数据
        $this->props_params = $params;

        // 插件配置信息
        $base = BaseService::BaseConfig();
        $this->base_config = $base['data'];
        MyViewAssign('base_config', $this->base_config);

        // 主操作菜单
        MyViewAssign('plugins_nav_menu_list', BaseService::AdminNavMenuList($this->base_config));
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
}
?>