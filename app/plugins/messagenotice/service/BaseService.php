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
use app\service\PluginsService;
use app\service\OrderService;
use app\service\UserService;

/**
 * 消息通知 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-11-18
 * @desc    description
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [];

    // 基础私有字段
    public static $base_config_private_field = [
        'partner_id',
        'partner_secret',
        'device_id',
        'message_email_template',
        'order_await_pay_email_template',
        'order_pay_success_email_template',
        'order_pay_success_extraction_email_template',
        'order_pay_success_fictitious_email_template',
        'order_delivery_email_template',
        'order_delivery_extraction_email_template',
        'order_collect_email_template',
        'order_close_email_template',
        'admin_new_order_email_template',
        'admin_new_user_audit_email_template',
        'admin_new_ask_email_template',
        'user_ask_reply_email_template',
        'user_ask_comments_email_template',
    ];

    // 订单通知邮件变量字段
    public static $order_email_var_fields = [
        ['name'=>'订单号', 'field'=>'order_no'],
        ['name'=>'订单单价', 'field'=>'price'],
        ['name'=>'订单总价', 'field'=>'total_price'],
        ['name'=>'支付金额', 'field'=>'pay_price'],
        ['name'=>'来源终端', 'field'=>'client_type_name'],
        ['name'=>'订单类型', 'field'=>'order_model_name'],
        ['name'=>'出货仓库', 'field'=>'warehouse_name'],
        ['name'=>'订单状态', 'field'=>'status_name'],
        ['name'=>'订单支付状态', 'field'=>'pay_status_name'],
        ['name'=>'支付方式', 'field'=>'payment_name'],
        ['name'=>'商品信息', 'field'=>'goods'],
        ['name'=>'订单取货码', 'field'=>'order_code'],
        ['name'=>'用户留言', 'field'=>'user_note'],
        ['name'=>'快递公司', 'field'=>'express_name'],
        ['name'=>'快递单号', 'field'=>'express_number'],
        ['name'=>'用户名', 'field'=>'user_name'],
        ['name'=>'用户绑定手机', 'field'=>'user_mobile'],
        ['name'=>'用户绑定邮箱', 'field'=>'user_email'],
        ['name'=>'收货/取货别名标签', 'field'=>'address_alias'],
        ['name'=>'收货/取货姓名', 'field'=>'address_name'],
        ['name'=>'收货/取货电话', 'field'=>'address_tel'],
        ['name'=>'收货/取货地址', 'field'=>'address_content'],
        ['name'=>'订单创建时间', 'field'=>'add_time'],
        ['name'=>'订单支付时间', 'field'=>'pay_time'],
        ['name'=>'订单发货时间', 'field'=>'delivery_time'],
        ['name'=>'订单收货时间', 'field'=>'collect_time'],
        ['name'=>'订单取消/关闭时间', 'field'=>'close_time'],
        ['name'=>'取消/关闭类型', 'field'=>'type_msg'],
    ];

    // 新用户待审通知邮件变量字段
    public static $user_audit_email_var_fields = [
        ['name'=>'用户ID', 'field'=>'id'],
        ['name'=>'用户展示名称', 'field'=>'user_name_view'],
        ['name'=>'昵称', 'field'=>'nickname'],
        ['name'=>'用户名', 'field'=>'username'],
        ['name'=>'手机', 'field'=>'mobile'],
        ['name'=>'邮箱', 'field'=>'email'],
        ['name'=>'头像', 'field'=>'avatar'],
        ['name'=>'性别', 'field'=>'gender_text'],
        ['name'=>'注册时间', 'field'=>'add_time_text'],
    ];

    // 问答通知邮件变量字段
    public static $ask_email_var_fields = [
        ['name'=>'问答ID', 'field'=>'ask_id'],
        ['name'=>'用户ID', 'field'=>'user_id'],
        ['name'=>'用户展示名称', 'field'=>'user_name_view'],
        ['name'=>'商品ID', 'field'=>'goods_id'],
        ['name'=>'商品标题', 'field'=>'goods_title'],
        ['name'=>'联系人', 'field'=>'name'],
        ['name'=>'联系电话', 'field'=>'tel'],
        ['name'=>'问答标题', 'field'=>'title'],
        ['name'=>'问答内容', 'field'=>'content'],
        ['name'=>'回复内容', 'field'=>'reply'],
        ['name'=>'回复时间', 'field'=>'reply_time'],
        ['name'=>'提问时间', 'field'=>'add_time'],
    ];

    // 问答评论通知邮件变量字段
    public static $ask_comments_email_var_fields = [
        ['name'=>'问答ID', 'field'=>'ask_id'],
        ['name'=>'用户ID', 'field'=>'user_id'],
        ['name'=>'用户展示名称', 'field'=>'user_name_view'],
        ['name'=>'评论内容', 'field'=>'content'],
        ['name'=>'评论时间', 'field'=>'add_time'],
    ];

    // 日志状态（0待发送，1已发送，2已失败）
    public static $log_status_list = [
        0 => ['value'=>0, 'name'=>'待发送'],
        1 => ['value'=>1, 'name'=>'已发送'],
        2 => ['value'=>2, 'name'=>'已失败'],
    ];

    // 语音类型（0订单通知，1用户审核，2扫码收款）
    public static $log_voice_type_list = [
        0 => ['value'=>0, 'name'=>'订单通知'],
        1 => ['value'=>1, 'name'=>'用户审核'],
        2 => ['value'=>2, 'name'=>'扫码收款'],
    ];

    /**
     * 基础配置信息保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseConfigSave($params = [])
    {
        return PluginsService::PluginsDataSave(['plugins'=>'messagenotice', 'data'=>$params], self::$base_config_attachment_field);
    }

    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [boolean]          $is_cache [是否缓存中读取]
     */
    public static function BaseConfig($is_cache = true)
    {
        $ret = PluginsService::PluginsData('messagenotice', self::$base_config_attachment_field, $is_cache);
        if(empty($ret['data']))
        {
            $ret['data'] = [];
        }

        // 邮件通知模板
        $arr = [
            'message_email_template',
            'order_await_pay_email_template',
            'order_pay_success_email_template',
            'order_pay_success_extraction_email_template',
            'order_pay_success_fictitious_email_template',
            'order_delivery_email_template',
            'order_delivery_extraction_email_template',
            'order_collect_email_template',
            'order_close_email_template',
            'admin_new_order_email_template',
            'admin_new_user_audit_email_template',
            'admin_new_ask_email_template',
            'user_ask_reply_email_template',
            'user_ask_comments_email_template',
        ];
        foreach($arr as $field)
        {
            if(!empty($ret['data'][$field]))
            {
                $ret['data'][$field] = htmlspecialchars_decode($ret['data'][$field]);
            }
        }

        return $ret;
    }

    /**
     * 短信日志添加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [string]          $mobile   [手机号码]
     * @param   [string]          $template [短信模板]
     * @param   [string]          $sms_sign [短信签名]
     * @param   [array]           $codes    [变量参数]
     */
    public static function SmsLogInsert($mobile, $template, $sms_sign, $codes = [])
    {
        $data = [
            'status'    => 0,
            'mobile'    => $mobile,
            'template'  => $template,
            'sms_sign'  => $sms_sign,
            'codes'     => empty($codes) ? '' : (is_array($codes) ? json_encode($codes, JSON_UNESCAPED_UNICODE) : $codes),
            'add_time'  => time(),
        ];
        $data['id'] = Db::name('PluginsMessagenoticeSmsLog')->insertGetId($data);
        return $data;
    }

    /**
     * 短信日志回调
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]          $log    [日志信息]
     * @param   [boolean]        $status [状态 true | false]
     * @param   [string]         $error  [错误信息]
     */
    public static function SmsLogResponse($log, $status, $error = '')
    {
        if(!empty($log) && !empty($log['id']))
        {
            $data = [
                'status'    => $status ? 1 : 2,
                'reason'    => $status ? '' : $error,
                'tsc'       => time()-$log['add_time'],
                'upd_time'  => time(),
            ];
            Db::name('PluginsMessagenoticeSmsLog')->where(['id'=>$log['id']])->update($data);
        }
        return true;
    }

    /**
     * 邮件日志添加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [string]          $email    [邮箱]
     * @param   [string]          $content  [邮件内容]
     */
    public static function EmailLogInsert($email, $content)
    {
        $data = [
            'status'    => 0,
            'email'     => $email,
            'content'   => $content,
            'add_time'  => time(),
        ];
        $data['id'] = Db::name('PluginsMessagenoticeEmailLog')->insertGetId($data);
        return $data;
    }

    /**
     * 邮件日志回调
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]          $log    [日志信息]
     * @param   [boolean]        $status [状态 true | false]
     * @param   [string]         $error  [错误信息]
     */
    public static function EmailLogResponse($log, $status, $error = '')
    {
        if(!empty($log) && !empty($log['id']))
        {
            $data = [
                'status'    => $status ? 1 : 2,
                'reason'    => $status ? '' : $error,
                'tsc'       => time()-$log['add_time'],
                'upd_time'  => time(),
            ];
            Db::name('PluginsMessagenoticeEmailLog')->where(['id'=>$log['id']])->update($data);
        }
        return true;
    }

    /**
     * 语音日志添加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function VoiceLogInsert($params = [])
    {
        // 配置为空则重新读取
        if(empty($params['config']))
        {
            $base = self::BaseConfig();
            $params['config'] = $base['data'];
        }

        // 是否指定设备ID
        $device_id = empty($params['device_id']) ? (empty($params['config']['device_id']) ? '' : $params['config']['device_id']) : $params['device_id'];
        $data = [
            'device_id' => $device_id,
            'status'    => 0,
            'type'      => empty($params['type']) ? 0 : intval($params['type']),
            'content'   => empty($params['content']) ? '' : $params['content'],
            'add_time'  => time(),
        ];
        $data['id'] = Db::name('PluginsMessagenoticeVoiceLog')->insertGetId($data);
        return $data;
    }

    /**
     * 语音日志回调
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function VoiceLogResponse($params = [])
    {
        if(!empty($params['log']) && !empty($params['log']['id']))
        {
            // 状态 true | false
            $status = isset($params['status']) ? $params['status'] : 0;
            // 错误信息
            $error = empty($params['error']) ? '' : $params['error'];
            // 日志数据
            $data = [
                'status'    => $status ? 1 : 2,
                'reason'    => $status ? '' : $error,
                'tsc'       => time()-$params['log']['add_time'],
                'upd_time'  => time(),
            ];
            Db::name('PluginsMessagenoticeVoiceLog')->where(['id'=>$params['log']['id']])->update($data);
            return true;
        }
        return false;
    }

    /**
     * 语音发送
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-12-17
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function VoiceSend($params = [])
    {
        // 基础参数
        if(empty($params['content']))
        {
            return ['status'=>false, 'msg'=>'通知内容为空'];
        }

        // 配置为空则重新读取
        if(empty($params['config']))
        {
            $base = self::BaseConfig();
            $params['config'] = $base['data'];
        }

        // 是否指定设备ID
        $device_id = empty($params['device_id']) ? (empty($params['config']['device_id']) ? '' : $params['config']['device_id']) : $params['device_id'];

        // 基础参数
        if(empty($device_id) || empty($params['config']['partner_id']) || empty($params['config']['partner_secret']))
        {
            return ['status'=>false, 'msg'=>'设备未配置'];
        }

        // 发送处理
        $request_params = [
            'device_id'     => $device_id,
            'notify_type'   => 'sound',
            'data'          => [
                'message'  => $params['content'],
                'msg_id'   => time(),
            ],
        ];
        $timestamp = time();
        $signature = md5($params['config']['partner_id'].$timestamp.$params['config']['partner_secret']);
        $header = [
            'X-PARTNER-ID: '.$params['config']['partner_id'],
            'X-TIMESTAMP: '.$timestamp,
            'X-SIGNATURE: '.$signature,
        ];
        $url = 'https://notify.justap.cn/iot/notify';
        $ret = CurlPost($url, $request_params, 1, 30, null, $header);
        if($ret['code'] == 0)
        {
            $res = json_decode($ret['data'], true);
            if(!empty($res) && isset($res['code']) && isset($res['msg']))
            {
                return ['status'=>($res['code'] == 200), 'msg'=>$res['msg']];
            }
            $msg = empty($res['msg']) ? $ret['data'] : $res['msg'];
            return ['status'=>false, 'msg'=>'通知请求失败('.$msg.')'];
        }
        return ['status'=>false, 'msg'=>$ret['msg']];
    }

    /**
     * 后台导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-06
     * @desc    description
     */
    public static function AdminNavMenuList()
    {
        return [
            [
                'name'      => '基础配置',
                'control'   => 'admin',
                'action'    => 'index',
            ],
            [
                'name'      => '短信日志',
                'control'   => 'smslog',
                'action'    => 'index',
            ],
            [
                'name'      => '邮件日志',
                'control'   => 'emaillog',
                'action'    => 'index',
            ],
            [
                'name'      => '语音日志',
                'control'   => 'voicelog',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 订单信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-25
     * @desc    description
     * @param   [int]          $order_id [订单id]
     * @param   [int]          $status   [订单号]
     */
    public static function OrderData($order_id, $status = null)
    {
        $data_params = [
            'm'         => 0,
            'n'         => 1,
            'where'     => [
                ['is_delete_time', '=', 0],
                ['id', '=', intval($order_id)],
            ],
            'user_type' => 'admin',
            'is_public' => 0,
        ];
        if($status !== null)
        {
            $data_params['where'][] = ['status', '=', intval($status)];
        }
        $ret = OrderService::OrderList($data_params);
        $order = [];
        if(!empty($ret['data']) && !empty($ret['data'][0]))
        {
            $order = $ret['data'][0];
            // 商品信息
            $order['goods'] = '';
            if(!empty($order['items']))
            {
                foreach($order['items'] as $k=>$v)
                {
                    if($k > 0)
                    {
                        $order['goods'] .= '； ';
                    }
                    $order['goods'] .= $v['title'].(empty($v['spec_text']) ? '' : '('.$v['spec_text'].')');
                }
            }

            // 取货码
            $order['order_code'] = (empty($order['extraction_data']) || empty($order['extraction_data']['code'])) ? '' : $order['extraction_data']['code'];

            // 用户信息
            if(empty($order['user']))
            {
                $order['user_name'] = '';
                $order['user_mobile'] = '';
                $order['user_email'] = '';
            } else {
                $order['user_name'] = $order['user']['user_name_view'];
                $order['user_mobile'] = $order['user']['mobile'];
                $order['user_email'] = $order['user']['email'];
            }

            // 地址信息
            if(empty($order['address_data']))
            {
                $order['address_alias'] = '';
                $order['address_name'] = '';
                $order['address_tel'] = '';
                $order['address_content'] = '';
            } else {
                $ads = $order['address_data'];
                $order['address_alias'] = $ads['alias'];
                $order['address_name'] = $ads['name'];
                $order['address_tel'] = $ads['tel'];
                $order['address_content'] = $ads['province_name'].$ads['city_name'].$ads['county_name'].$ads['address'];
            }

            // 订单关闭取消
            $order['type_msg'] = ($order['status'] == 5) ? '取消' : (($order['status'] == 6) ? '关闭' : '');

            // 价格增加符号
            foreach(self::$order_email_var_fields as $v)
            {
                if(isset($order[$v['field']]) && in_array($v['field'], ['price', 'pay_price', 'total_price']))
                {
                    $order[$v['field']] = $order['currency_data']['currency_symbol'].' '.PriceBeautify($order[$v['field']]).' '.$order['currency_data']['currency_name'];
                }
            }

            // 支付方式、由于是订单先提交事件先产生，支付日志还没写入，这里使用支付ID先自己查询支付方式
            if(empty($order['payment_name']) && !empty($order['payment_id']))
            {
                $order['payment_name'] = Db::name('Payment')->where(['id'=>$order['payment_id']])->value('name');
            }

            // 快递信息
            $order['express_name'] = empty($order['express_data']) ? '' : implode(',', array_column($order['express_data'], 'express_name'));
            $order['express_number'] = empty($order['express_data']) ? '' : implode(',', array_column($order['express_data'], 'express_number'));
        }
        return $order;
    }

    /**
     * 问答数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-04-27
     * @desc    description
     * @param   [array]          $ask [问答数据]
     */
    public static function AskData($ask)
    {
        // 提问id
        $ask['ask_id'] = isset($ask['id']) ? $ask['id'] : '';

        // 用户信息
        if(!empty($ask['user_id']))
        {
            $ask['user'] = UserService::GetUserViewInfo($ask['user_id']);
            $ask['user_name_view'] = (empty($ask['user']) || empty($ask['user']['user_name_view'])) ? '' : $ask['user']['user_name_view'];
        } else {
            $ask['user_id'] = '';
        }
        // 商品信息
        if(!empty($ask['goods_id']))
        {
            $ask['goods_title'] = Db::name('Goods')->where(['id'=>$ask['goods_id']])->value('title');
        } else {
            $ask['goods_id'] = '';
        }
        // 提问时间
        $ask['add_time'] = empty($ask['add_time']) ? '' : date('Y-m-d H:i:s', $ask['add_time']);
        // 回复时间
        $ask['reply_time'] = empty($ask['reply_time']) ? '' : date('Y-m-d H:i:s', $ask['reply_time']);

        return $ask;
    }

    /**
     * 问答评论数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-04-27
     * @desc    description
     * @param   [array]          $ask_comments [问答评论数据]
     */
    public static function AskCommentsData($ask_comments)
    {
        // 用户信息
        if(!empty($ask_comments['user_id']))
        {
            $ask_comments['user'] = UserService::GetUserViewInfo($ask_comments['user_id']);
            $ask_comments['user_name_view'] = (empty($ask_comments['user']) || empty($ask_comments['user']['user_name_view'])) ? '' : $ask_comments['user']['user_name_view'];
        } else {
            $ask_comments['user_id'] = '';
        }

        // 评论时间
        $ask_comments['add_time'] = empty($ask_comments['add_time']) ? '' : date('Y-m-d H:i:s', $ask_comments['add_time']);

        return $ask_comments;
    }
}
?>