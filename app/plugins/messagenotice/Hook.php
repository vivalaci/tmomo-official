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
namespace app\plugins\messagenotice;

use app\plugins\messagenotice\service\BaseService;
use app\plugins\messagenotice\service\UserNoticeService;
use app\plugins\messagenotice\service\AdminNoticeService;
use app\plugins\messagenotice\service\AskNoticeService;

/**
 * 消息通知 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 配置信息
    private $base_config;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-09T14:25:44+0800
     * @param    [array]       $params [输入参数]
     */
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 配置信息
            $config = BaseService::BaseConfig();
            $this->base_config = empty($config['data']) ? [] : $config['data'];
   
            // 走钩子
            switch($params['hook_name'])
            {
                // 订单状态改变处理
                case 'plugins_service_order_status_change_history_success_handle' :
                    $this->OrderStatusChangeHandle($params);
                    break;

                // 订单添加成功
                case 'plugins_service_buy_order_insert_end' :
                    $this->OrderInsertHandle($params);
                    break;

                // 消息添加成功
                case 'plugins_service_message_add' :
                    $this->MessageInsertHandle($params);
                    break;

                // 用户添加成功
                case 'plugins_service_user_register_end' :
                    $this->UserInsertHandle($params);
                    break;

                // 问答添加成功
                case 'plugins_ask_service_ask_insert_success' :
                    $this->PluginsAskInsertSuccessHandle($params);
                    break;

                // 问答回复成功
                case 'plugins_ask_service_ask_reply_success' :
                    $this->PluginsAskReplySuccessHandle($params);
                    break;

                // 问答评论成功
                case 'plugins_ask_service_ask_comments_success' :
                    $this->PluginsAskCommentsSuccessHandle($params);
                    break;

                // 问答评论审核成功
                case 'plugins_ask_service_ask_comments_audit_success' :
                    $this->PluginsAskCommentsAuditSuccessHandle($params);
                    break;
            }
        }
    }

    /**
     * 问答评论审核成功 - 通知用户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function PluginsAskCommentsAuditSuccessHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['user_id']))
        {
            AskNoticeService::CommentsUserSend($this->base_config, $params['data']);
        }
    }

    /**
     * 问答评论成功 - 通知用户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function PluginsAskCommentsSuccessHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['user_id']) && isset($params['data']['status']) && $params['data']['status'] == 1)
        {
            AskNoticeService::CommentsUserSend($this->base_config, $params['data']);
        }
    }

    /**
     * 问答回复成功 - 通知用户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function PluginsAskReplySuccessHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['email_notice']))
        {
            AskNoticeService::ReplySUserSend($this->base_config, $params['data']);
        }
    }

    /**
     * 问答添加成功 - 通知管理员
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function PluginsAskInsertSuccessHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data']['user_id']))
        {
            AskNoticeService::AdminSend($this->base_config, $params['data']);
        }
    }

    /**
     * 用户添加成功处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function UserInsertHandle($params = [])
    {
        if(!empty($params['user']))
        {
            AdminNoticeService::UserAuditSend($this->base_config, $params['user']);
        }
    }

    /**
     * 消息添加成功处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function MessageInsertHandle($params = [])
    {
        if(!empty($params['data']) && !empty($params['data_id']))
        {
            UserNoticeService::MessageSend($this->base_config, $params['data_id'], $params['data']);
        }
    }

    /**
     * 订单添加成功处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function OrderInsertHandle($params = [])
    {
        if(!empty($params['order']) && isset($params['order']['status']) && !empty($params['order_id']))
        {
            switch($params['order']['status'])
            {
                // 待支付
                // 待发货
                case 1 :
                case 2 :
                    // 用户通知
                    UserNoticeService::OrderSend($this->base_config, $params['order_id'], $params['order']['status']);
                    break;
            }

            // 管理员通知
            AdminNoticeService::AdminNewOrderSend($this->base_config, $params['order_id']);
        }
    }

    /**
     * 订单状态改变处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-24
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function OrderStatusChangeHandle($params = [])
    {
        if(!empty($params['data']) && isset($params['data']['new_status']) && !empty($params['order_id']))
        {
            // 用户通知
            UserNoticeService::OrderSend($this->base_config, $params['order_id'], $params['data']['new_status']);

            // 管理员通知
            if($params['data']['new_status'] == 2)
            {
                AdminNoticeService::AdminNewOrderSend($this->base_config, $params['order_id']);
            }
        }
    }
}
?>