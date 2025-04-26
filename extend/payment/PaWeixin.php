<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace payment;

use think\facade\Db;
use app\service\PayLogService;

/**
 * PaymentAsia - 微信支付
 * @author   Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-22
 * @desc    description
 */
class PaWeixin
{
    // 插件配置参数
    private $config;

    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-17
     * @desc    description
     * @param   [array]           $params [输入参数（支付配置参数）]
     */
    public function __construct($params = [])
    {
        $this->config = $params;
    }

    /**
     * 配置信息
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-19
     * @desc    description
     */
    public function Config()
    {
        // 基础信息
        $base = [
            'name'          => 'PaymentAsia微信支付',  // 插件名称
            'version'       => '1.0.0',  // 插件版本
            'apply_version' => '不限',  // 适用系统版本描述
            'apply_terminal'=> ['pc'], // 适用终端 默认全部 ['pc', 'h5', 'ios', 'android', 'alipay', 'weixin', 'baidu', 'toutiao']
            'desc'          => 'PaymentAsia微信支付',  // 插件描述（支持html）
            'author'        => 'Devil',  // 开发者
            'author_url'    => 'http://shopxo.net/',  // 开发者主页
        ];

        // 配置信息
        $element = [
            [
                'element'       => 'input',
                'type'          => 'text',
                'default'       => '',
                'name'          => 'merchant_token',
                'placeholder'   => 'Merchant Token',
                'title'         => 'Merchant Token',
                'is_required'   => 0,
                'message'       => '請填寫Merchant Token',
            ],
            [
                'element'       => 'input',
                'type'          => 'text',
                'default'       => '',
                'name'          => 'secret_code',
                'placeholder'   => 'Secret Code',
                'title'         => 'Secret Code',
                'is_required'   => 0,
                'message'       => '請填寫Secret Code',
            ],
        ];

        return [
            'base'      => $base,
            'element'   => $element,
        ];
    }

    /**
     * 支付入口
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-19
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Pay($params = [])
    {
        // 参数
        if(empty($params))
        {
            return DataReturn('參數不能爲空', -1);
        }
        
        // 配置信息
        if(empty($this->config))
        {
            return DataReturn('支付缺少配置', -1);
        }

        $address = $params['business_data'][0]['address_data'];
        $user = $params['user'];

        // 支付参数
        $parameter = [
            'merchant_reference'=>  $params['order_no'],
            'currency'             => 'HKD',
            'amount'         => (string) $params['total_price'],
            'customer_ip'             => $_SERVER['REMOTE_ADDR'],
            'customer_email'             => (string) isset($user['email']) ? $user['email'] : 'shop@tmomo.hk',
            'customer_first_name'             => (string) isset($address['name']) ? $address['name'] : '',
            'customer_last_name'             => (string) isset($address['name']) ? $address['name'] : '',
            'customer_phone'             => (string) isset($address['tel']) ? $address['tel'] : '',
            'network'             => 'Wechat',
            'lang'             => 'zh-tw',
            'subject'             => $params['name'],
            'notify_url'        => $params['notify_url'],
            'return_url'        => $params['call_back_url'],
        ];

        // 生成签名
        $parameter['sign'] = $this->SignCreated($parameter);
 
        $action_url = 'https://payment.pa-sys.com/app/page/'.$this->config['merchant_token'];

        $html  = "<form id='pay_form' name='pay_form' style='text-align:center;' method='post' action='".$action_url."'>";
        $html .= "<input type='hidden' name='merchant_reference' value='".$parameter['merchant_reference']."'>";
        $html .= "<input type='hidden' name='currency' value='".$parameter['currency']."'>";
        $html .= "<input type='hidden' name='amount' value='".$parameter['amount']."'>";
        $html .= "<input type='hidden' name='customer_ip' value='".$parameter['customer_ip']."'>";
        $html .= "<input type='hidden' name='customer_email' value='".$parameter['customer_email']."'>";
        $html .= "<input type='hidden' name='customer_first_name' value='".$parameter['customer_first_name']."'>";
        $html .= "<input type='hidden' name='customer_last_name' value='".$parameter['customer_last_name']."'>";
        $html .= "<input type='hidden' name='customer_phone' value='".$parameter['customer_phone']."'>";
        $html .= "<input type='hidden' name='network' value='Wechat'>";
        $html .= "<input type='hidden' name='lang' value='zh-tw'>";
        $html .= "<input type='hidden' name='subject' value='".$parameter['subject']."'>";
        $html .= "<input type='hidden' name='notify_url' value='".$parameter['notify_url']."'>";
        $html .= "<input type='hidden' name='return_url' value='".$parameter['return_url']."'>";
        $html .= "<input type='hidden' name='sign' value='".$parameter['sign']."'>";
        $html .= "<input type='submit' value='立即支付'></form>";

        //submit按钮控件请不要含有name属性
        $html .= "<script>document.forms['pay_form'].submit();</script>";        

        // 支付请求记录
        PayLogService::PayLogRequestRecord($params['order_no'], ['request_params'=>$parameter]);

        die($html);
    }

    /**
     * 支付回调处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-19
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Respond($params = [])
    {
        if(empty($this->config))
        {
            return DataReturn('配置有誤', -1);
        }
        if(empty($params['merchant_reference']))
        {
            return DataReturn('支付失敗', -1);
        }
        if(empty($params['sign']))
        {
            return DataReturn('簽名為空', -1);
        }

        // 获取支付日志订单
        $pay_log_data = Db::name('PayLog')->where(['log_no'=>$params['merchant_reference']])->find();
        if(empty($pay_log_data))
        {
            return DataReturn(MyLang('common_service.order.pay_log_error_tips'), -1);
        }

        $secret = $this->config['secret_code'];

        $fields = array( 
            'amount', 
            'currency', 
            'request_reference',
            'merchant_reference', 
            'status',
            'sign'
        );
        $data = array();
       
         foreach ($fields as $_field) {
             $data[$_field] = isset($_POST[$_field]) ? htmlspecialchars($_POST[$_field], ENT_QUOTES, 'UTF-8') : '';
        }
        
        $sign = $data['sign']; 
        unset($data['sign']); 
        ksort($data);

        if ($sign !== hash('SHA512', http_build_query($data) . $secret)) 
        {
            return DataReturn('驗證籤名失敗', -1); //验证签名失败，请忽略此返回讯息
        }
        if (bccomp($pay_log_data['total_price'], $data['amount'], 2) !== 0) { 
            return DataReturn('訂單金額與返回金額不一致', -1); //订单金额应该与返回金额完全一致，验证失败，请忽略此返回讯息
        }
        if ($data['currency'] !== 'HKD') {
            return DataReturn('訂單貨幣與返回貨幣不一致', -1); //订单货币应该与返回货币完全一致，验证失败，请忽略此返回讯息
        }

        if(isset($data['status']) && $data['status'] == '1')
        {

            return DataReturn('支付成功', 0, $this->ReturnData($params));
        }
        return DataReturn('支付失敗', -1);
    }

    /**
     * [ReturnData 返回数据统一格式]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-10-06T16:54:24+0800
     * @param    [array]                   $data [返回数据]
     */
    private function ReturnData($data)
    {
        // 返回数据固定基础参数
        $data['trade_no']       = $data['request_reference'];        // 支付平台 - 订单号
        $data['buyer_user']     = '';       // 支付平台 - 用户
        $data['out_trade_no']   = $data['merchant_reference'];    // 本系统发起支付的 - 订单号
        $data['subject']        = isset($data['status']) ? '狀態:'.$data['status'] : ''; // 本系统发起支付的 - 商品名称
        $data['pay_price']      = $data['amount'];   // 本系统发起支付的 - 总价

        return $data;
    }

    /**
     * 退款处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-05-28
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Refund($params = [])
    {

        // 参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order_no',
                'error_msg'         => '訂單號不能爲空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'refund_price',
                'error_msg'         => '退款金額不能爲空',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 远程查询支付状态
        $parameter = [
            'merchant_reference'      => $params['order_no'],
            'amount'     => $params['refund_price'],
        ];

        // 生成签名
        $parameter['sign'] = $this->SignCreated($parameter);

        // 请求post
        $result = $this->HttpRequest('https://gateway.pa-sys.com/v1.1/online/'.$this->config['merchant_token'].'/transactions/refund', $parameter);

        if(isset($result['response']['code']) && $result['response']['code'] == '200')
        {
            // 统一返回格式
            $data = [
                'out_trade_no'  => isset($result['payload']['merchant_reference']) ? $result['payload']['merchant_reference'] : '',
                'trade_no'      => isset($result['payload']['refund_reference']) ? $result['payload']['refund_reference'] : '',
                'buyer_user'    => '',
                'refund_price'  => isset($result['payload']['refund_amount']) ? $result['payload']['refund_amount'] : $params['refund_price'],
                'return_params' => $result,
            ];
            return DataReturn('退款成功', 0, $data);
        }
        return DataReturn('退款失敗', -100);
    }

    /**
     * [HttpRequest 网络请求]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2017-09-25T09:10:46+0800
     * @param    [string]          $url  [请求url]
     * @param    [array]           $data [发送数据]
     * @return   [mixed]                 [请求返回数据]
     */
    private function HttpRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $body_string = '';
        if(is_array($data) && 0 < count($data))
        {
            foreach($data as $k => $v)
            {
                $body_string .= $k.'='.urlencode($v).'&';
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body_string);
        }
        $headers = array('content-type: application/x-www-form-urlencoded;charset=UTF-8');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $reponse = curl_exec($ch);

        if(curl_errno($ch))
        {
            return false;
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if(200 !== $httpStatusCode)
            {
                return false;
            }
        }
        curl_close($ch);
        return json_decode($reponse, true);
    }

    /**
     * 签名生成
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-15
     * @desc    description
     * @param   [array]          $params [需要签名的参数]
     */
    public function SignCreated($params)
    {
        unset($params['sign']);
        ksort($params);
        return hash('SHA512', http_build_query($params) . $this->config['secret_code']);
    }
}
?>