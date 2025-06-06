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

use app\plugins\ask\service\BaseService;

/**
 * 问答增强版
 * @author   Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2018-11-30
 * @desc    description
 */
class Common
{
    // 公共属性参数数据
    protected $props_params;

    // 插件配置信息
    protected $plugins_config;

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
    }
}
?>