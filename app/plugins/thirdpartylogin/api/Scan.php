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
namespace app\plugins\thirdpartylogin\api;

use app\plugins\thirdpartylogin\api\Common;
use app\plugins\thirdpartylogin\service\ScanLoginService;

/**
 * 第三方登录 - 扫码登录
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class Scan extends Common
{
    /**
     * 扫码登录状态记录
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function StatusRecord($params = [])
    {
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return ScanLoginService::SetScanValue($params);
    }

    /**
     * 登录确认
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Login($params = [])
    {
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return ScanLoginService::ConfirmLogin($params);
    }
}
?>