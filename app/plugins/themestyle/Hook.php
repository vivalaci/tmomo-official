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
namespace app\plugins\themestyle;

use app\plugins\themestyle\service\BaseService;
use app\plugins\themestyle\service\ThemeStyleConfigService;

/**
 * 默认主题样式 - 钩子入口
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class Hook
{
    // 配置信息
    private $plugins_config;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date     2021-05-07
     * @param    [array]        $params [输入参数]
     */
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];
   
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 主题样式
                case 'plugins_service_system_view_theme_style_data' :
                    if(isset($this->plugins_config['is_web_open']) && $this->plugins_config['is_web_open'] == 1)
                    {
                        $this->ViewThemeStyleHandle($params);
                    }
                    break;

                // api公共参数
                case 'plugins_service_base_common' :
                    if(isset($this->plugins_config['is_app_open']) && $this->plugins_config['is_app_open'] == 1)
                    {
                        $params['data']['plugins_themestyle_data'] = empty($this->plugins_config['app_theme']) ? 'yellow' : $this->plugins_config['app_theme'];
                    }
                    break;
            }
            return $ret;
        }
    }

    /**
     * 主题样式
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function ViewThemeStyleHandle($params = [])
    {
        $data_params = [
            'where' => [
                ['is_enable', '=', 1],
                ['is_default', '=', 1],
            ],
        ];
        $ret = ThemeStyleConfigService::ConfigList($data_params);
        if(!empty($ret['data']) && !empty($ret['data'][0]) && !empty($ret['data'][0]['config']))
        {
            $config = $ret['data'][0]['config'];
            foreach($config as $k=>$v)
            {
                $params['data'][$k] = $v;
            }
        }
    }
}
?>