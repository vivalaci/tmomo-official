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
namespace app\plugins\sms;

use app\plugins\sms\service\BaseService;
use app\plugins\sms\service\SmsRequestService;

/**
 * 短信 - 钩子入口
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

            // 1. 是否开启短信
            // 2. 配置平台类型
            $is_sms_open = isset($this->plugins_config['is_sms_open']) && $this->plugins_config['is_sms_open'] == 1 && !empty($this->plugins_config['platform']) && !empty($this->plugins_config['system_template']) && !empty($this->plugins_config['system_template'][$this->plugins_config['platform']]);
   
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 配置信息
                case 'plugins_service_config_sms_template_value' :
                    if($is_sms_open && !empty($params['key']) && isset($this->plugins_config['system_template'][$this->plugins_config['platform']][$params['key']]))
                    {
                        $params['value'] = $this->plugins_config['system_template'][$this->plugins_config['platform']][$params['key']];
                    }
                    break;

                // 静态数据
                case 'plugins_service_const_data' :
                    if($params['key'] == 'common_sms_log_platform_list')
                    {
                        $platform = BaseService::ConstData('sms_platform_list');
                        if(!empty($platform) && is_array($platform))
                        {
                            foreach($platform as $pv)
                            {
                                $params['data'][$pv['value']] = ['value' => $pv['value'], 'name' => $pv['name']];
                            }
                        }
                    }
                    break;

                // 短信发送处理
                case 'plugins_extend_sms_send_request_handle' :
                    if($is_sms_open)
                    {
                        $ret = SmsRequestService::Run($this->plugins_config, $params);
                    }
                    break;
            }
            return $ret;
        }
    }
}
?>