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
namespace app\plugins\intellectstools\service;

use think\facade\Db;
use app\service\ResourcesService;
use app\service\PayLogService;
use app\service\PaymentService;
use app\service\OrderService;
use app\service\AppMiniUserService;
use app\plugins\intellectstools\service\BaseService;

/**
 * 智能工具箱 - 头条支付分账服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class ToutiaoSettlementService
{
    // 周期时间、默认4天
    public static $time_value = 60*60*24*4;

    /**
     * 列表数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-01
     * @desc    description
     * @param   [array]          $data   [数据列表]
     * @param   [array]          $params [输入参数]
     */
    public static function PayLogListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            $data = PayLogService::PayLogListHandle($data, $params);
            $status_list = BaseService::$toutiaosettlement_status_list;
            foreach($data as &$v)
            {
                // 分账状态
                $v['status_name'] = array_key_exists($v['status'], $status_list) ? $status_list[$v['status']]['name'] : '';

                // 是否可以分账
                $v['is_settlement'] = (isset($v['status']) && $v['status'] != 1 && isset($v['is_push']) && $v['is_push'] == 1 && !empty($v['push_time']) && strtotime($v['push_time'])+self::$time_value < time()) ? 1 : 0;

                // 推送时间
                if(array_key_exists('push_time', $v))
                {
                    $v['push_time'] = empty($v['push_time']) ? '' : date('Y-m-d H:i:s', $v['push_time']);
                }
            }
        }
        return $data;
    }

    /**
     * 分账结算
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function Settlement($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'ids',
                'error_msg'         => '请选择支付日志',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 支付方式
        $payment = PaymentService::PaymentData(['where'=>['payment'=>'Toutiao']]);
        if(empty($payment))
        {
            return DataReturn(MyLang('payment_method_error_tips'), -1);
        }

        // 数据列表
        $where = [
            ['status', '=', 1],
            ['payment', '=', 'Toutiao'],
            ['id', 'in', $params['ids']],
            ['pay_time', '>', 0],
        ];
        $data = Db::name('PayLog')->where($where)->select()->toArray();
        if(empty($data))
        {
            return DataReturn(MyLang('no_data'), -1);
        }

        // 开始操作
        $sucs = 0;
        $fail = 0;
        foreach($data as $v)
        {
            // 是否满足可分账时间
            if($v['pay_time']+self::$time_value > time())
            {
                continue;
            }

            // 分账操作
            $pay_params = [
                'out_settle_no'     => $v['log_no'].$v['id'],
                'order_no'          => $v['log_no'],
            ];
            $ret = (new \payment\Toutiao($payment['config']))->Settlement($pay_params);
            $settlement_data = [
                'pay_id'    => $v['id'],
                'status'    => 2,
            ];
            if($ret['code'] == 0)
            {
                if(isset($ret['data']['status']) && $ret['data']['status'] == 0)
                {
                    $settlement_data['status'] = 1;
                    $settlement_data['reason'] = '';
                } else {
                    $settlement_data['reason'] = $ret['data']['error'];
                }
                $settlement_data['response_data'] = (!empty($ret['data']['data']) && is_array($ret['data']['data'])) ? json_encode($ret['data']['data'], JSON_UNESCAPED_UNICODE) : $ret['data']['data'];
            } else {
                $settlement_data['reason'] = $ret['msg'];
            }
            $info = Db::name('PluginsIntellectstoolsToutiaosettlement')->where(['pay_id'=>$v['id']])->find();
            if(empty($info))
            {
                $settlement_data['add_time'] = time();
                Db::name('PluginsIntellectstoolsToutiaosettlement')->insertGetId($settlement_data);
            } else {
                $settlement_data['upd_time'] = time();
                Db::name('PluginsIntellectstoolsToutiaosettlement')->where(['id'=>$info['id']])->update($settlement_data);
            }
            if($settlement_data['status'] == 1)
            {
                $sucs++;
            } else {
                $fail++;
            }
        }
        if($fail == 0  && $sucs == 0)
        {
            return DataReturn(MyLang('no_data'), -1);
        }
        if($fail == 0)
        {
            return DataReturn(MyLang('operate_success'), 0);
        }
        if($sucs == 0)
        {
            return DataReturn('操作失败、请刷新查看错误！', -1);
        }
        return DataReturn('成功('.$sucs.'), 失败('.$fail.')', 0);
    }

    /**
     * 订单推送
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function OrderPushHandle($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '订单日志id日志',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 获取支付日志用户id
        $where = [
            ['id', '=', intval($params['id'])],
            ['status', '=', 1],
        ];
        $pay_log = Db::name('PayLog')->where($where)->find();
        if(empty($pay_log))
        {
            return DataReturn('没有相关支付日志信息', -1);
        }

        // 订单用户
        $user = Db::name('User')->where(['id'=>$pay_log['user_id']])->find();
        if(empty($user))
        {
            return DataReturn('支付单用户无效', -1);
        }
        $where = [
            ['user_id', '=', $pay_log['user_id']],
            ['toutiao_openid', '<>', ''],
        ];
        $user_platform = Db::name('UserPlatform')->where($where)->find();
        if(empty($user_platform) || empty($user_platform['toutiao_openid']))
        {
            return DataReturn('用户openid为空', -1);
        }

        // 当前结算数据
        $where = [
            ['pay_id', '=', $pay_log['id']],
        ];
        $info = Db::name('PluginsIntellectstoolsToutiaosettlement')->where($where)->find();
        if(empty($info))
        {
            $info = [
                'pay_id'    => $pay_log['id'],
                'status'    => 0,
                'is_push'   => 0,
                'add_time'  => time(),
            ];
            $info['id'] = Db::name('PluginsIntellectstoolsToutiaosettlement')->insertGetId($info);
            if($info['id'] <= 0)
            {
                return DataReturn('推送结算单添加失败', -1);
            }
        }

        // access_token
        $config = [
            'appid'     => AppMiniUserService::AppMiniConfig('common_app_mini_toutiao_appid'),
            'secret'    => AppMiniUserService::AppMiniConfig('common_app_mini_toutiao_appsecret'),
        ];
        if(empty($config['appid']) || empty($config['secret']))
        {
            return DataReturn('头条小程序密钥未配置', -1);
        }
        $access_token = (new \base\Toutiao($config))->GetMiniAccessToken();
        if($access_token === false)
        {
            return DataReturn('access_token获取失败', -1);
        }

        // 普通订单
        // 订单数据
        $parameters = [
            'access_token'  => $access_token,
            'app_name'      => 'douyin',
            'open_id'       => $user_platform['toutiao_openid'],
            'order_detail'  => [],
            'order_status'  => 4,
            'order_type'    => 0,
            'update_time'   => time(),
        ];
        $order_detail = [
            'order_id'      => $pay_log['log_no'],
            'create_time'   => (int) $pay_log['add_time'],
            'status'        => '340',
            'amount'        => 1,
            'total_price'   => (int) (($pay_log['pay_price']*1000)/10),
            'detail_url'    => 'pages/index/index',
            'item_list'     => [
                [
                    'item_code' => '1000',
                    'img'       => ResourcesService::AttachmentPathViewHandle(MyC('home_site_logo_square', MyC('home_site_logo'), true)),
                    'title'     => '商城订单',
                    'amount'    => 1,
                    'price'     => (int) (($pay_log['pay_price']*1000)/10),
                ]
            ],
        ];
        $parameters['order_detail'] = json_encode($order_detail, JSON_UNESCAPED_UNICODE);

        // 推送数据
        $url = 'https://developer.toutiao.com/api/apps/order/v2/push';
        $res = CurlPost($url, $parameters, 1);
        if($res['code'] != 0)
        {
            return $res;
        }
        $result = json_decode($res['data'], true);
        if(isset($result['err_code']) && $result['err_code'] == 0)
        {
            Db::name('PluginsIntellectstoolsToutiaosettlement')->where(['id'=>$info['id']])->update([
                'is_push'       => 1,
                'reason'        => '',
                'response_data' => empty($res['data']) ? '' : $res['data'],
                'push_time'     => time(),
                'upd_time'      => time(),
            ]);
            return DataReturn(MyLang('push_success'), 0);
        }
        $msg = $result['err_msg'].'('.$result['err_code'].')';
        Db::name('PluginsIntellectstoolsToutiaosettlement')->where(['id'=>$info['id']])->update([
            'is_push'       => 0,
            'reason'        => $msg,
            'response_data' => empty($res['data']) ? '' : $res['data'],
            'upd_time'      => time(),
        ]);
        return DataReturn($msg, -1);        
    }
}
?>