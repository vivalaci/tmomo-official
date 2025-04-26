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
namespace app\plugins\signin\service;

use think\facade\Db;
use app\service\UserService;
use app\service\IntegralService;
use app\plugins\signin\service\BaseService;
use app\plugins\signin\service\QrcodeService;

/**
 * 签到 - 签到服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class SigninService
{
    /**
     * 用户签到总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function SigninTotal($where = [])
    {
        return (int) Db::name('PluginsSigninQrcodeData')->where($where)->count();
    }

    /**
     * 用户签到数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function SigninList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        // 获取数据
        $data = Db::name('PluginsSigninQrcodeData')->where($where)->field($field)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, QrcodeService::DataHandle($data, $params));
    }

    /**
     * 签到
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-16
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SigninComing($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user_id',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '签到码id有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 查询今天是否已签到
        $info = self::DayComingData($params['user_id'], $params['id']);
        if(!empty($info))
        {
            return DataReturn('今日已签到、明日再来！', -1);
        }

        // 签到码
        $qrcode = Db::name('PluginsSigninQrcode')->where(['id'=>intval($params['id'])])->field('user_id,reward_master,reward_invitee')->find();

        // 签到数据
        $data = [
            'qrcode_id' => intval($params['id']),
            'user_id'   => intval($params['user_id']),
            'ymd'       => date('Ymd'),
            'add_time'  => time(),
        ];

        // 获取可得到的奖励积分
        $res = self::ComingIntegralCompute($data['qrcode_id'], $data['user_id']);
        if($res['code'] != 0)
        {
            return $res;
        }
        $data['integral'] = $res['data']['reward_invitee'];

        // 开启事务
        Db::startTrans();

        // 捕获异常
        try {
            // 添加签到数据
            if(Db::name('PluginsSigninQrcodeData')->insertGetId($data) <= 0)
            {
                throw new \Exception('签到失败、请稍后再试！');
            }

            // 受邀人用户积分增加
            self::UserIntegralInc($data['user_id'], $data['integral']);

            // 邀请人积分增加
            if(!empty($res['data']['user_id']) && !empty($res['data']['reward_master']))
            {
                self::UserIntegralInc($res['data']['user_id'], $res['data']['reward_master']);
            }

            // 完成
            Db::commit();
            return DataReturn('签到成功', 0, $data['integral']);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 用户积分增加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-21
     * @desc    description
     * @param   [int]          $user_id         [用户id]
     * @param   [int]          $integral        [积分]
     * @param   [boolean]      $is_login_record [是否需要更新用户当前session]
     */
    public static function UserIntegralInc($user_id, $integral, $is_login_record = false)
    {
        // 用户积分增加
        $user_integral = Db::name('User')->where(['id'=>$user_id])->value('integral');
        if(!Db::name('User')->where(['id'=>$user_id])->inc('integral', $integral)->update())
        {
            throw new \Exception('用户积分增加失败');
        }

        // 积分日志
        $res = IntegralService::UserIntegralLogAdd($user_id, $user_integral, $integral, '签到奖励', 1);
        if(!$res)
        {
            throw new \Exception('积分日志记录失败');
        }

        // 当前登录用户
        $user = UserService::LoginUserInfo();
        if(!empty($user) && $user['id'] == $user_id)
        {
            // 更新用户登录缓存数据
            UserService::UserLoginRecord($user_id);
        }
    }

    /**
     * 签到积分计算
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-16
     * @desc    description
     * @param   [int]          $qrcode_id [签到码id]
     * @param   [int]          $user_id   [用户id]
     */
    public static function ComingIntegralCompute($qrcode_id, $user_id)
    {
        // 获取签到数据
        $ret = QrcodeService::QrcodeList(['where'=>['id'=>$qrcode_id, 'is_enable'=>1]]);
        if(empty($ret['data']) || empty($ret['data'][0]))
        {
            return DataReturn('签到码数据有误', -1);
        }
        $qrcode = $ret['data'][0];

        // 签到赠送积分
        $reward_invitee = $qrcode['reward_invitee'];

        // 是否翻倍奖励
        if(!empty($qrcode['continuous_rules']) && is_array($qrcode['continuous_rules']))
        {
            // 获取配置规则最大连续天数值
            $max_number = max(array_column($qrcode['continuous_rules'], 'number'));

            // 获取连续天数计算
            if(!empty($max_number))
            {
                $number = self::ContinuousNumberCompute($qrcode_id, $user_id, $max_number);
                if($number > 0)
                {
                    // 规则从小到大排序并计算
                    $rules = [];
                    $continuous_rules = self::RulesQuickSort($qrcode['continuous_rules']);
                    foreach($continuous_rules as $k=>$v)
                    {
                        if($number >= $v['number'])
                        {
                            // 下一个索引不存在或者大于下一个索引的值则继续匹配
                            // 小于则无匹配项
                            if(!array_key_exists($k+1, $continuous_rules) || $number < $continuous_rules[$k+1]['number'])
                            {
                                $rules = $v;
                                break;
                            }
                        }
                    }

                    // 是否配置翻倍值
                    if(!empty($rules) && !empty($rules['value']))
                    {
                        $reward_invitee += intval($reward_invitee*$rules['value']);
                    }
                }
            }
        }

        // 指定时间内签到奖励
        if(!empty($qrcode['specified_time_reward']) && !empty($qrcode['specified_time_reward']['time_start']) && !empty($qrcode['specified_time_reward']['time_end']) && !empty($qrcode['specified_time_reward']['value']))
        {
            $time = time();
            $date = date('Y-m-d');
            $time_start = strtotime($date.' '.$qrcode['specified_time_reward']['time_start']);
            $time_end = strtotime($date.' '.$qrcode['specified_time_reward']['time_end']);
            if($time >= $time_start && $time <= $time_end)
            {
                $reward_invitee += intval($qrcode['specified_time_reward']['value']);
            }
        }

        // 今日发放是否已经超过限制
        if(!empty($qrcode['day_number_limit']))
        {
            $count = Db::name('PluginsSigninQrcodeData')->where(['qrcode_id'=>$qrcode_id, 'ymd'=>date('Ymd')])->sum('integral');
            if($count+$reward_invitee > $qrcode['day_number_limit'])
            {
                return DataReturn('今日发放积分已超过上限、明日再来！', -1);
            }
        }

        // 总计发放是否已经超过限制
        if(!empty($qrcode['max_number_limit']))
        {
            $count = Db::name('PluginsSigninQrcodeData')->where(['qrcode_id'=>$qrcode_id])->sum('integral');
            if($count+$reward_invitee > $qrcode['max_number_limit'])
            {
                return DataReturn('总计发放积分已超过上限！', -1);
            }
        }

        return DataReturn('success', 0, [
            'user_id'           => $qrcode['user_id'],
            'reward_master'     => $qrcode['reward_master'],
            'reward_invitee'    => $reward_invitee,
        ]);
    }

    /**
     * 联系规则排序处理、防止添加的顺序不是标准的
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-16
     * @desc    description
     * @param   [array]          $data [排序规则]
     */
    public static function RulesQuickSort($data)
    {
        if(!empty($data) && is_array($data))
        {
            $len = count($data);
            if($len <= 1)
            {
                return $data;
            }

            $base = $data[0];
            $left_array = [];
            $right_array = [];
            for($i=1; $i<$len; $i++)
            {
                if($base['number'] > $data[$i]['number'])
                {
                    $left_array[] = $data[$i];
                } else {
                    $right_array[] = $data[$i];
                }
            }
            if(!empty($left_array))
            {
                $left_array = self::RulesQuickSort($left_array);
            }
            if(!empty($right_array))
            {
                $right_array = self::RulesQuickSort($right_array);
            }

            return array_merge($left_array, array($base), $right_array);
        }
        return $data;
    }

    /**
     * 连续签到天数计算
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-16
     * @desc    description
     * @param   [int]          $qrcode_id [签到码id]
     * @param   [int]          $user_id   [用户id]
     * @param   [int]          $max_number[最大数据条数]
     */
    public static function ContinuousNumberCompute($qrcode_id, $user_id, $max_number)
    {
        $number = 0;
        $data = Db::name('PluginsSigninQrcodeData')->where(['qrcode_id' => $qrcode_id, 'user_id' => $user_id])->order('add_time desc')->limit($max_number)->column('ymd');
        if(!empty($data))
        {
            // 根据最大值生成日期计算
            for($i=1; $i<=$max_number; $i++)
            {
                if(in_array(date('Ymd', time()-$i*3600*24), $data))
                {
                    $number++;
                } else {
                    break;
                }
            }
        }
        return $number;
    }

    /**
     * 获取当日签到数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-16
     * @desc    description
     * @param   [int]          $user_id   [用户id]
     * @param   [int]          $qrcode_id [签到码]
     */
    public static function DayComingData($user_id, $qrcode_id = 0)
    {
        // 是否开启按照签到码限制签到
        $base_where = self::ComingIsQrcodeLimit($qrcode_id);

        // 签到数据查询
        $where = [
            ['ymd', '=', date('Ymd')],
            ['user_id', '=', intval($user_id)],
        ];
        return Db::name('PluginsSigninQrcodeData')->where(array_merge($base_where, $where))->find();
    }

    /**
     * 获取团队签到数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-17
     * @desc    description
     * @param   [int]          $qrcode_id [签到码]
     * @param   [int]          $user_id   [用户id]
     */
    public static function TeamSigninData($qrcode_id, $user_id)
    {
        // 初始化返回数据格式
        $result = [
            'total' => 0,
            'day'   => 0,
        ];

        // 获取今日数据统计
        $result['day'] = Db::name('PluginsSigninQrcodeData')->where(['qrcode_id'=>$qrcode_id, 'ymd'=>date('Ymd')])->count();

        // 总统计
        $result['total'] = Db::name('PluginsSigninQrcodeData')->where(['qrcode_id'=>$qrcode_id])->count();
        
        return $result;
    }

    /**
     * 按照签到码进行限制签到状态
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-20
     * @desc    description
     * @param   [int]          $qrcode_id [签到码]
     */
    public static function ComingIsQrcodeLimit($qrcode_id)
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();

        // 是否开启按照签到码限制签到
        return (!empty($base['data']) && isset($base['data']['is_qrcode_limit']) && $base['data']['is_qrcode_limit'] == 1) ? [['qrcode_id', '=', intval($qrcode_id)]] : [];
    }

    /**
     * 获取用户签到数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-17
     * @desc    description
     * @param   [int]          $user_id   [用户id]
     * @param   [int]          $qrcode_id [签到码]
     */
    public static function UserSigninData($user_id, $qrcode_id = 0)
    {
        // 初始化返回数据格式
        $result = [
            'total'        => 0,
            'integral'     => 0,
            'current_day'  => 0,
            'history_day'  => [],
        ];

        // 是否开启按照签到码限制签到
        $base_where = self::ComingIsQrcodeLimit($qrcode_id);

        // 获取签到数据
        $where = [
            ['user_id', '=', $user_id],
            ['add_time', '>=', strtotime(date('Y-m-01 00:00:00'))],
            ['add_time', '<=', time()],
        ];
        $data = Db::name('PluginsSigninQrcodeData')->where(array_merge($base_where, $where))->select()->toArray();
        if(!empty($data))
        {
            foreach($data as $v)
            {
                $result['history_day'][] = date('j', $v['add_time']);
            }
        }
        // 今日是否已签到
        if(!empty($result['history_day']) && in_array(date('j'), $result['history_day']))
        {
            $result['current_day'] = 1;
        }

        // 总统计
        $where = [
            ['user_id', '=', $user_id],
        ];
        $result['total'] = Db::name('PluginsSigninQrcodeData')->where(array_merge($base_where, $where))->count();

        // 获取今日数据统计
        $where = [
            ['user_id', '=', $user_id],
            ['ymd', '=', date('Ymd')],
        ];
        $result['integral'] = Db::name('PluginsSigninQrcodeData')->where(array_merge($base_where, $where))->value('integral');

        return $result;
    }
}
?>