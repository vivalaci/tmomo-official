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

use think\facade\Db;
use app\plugins\messagenotice\service\BaseService;

/**
 * 消息通知 - 用户通知服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-11-18
 * @desc    description
 */
class UserNoticeService
{
    /**
     * 用户订单消息通知
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]         $config     [插件配置]
     * @param   [int]           $order_id   [订单id]
     * @param   [int]           $new_status [订单最新状态]
     */
    public static function OrderSend($config, $order_id, $new_status)
    {
        // 是否开启短信通知
        $is_order_sms_notice = (isset($config['is_order_sms_notice']) && $config['is_order_sms_notice'] == 1);
        // 是否邮件短信通知
        $is_order_email_notice = (isset($config['is_order_email_notice']) && $config['is_order_email_notice'] == 1);

        if($is_order_sms_notice || $is_order_email_notice)
        {
            // 状态对应模板
            $arr = [
                // 待支付
                1 => [
                    'sms'   => [
                        'template'  => 'order_await_pay_sms_template',
                        'codes'     => ['order_no', 'total_price'],
                    ],
                    'email' => 'order_await_pay_email_template',
                ],
                // 订单收货完成
                4 => [
                    'sms'   => [
                        'template'  => 'order_collect_sms_template',
                        'codes'     => ['order_no'],
                    ],
                    'email' => 'order_collect_email_template',
                ],
                // 订单取消
                5 => [
                    'sms'   => [
                        'template'  => 'order_close_sms_template',
                        'codes'     => ['type_msg', 'order_no'],
                    ],
                    'email' => 'order_close_email_template',
                ],
                // 订单关闭
                6 => [
                    'sms'   => [
                        'template'  => 'order_close_sms_template',
                        'codes'     => ['type_msg', 'order_no'],
                    ],
                    'email' => 'order_close_email_template',
                ],
            ];
            // 订单类型单独模板
            $arr_model = [
                // 订单支付成功
                2 => [
                    // 快递订单
                    0 => [
                        'sms'   => [
                            'template'  => 'order_pay_success_sms_template',
                            'codes'     => ['order_no', 'total_price'],
                        ],
                        'email' => 'order_pay_success_email_template',
                    ],
                    // 自提订单
                    2 => [
                        'sms'   => [
                            'template'  => 'order_pay_success_extraction_sms_template',
                            'codes'     => ['order_no', 'total_price', 'order_code', 'address_name', 'address_tel', 'address_content'],
                        ],
                        'email' => 'order_pay_success_extraction_email_template',
                    ],
                    // 虚拟订单
                    3 => [
                        'sms'   => [
                            'template'  => 'order_pay_success_fictitious_sms_template',
                            'codes'     => ['order_no', 'total_price'],
                        ],
                        'email' => 'order_pay_success_fictitious_email_template',
                    ],
                ],
                // 订单发货
                3 => [
                    // 快递订单
                    0 => [
                        'sms'   => [
                            'template'  => 'order_delivery_sms_template',
                            'codes'     => ['order_no', 'express_name', 'express_number'],
                        ],
                        'email' => 'order_delivery_email_template',
                    ],
                    // 自提订单
                    2 => [
                        'sms'   => [
                            'template'  => 'order_delivery_extraction_sms_template',
                            'codes'     => ['order_no'],
                        ],
                        'email' => 'order_delivery_extraction_email_template',
                    ],
                ],
            ];
            // 订单信息
            $order = BaseService::OrderData($order_id, $new_status);
            if(!empty($order))
            {
                // 匹配模板
                $template = [];
                if(array_key_exists($new_status, $arr) && !empty($arr[$new_status]))
                {
                    $template = $arr[$new_status];
                } else {
                    // 订单类型模板
                    if(isset($order['order_model']) && !empty($arr_model[$new_status]) && !empty($arr_model[$new_status][$order['order_model']]))
                    {
                        $template = $arr_model[$new_status][$order['order_model']];
                    }
                }
                if(!empty($template))
                {
                    // 公共消息参数
                    $verify_params = [
                        'expire_time'   => MyC('common_verify_expire_time'),
                        'interval_time' => MyC('common_verify_interval_time'),
                        'is_frq'        => 0,
                    ];

                    // 短信
                    $sms_sign = empty($config['sms_sign']) ? MyC('common_sms_sign') : $config['sms_sign'];
                    if(!empty($sms_sign) && !empty($template['sms']) && !empty($config[$template['sms']['template']]))
                    {
                        // 订单手机号码
                        $mobile = '';
                        if($order['order_model'] == 0 && !empty($order['address_data']) && !empty($order['address_data']['tel']) && CheckMobile($order['address_data']['tel']))
                        {
                            $mobile = $order['address_data']['tel'];
                        }
                        // 无订单手机则取用户的手机
                        if(empty($mobile) && !empty($order['user']) && !empty($order['user']['mobile']))
                        {
                            $mobile = $order['user']['mobile'];
                        }
                        if(!empty($mobile))
                        {
                            // 变量处理
                            $codes = [];
                            if(!empty($template['sms']['codes']))
                            {
                                foreach($template['sms']['codes'] as $code)
                                {
                                    $codes[$code] = isset($order[$code]) ? $order[$code] : '';
                                }
                            }
                            // 添加日志
                            $log = BaseService::SmsLogInsert($mobile, $config[$template['sms']['template']], $sms_sign, $codes);

                            // 调用短信驱动发送
                            $obj = new \base\Sms($verify_params);
                            $res = $obj->SendTemplate($mobile, $config[$template['sms']['template']], $sms_sign, $codes);

                            // 更新日志
                            BaseService::SmsLogResponse($log, $res, $obj->error);
                        }
                    }

                    // 邮件
                    if(!empty($template['email']) && !empty($config[$template['email']]))
                    {
                        // 用户邮箱是否存在正确
                        if(!empty($order['user']) && !empty($order['user']['email']) && CheckEmail($order['user']['email']))
                        {
                            $search = [];
                            $replace = [];
                            foreach(BaseService::$order_email_var_fields as $v)
                            {
                                $search[] = '${'.$v['field'].'}';
                                $replace[] = isset($order[$v['field']]) ? $order[$v['field']] : '';
                            }
                            $content = str_replace($search, $replace, $config[$template['email']]);

                            // 添加日志
                            $log = BaseService::EmailLogInsert($order['user']['email'], $content);

                            // 调用邮件驱动发送
                            $obj = new \base\Email($verify_params);
                            $email_params = [
                                'email'     => $order['user']['email'],
                                'content'   => $content,
                                'title'     => '订单变更通知 - '.MyC('home_site_name'),
                            ];
                            $res = $obj->SendHtml($email_params);
                            // 更新日志
                            BaseService::EmailLogResponse($log, $res, $obj->error);
                        }
                    }
                }
            }
        }
        return DataReturn('success', 0);
    }

    /**
     * 公共消息发送
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]        $config     [插件配置]
     * @param   [int]          $message_id [消息id]
     * @param   [array]        $message    [消息内容]
     */
    public static function MessageSend($config, $message_id, $message)
    {
        if(isset($config['is_message_email_notice']) && $config['is_message_email_notice'] == 1 && !empty($config['message_email_template']))
        {
            // 获取邮箱
            $email = Db::name('User')->where(['id'=>$message['user_id']])->value('email');
            if(!empty($email))
            {
                // 内容处理
                $search = [
                    '${message_title}',
                    '${message_type}',
                    '${message_content}',
                ];
                $replace = [
                    $message['title'],
                    $message['type'],
                    $message['detail'],
                ];
                $content = str_replace($search, $replace, $config['message_email_template']);

                // 添加日志
                $log = BaseService::EmailLogInsert($email, $content);

                // 调用邮件驱动发送
                $verify_params = [
                    'expire_time'   => MyC('common_verify_expire_time'),
                    'interval_time' => MyC('common_verify_interval_time'),
                    'is_frq'        => 0,
                ];
                $obj = new \base\Email($verify_params);
                $email_params = [
                    'email'     => $email,
                    'content'   => $content,
                    'title'     => $message['title'].'通知 - '.MyC('home_site_name'),
                ];
                $res = $obj->SendHtml($email_params);
                // 更新日志
                BaseService::EmailLogResponse($log, $res, $obj->error);
            }
        }
    }
}
?>