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
namespace app\plugins\messagenotice\service;

use app\plugins\messagenotice\service\BaseService;

/**
 * 消息通知 - 管理员通知服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-11-18
 * @desc    description
 */
class AdminNoticeService
{
    /**
     * 管理员新订单提醒
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]         $config     [插件配置]
     * @param   [int]           $order_id   [订单id]
     * @param   [array]         $params     [输入参数]
     */
    public static function AdminNewOrderSend($config, $order_id, $params = [])
    {
        // 是否开启短信通知
        $is_admin_new_order_sms_notice = (isset($config['is_admin_new_order_sms_notice']) && $config['is_admin_new_order_sms_notice'] == 1 && !empty($config['admin_new_order_sms_template']) && !empty($config['admin_new_order_sms_mobile']));
        // 是否开启邮件通知
        $is_admin_new_order_email_notice = (isset($config['is_admin_new_order_email_notice']) && $config['is_admin_new_order_email_notice'] == 1 && !empty($config['admin_new_order_email_template']) && !empty($config['admin_new_order_email_accounts']));
        // 是否开启语音通知
        $is_admin_new_order_voice_notice = (isset($config['is_admin_new_order_voice_notice']) && $config['is_admin_new_order_voice_notice'] == 1 && !empty($config['admin_new_order_voice_template']));

        if($is_admin_new_order_sms_notice || $is_admin_new_order_email_notice || $is_admin_new_order_voice_notice)
        {
            // 订单信息
            $order = BaseService::OrderData($order_id);
            // 1. 订单预约模式
            //      1.1 订单状态为（待确认）
            // 2. 订单非预约模式
            //      2.1 非线下支付   并且    对的状态为（支付、发货、收货）
            //      2.2 线下支付    并且    订单为（待支付）    并且    线下支付正常进行（否）
            //      2.3 线下支付    并且    订单状态为（支付、发货、收货）    并且    线下支付正常进行（是）
            $is_booking = MyC('common_order_is_booking');
            $is_under_line_order_normal = MyC('common_is_under_line_order_normal');
            if(!empty($order) && (
                ($is_booking == 1 && $order['status'] == 0) || (
                    $is_booking != 1 && (
                        ($order['is_under_line'] == 0 && !in_array($order['status'], [0,1,5,6])) || 
                        ($order['is_under_line'] == 1 && $order['status'] == 1 && $is_under_line_order_normal != 1) || 
                        ($order['is_under_line'] == 1 && !in_array($order['status'], [0,1,5,6]) && $is_under_line_order_normal == 1)
                    )
                )
            )) {
                // 公共消息参数
                $verify_params = [
                    'expire_time'   => MyC('common_verify_expire_time'),
                    'interval_time' => MyC('common_verify_interval_time'),
                    'is_frq'        => 0,
                ];

                // 短信
                $sms_sign = empty($config['sms_sign']) ? MyC('common_sms_sign') : $config['sms_sign'];
                if($is_admin_new_order_sms_notice && !empty($sms_sign))
                {
                    // 模板变量
                    $codes = [
                        'order_no'      => $order['order_no'],
                        'total_price'   => $order['total_price'],
                    ];
                    // 添加日志
                    $log = [];
                    foreach(explode(',', $config['admin_new_order_sms_mobile']) as $mobile)
                    {
                        $log[] = BaseService::SmsLogInsert($mobile, $config['admin_new_order_sms_template'], $sms_sign, $codes);
                    }

                    // 调用短信驱动发送
                    $obj = new \base\Sms($verify_params);
                    $res = $obj->SendTemplate($config['admin_new_order_sms_mobile'], $config['admin_new_order_sms_template'], $sms_sign, $codes);

                    // 更新日志
                    foreach($log as $lv)
                    {
                        BaseService::SmsLogResponse($lv, $res, $obj->error);
                    }
                }

                // 邮件
                if($is_admin_new_order_email_notice)
                {
                    // 模板内容
                    $search = [];
                    $replace = [];
                    foreach(BaseService::$order_email_var_fields as $v)
                    {
                        $search[] = '${'.$v['field'].'}';
                        $replace[] = isset($order[$v['field']]) ? $order[$v['field']] : '';
                    }
                    $content = str_replace($search, $replace, $config['admin_new_order_email_template']);

                    // 添加日志
                    $log = [];
                    foreach(explode(',', $config['admin_new_order_email_accounts']) as $email)
                    {
                        $log[] = BaseService::EmailLogInsert($email, $content);
                    }

                    // 调用邮件驱动发送
                    $obj = new \base\Email($verify_params);
                    $email_params = [
                        'email'     => $config['admin_new_order_email_accounts'],
                        'content'   => $content,
                        'title'     => '新订单提醒 - '.MyC('home_site_name'),
                    ];
                    $res = $obj->SendHtml($email_params);

                    // 更新日志
                    foreach($log as $lv)
                    {
                        BaseService::EmailLogResponse($lv, $res, $obj->error);
                    }
                }

                // 语音
                if($is_admin_new_order_voice_notice)
                {
                    // 模板内容
                    $search = [];
                    $replace = [];
                    foreach(['price', 'total_price', 'pay_price'] as $v)
                    {
                        $search[] = '${'.$v.'}';
                        $replace[] = isset($order[$v]) ? PriceBeautify($order[$v]) : '';
                    }
                    $content = str_replace($search, $replace, $config['admin_new_order_voice_template']);

                    // 添加日志
                    $log = BaseService::VoiceLogInsert(array_merge($params, ['config'=>$config, 'content'=>$content, 'type'=>0]));

                    // 调用语音发送
                    $res = BaseService::VoiceSend(array_merge($params, ['config'=>$config, 'content'=>$content]));

                    // 更新日志
                    BaseService::VoiceLogResponse(array_merge($params, ['log'=>$log, 'status'=>$res['status'], 'error'=>$res['msg']]));
                }
            }
        }
    }

    /**
     * 用户注册审核通知
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-06-11
     * @desc    description
     * @param   [array]        $config  [插件配置]
     * @param   [array]        $user    [用户信息]
     * @param   [array]        $params  [输入参数]
     */
    public static function UserAuditSend($config, $user, $params = [])
    {
        if(isset($user['status']) && $user['status'] == 3)
        {
            // 是否开启短信通知
            $is_admin_new_user_audit_sms_notice = (isset($config['is_admin_new_user_audit_sms_notice']) && $config['is_admin_new_user_audit_sms_notice'] == 1 && !empty($config['admin_new_user_audit_sms_template']) && !empty($config['admin_new_user_audit_sms_mobile']));
            // 是否邮件短信通知
            $is_admin_new_user_audit_email_notice = (isset($config['is_admin_new_user_audit_email_notice']) && $config['is_admin_new_user_audit_email_notice'] == 1 && !empty($config['admin_new_user_audit_email_template']) && !empty($config['admin_new_user_audit_email_accounts']));
            // 是否开启语音通知
            $is_admin_new_user_audit_voice_notice = (isset($config['is_admin_new_user_audit_voice_notice']) && $config['is_admin_new_user_audit_voice_notice'] == 1 && !empty($config['admin_new_user_audit_voice_template']));

            if($is_admin_new_user_audit_sms_notice || $is_admin_new_user_audit_email_notice || $is_admin_new_user_audit_voice_notice)
            {
                // 公共消息参数
                $verify_params = [
                    'expire_time'   => MyC('common_verify_expire_time'),
                    'interval_time' => MyC('common_verify_interval_time'),
                    'is_frq'        => 0,
                ];

                // 短信
                $sms_sign = empty($config['sms_sign']) ? MyC('common_sms_sign') : $config['sms_sign'];
                if($is_admin_new_user_audit_sms_notice && !empty($sms_sign))
                {
                    // 模板变量
                    $codes = [
                        'user_name_view'    => $user['user_name_view'],
                    ];
                    // 添加日志
                    $log = [];
                    foreach(explode(',', $config['admin_new_user_audit_sms_mobile']) as $mobile)
                    {
                        $log[] = BaseService::SmsLogInsert($mobile, $config['admin_new_user_audit_sms_template'], $sms_sign, $codes);
                    }

                    // 调用短信驱动发送
                    $obj = new \base\Sms($verify_params);
                    $res = $obj->SendTemplate($config['admin_new_user_audit_sms_mobile'], $config['admin_new_user_audit_sms_template'], $sms_sign, $codes);

                    // 更新日志
                    foreach($log as $lv)
                    {
                        BaseService::SmsLogResponse($lv, $res, $obj->error);
                    }
                }

                // 邮件
                if($is_admin_new_user_audit_email_notice)
                {
                    // 模板内容
                    $search = [];
                    $replace = [];
                    foreach(BaseService::$user_audit_email_var_fields as $v)
                    {
                        $search[] = '${'.$v['field'].'}';
                        $replace[] = isset($user[$v['field']]) ? $user[$v['field']] : '';
                    }
                    $content = str_replace($search, $replace, $config['admin_new_user_audit_email_template']);

                    // 添加日志
                    $log = [];
                    foreach(explode(',', $config['admin_new_user_audit_email_accounts']) as $email)
                    {
                        $log[] = BaseService::EmailLogInsert($email, $content);
                    }

                    // 调用邮件驱动发送
                    $obj = new \base\Email($verify_params);
                    $email_params = [
                        'email'     => $config['admin_new_user_audit_email_accounts'],
                        'content'   => $content,
                        'title'     => '新用户审核提醒 - '.MyC('home_site_name'),
                    ];
                    $res = $obj->SendHtml($email_params);

                    // 更新日志
                    foreach($log as $lv)
                    {
                        BaseService::EmailLogResponse($lv, $res, $obj->error);
                    }
                }

                // 语音
                if($is_admin_new_user_audit_voice_notice)
                {
                    // 模板内容
                    $content = $config['admin_new_user_audit_voice_template'];

                    // 添加日志
                    $log = BaseService::VoiceLogInsert(array_merge($params, ['config'=>$config, 'content'=>$content, 'type'=>1]));

                    // 调用语音发送
                    $res = BaseService::VoiceSend(array_merge($params, ['config'=>$config, 'content'=>$content]));

                    // 更新日志
                    BaseService::VoiceLogResponse(array_merge($params, ['log'=>$log, 'status'=>$res['status'], 'error'=>$res['msg']]));
                }
            }
        }
    }
}
?>