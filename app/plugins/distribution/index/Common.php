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
namespace app\plugins\distribution\index;

use app\plugins\distribution\service\BaseService;

/**
 * 分销 - 公共
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
     * @date    2020-06-02
     * @desc    description
     */
    public function ViewInit()
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();
        $this->plugins_config = $base['data'];
        MyViewAssign('plugins_config', $this->plugins_config);

        // 用户中心主导航名称
        MyViewAssign('user_center_main_title', '我的分销');

        // 用户分销等级
        if(!empty($this->user['id']))
        {
            $user_level = BaseService::UserDistributionLevel($this->user['id'], $this->plugins_config);
            if($user_level['code'] == 0)
            {
                // ajax判断是否有分销等级权限
                if(IS_AJAX && empty($user_level['data']))
                {
                    exit(json_encode(DataReturn('当前没有分销权限', -10)));
                }

                MyViewAssign('user_level', $user_level['data']);
            }
        }

        // 用户中心菜单
        $center_nav = BaseService::WebUserCenterNav($this->plugins_config);
        MyViewAssign('center_nav', $center_nav);
    }

    /**
     * 多商户登录校验
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-15
     * @desc    description
     */
    protected function IsShopLogin()
    {
        IsUserLogin(PluginsHomeUrl('shop', 'login', 'index'));
    }
}
?>