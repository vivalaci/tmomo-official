<?php

namespace Qcloud\Sms;

use Qcloud\Sms\SmsSenderUtil;

/**
 * 单发短信类
 *
 */
class SmsSingleSender
{
    private $url;
    private $appid;
    private $appkey;
    private $util;
    private $data;
    private $wholeUrl;

    /**
     * 构造函数
     *
     * @param string $appid  sdkappid
     * @param string $appkey sdkappid对应的appkey
     */
    public function __construct($appid, $appkey)
    {
        $this->url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms";
        $this->appid =  $appid;
        $this->appkey = $appkey;
        $this->util = new SmsSenderUtil();
    }

    /**
     * 普通单发
     *
     * 普通单发需明确指定内容，如果有多个签名，请在内容中以【】的方式添加到信息内容中，否则系统将使用默认签名。
     *
     * @param int    $type        短信类型，0 为普通短信，1 营销短信
     * @param string $nationCode  国家码，如 86 为中国
     * @param string $phoneNumber 不带国家码的手机号
     * @param string $msg         信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param string $extend      扩展码，可填空串
     * @param string $ext         服务端原样返回的参数，可填空串
     * @return string 应答json字符串，详细内容参见腾讯云协议文档
     */
    public function send($type, $nationCode, $phoneNumber, $msg, $extend = "", $ext = "")
    {
        $random = $this->util->getRandom();
        $curTime = time();
        $this->wholeUrl = $this->url . "?sdkappid=" . $this->appid . "&random=" . $random;

        // 按照协议组织 post 包体
        $this->data = new \stdClass();
        $tel = new \stdClass();
        $tel->nationcode = "".$nationCode;
        $tel->mobile = "".$phoneNumber;

        $this->data->tel = $tel;
        $this->data->type = (int)$type;
        $this->data->msg = $msg;
        $this->data->sig = hash("sha256",
            "appkey=".$this->appkey."&random=".$random."&time="
            .$curTime."&mobile=".$phoneNumber, FALSE);
        $this->data->time = $curTime;
        $this->data->extend = $extend;
        $this->data->ext = $ext;
    }

    /**
     * 指定模板单发
     *
     * @param string $nationCode  国家码，如 86 为中国
     * @param string $phoneNumber 不带国家码的手机号
     * @param int    $templId     模板 id
     * @param array  $params      模板参数列表，如模板 {1}...{2}...{3}，那么需要带三个参数
     * @param string $sign        签名，如果填空串，系统会使用默认签名
     * @param string $extend      扩展码，可填空串
     * @param string $ext         服务端原样返回的参数，可填空串
     * @return string 应答json字符串，详细内容参见腾讯云协议文档
     */
    public function sendWithParams($nationCode, $phoneNumber, $templId = 0, $params = [],
        $sign = "", $extend = "", $ext = "")
    {
        $random = $this->util->getRandom();
        $curTime = time();
        $this->wholeUrl = $this->url . "?sdkappid=" . $this->appid . "&random=" . $random;

        // 按照协议组织 post 包体
        $this->data = new \stdClass();
        $tel = new \stdClass();
        $tel->nationcode = "".$nationCode;
        $tel->mobile = "".$phoneNumber;

        $this->data->tel = $tel;
        $this->data->sig = $this->util->calculateSigForTempl($this->appkey, $random,
            $curTime, $phoneNumber);
        $this->data->tpl_id = $templId;
        $this->data->params = $params;
        $this->data->sign = $sign;
        $this->data->time = $curTime;
        $this->data->extend = $extend;
        $this->data->ext = $ext;
    }

    /**
     * 请求接口
     */
    public function sendRequestUrl()
    {
        return $this->wholeUrl;
    }

    /**
     * 请求数据
     */
    public function sendRequestData()
    {
        return $this->data;
    }

    /**
     * 发送处理
     */
    public function sendHandle()
    {
        return $this->util->sendCurlPost($this->wholeUrl, $this->data);
    }
}
