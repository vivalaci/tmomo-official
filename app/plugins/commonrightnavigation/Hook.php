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
namespace app\plugins\commonrightnavigation;

use app\service\PluginsService;
use app\service\GoodsCartService;
use app\plugins\chat\service\BaseService as ChatBaseService;

/**
 * 右侧快捷导航 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 配置信息
    private $plugins_config;

    // 模块、控制器、方法
    private $module_name;
    private $controller_name;
    private $action_name;
    private $mca;

    // 基础数据附件字段
    private $plugins_config_attachment_field = [
        'alipay_mini_qrcode_images',
        'alipay_fuwu_qrcode_images',
        'weixin_mini_qrcode_images',
        'weixin_fuwu_qrcode_images',
        'baidu_mini_qrcode_images',
        'qq_mini_qrcode_images',
        'toutiao_mini_qrcode_images',
        'douyin_mini_qrcode_images',
        'h5_qrcode_images',
        'app_download_images',
    ];

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-09T14:25:44+0800
     * @param    [array]                    $params [输入参数]
     */
    public function handle($params = [])
    {
        // 是否控制器钩子
        // is_backend 当前为后端业务处理
        // hook_name 钩子名称
        if(isset($params['is_backend']) && $params['is_backend'] === true && !empty($params['hook_name']))
        {
            // 参数一   描述
            // 参数二   0 为处理成功, 负数为失败
            // 参数三   返回数据
            return DataReturn('返回描述', 0);

        // 默认返回视图
        } else {
            if(!empty($params['hook_name']))
            {
                // 获取应用数据
                $base = PluginsService::PluginsData('commonrightnavigation', $this->plugins_config_attachment_field);
                $this->plugins_config = $base['data'];

                // 当前模块/控制器/方法
                $this->module_name = RequestModule();
                $this->controller_name = RequestController();
                $this->action_name = RequestAction();
                $this->mca = $this->module_name.$this->controller_name.$this->action_name;

                $ret = '';
                if($this->IsValid())
                {
                    switch($params['hook_name'])
                    {
                        case 'plugins_css' :
                            $ret = 'static/plugins/commonrightnavigation/css/index/style.css';
                            break;

                        case 'plugins_js' :
                            $ret = 'static/plugins/commonrightnavigation/js/index/style.js';
                            break;

                        case 'plugins_view_common_bottom_footer' :
                            $ret = $this->Html($params);
                            break;                            
                    }
                }
                return $ret;
            }
        }
    }

    /**
     * 视图
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-06T16:16:34+0800
     * @param    [array]          $params [输入参数]
     */
    public function Html($params = [])
    {
        // 购物车汇总
        $cart_res = GoodsCartService::UserGoodsCartTotal(['user'=>$params['user']]);

        // 是否需要登录
        $login_event_class = empty($params['user']) ? 'login-event' : '';

        // 购物车
        $cart_list = GoodsCartService::GoodsCartList(['user'=>$params['user']]);
        $base = [
            'total_price'   => empty($cart_list['data']) ? '0.00' : PriceNumberFormat(array_sum(array_column($cart_list['data'], 'total_price'))),
            'cart_count'    => empty($cart_list['data']) ? 0 : array_sum(array_column($cart_list['data'], 'stock')),
            'ids'           => empty($cart_list['data']) ? '' : implode(',', array_column($cart_list['data'], 'id')),
        ];

        // 在线客服
        $chat = (isset($this->plugins_config['is_chat']) && $this->plugins_config['is_chat'] == 1) ? ChatBaseService::ChatUrl() : null;

        return MyView('../../../plugins/commonrightnavigation/view/index/public/content', [
            'module_controller_action'  => $this->mca,
            'cart_total'                => $cart_res['buy_number'],
            'cart_list'                 => $cart_list['data'],
            'login_event_class'         => $login_event_class,
            'user'                      => $params['user'],
            'config'                    => $this->plugins_config,
            'cart_base'                 => $base,
            'is_animation'              => 1,
            'chat'                      => $chat,
        ]);
    }

    /**
     * 是否有效
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-11-29
     * @desc    description
     * @return  [boolean]         [true|false]
     */
    public function IsValid()
    {
        if(!empty($this->plugins_config))
        {
            // 非全局
            if(isset($this->plugins_config['is_overall']) && $this->plugins_config['is_overall'] != 1)
            {
                // 非首页则空
                if($this->mca != 'indexindexindex')
                {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
?>