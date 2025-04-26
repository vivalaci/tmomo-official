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
namespace app\plugins\thirdpartylogin\index;

use app\service\SeoService;
use app\plugins\thirdpartylogin\index\Common;
use app\plugins\thirdpartylogin\service\BaseService;
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

        // 关闭顶部底部内容
        MyViewAssign('is_header', 0);
        MyViewAssign('is_footer', 0);

    }

    /**
     * 扫码登录
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 基础参数
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;

        // 扫码状态
        $scan = ScanLoginService::ScanStatusData($params);
        if($scan['code'] != 0)
        {
            return MyView('public/tips_error', ['msg'=>$scan['msg'], 'is_to_home'=>0]);
        }

        // 未登录则进入登录流程
        if(empty($this->user['id']))
        {
            // 如果是需要绑定账号则不进入登录流程
            if(empty($scan['data']) || !isset($scan['data']['status']) || $scan['data']['status'] != 3)
            {
                // 扫码开始
                $params['status'] = 0;
                ScanLoginService::SetScanValue($params);

                // 设置当前跳转回来地址
                BaseService::SetRedirectUrlValue(__MY_VIEW_URL__);

                // 设置来源标识数据
                BaseService::SetRequestValue(array_merge($params, ['request_type'=>'scan']));

                // 进入登录流程
                return MyRedirect(ScanLoginService::LoginUrl());
            }
        } else {
            // 没有扫码数据则证明用户手机端已经是登录状态，或者已完成了登录流程
            if(empty($scan['data']) || (isset($scan['data']['status']) && $scan['data']['status'] == 0))
            {
                $params['status'] = 1;
                $scan = ScanLoginService::SetScanValue($params);
            }
        }
        MyViewAssign('scan_data', $scan['data']);

        // 浏览器名称
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('授权登录', 2));
        return MyView('../../../plugins/thirdpartylogin/view/index/scan/index');
    }

    /**
     * 登录状态
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Check($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return ScanLoginService::ScanCheck($params);
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
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return ScanLoginService::ConfirmLogin($params);
    }
}
?>