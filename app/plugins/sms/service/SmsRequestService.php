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
namespace app\plugins\sms\service;

use app\service\SmsLogService;
use Qcloud\Sms\SmsSingleSender;

/**
 * 短信 - 短信发送请求服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class SmsRequestService
{
    /**
     * 发送处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-18
     * @desc    description
     * @param   [array]          $config [插件配置]
     * @param   [array]          $params [输入参数]
     */
    public static function Run($config, $params = [])
    {
        // 平台
        $platform = $config['platform'];

        // 系统模板配置信息
        if(empty($config['system_template']) || empty($config['system_template'][$platform]))
        {
            return DataReturn('短信模板未配置('.$platform.')', -1);
        }
        $system_template = $config['system_template'][$platform];

        // 更多模板配置信息
        if(!empty($config['more_template']) && !empty($config['more_template'][$platform]) && is_array($config['more_template'][$platform]))
        {
            $more_template = array_column($config['more_template'][$platform], 'value', 'key');
            if(!empty($params['template_value']) && !empty($more_template) && is_array($more_template) && array_key_exists($params['template_value'], $more_template))
            {
                $params['template_value'] = $more_template[$params['template_value']];
            }
        }

        // 处理方法
        $action = ucfirst($platform).'Handle';
        if(!method_exists(__CLASS__, $action))
        {
            return DataReturn('短信方法未定义('.$action.')', -1);
        }

        // 调用方法
        return self::$action($system_template, $params);
    }

    /**
     * loginsms短信发送处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-24
     * @desc    description
     * @param   [array]          $system_template [系统模板配置数据]
     * @param   [array]          $params          [短信参数]
     */
    public static function LoginsmsHandle($system_template, $params)
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'client_id',
                'error_msg'         => 'client_id未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'client_secret',
                'error_msg'         => 'client_secret未配置',
            ],
        ];
        $ret = ParamsChecked($system_template, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 是否存在短信模板内容
        if(empty($params['template_value']))
        {
            return DataReturn('短信模板内容未配置', -1);
        }

        // 获取access_token
        $key = 'plugins_sms_loginsns_access_token';
        $access_token = MyCache($key);
        if(empty($access_token))
        {
            $ret = CurlPost('https://api.login-sms.com/token', [
                'client_id'      => $system_template['client_id'],
                'client_secret'  => $system_template['client_secret'],
                'grant_type'     => 'client_credentials',
            ]);
            if($ret['code'] != 0)
            {
                return $ret;
            }
            $ret = json_decode($ret['data'], true);
            if(empty($ret['access_token']) || empty($ret['expires_in']))
            {
                $msg = empty($ret['error_description']) ? '短信access_token获取失败' : $ret['error_description'];
                return DataReturn($msg, -1, $ret);
            }
            $access_token = $ret['access_token'];

            // 存储缓存
            MyCache($key, $access_token, $ret['expires_in']);
        }

        // 模板处理
        if(!empty($params['template_var']) && is_array($params['template_var']))
        {
            foreach($params['template_var'] as $k=>$v)
            {
                $params['template_value'] = str_replace('${'.$k.'}', $v, $params['template_value']);
            }
        }

        // 签名
        $sign_name = empty($system_template['sign_name']) ? '' : $system_template['sign_name'];

        // 请求参数
        $request_url = 'https://api.login-sms.com/messages/send';
        $request_params = [
            'country'    => 'EC',
            'to_number'  => '593'.substr($params['mobile'], 1),
            'content'    => $sign_name.$params['template_value'],
        ];

        // 添加短信日志
        $sms_log = SmsLogService::SmsLogAdd('loginsms', $params['mobile'], $sign_name, $params['template_value'], $params['template_var'], $request_url, $request_params);
        if($sms_log['code'] != 0)
        {
            return $sms_log;
        }

        // 请求发送短信
        $ret = CurlPost($request_url, $request_params, 1, 30, '', ['Authorization: Bearer '.$access_token]);
        if($ret['code'] != 0)
        {
            return $ret;
        }
        $ret = json_decode($ret['data'], true);
        if(isset($ret['status']) && $ret['status'] == 200)
        {
            // 日志回调
            SmsLogService::SmsLogResponse($sms_log['data']['id'], 1, $ret, time()-$sms_log['data']['add_time']);
            return DataReturn(MyLang('send_success'), 0, $ret);
        }

        // 错误原因
        $msg = empty($ret['message']) ? '短信发送异常' : $ret['message'];
        // 日志回调
        SmsLogService::SmsLogResponse($sms_log['data']['id'], 2, $ret, time()-$sms_log['data']['add_time'], $msg);
        return DataReturn($msg, -1, $ret);
    }

    /**
     * 腾讯短信发送处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-19
     * @desc    description
     * @param   [array]          $system_template [系统模板配置数据]
     * @param   [array]          $params          [短信参数]
     */
    public static function TencentHandle($system_template, $params)
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'sdk_appid',
                'error_msg'         => 'SdkAppid未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'app_key',
                'error_msg'         => 'AppKey未配置',
            ],
        ];
        $ret = ParamsChecked($system_template, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 是否存在短信模板内容
        if(empty($params['template_value']))
        {
            return DataReturn('短信模板内容未配置', -1);
        }

        // 短信签名
        $sign_name = empty($system_template['sign_name']) ? $params['sign_name'] : $system_template['sign_name'];

        // 模板变量
        $params['template_var'] = empty($params['template_var']) ? '' : array_values($params['template_var']);

        // 引入类库
        require APP_PATH . "plugins/sms/lib/Tencent/index.php";

        // 处理发送短信
        $sender = new SmsSingleSender($system_template['sdk_appid'], $system_template['app_key']);
        $sender->sendWithParams('86', $params['mobile'], $params['template_value'], $params['template_var'], $sign_name);

        // 请求接口
        $request_url = $sender->sendRequestUrl();

        // 请求数据
        $request_params = DataObjectToArray($sender->sendRequestData());

        // 添加短信日志
        $sms_log = SmsLogService::SmsLogAdd('tencent', $params['mobile'], $sign_name, $params['template_value'], $params['template_var'], $request_url, $request_params);
        if($sms_log['code'] != 0)
        {
            return $sms_log;
        }

        // 处理发送
        $ret = json_decode($sender->sendHandle(), true);
        if(isset($ret['result']) && $ret['result'] == 0)
        {
            // 日志回调
            SmsLogService::SmsLogResponse($sms_log['data']['id'], 1, $ret, time()-$sms_log['data']['add_time']);
            return DataReturn(MyLang('send_success'), 0, $ret);
        }

        // 错误原因
        $msg = empty($ret['errmsg']) ? '短信发送异常' : $ret['errmsg'];
        // 日志回调
        SmsLogService::SmsLogResponse($sms_log['data']['id'], 2, $ret, time()-$sms_log['data']['add_time'], $msg);
        return DataReturn($msg, -1, $ret);
    }

    /**
     * 华为短信发送处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-19
     * @desc    description
     * @param   [array]          $system_template [系统模板配置数据]
     * @param   [array]          $params          [短信参数]
     */
    public static function HuaweiHandle($system_template, $params)
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'api_url',
                'error_msg'         => 'API接口地址未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'app_key',
                'error_msg'         => 'AppKey未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'app_secret',
                'error_msg'         => 'AppSecret未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'sign_sender',
                'error_msg'         => '签名通道号未配置',
            ],
        ];
        $ret = ParamsChecked($system_template, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 是否存在短信模板内容
        if(empty($params['template_value']))
        {
            return DataReturn('短信模板内容未配置', -1);
        }

        // 短信签名
        $sign_name = empty($system_template['sign_name']) ? $params['sign_name'] : $system_template['sign_name'];

        // 模板变量
        $params['template_var'] = empty($params['template_var']) ? '' : array_values($params['template_var']);

        // 请求接口
        if(substr($system_template['api_url'], -1) != '/')
        {
            $system_template['api_url'] .= '/';
        }
        $request_url = $system_template['api_url'].'sms/batchSendSms/v1';

        // 请求参数
        $request_params = [
            'from'           => $system_template['sign_sender'],
            'to'             => $params['mobile'],
            'templateId'     => $params['template_value'],
            'templateParas'  => empty($params['template_var']) ? '' : json_encode($params['template_var']),
            'signature'      => $sign_name
        ];

        // 添加短信日志
        $sms_log = SmsLogService::SmsLogAdd('huawei', $params['mobile'], $sign_name, $params['template_value'], $params['template_var'], $request_url, $request_params);
        if($sms_log['code'] != 0)
        {
            return $sms_log;
        }

        // 构造X-WSSE参数值
        $now = date('Y-m-d\TH:i:s\Z');
        $nonce = uniqid();
        $base64 = base64_encode(hash('sha256', ($nonce . $now . $system_template['app_secret'])));
        $wsse = sprintf("UsernameToken Username=\"%s\",PasswordDigest=\"%s\",Nonce=\"%s\",Created=\"%s\"", $system_template['app_key'], $base64, $nonce, $now);

        // 发送请求
        $context_options = [
            'http' => [
                'method'         => 'POST',
                'header'         => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Authorization: WSSE realm="SDP",profile="UsernameToken",type="Appkey"',
                    'X-WSSE: ' . $wsse,
                ],
                'content'        => http_build_query($request_params),
                'ignore_errors'  => true,
            ],
            //为防止因HTTPS证书认证失败造成API调用失败，需要先忽略证书信任问题
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ];
        $ret = json_decode(file_get_contents($request_url, false, stream_context_create($context_options)), true);
        if(!empty($ret['result']) && !empty($ret['result'][0]) && isset($ret['result'][0]['status']) && $ret['result'][0]['status'] == 000000)
        {
            // 日志回调
            SmsLogService::SmsLogResponse($sms_log['data']['id'], 1, $ret, time()-$sms_log['data']['add_time']);
            return DataReturn(MyLang('send_success'), 0, $ret);
        }

        // 错误原因
        $msg = empty($ret['description']) ? '短信发送异常' : $ret['description'].'('.$ret['code'].')';
        // 日志回调
        SmsLogService::SmsLogResponse($sms_log['data']['id'], 2, $ret, time()-$sms_log['data']['add_time'], $msg);
        return DataReturn($msg, -1, $ret);        
    }

    /**
     * 百度短信发送处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-19
     * @desc    description
     * @param   [array]          $system_template [系统模板配置数据]
     * @param   [array]          $params          [短信参数]
     */
    public static function BaiduHandle($system_template, $params)
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'access_key',
                'error_msg'         => 'AccessKey未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'secret_key',
                'error_msg'         => 'SecretKey未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'sign_id',
                'error_msg'         => '签名ID未配置',
            ],
        ];
        $ret = ParamsChecked($system_template, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 是否存在短信模板内容
        if(empty($params['template_value']))
        {
            return DataReturn('短信模板内容未配置', -1);
        }

        // 服务域名 (北京 smsv3.bj.baidubce.com, 苏州 smsv3.su.baidubce.com)
        $host = 'smsv3.bj.baidubce.com';
        $uri = '/api/v3/sendSms'; // 接口路径
        $bce_date = gmdate("Y-m-d\TH:i:s\Z");

        // 签名有效期 (秒)
        $validity = '1800'; 
        // 代签名头域
        $signed_header = 'host;x-bce-date';
        // 1: 规范化请求和前缀字符串
        // 规范化请求  由HTTP请求信息规范化后生成
        $canonical_request = "POST\n" .$uri."\n\nhost:" .$host."\nx-bce-date:" .urlencode($bce_date);
        // 前缀字符串  由除sk字段外的签名信息生成
        $auth_string_prefix = "bce-auth-v1/".$system_template['access_key']."/".$bce_date."/".$validity;
        // 2: 派生签名密钥 signing_key  signing_key = HMAC-SHA-256-HEX("sk", auth_string_prefix)
        $signing_key = bin2hex(hash_hmac('sha256', $auth_string_prefix, $system_template['secret_key'], true));
        // 3: 签名摘要 signature  signature = HMAC-SHA-256-HEX(signing_key, canonical_request)
        $signature = bin2hex(hash_hmac('sha256', $canonical_request, $signing_key, true));
        // 4: 认证字符串 authorization  Authorization = auth_string_prefix/signed_header/signature
        $authorization = $auth_string_prefix.'/'.$signed_header.'/'.$signature;

        // 请求接口
        $request_url = "https://".$host.$uri;

        // 请求参数
        $request_params = [
            'signatureId'  => $system_template['sign_id'],
            'template'     => $params['template_value'],
            'mobile'       => $params['mobile'],
            'contentVar'   => $params['template_var'],
        ];

        // 添加短信日志
        $sms_log = SmsLogService::SmsLogAdd('baidu', $params['mobile'], $system_template['sign_id'], $params['template_value'], $params['template_var'], $request_url, $request_params);
        if($sms_log['code'] != 0)
        {
            return $sms_log;
        }

        // 请求头
        $header = [
            'Authorization:'.$authorization,
            'Host:'.$host,
            'x-bce-date:'.$bce_date,
        ];
        $ret =CurlPost($request_url, $request_params, 1, 30, '', $header);
        if($ret['code'] != 0)
        {
            return $ret;
        }
        $ret = json_decode($ret['data'], true);
        if(isset($ret['code']) && $ret['code'] == 1000)
        {
            // 日志回调
            SmsLogService::SmsLogResponse($sms_log['data']['id'], 1, $ret, time()-$sms_log['data']['add_time']);
            return DataReturn(MyLang('send_success'), 0, $ret);
        }

        // 错误原因
        $msg = empty($ret['message']) ? '短信发送异常' : $ret['message'].'('.$ret['code'].')';
        // 日志回调
        SmsLogService::SmsLogResponse($sms_log['data']['id'], 2, $ret, time()-$sms_log['data']['add_time'], $msg);
        return DataReturn($msg, -1, $ret);
    }

    /**
     * 云片短信发送处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-19
     * @desc    description
     * @param   [array]          $system_template [系统模板配置数据]
     * @param   [array]          $params          [短信参数]
     */
    public static function YunpianHandle($system_template, $params)
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'api_key',
                'error_msg'         => 'ApiKey未配置',
            ],
        ];
        $ret = ParamsChecked($system_template, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 是否存在短信模板内容
        if(empty($params['template_value']))
        {
            return DataReturn('短信模板内容未配置', -1);
        }

        // 请求接口
        $request_url = 'https://sms.yunpian.com/v2/sms/tpl_single_send.json';

        // 模板变量处理
        if(!empty($params['template_var']) && is_array($params['template_var']))
        {
            $tpl_value = '';
            foreach($params['template_var'] as $k=>$v)
            {
                $tpl_value .= '&#'.$k.'#='.urlencode($v);
            }
            $params['template_var'] = mb_substr($tpl_value, 1, null, 'utf-8');
        }
        // 请求参数
        $request_params = [
            'apikey'     => $system_template['api_key'],
            'mobile'     => $params['mobile'],
            'tpl_id'     => $params['template_value'],
            'tpl_value'  => $params['template_var'],
        ];

        // 添加短信日志
        $sms_log = SmsLogService::SmsLogAdd('yunpian', $params['mobile'], '', $params['template_value'], $params['template_var'], $request_url, $request_params);
        if($sms_log['code'] != 0)
        {
            return $sms_log;
        }

        // 发送
        $ret = CurlPost($request_url, $request_params);
        if($ret['code'] != 0)
        {
            return $ret;
        }
        $ret = json_decode($ret['data'], true);
        if(isset($ret['code']) && $ret['code'] == 0)
        {
            // 日志回调
            SmsLogService::SmsLogResponse($sms_log['data']['id'], 1, $ret, time()-$sms_log['data']['add_time']);
            return DataReturn(MyLang('send_success'), 0, $ret);
        }

        // 错误原因
        $msg = empty($ret['msg']) ? '短信发送异常' : $ret['msg'].'('.$ret['code'].')';
        // 日志回调
        SmsLogService::SmsLogResponse($sms_log['data']['id'], 2, $ret, time()-$sms_log['data']['add_time'], $msg);
        return DataReturn($msg, -1, $ret);
    }
}
?>