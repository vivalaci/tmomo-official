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
 * 消息通知 - 问答通知服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-11-18
 * @desc    description
 */
class AskNoticeService
{
    /**
     * 问答添加 - 通知管理员
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]         $config   [插件配置]
     * @param   [array]         $ask      [问答数据]
     * @param   [array]         $params   [输入参数]
     */
    public static function AdminSend($config, $ask, $params = [])
    {
        if(!empty($ask))
        {
            // 是否开启短信通知
            $is_admin_new_ask_sms_notice = (isset($config['is_admin_new_ask_sms_notice']) && $config['is_admin_new_ask_sms_notice'] == 1 && !empty($config['admin_new_ask_sms_template']) && !empty($config['admin_new_ask_sms_mobile']));
            // 是否开启邮件通知
            $is_admin_new_ask_email_notice = (isset($config['is_admin_new_ask_email_notice']) && $config['is_admin_new_ask_email_notice'] == 1 && !empty($config['admin_new_ask_email_template']) && !empty($config['admin_new_ask_email_accounts']));
            // 是否开启语音通知
            $is_admin_new_ask_voice_notice = (isset($config['is_admin_new_ask_voice_notice']) && $config['is_admin_new_ask_voice_notice'] == 1 && !empty($config['admin_new_ask_voice_template']));

            if($is_admin_new_ask_sms_notice || $is_admin_new_ask_email_notice || $is_admin_new_ask_voice_notice)
            {
                // 问答数据
                $data = BaseService::AskData($ask);

                // 公共消息参数
                $verify_params = [
                    'expire_time'   => MyC('common_verify_expire_time'),
                    'interval_time' => MyC('common_verify_interval_time'),
                    'is_frq'        => 0,
                ];

                // 短信
                $sms_sign = empty($config['sms_sign']) ? MyC('common_sms_sign') : $config['sms_sign'];
                if($is_admin_new_ask_sms_notice && !empty($sms_sign))
                {
                    // 模板变量
                    $codes = [
                        'user_name_view'    => $data['user_name_view'],
                    ];
                    // 添加日志
                    $log = [];
                    foreach(explode(',', $config['admin_new_ask_sms_mobile']) as $mobile)
                    {
                        $log[] = BaseService::SmsLogInsert($mobile, $config['admin_new_ask_sms_template'], $sms_sign, $codes);
                    }

                    // 调用短信驱动发送
                    $obj = new \base\Sms($verify_params);
                    $res = $obj->SendTemplate($config['admin_new_ask_sms_mobile'], $config['admin_new_ask_sms_template'], $sms_sign, $codes);

                    // 更新日志
                    foreach($log as $lv)
                    {
                        BaseService::SmsLogResponse($lv, $res, $obj->error);
                    }
                }

                // 邮件
                if($is_admin_new_ask_email_notice)
                {
                    // 模板内容
                    $search = [];
                    $replace = [];
                    foreach(BaseService::$ask_email_var_fields as $v)
                    {
                        $search[] = '${'.$v['field'].'}';
                        $replace[] = isset($data[$v['field']]) ? $data[$v['field']] : '';
                    }
                    $content = str_replace($search, $replace, $config['admin_new_ask_email_template']);

                    // 添加日志
                    $log = [];
                    foreach(explode(',', $config['admin_new_ask_email_accounts']) as $email)
                    {
                        $log[] = BaseService::EmailLogInsert($email, $content);
                    }

                    // 调用邮件驱动发送
                    $obj = new \base\Email($verify_params);
                    $email_params = [
                        'email'     => $config['admin_new_ask_email_accounts'],
                        'content'   => $content,
                        'title'     => '新问答提醒 - '.MyC('home_site_name'),
                    ];
                    $res = $obj->SendHtml($email_params);

                    // 更新日志
                    foreach($log as $lv)
                    {
                        BaseService::EmailLogResponse($lv, $res, $obj->error);
                    }
                }

                // 语音
                if($is_admin_new_ask_voice_notice)
                {
                    // 模板内容
                    $search = [];
                    $replace = [];
                    foreach(['price', 'total_price', 'pay_price'] as $v)
                    {
                        $search[] = '${'.$v.'}';
                        $replace[] = isset($data[$v]) ? PriceBeautify($data[$v]) : '';
                    }
                    $content = str_replace($search, $replace, $config['admin_new_ask_voice_template']);

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
     * 问答回复 - 通知用户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]         $config   [插件配置]
     * @param   [array]         $ask      [问答数据]
     * @param   [array]         $params   [输入参数]
     */
    public static function ReplySUserSend($config, $ask, $params = [])
    {
        if(!empty($ask))
        {
            // 是否开启短信通知
            $is_user_ask_reply_sms_notice = (isset($config['is_user_ask_reply_sms_notice']) && $config['is_user_ask_reply_sms_notice'] == 1 && !empty($config['user_ask_reply_sms_template']));
            // 是否开启邮件通知
            $is_user_ask_reply_email_notice = (isset($config['is_user_ask_reply_email_notice']) && $config['is_user_ask_reply_email_notice'] == 1 && !empty($config['user_ask_reply_email_template']));

            if($is_user_ask_reply_sms_notice || $is_user_ask_reply_email_notice)
            {
                if((!empty($ask['mobile_notice']) || !empty($ask['email_notice'])))
                {
                    // 问答数据
                    $data = BaseService::AskData($ask);

                    // 公共消息参数
                    $verify_params = [
                        'expire_time'   => MyC('common_verify_expire_time'),
                        'interval_time' => MyC('common_verify_interval_time'),
                        'is_frq'        => 0,
                    ];

                    // 短信
                    $sms_sign = empty($config['sms_sign']) ? MyC('common_sms_sign') : $config['sms_sign'];
                    if($is_user_ask_reply_sms_notice && !empty($sms_sign) && !empty($ask['mobile_notice']))
                    {
                        // 模板变量
                        $codes = [
                            'site_name' => MyC('home_site_name'),
                        ];
                        // 添加日志
                        $log = BaseService::SmsLogInsert($ask['mobile_notice'], $config['user_ask_reply_sms_template'], $sms_sign, $codes);

                        // 调用短信驱动发送
                        $obj = new \base\Sms($verify_params);
                        $res = $obj->SendTemplate($ask['mobile_notice'], $config['user_ask_reply_sms_template'], $sms_sign, $codes);

                        // 更新日志
                        BaseService::SmsLogResponse($log, $res, $obj->error);
                    }

                    // 邮件
                    if($is_user_ask_reply_email_notice && !empty($ask['email_notice']))
                    {
                        // 模板内容
                        $search = [];
                        $replace = [];
                        foreach(BaseService::$ask_email_var_fields as $v)
                        {
                            $search[] = '${'.$v['field'].'}';
                            $replace[] = isset($data[$v['field']]) ? $data[$v['field']] : '';
                        }
                        $content = str_replace($search, $replace, $config['user_ask_reply_email_template']);

                        // 添加日志
                        $log = BaseService::EmailLogInsert($ask['email_notice'], $content);

                        // 调用邮件驱动发送
                        $obj = new \base\Email($verify_params);
                        $email_params = [
                            'email'     => $ask['email_notice'],
                            'content'   => $content,
                            'title'     => '问答回复提醒 - '.MyC('home_site_name'),
                        ];
                        $res = $obj->SendHtml($email_params);

                        // 更新日志
                        BaseService::EmailLogResponse($log, $res, $obj->error);
                    }
                }
            }
        }
    }

    /**
     * 问答评论 - 通知用户
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]         $config         [插件配置]
     * @param   [array]         $ask_comments   [问答评论数据]
     * @param   [array]         $params         [输入参数]
     */
    public static function CommentsUserSend($config, $ask_comments, $params = [])
    {
        if(!empty($ask_comments))
        {
            // 是否开启短信通知
            $is_user_ask_comments_sms_notice = (isset($config['is_user_ask_comments_sms_notice']) && $config['is_user_ask_comments_sms_notice'] == 1 && !empty($config['user_ask_comments_sms_template']));
            // 是否开启邮件通知
            $is_user_ask_comments_email_notice = (isset($config['is_user_ask_comments_email_notice']) && $config['is_user_ask_comments_email_notice'] == 1 && !empty($config['user_ask_comments_email_template']));

            if($is_user_ask_comments_sms_notice || $is_user_ask_comments_email_notice)
            {
                // 问答数据
                $data = BaseService::AskCommentsData($ask_comments);
                if(!empty($data['user']) && (!empty($data['user']['mobile']) || !empty($data['user']['email'])))
                {
                    // 公共消息参数
                    $verify_params = [
                        'expire_time'   => MyC('common_verify_expire_time'),
                        'interval_time' => MyC('common_verify_interval_time'),
                        'is_frq'        => 0,
                    ];

                    // 短信
                    $sms_sign = empty($config['sms_sign']) ? MyC('common_sms_sign') : $config['sms_sign'];
                    if($is_user_ask_comments_sms_notice && !empty($sms_sign) && !empty($data['user']['mobile']))
                    {
                        // 模板变量
                        $codes = [
                            'site_name' => MyC('home_site_name'),
                        ];
                        // 添加日志
                        $log = BaseService::SmsLogInsert($data['user']['mobile'], $config['user_ask_comments_sms_template'], $sms_sign, $codes);

                        // 调用短信驱动发送
                        $obj = new \base\Sms($verify_params);
                        $res = $obj->SendTemplate($data['user']['mobile'], $config['user_ask_comments_sms_template'], $sms_sign, $codes);

                        // 更新日志
                        BaseService::SmsLogResponse($log, $res, $obj->error);
                    }

                    // 邮件
                    if($is_user_ask_comments_email_notice && !empty($data['user']['email']))
                    {
                        // 模板内容
                        $search = [];
                        $replace = [];
                        foreach(BaseService::$ask_comments_email_var_fields as $v)
                        {
                            $search[] = '${'.$v['field'].'}';
                            $replace[] = isset($data[$v['field']]) ? $data[$v['field']] : '';
                        }
                        $content = str_replace($search, $replace, $config['user_ask_comments_email_template']);

                        // 添加日志
                        $log = BaseService::EmailLogInsert($data['user']['email'], $content);

                        // 调用邮件驱动发送
                        $obj = new \base\Email($verify_params);
                        $email_params = [
                            'email'     => $data['user']['email'],
                            'content'   => $content,
                            'title'     => '问答评论提醒 - '.MyC('home_site_name'),
                        ];
                        $res = $obj->SendHtml($email_params);

                        // 更新日志
                        BaseService::EmailLogResponse($log, $res, $obj->error);
                    }
                }
            }
        }
    }
}
?>