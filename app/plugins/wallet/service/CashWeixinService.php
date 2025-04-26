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
namespace app\plugins\wallet\service;

use think\facade\Db;
use app\service\PluginsDataConfigService;
use app\plugins\wallet\service\CashPaymentService;

/**
 * 钱包 - 钱包余额提现到微信服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class CashWeixinService
{
    // 配置信息
    public static $config;

    /**
     * 微信转账创建
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-19
     * @desc    description
     * @param   [array]          $cash   [提现数据]
     * @param   [array]          $params [输入参数]
     */
    public static function TransferCreate($cash, $params = [])
    {
        // 数据配置
        self::$config = PluginsDataConfigService::DataConfigData('wallet');

        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'weixin_merchant_id',
                'error_msg'         => '商户号未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'weixin_appid',
                'error_msg'         => 'appid未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'weixin_api_key_v3',
                'error_msg'         => 'api安全密钥v3未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'weixin_serial_no',
                'error_msg'         => '商户证书序列号未配置',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'weixin_private_key',
                'error_msg'         => '商户私钥证书未配置',
            ],
        ];
        $ret = ParamsChecked(self::$config, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        if(empty($cash['bank_accounts']))
        {
            return DataReturn('微信openid为空', -1);
        }

        // 转账数据
        $data = [
            'appid'                 => self::$config['weixin_appid'],
            'out_batch_no'          => $cash['cash_no'],
            'batch_name'            => '001',
            'batch_remark'          => 'gbz',
            'total_amount'          => $cash['money']*100,
            'total_num'             => 1,
            'transfer_detail_list'  => [
                [
                    'out_detail_no'    => $cash['cash_no'],
                    'transfer_amount'  => $cash['money']*100,
                    'transfer_remark'  => 'dbz',
                    'openid'           => $cash['bank_accounts'],
                    'user_name'        => self::ContentEncrypt($cash['bank_username'])
                ]
            ],
            // 'transfer_scene_id'     => '1000',
            // 'notify_url'            => 'https://www.weixin.qq.com/wxpay/pay.php'
        ];

        // 先释放原来为处理的数据
        CashPaymentService::CashPaymentRelease($cash);

        // 转账数据添加
        $insert = CashPaymentService::CashPaymentInsert($cash, $data, $params);
        if($insert['code'] != 0)
        {
            return $insert;
        }
        $start_time = time();

        // 请求接口
        $ret = self::WeixinRequest('/v3/transfer/batches', $data, 'POST', false);

        // 回调处理
        $status = 2;
        $reason = '异常错误';
        $out_order_no = '';
        if($ret['code'] == 0)
        {
            $response = json_decode($ret['data'], true);
            // ACCEPTED 已受理, PROCESSING 转账中, FINISHED 已完成, CLOSED 已关闭
            if(isset($response['batch_status']) && in_array($response['batch_status'], ['ACCEPTED', 'PROCESSING', 'FINISHED']))
            {
                $status = 1;
                $reason = '';
                $out_order_no = $response['batch_id'];
            } else {
                $reason = empty($response['message']) ? $ret['data'] : $response['message'];
            }
        } else {
            $reason = $ret['msg'];
        }

        // 转账数据回调
        return CashPaymentService::CashPaymentResponse($cash, $insert['data'], $start_time, $ret['data'], $status, $reason, $out_order_no);
    }

    /**
     * 商户证书key
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-20
     * @desc    description
     */
    public static function PrivateKey()
    {
        $result = '';
        if(stripos(self::$config['weixin_private_key'], '-----') === false)
        {
            $result = "-----BEGIN PRIVATE KEY-----\n";
            $result .= wordwrap(self::$config['weixin_private_key'], 64, "\n", true);
            $result .= "\n-----END PRIVATE KEY-----";
        } else {
            $result = self::$config['weixin_private_key'];
        }
        return $result;
    }

    /**
     * 获取微信平台证书
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-20
     * @desc    description
     */
    public static function WeixinPlatformCertificate()
    {
        // 缓存
        $key = md5('plugins-wallet-cash-weixin-platform-data-'.self::$config['weixin_merchant_id']);
        $result = MyCache($key);
        if(empty($result) || $result['expire_time'] < time())
        {
            // 请求接口
            $ret = self::WeixinRequest('/v3/certificates');
            if($ret['code'] != 0)
            {
                return $ret;
            }
            $response = $ret['data'];

            // 是否有错误
            if(!empty($response['message']))
            {
                return DataReturn($response['message'], -1);
            }

            // 是否有设置证书
            if(empty($response['data']))
            {
                return DataReturn('请先在微信支付平台设置平台证书', -1);
            }
            // 获取最后一个证书
            $data = $response['data'][count($response['data'])-1];

            // 证书解密
            $certificate = $data['encrypt_certificate'];
            $ret = self::DecryptToString($certificate['associated_data'], $certificate['nonce'], $certificate['ciphertext']);
            if($ret['code'] != 0)
            {
                return $ret;
            }

            // 缓存数据
            $result = [
                'serial_no'    => $data['serial_no'],
                'certificate'  => $ret['data'],
                'expire_time'  => (new \DateTime($data['expire_time']))->getTimestamp(),
                'time'         => time(),
            ];
            MyCache($key, $result);
        }
        return DataReturn('success', 0, $result);
    }

    /**
     * 数据解密
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-20
     * @desc    description
     * @param   [string]          $associated_data [加密证书的附加数据、固定为 certificate ]
     * @param   [string]          $nonce_str       [加密的随机数]
     * @param   [string]          $ciphertext      [平台加密的数据]
     */
    public static function DecryptToString($associated_data, $nonce_str, $ciphertext)
    {
        $AUTH_TAG_LENGTH_BYTE = 16;
        $ciphertext = \base64_decode($ciphertext);
        if(strlen($ciphertext) <= $AUTH_TAG_LENGTH_BYTE)
        {
            return DataReturn('平台证书长度有误', -1);
        }
        // ext-sodium (default installed on >= PHP 7.2)
        if(function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available())
        {
            $result = \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associated_data, $nonce_str, self::$config['weixin_api_key_v3']);
            return DataReturn('success', 0, $result);
        }
        // ext-libsodium (need install libsodium-php 1.x via pecl)
        if(function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available())
        {
            $result = \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associated_data, $nonce_str, self::$config['weixin_api_key_v3']);
            return DataReturn('success', 0, $result);
        }
        // openssl (PHP >= 7.1 support AEAD)
        if(PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods()))
        {
            $ctext = substr($ciphertext, 0, -$AUTH_TAG_LENGTH_BYTE);
            $authTag = substr($ciphertext, -$AUTH_TAG_LENGTH_BYTE);
            $result = \openssl_decrypt($ctext, 'aes-256-gcm', self::$config['weixin_api_key_v3'], \OPENSSL_RAW_DATA, $nonce_str, $authTag, $associated_data);
            return DataReturn('success', 0, $result);
        }
        return DataReturn('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php', -1);
    }

    /**
     * 内容加密
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-20
     * @desc    description
     * @param   [string]          $str [需要加密的内容字符串]
     */
    public static function ContentEncrypt($str)
    {
        $certificate = self::WeixinPlatformCertificate();
        if($certificate['code'] == 0)
        {
            // 证书处理
            if(stripos($certificate['data']['certificate'], '-----') === false)
            {
                $apiclient_cert = "-----BEGIN CERTIFICATE-----\n";
                $apiclient_cert .= wordwrap($certificate['data']['certificate'], 64, "\n", true);
                $apiclient_cert .= "\n-----END CERTIFICATE-----";
            } else {
                $apiclient_cert = $certificate['data']['certificate'];
            }

            $encrypted = '';
            if(openssl_public_encrypt($str, $encrypted, $apiclient_cert, OPENSSL_PKCS1_OAEP_PADDING))
            {
                return base64_encode($encrypted);
            }
        }
        return $str;
    }

    /**
     * 接口请求
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-20
     * @desc    description
     * @param   [string]          $path    [接口路径地址]
     * @param   [array]           $body    [请求数据]
     * @param   [string]          $method  [请求类型]
     * @param   [boolean]         $is_json [是否json格式返回原数据]
     */
    public static function WeixinRequest($path, $body = '', $method = 'GET', $is_json = true)
    {
        // 平台证书序列号
        if($path != '/v3/certificates')
        {
            $certificate = self::WeixinPlatformCertificate();
            if($certificate['code'] != 0)
            {
                return $certificate;
            }
        }

        // 请求签名+token
        $timestamp = time();
        $nonce = strtoupper(RandomString(32));
        $body_json = empty($body) ? '' : json_encode($body, JSON_UNESCAPED_UNICODE);
        $message = $method."\n".$path."\n".$timestamp."\n".$nonce."\n".$body_json."\n";
        openssl_sign($message, $raw_sign, self::PrivateKey(), OPENSSL_ALGO_SHA256);
        $sign = base64_encode($raw_sign);
        $schema = 'WECHATPAY2-SHA256-RSA2048';
        $token = sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"', self::$config['weixin_merchant_id'], $nonce, $timestamp, self::$config['weixin_serial_no'], $sign);

        // 头信息
        $header = [
            'Authorization: '.$schema.' '.$token,
            'Accept: application/json',
            'User-Agent: */*',
        ];
        // 非证书获取增加平台证书序列号
        if($path != '/v3/certificates')
        {
            $header[] = 'Wechatpay-Serial: '.$certificate['data']['serial_no'];
        }

        // 请求接口
        $url = 'https://api.mch.weixin.qq.com';
        $ret = CurlPost($url.$path, $body, 1, 30, $method, $header);
        if(!empty($ret['data']) && $is_json)
        {
            $ret['data'] = json_decode($ret['data'], true);
        }
        return $ret;
    }
}
?>