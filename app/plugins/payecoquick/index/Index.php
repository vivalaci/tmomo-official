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
namespace app\plugins\payecoquick\index;

use app\plugins\payecoquick\index\Common;
use app\plugins\payecoquick\service\PayHandleService;

/**
 * 易联快捷支付 - 首页
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-11-18
 * @desc    description
 */
class Index extends Common
{
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
        parent::__construct($params);

        // 是否已经登录
        IsUserLogin();
    }

    /**
     * 支付页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        $data = MyCache('plugins_payecoquick_pay_key_'.$this->user['id']);
        if(empty($data))
        {
            MyViewAssign('msg', '支付参数为空或已过期、请重新发起支付');
            return MyView('public/tips_error');
        }

        MyViewAssign('data', $data);
        return MyView('../../../plugins/payecoquick/view/index/index/index');
    }

    /**
     * 获取验证码
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Verify($params = [])
    {
        return PayHandleService::Verify($params);
    }

    /**
     * 支付
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Pay($params = [])
    {
        return PayHandleService::Pay($params);
    }
}
?>