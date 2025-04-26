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
namespace app\plugins\distribution\service;

use think\facade\Db;
use app\service\ResourcesService;
use app\service\SystemBaseService;
use app\plugins\distribution\service\BusinessService;

/**
 * 分销 - 数据统计服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-06-12T01:13:44+0800
 */
class StatisticalService
{
    // 近3天,近7天,近15天,近30天
    public static $nearly_three_days;
    public static $nearly_seven_days;
    public static $nearly_fifteen_days;
    public static $nearly_thirty_days;

    // 昨天
    public static $yesterday_time_start;
    public static $yesterday_time_end;

    // 今天
    public static $today_time_start;
    public static $today_time_end;

    // 近365天
    public static $year_time_start;
    public static $year_time_end;

    // 近180天
    public static $half_year_time_start;
    public static $half_year_time_end;

    // 近30天
    public static $thirty_time_start;
    public static $thirty_time_end;

    // 近15天
    public static $fifteen_time_start;
    public static $fifteen_time_end;

    // 近7天
    public static $seven_time_start;
    public static $seven_time_end;

    // 近3天
    public static $three_time_start;
    public static $three_time_end;

    // 上月
    public static $last_month_time_start;
    public static $last_month_time_end;

    // 当月
    public static $this_month_time_start;
    public static $this_month_time_end;

    // 去年
    public static $this_year_time_start;
    public static $this_year_time_end;

    // 今年
    public static $last_year_time_start;
    public static $last_year_time_end;

    /**
     * 初始化
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-12T01:13:44+0800
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function Init($params = [])
    {
        static $object = null;
        if(!is_object($object))
        {
            // 初始化标记对象，避免重复初始化
            $object = (object) [];

            // 昨天日期
            self::$yesterday_time_start = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
            self::$yesterday_time_end = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));

            // 今天日期
            self::$today_time_start = strtotime(date('Y-m-d 00:00:00'));
            self::$today_time_end = time();

            // 近365天日期
            self::$year_time_start = strtotime(date('Y-m-d 00:00:00', strtotime('-365 day')));
            self::$year_time_end = time();

            // 近180天日期
            self::$half_year_time_start = strtotime(date('Y-m-d 00:00:00', strtotime('-180 day')));
            self::$half_year_time_end = time();

            // 近30天日期
            self::$thirty_time_start = strtotime(date('Y-m-d 00:00:00', strtotime('-29 day')));
            self::$thirty_time_end = time();

            // 近15天日期
            self::$fifteen_time_start = strtotime(date('Y-m-d 00:00:00', strtotime('-14 day')));
            self::$fifteen_time_end = time();

            // 近7天日期
            self::$seven_time_start = strtotime(date('Y-m-d 00:00:00', strtotime('-6 day')));
            self::$seven_time_end = time();

            // 近3天日期
            self::$three_time_start = strtotime(date('Y-m-d 00:00:00', strtotime('-2 day')));
            self::$three_time_end = time();

            // 上月
            self::$last_month_time_start = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month', strtotime(date('Y-m', time())))));
            self::$last_month_time_end = strtotime(date('Y-m-t 23:59:59', strtotime('-1 month', strtotime(date('Y-m', time())))));

            // 当月
            self::$this_month_time_start = strtotime(date('Y-m-01 00:00:00'));
            self::$this_month_time_end = time();

            // 去年
            self::$last_year_time_start = strtotime(date('Y-01-01 00:00:00', strtotime('-1 year', strtotime(date('Y-m', time())))));
            self::$last_year_time_end = strtotime(date('Y-12-31 23:59:59', strtotime('-1 year', strtotime(date('Y-m', time())))));

            // 今年
            self::$this_year_time_start = strtotime(date('Y-01-01 00:00:00'));
            self::$this_year_time_end = time();


            // 近3天,近7天,近15天,近30天
            $nearly_all = [
                3   => 'nearly_three_days',
                7   => 'nearly_seven_days',
                15  => 'nearly_fifteen_days',
                30  => 'nearly_thirty_days',
            ];
            foreach($nearly_all as $day=>$name)
            {
                $date = [];
                $time = time();
                for($i=0; $i<$day; $i++)
                {
                    $date[] = [
                        'start_time'    => strtotime(date('Y-m-d 00:00:00', time()-$i*3600*24)),
                        'end_time'      => strtotime(date('Y-m-d 23:59:59', time()-$i*3600*24)),
                        'name'          => date('Y-m-d', time()-$i*3600*24),
                    ];
                }
                self::${$name} = array_reverse($date);
            }
        }
    }

    /**
     * 获取时间列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-31
     * @desc    description
     * @param   [array]           $params [数据参数]
     */
    public static function DateTimeList($params = [])
    {
        // 初始化
        self::Init($params);

        // 统计时间配置列表
        return [
            '1-day' => [
                'key'   => '1-day',
                'name'  => '今日',
                'start' => date('Y-m-d H:i:s', StatisticalService::$today_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$today_time_end),
            ],
            '2-day' => [
                'key'   => '2-day',
                'name'  => '昨日',
                'start' => date('Y-m-d H:i:s', StatisticalService::$yesterday_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$yesterday_time_end),
            ],
            '3-day' => [
                'key'   => '3-day',
                'name'  => '近3天',
                'start' => date('Y-m-d H:i:s', StatisticalService::$three_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$three_time_end),
            ],
            '7-day' => [
                'key'   => '7-day',
                'name'  => '近7天',
                'start' => date('Y-m-d H:i:s', StatisticalService::$seven_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$seven_time_end),
            ],
            '15-day' => [
                'key'   => '15-day',
                'name'  => '近15天',
                'start' => date('Y-m-d H:i:s', StatisticalService::$fifteen_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$fifteen_time_end),
            ],
            '30-day' => [
                'key'   => '30-day',
                'name'  => '近30天',
                'start' => date('Y-m-d H:i:s', StatisticalService::$thirty_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$thirty_time_end),
            ],
            '180-day' => [
                'key'   => '180-day',
                'name'  => '近半年',
                'start' => date('Y-m-d H:i:s', StatisticalService::$half_year_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$half_year_time_end),
            ],
            '365-day' => [
                'key'   => '365-day',
                'name'  => '近1年',
                'start' => date('Y-m-d H:i:s', StatisticalService::$year_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$year_time_end),
            ],
            'this-month' => [
                'key'   => 'this-month',
                'name'  => '当月',
                'start' => date('Y-m-d H:i:s', StatisticalService::$this_month_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$this_month_time_end),
            ],
            'last-month' => [
                'key'   => 'last-month',
                'name'  => '上月',
                'start' => date('Y-m-d H:i:s', StatisticalService::$last_month_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$last_month_time_end),
            ],
            'this-year' => [
                'key'   => 'this-year',
                'name'  => '今年',
                'start' => date('Y-m-d H:i:s', StatisticalService::$this_year_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$this_year_time_end),
            ],
            'last-year' => [
                'key'   => 'last-year',
                'name'  => '去年',
                'start' => date('Y-m-d H:i:s', StatisticalService::$last_year_time_start),
                'end'   => date('Y-m-d H:i:s', StatisticalService::$last_year_time_end),
            ],
        ];
    }

    /**
     * 区间时间创建
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-30
     * @desc    description
     * @param   [int]          $start [起始时间]
     * @param   [int]          $end   [结束时间]
     */
    public static function DayCreate($start, $end)
    {
        $data = [];
        while(true)
        {
            // 计算时间条件
            $temp_end = strtotime('+1 day', $start);

            // 最大时间减1秒，条件使用 start >= ? && end <= ?
            // start 2021-01-01 00:00:00 , end 2021-01-01 23:59:58
            $data[] = [
                'start' => $start,
                'end'   => $temp_end-1,
                'title' => date('Y-m-d', $start),
                'date'  => date('Y-m-d H:i:s', $start).' - '.date('Y-m-d H:i:s', $temp_end-1),
            ];

            // 结束跳出循环
            if($temp_end >= $end)
            {
                // 结束使用最大时间替代计算的最后一个最大时间
                $count = count($data)-1;
                $data[$count]['end'] = $end;
                $data[$count]['date'] = date('Y-m-d H:i:s', $data[$count]['start']).' - '.date('Y-m-d H:i:s', $end);
                break;
            }
            $start = $temp_end;
        }
        return $data;
    }

    /**
     * 收益趋势
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2019-06-12T01:13:44+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserProfitFifteenTodayTotal($params = [])
    {
        // 初始化
        self::Init($params);

        // 循环获取统计数据
        $data = [];
        $name_arr = [];
        $date = self::DayCreate($params['start'], $params['end']);
        foreach($date as $day)
        {
            // 当前日期名称
            $name_arr[] = $day['title'];

            // 获取收益金额
            $where = [
                ['add_time', '>=', $day['start']],
                ['add_time', '<=', $day['end']],
                ['status', '<=', 2],
            ];
            if(!empty($params['user']))
            {
                $where[] = ['user_id', '=', $params['user']['id']];
            }
            $data[] = PriceNumberFormat(Db::name('PluginsDistributionProfitLog')->where($where)->sum('profit_price'));
        }

        // 数据组装
        $result = [
            'name_arr'  => $name_arr,
            'data'      => $data,
        ];
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 推广用户趋势
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2019-06-12T01:13:44+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserExtensionFifteenTodayTotal($params = [])
    {
        // 初始化
        self::Init($params);

        // 循环获取统计数据
        $data = [];
        $name_arr = [];
        $date = self::DayCreate($params['start'], $params['end']);
        foreach($date as $day)
        {
            // 当前日期名称
            $name_arr[] = $day['title'];

            // 获取用户总数
            $where = [
                ['add_time', '>=', $day['start']],
                ['add_time', '<=', $day['end']],
            ];
            if(!empty($params['user']))
            {
                $where[] = ['referrer', '=', $params['user']['id']];
            } else {
                $where[] = ['referrer', '>', 0];
            }
            $data[] = Db::name('User')->where($where)->count('id');
        }

        // 数据组装
        $result = [
            'name_arr'  => $name_arr,
            'data'      => $data,
        ];
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 用户收益总额
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-05T22:08:34+0800
     * @param    [int]                   $status    [结算状态（0待生效, 1待结算, 2已结算, 3已失效）]
     * @param    [int]                   $user_id   [用户id]
     * @param    [int]                   $field     [指定统计字段]
     */
    public static function UserProfitPriceTotal($status = 0, $user_id = null, $field = 'profit_price')
    {
        $where = [
            ['status', '=', $status],
        ];
        if(!empty($user_id))
        {
            $where[] = ['user_id', '=', intval($user_id)];
        }
        return PriceNumberFormat(Db::name('PluginsDistributionProfitLog')->where($where)->sum($field));
    }

    /**
     * 自提订单统计总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-05T22:08:34+0800
     * @param    [int]      $user_id [用户id]
     * @param    [int]      $status  [状态（0待处理, 1已处理）]
     */
    public static function ExtractionStatusTotal($user_id, $status = 0)
    {
        $where = [
            'o.status'  => [2,3,4],
            'po.status' => $status,
        ];
        if(!empty($user_id))
        {
            $where['po.user_id'] = intval($user_id);
        }
        return (int) Db::name('PluginsDistributionUserSelfExtractionOrder')->alias('po')->join('order o', 'o.id=po.order_id')->where($where)->count();
    }

    /**
     * 统计数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function BaseData($params = [])
    {
        // 基础条件
        $map = BusinessService::BaseDataMap($params);
        // 新增客户总数
        $order_new_user_count = BusinessService::BaseDataOrderNewUserCountTotal($map['order_where'], $map['order_new_user_not_front_where']);
        // 新增客户-有效（以日期+用户id分组然后获取总数，然后筛选数量及总价）
        $order_new_user_valid_count = BusinessService::BaseDataOrderNewValidUserCountTotal($map['order_where'], $map['order_new_user_not_front_where']);
        // 新增客户-需复购客户（以日期+用户id分组然后获取总数，然后筛选数量及总价）
        $order_new_user_need_repurchase_count = BusinessService::BaseDataOrderNewValidUserNeedRepurchaseCountTotal($map['order_where'], $map['order_new_user_not_front_where']);

        // 新增客户总额GMV
        $order_new_user_total_price = BusinessService::BaseDataOrderNewUserPriceGMVSummaryTotal($map['order_where'], $map['order_new_user_not_front_where']);

        // 订单总数
        $order_user_count = BusinessService::BaseDataOrderUserCountTotal($map['order_where']);

        // 订单总额GMV
        $order_user_total_price = BusinessService::BaseDataOrderUserPriceGMVSummaryTotal($map['order_where']);

        return [
            'order_new_user_count'                  => $order_new_user_count,
            'order_new_user_valid_count'            => $order_new_user_valid_count,
            'order_new_user_need_repurchase_count'  => $order_new_user_need_repurchase_count,
            'order_new_user_total_price'            => PriceNumberFormat($order_new_user_total_price),
            'order_user_count'                      => $order_user_count,
            'order_user_total_price'                => PriceNumberFormat($order_user_total_price),
        ];
    }

    /**
     * 收益统计数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ProfitData($params = [])
    {
        $stay_price     = 0;
        $vaild_price    = 0;
        $already_price  = 0;
        $total_price    = 0;
        if(!empty($params['user']))
        {
            $stay_price = self::UserProfitPriceTotal(0, $params['user']['id']);
            $vaild_price = self::UserProfitPriceTotal(1, $params['user']['id']);
            $already_price = self::UserProfitPriceTotal(2, $params['user']['id']);
            $total_price = PriceNumberFormat($stay_price+$vaild_price+$already_price);
        }
        return [
            'stay_price'    => $stay_price,
            'vaild_price'   => $vaild_price,
            'already_price' => $already_price,
            'total_price'   => $total_price,
        ];
    }

    /**
     * 获取推广用户数据
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-12-08T00:52:25+0800
     * @desc     description
     * @param    [array]                  $params [输入参数]
     */
    public static function UserPromotionTotalData($params = 0)
    {
        $map = BusinessService::UserPromotionMap($params);
        return [
            'user_count'               => BusinessService::UserPromotionAllTotal($map['user_where']),
            'valid_user_count'         => BusinessService::UserPromotionValidTotal($map['valid_where']),
            'not_consumed_user_count'  => BusinessService::UserPromotionNotConsumedTotal($map['valid_where'], $map['not_where']),
        ];
    }

    /**
     * 基础统计数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function StatsData($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'type',
                'error_msg'         => '类型为空',
            ],
        ];
        if(in_array($params['type'], ['profit', 'user']))
        {
            $p[] = [
                'checked_type'      => 'empty',
                'key_name'          => 'start',
                'error_msg'         => '开始时间不能为空',
            ];
            $p[] = [
                'checked_type'      => 'empty',
                'key_name'          => 'end',
                'error_msg'         => '结束时间不能为空',
            ];
        }
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 时间处理
        if(!empty($params['start']))
        {
            $params['start'] = strtotime($params['start']);
        }
        if(!empty($params['end']))
        {
            $params['end'] = strtotime($params['end']);
        }

        // 是否是否合法
        if(!empty($params['start']) && !empty($params['end']) && $params['end'] < $params['start'])
        {
            return DataReturn('开始时间不能小于结束时间', -1);
        }

        // 根据类型处理数据
        switch($params['type'])
        {
            // 基础数据
            case 'base' :
                $base_data = self::BaseData($params);
                $user_promotion_data = self::UserPromotionTotalData($params);
                $ret = DataReturn('success', 0, [
                    'base_data'            => $base_data,
                    'user_promotion_data'  => $user_promotion_data,
                ]);
                break;

            // 收益走势
            case 'profit' :
                $ret = self::UserProfitFifteenTodayTotal($params);
                break;

            // 推广用户走势
            case 'user' :
                $ret = self::UserExtensionFifteenTodayTotal($params);
                break;

            default :
                $ret = DataReturn('类型有误', -1);
        }
        return $ret;
    }

    /**
     * 手机端统计数据 - 用户推广数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-11
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AppStatssUserPromotionDataList($params = [])
    {
        // 时间处理
        if(!empty($params['start']))
        {
            $params['start'] = strtotime($params['start']);
        }
        if(!empty($params['end']))
        {
            $params['end'] = strtotime($params['end']);
        }

        // 用户推广统计
        $user_promotion_data = self::UserPromotionTotalData($params);
        return [
            [
                'name'     => '已推广客户',
                'value'    => $user_promotion_data['user_count'],
                'ent'      => 'am-text-blue',
                'unit'     => '人',
                'to_page'  => '/pages/plugins/distribution/promotion-user/promotion-user?type=0',
            ],
            [
                'name'     => '已消费客户',
                'value'    => $user_promotion_data['valid_user_count'],
                'ent'      => 'am-text-green',
                'unit'     => '人',
                'to_page'  => '/pages/plugins/distribution/promotion-user/promotion-user?type=1',
            ],
            [
                'name'     => '未消费客户',
                'value'    => $user_promotion_data['not_consumed_user_count'],
                'ent'      => 'am-text-yellow',
                'unit'     => '人',
                'to_page'  => '/pages/plugins/distribution/promotion-user/promotion-user?type=2',
            ]
        ];
    }

    /**
     * 手机端统计数据 - 基础统计数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-11
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AppStatssUserBaseDataList($params = [])
    {
        // 时间处理
        if(!empty($params['start']))
        {
            $params['start'] = strtotime($params['start']);
        }
        if(!empty($params['end']))
        {
            $params['end'] = strtotime($params['end']);
        }

        // 基础统计
        $host = SystemBaseService::AttachmentHost().'/static/plugins/distribution/images/app/statistics/';
        $currency_symbol = ResourcesService::CurrencyDataSymbol();
        $base_data = self::BaseData($params);

        return [
            [
                'name'      => '新增客户',
                'value'     => $base_data['order_new_user_count'],
                'icon'      => $host.'user.png',
                'unit'      => '人',
                'to_page'   => '/pages/plugins/distribution/promotion-user/promotion-user?type=3',
                'data'      => [
                    [
                        'name'     => '有效( '.$base_data['order_new_user_valid_count'].' )',
                        'to_page'  => '/pages/plugins/distribution/promotion-user/promotion-user?type=4',
                    ],
                    [
                        'name'     => '需复购( '.$base_data['order_new_user_need_repurchase_count'].' )',
                        'to_page'  => '/pages/plugins/distribution/promotion-user/promotion-user?type=5',
                    ]
                ],
            ],
            [
                'name'      => '新增客户总GMV',
                'value'     => PriceNumberFormat($base_data['order_new_user_total_price']),
                'icon'      => $host.'user-all-count.png',
                'first'     => $currency_symbol,
                'to_page'   => '/pages/plugins/distribution/promotion-order/promotion-order?type=0',
            ],
            [
                'name'      => '订单总数',
                'value'     => $base_data['order_user_count'],
                'icon'      => $host.'order.png',
                'unit'      => '条',
                'to_page'   => '/pages/plugins/distribution/promotion-order/promotion-order?type=1',
            ],
            [
                'name'      => '订单总GMV',
                'value'     => PriceNumberFormat($base_data['order_user_total_price']),
                'icon'      => $host.'order-all-count.png',
                'first'     => $currency_symbol,
                'to_page'   => '/pages/plugins/distribution/promotion-order/promotion-order?type=2',
            ]
        ];
    }

    /**
     * 手机端统计数据 - 收益统计数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-11
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AppStatssProfitDataList($params = [])
    {
        // 时间处理
        if(!empty($params['start']))
        {
            $params['start'] = strtotime($params['start']);
        }
        if(!empty($params['end']))
        {
            $params['end'] = strtotime($params['end']);
        }

        // 返佣统计
        $profit_data = self::ProfitData($params);
        return [
            ['name' => '返佣总额', 'value' => PriceNumberFormat($profit_data['total_price']), 'ent' => 'cr-base'],
            ['name' => '待生效', 'value' => PriceNumberFormat($profit_data['stay_price']), 'ent' => 'am-text-yellow'],
            ['name' => '待结算', 'value' => PriceNumberFormat($profit_data['vaild_price']), 'ent' => 'am-text-blue'],
            ['name' => '已结算', 'value' => PriceNumberFormat($profit_data['already_price']), 'ent' => 'am-text-green']
        ];
    }
}
?>