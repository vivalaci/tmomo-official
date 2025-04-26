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
namespace app\plugins\payecoquick\service;

use payment\PayecoQuick;
use app\service\PaymentService;

/**
 * 易联快捷支付 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-11-18
 * @desc    description
 */
class PayHandleService
{
    // 支付插件配置
    public static $config;
    public static $obj;

    /**
     * 初始化
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-04-27
     * @desc    description
     */
    public static function Init()
    {
        $payment = PaymentService::PaymentData(['where'=>['payment'=>'PayecoQuick']]);
        if(empty($payment['config']))
        {
            return DataReturn('请先配置支付信息', -1);
        }
        self::$config = $payment['config'];
        self::$obj = new PayecoQuick(self::$config);

        return DataReturn('success', 0);
    }

    /**
     * 获取验证码
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-04-27
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function Verify($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'name',
                'error_msg'         => '请输入姓名',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'idcard',
                'error_msg'         => '请输入身份证号码',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'bank_card',
                'error_msg'         => '请输入银行卡号',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'mobile',
                'error_msg'         => '请输入手机号码',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order_no',
                'error_msg'         => '订单号为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'total_price',
                'error_msg'         => '支付金额为空',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 初始化
        $ret = self::Init();
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 请求参数
        $parameter = [
            'TradeCode'         => 'ApiSendSmCodeV2',
            'Version'           => '2.0.0',
            'MerchantId'        => self::$config['merchant_no'],
            'SmId'              => md5(time().GetNumberCode(10)),
            'MerchOrderId'      => $params['order_no'],
            'TradeTime'         => date('YmdHis'),
            'MobileNo'          => $params['mobile'],
            'VerifyTradeCode'   => 'PayByAccV2',
            'SmParam'           => '|'.$params['name'].'|'.$params['idcard'].'|'.$params['bank_card'].'|'.$params['total_price'],
        ];

        // 签名字符串
        $str = self::$obj->SignContent($parameter);

        // 私钥签名
        $sign = self::$obj->MyRsaSign($str['str1']);

        //通讯报文
        $query = "?TradeCode=".$parameter['TradeCode']."&".$str['str2']."&Sign=".$sign;

        // 请求接口
        $ret = self::$obj->HttpResponseGET($query);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 数据是否正确
        if(isset($ret['data']['head']['retCode']) && $ret['data']['head']['retCode'] == '0000')
        {
            $body = $ret['data']['body'];
            return DataReturn(MyLang('send_success'), 0, [
                'sms_id'    => $body['SmId'],
                'is_pwd'    => ($body['NeedPwd'] == 1) ? 1 : 0,
                // 这里不使用密码了、支付平台暂时不是强制需要传递密码
                'is_pwd'    => 0,
            ]);
        }
        return DataReturn($ret['data']['head']['retMsg'].'('.$ret['data']['head']['retCode'].')', -1);
    }

    /**
     * 支付
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-04-27
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function Pay($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'name',
                'error_msg'         => '请输入姓名',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'idcard',
                'error_msg'         => '请输入身份证号码',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'bank_card',
                'error_msg'         => '请输入银行卡号',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'mobile',
                'error_msg'         => '请输入手机号码',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'sms_id',
                'error_msg'         => '请先点击发送短信',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'sms_code',
                'error_msg'         => '请输入验证码',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order_no',
                'error_msg'         => '订单号为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'total_price',
                'error_msg'         => '支付金额为空',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 初始化
        $ret = self::Init();
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 请求参数
        $parameter = [
            'TradeCode'         => 'PayByAccV2',
            'Version'           => '2.0.0',
            'MerchantId'        => self::$config['merchant_no'],
            'IndustryId'        => self::$config['industry_id'],
            'MerchOrderId'      => $params['order_no'],
            'Amount'            => $params['total_price'],
            'OrderDesc'         => $params['subject'],
            'TradeTime'         => date('YmdHis'),
            'ExpTime'           => self::$obj->OrderAutoCloseTime(),
            'NotifyUrl'         => $params['notify_url'],
            'ExtData'           => self::$config['industry_name'],
            'MiscData'          => $params['mobile'].'|||'.$params['name'].'|'.$params['idcard'].'|'.$params['bank_card'].'|||||',
            'NotifyFlag'        => 0,
            'SmId'              => $params['sms_id'],
            'SmCode'            => $params['sms_code'],
            'pwd'               => empty($params['pwd']) ? '' : $params['pwd'],
        ];

        // 签名字符串
        $str = self::$obj->SignContent($parameter);

        // 私钥签名
        $sign = self::$obj->MyRsaSign($str['str1']);

        //通讯报文
        $query = "?TradeCode=".$parameter['TradeCode']."&".$str['str2']."&Sign=".$sign;

        // 请求接口
        $ret = self::$obj->HttpResponseGET($query);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 数据是否正确
        if(isset($ret['data']['head']['retCode']) && $ret['data']['head']['retCode'] == '0000' && !empty($ret['data']['body']) && $ret['data']['body']['Status'] == '02')
        {
            return DataReturn(MyLang('pay_success'), 0, MyUrl('index/order/index'));
        }
        return DataReturn($ret['data']['head']['retMsg'].'('.$ret['data']['head']['retCode'].')', -1);
    }
}
?>