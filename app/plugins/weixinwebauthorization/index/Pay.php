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
namespace app\plugins\weixinwebauthorization\index;

use app\plugins\weixinwebauthorization\service\BaseService;
use app\plugins\weixinwebauthorization\service\AuthService;

/**
 * 微信登录 - 微信里面支付
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Pay
{
    /**
     * 支付授权
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-05-25T14:44:32+0800
     * @param    [array]      $params [输入参数]
     */
    public function Index($params = [])
    {
        // 调用授权
        $ret = AuthService::Auth($params);
        if($ret['code'] == 0)
        {
            return MyRedirect($ret['data']);
        }
        return BaseService::ViewErrorHandle($ret);
    }
}
?>