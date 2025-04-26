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
namespace app\plugins\themeswitch\admin;

/**
 * 主题切换 - 公共
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-03-06
 * @desc    description
 */
class Common
{
    // 公共属性参数数据
    protected $props_params;

    /**
     * 构造方法
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-03-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function __construct($params = [])
    {
        // 公共属性参数数据
        $this->props_params = $params;
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