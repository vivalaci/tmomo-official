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
namespace app\plugins\membershiplevelvip\index;

use app\plugins\membershiplevelvip\index\Common;
use app\plugins\membershiplevelvip\service\LevelBuyService;
use app\plugins\membershiplevelvip\service\PayService;

/**
 * 会员等级增强版插件 - 会员购买
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Buy extends Common
{
    /**
     * 构造方法
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-07-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // 是否已经登录
        if(!isset($this->plugins_action_name) || $this->plugins_action_name != 'respond')
        {
            IsUserLogin();
        }
    }

    /**
     * 会员购买订单创建
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-18
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Create($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 用户
        $params['user'] = $this->user;
        return LevelBuyService::BuyOrderCreate($params);
    }

    /**
     * 会员续费
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-18
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Renew($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 用户
        $params['user'] = $this->user;
        return LevelBuyService::BuyOrderRenew($params);
    }

    /**
     * 支付
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-03-15T23:51:50+0800
     * @param   [array]          $params [输入参数]
     */
    public function Pay($params = [])
    {
        $params['user'] = $this->user;
        $ret = PayService::Pay($params);
        if($ret['code'] == 0)
        {
            return MyRedirect($ret['data']['data']);
        }
        return MyView('public/tips_error', ['msg'=>$ret['msg']]);
    }

    /**
     * 支付状态校验
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-03-15T23:51:50+0800
     * @param   [array]          $params [输入参数]
     */
    public function PayCheck($params = [])
    {
        if(input('post.'))
        {
            $params['user'] = $this->user;
            return PayService::LevelPayCheck($params);
        }
        return MyView('public/tips_error', ['msg'=>MyLang('illegal_access_tips')]);
    }

    /**
     * 支付同步页面
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Respond($params = [])
    {
        // 是否自定义状态
        if(isset($params['appoint_status']))
        {
            $ret = ($params['appoint_status'] == 0) ? DataReturn(MyLang('pay_success'), 0) : DataReturn(MyLang('pay_fail'), -100);
        } else {
            // 获取支付回调数据
            $params['user'] = $this->user;
            $ret = PayService::Respond($params);
        }

        // 自定义链接
        MyViewAssign([
            'to_url'    => PluginsHomeUrl('membershiplevelvip', 'order', 'index'),
            'to_title'  => '开通订单',
            'msg'       => $ret['msg'],
        ]);

        // 状态
        if($ret['code'] == 0)
        {
            return MyView('public/tips_success');
        }
        return MyView('public/tips_error');
    }
}
?>