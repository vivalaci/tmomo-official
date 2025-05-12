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
use app\service\UserService;
use app\service\PaymentService;
use app\service\ResourcesService;
use app\service\OrderService;
use app\service\OrderCurrencyService;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\ExtractionService;

/**
 * 分销 - 业务服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class BusinessService
{
    /**
     * 获取用户团队列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserTeamList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        $data = Db::name('User')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::TeamDataListHandle($data));
    }

    /**
     * 团队用户数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]          $data   [列表数据]
     * @param   [array]          $params [输入参数]
     */
    public static function TeamDataListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            $is_privacy = isset($params['is_privacy']) ? (boolean) $params['is_privacy'] : true;
            $config = empty($params['plugins_config']) ? [] : $params['plugins_config'];
            $common_platform_type = MyConst('common_platform_type');
            foreach($data as &$v)
            {
                // 用户信息处理
                $v = UserService::GetUserViewInfo($v['id'], $v, $is_privacy);

                // 当前用户下一级总数
                $referrer_count = self::UserTeamTotal(self::UserTeamWhere(['referrer_id'=>$v['id']]));
                $v['referrer_count'] = empty($referrer_count) ? '' : $referrer_count;

                // 当前用户下一级消费总数、总金额、最后一次下单时间
                $find_where = array_merge([
                    ['u.referrer', '=', $v['id']],
                    ['o.status', 'in', [2,3,4]],
                ], self::UserTeamExtUserWhere($params, 'u.'));
                $find_order_count = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($find_where)->count('o.id');
                $v['find_order_count'] = ($find_order_count <= 0) ? '' : $find_order_count;
                $find_order_total = PriceNumberFormat(Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($find_where)->sum('o.total_price'));
                $v['find_order_total'] = ($find_order_total <= 0) ? '' : $find_order_total;
                $find_order_last_time = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($find_where)->order('o.id desc')->value('o.add_time');
                $v['find_order_last_time'] = empty($find_order_last_time) ? '' : date('Y-m-d H:i:s', $find_order_last_time);

                // 订单有效总数、金额、最后一次下单时间
                $order_where = array_merge([
                    ['user_id', '=', $v['id']],
                    ['status', 'not in', [5,6]],
                ], self::UserTeamExtOrderWhere($params));
                $order_count = Db::name('Order')->where($order_where)->count();
                $v['order_count'] = ($order_count <= 0) ? '' : $order_count;
                $order_total = PriceNumberFormat(Db::name('Order')->where($order_where)->sum('total_price'));
                $v['order_total'] = ($order_total <= 0) ? '' : $order_total;
                $order_last_time = Db::name('Order')->where($order_where)->order('id desc')->value('add_time');
                $v['order_last_time'] = empty($order_last_time) ? '' : date('Y-m-d H:i:s', $order_last_time);

                // 二维码
                if(!empty($common_platform_type) && is_array($common_platform_type))
                {
                    $share_qrcode = [];
                    $path = ROOT.'public';
                    foreach($common_platform_type as $pv)
                    {
                        $url = DS.'static'.DS.'upload'.DS.'images'.DS.'plugins_distribution'.DS.'qrcode'.DS.SYSTEM_TYPE.DS.$pv['value'].DS.date('Y', $v['add_time']).DS.date('m', $v['add_time']).DS.date('d', $v['add_time']).DS.date('YmdHis', $v['add_time']).$v['id'].'.png';
                        if(file_exists($path.$url))
                        {
                            $share_qrcode[] = [
                                'url'   => ResourcesService::AttachmentPathViewHandle($url),
                                'name'  => $pv['name'],
                            ];
                        }
                    }
                }
                $v['share_qrcode'] = $share_qrcode;

                // 分销等级
                $res = BaseService::UserDistributionLevel($v['id'], $config);
                $v['distribution_auto_level_data'] = $res['data'];

                // 加入时间
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
        }
        return $data;
    }

    /**
     * 用户团队列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserTeamWhere($params = [])
    {
        $where = array_merge([
            ['is_delete_time', '=', 0],
        ], self::UserTeamExtWhere($params));

        // 关键字根据订单筛选
        if(!empty($params['keywords']))
        {
            $user_ids = Db::name('User')->where('number_code|username|nickname|mobile|email', '=', $params['keywords'])->column('id');
            if(!empty($user_ids))
            {
                $where[] = ['id', 'in', $user_ids];
            } else {
                // 无数据条件，避免搜索条件没有数据造成的错觉
                $where[] = ['id', '=', 0];
            }
        }

        // id
        if(!empty($params['id']))
        {
            $where[] = ['id', '=', intval($params['id'])];
        }

        // 指定邀请用户id
        if(!empty($params['referrer_id']))
        {
            $where[] = ['referrer', '=', intval($params['referrer_id'])];
        }

        // 用户数据
        if(!empty($params['user']) && !empty($params['user']['id']))
        {
            $where[] = ['referrer', '=', $params['user']['id']];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 性别
            if(isset($params['gender']) && $params['gender'] > -1)
            {
                $where[] = ['gender', '=', intval($params['gender'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['add_time', '<', strtotime($params['time_end'])];
            }
        }

        return $where;
    }

    /**
     * 我的团队额外条件
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-09
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UserTeamExtWhere($params = [])
    {
        // 用户id容器
        $eq_where_user_ids = [];
        $neq_where_user_ids = [];

        // 是否指定订单时间（是否反向）
        $order_time_where = self::UserTeamExtOrderWhere($params);
        if(!empty($order_time_where))
        {
            $order_time_where[] = ['status', 'not in', [5,6]];
            $user_ids = Db::name('Order')->where($order_time_where)->column('distinct user_id');
            $eq_where_user_ids = empty($user_ids) ? [0] : $user_ids;
        }

        // 是否已下单
        // 如果是和否都选择了，则不处理
        // 如果已选择时间则加上时间
        if(isset($params['team_search_buy_type']) && $params['team_search_buy_type'] !== '' && strlen($params['team_search_buy_type']) == 1)
        {
            $where = array_merge($order_time_where, [
                ['status', 'not in', [5,6]]
            ]);
            $user_ids = Db::name('Order')->where($where)->column('distinct user_id');
            if($params['team_search_buy_type'] == 1)
            {
                if(empty($user_ids))
                {
                    if(empty($eq_where_user_ids))
                    {
                        $eq_where_user_ids = [0];
                    }
                } else {
                    $eq_where_user_ids = array_filter(array_merge($eq_where_user_ids, $user_ids));
                }
            } else {
                if(empty($user_ids))
                {
                    if(empty($neq_where_user_ids))
                    {
                        $neq_where_user_ids = [0];
                    }
                } else {
                    $neq_where_user_ids = array_filter(array_merge($eq_where_user_ids, $user_ids));
                }
            }
        }

        // 条件组合
        $where = [];
        // 等于用户id
        if(!empty($eq_where_user_ids))
        {
            $where[] = ['id', 'in', $eq_where_user_ids];
        }
        // 不等于用户id
        if(!empty($neq_where_user_ids))
        {
            $where[] = ['id', 'not in', $neq_where_user_ids];
        }

        // 是否指定用户注册时间（是否反向）
        $where = array_merge($where, self::UserTeamExtUserWhere($params));

        return $where;
    }

    /**
     * 我的团队额外条件 - 用户时间
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-10
     * @desc    description
     * @param   [array]           $params       [输入参数]
     * @param   [string]          $field_first  [字段前缀]
     */
    public static function UserTeamExtUserWhere($params = [], $field_first = '')
    {
        $user_time_where = [];
        if(isset($params['team_search_user_time_reverse']) && $params['team_search_user_time_reverse'] == 1)
        {
            if(!empty($params['team_search_user_time_start']))
            {
                $user_time_where[] = [$field_first.'add_time', '<=', strtotime($params['team_search_user_time_start'])];
            }
            if(!empty($params['team_search_user_time_end']))
            {
                $user_time_where[] = [$field_first.'add_time', '>=', strtotime($params['team_search_user_time_end'])];
            }
        } else {
            if(!empty($params['team_search_user_time_start']))
            {
                $user_time_where[] = [$field_first.'add_time', '>=', strtotime($params['team_search_user_time_start'])];
            }
            if(!empty($params['team_search_user_time_end']))
            {
                $user_time_where[] = [$field_first.'add_time', '<=', strtotime($params['team_search_user_time_end'])];
            }
        }
        return $user_time_where;
    }

    /**
     * 我的团队额外条件 - 订单时间
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-10
     * @desc    description
     * @param   [array]           $params       [输入参数]
     * @param   [string]          $field_first  [字段前缀]
     */
    public static function UserTeamExtOrderWhere($params = [], $field_first = '')
    {
        $order_time_where = [];
        if(isset($params['team_search_order_time_reverse']) && $params['team_search_order_time_reverse'] == 1)
        {
            if(!empty($params['team_search_order_time_start']))
            {
                $order_time_where[] = [$field_first.'add_time', '<=', strtotime($params['team_search_order_time_start'])];
            }
            if(!empty($params['team_search_order_time_end']))
            {
                $order_time_where[] = [$field_first.'add_time', '>=', strtotime($params['team_search_order_time_end'])];
            }
        } else {
            if(!empty($params['team_search_order_time_start']))
            {
                $order_time_where[] = [$field_first.'add_time', '>=', strtotime($params['team_search_order_time_start'])];
            }
            if(!empty($params['team_search_order_time_end']))
            {
                $order_time_where[] = [$field_first.'add_time', '<=', strtotime($params['team_search_order_time_end'])];
            }
        }
        return $order_time_where;
    }

    /**
     * 用户团队总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function UserTeamTotal($where)
    {
        return Db::name('User')->where($where)->count();
    }

    /**
     * 获取用户订单列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserOrderList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'o.id,o.user_id,o.order_no,o.status,o.pay_status,o.total_price,o.refund_price,o.client_type,o.order_model,o.buy_number_count,o.add_time,u.username,u.nickname,u.mobile,u.email,u.avatar' : $params['field'];
        $order_by = empty($params['order_by']) ? 'o.id desc' : $params['order_by'];
        $data = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::UserOrderListHandle($data));
    }

    /**
     * 用户订单数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-16
     * @desc    description
     * @param   [array]          $data   [订单数据]
     * @param   [array]          $params [输入参数]
     */
    public static function UserOrderListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            // 基础参数
            $is_privacy = isset($params['is_privacy']) ? (boolean) $params['is_privacy'] : true;
            $is_items = isset($params['is_items']) ? intval($params['is_items']) : 1;

            // 订单id
            $order_ids = array_column($data, 'id');

            // 订单详情
            $detail = ($is_items == 1) ? OrderService::OrderItemList($data) : [];

            // 订单地址
            $address_data = OrderService::OrderAddressData($order_ids);

            // 订单货币
            $currency_data = OrderCurrencyService::OrderCurrencyGroupList($order_ids);

            // 支付方式名称
            $payment_list = PaymentService::OrderPaymentName(array_column($data, 'id'));

            // 静态数据
            $order_status_list = MyConst('common_order_status');
            $platform_type = MyConst('common_platform_type');
            $order_model = MyConst('common_order_type_list');
            $order_pay_status = MyConst('common_order_pay_status');
            foreach($data as &$v)
            {
                // 订单货币
                $v['currency_data'] = (!empty($currency_data) && is_array($currency_data) && array_key_exists($v['id'], $currency_data)) ? $currency_data[$v['id']] : $currency_default;

                // 用户信息处理
                $v = UserService::GetUserViewInfo($v['user_id'], $v, $is_privacy);
                $v['user'] = [
                    'avatar'        => $v['avatar'],
                    'user_name_view'=> $v['user_name_view'],
                    'username'      => $v['username'],
                    'nickname'      => $v['nickname'],
                    'mobile'        => $v['mobile'],
                    'email'         => $v['email'],
                ];

                // 销售模式+自提模式 地址信息
                $v['address_data'] = (!empty($address_data) && array_key_exists($v['id'], $address_data)) ? $address_data[$v['id']] : null;

                // 支付方式
                $v['payment_name'] = (!empty($payment_list) && is_array($payment_list) && array_key_exists($v['id'], $payment_list)) ? $payment_list[$v['id']] : '';

                // 订单状态
                $v['order_status_name'] = isset($order_status_list[$v['status']]) ? $order_status_list[$v['status']]['name'] : '';

                // 支付状态
                $v['order_pay_status_name'] = isset($order_pay_status[$v['pay_status']]) ? $order_pay_status[$v['pay_status']]['name'] : '';

                // 客户端
                $v['order_client_type_name'] = isset($platform_type[$v['client_type']]) ? $platform_type[$v['client_type']]['name'] : '';

                // 订单类型
                $v['order_order_model_name'] = isset($order_model[$v['order_model']]) ? $order_model[$v['order_model']]['name'] : '';

                // 订单详情
                if($is_items == 1 && !empty($detail) && array_key_exists($v['id'], $detail))
                {
                    $v['items'] = $detail[$v['id']];
                    $v['items_count'] = count($v['items']);
                    $v['describe'] = MyLang('common_service.order.order_item_summary_desc', ['buy_number_count'=>$v['buy_number_count'], 'currency_symbol'=>$v['currency_data']['currency_symbol'], 'total_price'=>$v['total_price']]);
                }

                // 订单时间
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
        }
        return $data;
    }

    /**
     * 用户订单列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserOrderWhere($params = [])
    {
        $where = [];

        // 关键字根据订单筛选
        if(!empty($params['keywords']))
        {
            $order_ids = Db::name('Order')->where('order_no', '=', $params['keywords'])->column('id');
            if(!empty($order_ids))
            {
                $where[] = ['o.id', 'in', $order_ids];
            } else {
                $user_ids = Db::name('User')->where('number_code|username|nickname|mobile|email', '=', $params['keywords'])->column('id');
                if(!empty($user_ids))
                {
                    $where[] = ['u.referrer', 'in', $user_ids];
                } else {
                    // 无数据条件，避免搜索条件没有数据造成的错觉
                    $where[] = ['u.id', '=', 0];
                }
            }
        }

        // 用户
        if(!empty($params['user']))
        {
            $where[] = ['u.referrer', '=', $params['user']['id']];
            $where[] = ['o.status', '>', 0];
        }

        // id
        if(!empty($params['id']))
        {
            $where[] = ['o.id', '=', intval($params['id'])];
        }
        // 订单号
        if(!empty($params['order_no']))
        {
            $where[] = ['o.order_no', '=', trim($params['order_no'])];
        }

        // 指定用户id
        if(!empty($params['uid']))
        {
            $where[] = ['o.user_id', '=', intval($params['uid'])];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 是否有退款
            if(isset($params['is_refund']) && $params['is_refund'] > -1)
            {
                if($params['is_refund'] == 0)
                {
                    $where[] = ['o.refund_price', '<=', 0];
                } else {
                    $where[] = ['o.refund_price', '>', 0];
                }
            }

            // 订单状态
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['o.status', '=', intval($params['status'])];
            }

            // 来源
            if(!empty($params['client_type']))
            {
                $where[] = ['o.client_type', '=', $params['client_type']];
            }

            // 支付方式
            if(isset($params['payment_id']) && $params['payment_id'] > -1)
            {
                $where[] = ['o.payment_id', '=', intval($params['payment_id'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['o.add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['o.add_time', '<', strtotime($params['time_end'])];
            }
        }

        return $where;
    }

    /**
     * 用户订单总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function UserOrderTotal($where)
    {
        return Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->count();
    }

    /**
     * 获取用户收益明细列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserProfitList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'pdl.*, o.order_no, o.status as order_status, o.pay_status as order_pay_status, o.client_type as order_client_type, o.refund_price' : $params['field'];
        $order_by = empty($params['order_by']) ? 'pdl.id desc' : $params['order_by'];
        $user_type = isset($params['user_type']) ? $params['user_type'] : 'user';

        $data = Db::name('PluginsDistributionProfitLog')->alias('pdl')->join('order o', 'pdl.order_id=o.id')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        if(!empty($data))
        {
            $common_platform_type = MyConst('common_platform_type');
            $order_status_list = MyConst('common_order_status');
            $order_pay_status = MyConst('common_order_pay_status');
            $level_name_list = BaseService::$level_name_list;
            $profit_status_list = BaseService::$profit_status_list;
            $profit_profit_type_list = BaseService::$profit_profit_type_list;
            foreach($data as &$v)
            {
                // 用户信息
                $v['user'] = ($user_type == 'admin') ? UserService::GetUserViewInfo($v['user_id']) : [];

                // 级别
                $v['level_name'] = isset($level_name_list[$v['level']]) ? $level_name_list[$v['level']]['name'] : '';

                // 佣金状态
                $v['status_name'] = (isset($v['status']) && isset($profit_status_list[$v['status']])) ? $profit_status_list[$v['status']]['name'] : '';

                // 佣金类型
                $v['profit_type_name'] = (isset($v['profit_type']) && isset($profit_profit_type_list[$v['profit_type']])) ? $profit_profit_type_list[$v['profit_type']]['name'] : '';

                // 订单状态
                $v['order_status_name'] = isset($order_status_list[$v['order_status']]) ? $order_status_list[$v['order_status']]['name'] : '';

                // 支付状态
                $v['order_pay_status_name'] = isset($order_pay_status[$v['order_pay_status']]) ? $order_pay_status[$v['order_pay_status']]['name'] : '';

                // 客户端
                $v['order_client_type_name'] = isset($common_platform_type[$v['order_client_type']]) ? $common_platform_type[$v['order_client_type']]['name'] : '';

                // 添加时间
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);

                // 更新时间
                $v['upd_time'] = ($v['upd_time'] > 0) ? date('Y-m-d H:i:s', $v['upd_time']) : '';
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 用户收益明细列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserProfitWhere($params = [])
    {
        $where = [];

        // 关键字根据订单筛选
        if(!empty($params['keywords']))
        {
            $order_ids = Db::name('Order')->where('order_no', '=', $params['keywords'])->column('id');
            if(!empty($order_ids))
            {
                $where[] = ['o.id', 'in', $order_ids];
            } else {
                $user_ids = Db::name('User')->where('number_code|username|nickname|mobile|email', '=', $params['keywords'])->column('id');
                if(!empty($user_ids))
                {
                    $where[] = ['pdl.user_id', 'in', $user_ids];
                } else {
                    // 无数据条件，避免搜索条件没有数据造成的错觉
                    $where[] = ['pdl.id', '=', 0];
                }
            }
        }

        // 用户
        if(!empty($params['user']))
        {
            $where[] = ['pdl.user_id', '=', $params['user']['id']];
        }

        // 指定用户id
        if(!empty($params['uid']))
        {
            $where[] = ['pdl.user_id', '=', intval($params['uid'])];
        }

        // id
        if(!empty($params['id']))
        {
            $where[] = ['pdl.id', '=', intval($params['id'])];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 佣金状态
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['pdl.status', '=', intval($params['status'])];
            }

            // 订单状态
            if(isset($params['order_status']) && $params['order_status'] > -1)
            {
                $where[] = ['o.status', '=', intval($params['order_status'])];
            }

            // 是否有退款
            if(isset($params['is_refund']) && $params['is_refund'] > -1)
            {
                if($params['is_refund'] == 0)
                {
                    $where[] = ['o.refund_price', '<=', 0];
                } else {
                    $where[] = ['o.refund_price', '>', 0];
                }
            }

            // 级别
            if(isset($params['level']) && $params['level'] > 0)
            {
                $where[] = ['pdl.level', '=', intval($params['level'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['pdl.add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['pdl.add_time', '<', strtotime($params['time_end'])];
            }
        }
        return $where;
    }

    /**
     * 用户收益明细总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function UserProfitTotal($where)
    {
        return Db::name('PluginsDistributionProfitLog')->alias('pdl')->join('order o', 'pdl.order_id=o.id')->where($where)->count();
    }

    /**
     * 获取用户积分明细列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserIntegralList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];

        $data = Db::name('PluginsDistributionIntegralLog')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        if(!empty($data))
        {
            $integral_status_list = BaseService::$integral_status_list;
            foreach($data as &$v)
            {
                // 用户信息
                $v['user'] = UserService::GetUserViewInfo($v['user_id']);

                // 状态
                $v['status_name'] = $integral_status_list[$v['status']]['name'];

                // 添加时间
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);

                // 更新时间
                $v['upd_time'] = ($v['upd_time'] > 0) ? date('Y-m-d H:i:s', $v['upd_time']) : '';
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 用户积分明细列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserIntegralWhere($params = [])
    {
        $where = [];

        // 关键字根据订单筛选
        if(!empty($params['keywords']))
        {
            $user_ids = Db::name('User')->where('number_code|username|nickname|mobile|email', '=', $params['keywords'])->column('id');
            if(!empty($user_ids))
            {
                $where[] = ['user_id', 'in', $user_ids];
            } else {
                // 无数据条件，避免搜索条件没有数据造成的错觉
                $where[] = ['id', '=', 0];
            }
        }

        // 用户
        if(!empty($params['user']))
        {
            $where[] = ['user_id', '=', $params['user']['id']];
        }

        // 指定用户id
        if(!empty($params['uid']))
        {
            $where[] = ['pdl.user_id', '=', intval($params['uid'])];
        }

        // id
        if(!empty($params['id']))
        {
            $where[] = ['id', '=', intval($params['id'])];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 状态
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['status', '=', intval($params['status'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['add_time', '<', strtotime($params['time_end'])];
            }
        }
        return $where;
    }

    /**
     * 用户积分明细总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function UserIntegralTotal($where)
    {
        return Db::name('PluginsDistributionIntegralLog')->where($where)->count();
    }

    /**
     * 获取取货点列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function ExtractionList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];

        $data = Db::name('PluginsDistributionUserSelfExtraction')->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        if(!empty($data))
        {
            $status_list = BaseService::$distribution_extraction_status_list;
            foreach($data as &$v)
            {
                // 基础数据处理
                $v = array_merge($v, ExtractionService::DataHandle($v));

                // 用户信息处理
                $v['user'] = UserService::GetUserViewInfo($v['user_id']);

                // logo
                $v['logo'] = ResourcesService::AttachmentPathViewHandle($v['logo']);

                // 状态
                $v['status_name'] = $status_list[$v['status']]['name'];

                // 添加时间
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);

                // 更新时间
                $v['upd_time'] = ($v['upd_time'] > 0) ? date('Y-m-d H:i:s', $v['upd_time']) : '';
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 取货点列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function ExtractionWhere($params = [])
    {
        $where = [];

        // 关键字根据订单筛选
        if(!empty($params['keywords']))
        {
            $where[] = ['name|tel|alias|address', 'like', '%'.$params['keywords'].'%'];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 状态
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['status', '=', intval($params['status'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['add_time', '<', strtotime($params['time_end'])];
            }
        }

        return $where;
    }

    /**
     * 取货点总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function ExtractionTotal($where)
    {
        return Db::name('PluginsDistributionUserSelfExtraction')->where($where)->count();
    }

    /**
     * 取货点订单列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ExtractionOrderList($params = [])
    {
        // 参数
        $where = empty($params['where']) ? [] : $params['where'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $field = 'o.order_no,o.pay_price,o.order_model,o.status as order_status,o.user_id as order_user_id,po.*';
        $order_by = empty($params['order_by']) ? 'po.id desc' : $params['order_by'];
        
        // 获取数据
        $data = Db::name('PluginsDistributionUserSelfExtractionOrder')->alias('po')->join('order o', 'o.id=po.order_id')->where($where)->field($field)->limit($m, $n)->order($order_by)->select()->toArray();
        if(!empty($data))
        {
            $user_type = isset($params['user_type']) ? $params['user_type'] : 'user';
            $order_status_list = MyConst('common_order_status');
            $take_status_list = BaseService::$order_status_list;
            foreach($data as &$v)
            {
                // 用户信息
                $v['user'] = ($user_type == 'admin') ? UserService::GetUserViewInfo($v['user_id']) : [];

                // 订单状态
                $v['order_status_name'] = ($v['order_model'] == 2 && $v['order_status'] == 2) ? '待取货' : (isset($order_status_list[$v['order_status']]) ? $order_status_list[$v['order_status']]['name'] : '');

                // 取货状态
                $v['status_name'] = isset($take_status_list[$v['status']]) ? $take_status_list[$v['status']]['name'] : '未知';

                // 创建时间
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);

                // 更新时间
                $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
            }
        }

        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 取货点订单列表 - 总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ExtractionOrderListTotal($where)
    {
        return Db::name('PluginsDistributionUserSelfExtractionOrder')->alias('po')->join('order o', 'o.id=po.order_id')->where($where)->count();
    }

    /**
     * 取货点订单列表 - 条件
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ExtractionOrderListWhere($params = [])
    {
        $where = [
            'o.status'      => [2,3,4],
        ];
        if(isset($params['status']) && $params['status'] > -1)
        {
            $where['po.status'] = intval($params['status']);
        }

        // 用户
        if(!empty($params['user']))
        {
            $where['po.user_id'] = $params['user']['id'];
        }

        // 取货点用户id
        if(!empty($params['user_id']))
        {
            $where['po.user_id'] = intval($params['user_id']);
        }

        // 关键字
        if(!empty($params['keywords']))
        {
            $is_keywords = false;
            // 订单号
            if(strlen($params['keywords']) != 4)
            {
                $where['o.order_no'] = trim($params['keywords']);
                $is_keywords = true;
            } else {
                // 取件码
                $order_id = Db::name('OrderExtractionCode')->where(['code'=>trim($params['keywords'])])->value('order_id');
                if(!empty($order_id))
                {
                    $where['o.id'] = $order_id;
                    $is_keywords = true;
                }
            }

            // 关键字处理
            if($is_keywords === false)
            {
                $where['o.id'] = 0;
            }
        }

        return $where;
    }

    /**
     * 团队保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-07-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function TeamSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user_id',
                'error_msg'         => '请选择用户',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'level_id',
                'error_msg'         => '请选择分销等级',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 用户信息
        $user = UserService::UserBaseInfo('id', intval($params['user_id']));
        if(empty($user))
        {
            return DataReturn(MyLang('user_info_incorrect_tips'), -1);
        }

        // 更新用户数据
        return UserService::UserUpdateHandle([
            'plugins_distribution_level'    => intval($params['level_id']),
        ], $user['id'], $params);
    }

    /**
     * 获取根据订单地址坐标用户分布
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserOrderMapData($params = [])
    {
        // 配置信息
        $config = empty($params['plugins_config']) ? [] : $params['plugins_config'];

        // 仅读取有效订单
        $where = [
            ['o.status', 'not in', [5,6]],
        ];

        // 按照坐标排序查询
        if(!empty($params['lng']) && !empty($params['lat']) && $params['lng'] != 0 && $params['lat'] != 0)
        {
            $distance = empty($config['user_order_map_distance']) ? 10 : intval($config['user_order_map_distance']);
            $coordinate = ReturnSquarePoint($params['lng'], $params['lat'], $distance);
            if(!empty($coordinate))
            {
                // 排除0
                $where[] = ['oa.lng', '<>', 0];
                $where[] = ['oa.lat', '<>', 0];

                // 范围
                $where[] = ['oa.lat', '>', $coordinate['right-bottom']['lat']];
                $where[] = ['oa.lat', '<', $coordinate['left-top']['lat']];
                $where[] = ['oa.lng', '>', $coordinate['left-top']['lng']];
                $where[] = ['oa.lng', '<', $coordinate['right-bottom']['lng']];
            }
        }

        // 是否查看全部用户分布数据
        $is_view_all_user = isset($config['is_promotion_view_all_user_distribution']) && $config['is_promotion_view_all_user_distribution'] == 1;
        // 是否用户访问、则新增邀请用户条件
        if(!empty($params['user']) && !$is_view_all_user)
        {
            $user_ids = Db::name('User')->where(['referrer'=>$params['user']['id']])->column('id');
            $where[] = ['o.user_id', 'in', empty($user_ids) ? [0] : $user_ids];
        }

        // 获取数据
        $data = [];
        $field = 'o.user_id,oa.lng,oa.lat,oa.province_name,oa.city_name,oa.county_name,oa.address';
        $order = Db::name('Order')->alias('o')->join('order_address oa', 'o.id=oa.order_id')->field($field)->where($where)->group('o.user_id')->select()->toArray();
        if(!empty($order))
        {
            $temp_order = array_column($order, null, 'user_id');
            $data = array_values(UserService::GetUserViewInfo(array_keys($temp_order)));
            foreach($data as &$v)
            {
                // 坐标放入用户数据
                $temp = $temp_order[$v['id']];
                $v['lng'] = $temp['lng'];
                $v['lat'] = $temp['lat'];
                $v['address'] = $temp['province_name'].$temp['city_name'].$temp['county_name'].$temp['address'];

                // 获取用户订单总数
                $where = [
                    ['user_id', '=', $v['id']],
                    ['status', 'not in', [5,6]],
                ];
                $v['order_count'] = Db::name('Order')->where($where)->count();
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 获取推广用户数据条件
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UserPromotionMap($params = [])
    {
        // 时间处理
        if(!empty($params['start']) && stripos($params['start'], '-') !== false)
        {
            if(stripos($params['start'], '+') !== false)
            {
                $params['start'] = urldecode($params['start']);
            }
            $params['start'] = strtotime($params['start']);
        }
        if(!empty($params['end']) && stripos($params['end'], '-') !== false)
        {
            if(stripos($params['end'], '+') !== false)
            {
                $params['end'] = urldecode($params['end']);
            }
            $params['end'] = strtotime($params['end']);
        }

        // 用户总数
        $user_where = [];
        if(!empty($params['user']))
        {
            $user_where[] = ['referrer', '=', $params['user']['id']];
        } else {
            $user_where[] = ['referrer', '>', 0];
        }
        if(!empty($params['start']))
        {
            $user_where[] = ['add_time', '>=', $params['start']];
        }
        if(!empty($params['end']))
        {
            $user_where[] = ['add_time', '<=', $params['end']];
        }

        // 已消费用户总数
        $valid_where = [
            ['o.status', '<=', 4],
        ];
        if(!empty($params['user']))
        {
            $valid_where[] = ['u.referrer', '=', $params['user']['id']];
        } else {
            $valid_where[] = ['u.referrer', '>', 0];
        }
        if(!empty($params['start']))
        {
            $valid_where[] = ['u.add_time', '>=', $params['start']];
        }
        if(!empty($params['end']))
        {
            $valid_where[] = ['u.add_time', '<=', $params['end']];
        }

        // 未消费用户总数、已取消、已关闭、无订单用户
        $not_where = [];
        if(!empty($params['user']))
        {
            $not_where[] = ['u.referrer', '=', $params['user']['id']];
        } else {
            $not_where[] = ['u.referrer', '>', 0];
        }
        if(!empty($params['start']))
        {
            $not_where[] = ['u.add_time', '>=', $params['start']];
        }
        if(!empty($params['end']))
        {
            $not_where[] = ['u.add_time', '<=', $params['end']];
        }

        return [
            'user_where'   => $user_where,
            'valid_where'  => $valid_where,
            'not_where'    => $not_where,
        ];
    }

    /**
     * 推广用户总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function UserPromotionAllTotal($where)
    {
        return Db::name('User')->where($where)->count('id');
    }

    /**
     * 推广用户列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UserPromotionAllList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        $data = Db::name('User')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::TeamDataListHandle($data));
    }

    /**
     * 推广有效用户总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function UserPromotionValidTotal($where)
    {
        return Db::name('Order')->alias('o')->join('user u', 'u.id=o.user_id')->where($where)->count('distinct u.id');
    }

    /**
     * 推广有效用户列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UserPromotionValidList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'u.*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'u.id desc' : $params['order_by'];
        $data = Db::name('Order')->alias('o')->join('user u', 'u.id=o.user_id')->field($field)->where($where)->group('u.id')->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::TeamDataListHandle($data));
    }

    /**
     * 推广有效用户总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]          $valid_where [有效用户条件]
     * @param   [array]          $not_where   [未消费用户条件]
     */
    public static function UserPromotionNotConsumedTotal($valid_where, $not_where)
    {
        return Db::name('Order')->alias('o')->rightJoin('user u', 'u.id=o.user_id')->field('o.*')->where($not_where)->where('u.id', 'not in', function($query) use($valid_where) {
            $query->name('Order')->alias('o')->join('user u', 'u.id=o.user_id')->where($valid_where)->field('u.id');
        })->count('distinct u.id');
    }

    /**
     * 推广有效用户列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UserPromotionNotConsumedList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $valid_where = empty($params['valid_where']) ? [] : $params['valid_where'];
        $field = empty($params['field']) ? 'u.*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'u.id desc' : $params['order_by'];
        $data = Db::name('Order')->alias('o')->rightJoin('user u', 'u.id=o.user_id')->field('o.*')->where($where)->where('u.id', 'not in', function($query) use($valid_where) {
            $query->name('Order')->alias('o')->join('user u', 'u.id=o.user_id')->where($valid_where)->field('u.id');
        })->group('u.id')->field($field)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::TeamDataListHandle($data));
    }

    /**
     * 统计数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function BaseDataMap($params = [])
    {
        // 时间处理
        if(!empty($params['start']) && stripos($params['start'], '-') !== false)
        {
            if(stripos($params['start'], '+') !== false)
            {
                $params['start'] = urldecode($params['start']);
            }
            $params['start'] = strtotime($params['start']);
        }
        if(!empty($params['end']) && stripos($params['end'], '-') !== false)
        {
            if(stripos($params['end'], '+') !== false)
            {
                $params['end'] = urldecode($params['end']);
            }
            $params['end'] = strtotime($params['end']);
        }

        // 指定用户id
        $user_id = empty($params['user']) ? 0 : $params['user']['id'];

        // 订单总数、订单总额
        $order_where = [
            ['o.status', 'not in', [5,6]],
        ];
        if(!empty($params['start']))
        {
            $order_where[] = ['o.add_time', '>=', $params['start']];
        }
        if(!empty($params['end']))
        {
            $order_where[] = ['o.add_time', '<=', $params['end']];
        }
        if(!empty($user_id))
        {
            $order_where[] = ['u.referrer', '=', $user_id];
        }

        // 新增客户排除用户
        // 如果没有指定时间则直接计算只有一次下单的客户
        $order_new_user_not_front_where = [];
        if(!empty($params['start']))
        {
            // 新增客户、在当前时间范围内有订单，前面时间无订单
            // 查询前面的用户
            $order_new_user_not_front_where = [
                ['o.status', 'not in', [5,6]],
            ];
            if(!empty($params['start']))
            {
                $order_new_user_not_front_where[] = ['o.add_time', '<', $params['start']];
            }
            if(!empty($user_id))
            {
                $order_new_user_not_front_where[] = ['u.referrer', '=', $user_id];
            }
        }
        return [
            // 订单总数、订单总额
            'order_where'                     => $order_where,
            // 新增客户总数、新增客户总GMV排除用户条件
            'order_new_user_not_front_where'  => $order_new_user_not_front_where,
        ];
    }

    /**
     * 新增客户 - 总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-15
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function BaseDataOrderNewUserCountTotal($where, $not_front_where = [])
    {
        // 如果没有指定时间则直接计算只有一次下单的客户
        if(empty($not_front_where))
        {
            $count = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field('COUNT(u.id) AS count')->group('u.id')->having('count=1')->count();
        } else {
            $front_user_ids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($not_front_where)->column('DISTINCT u.id');
            if(!empty($front_user_ids))
            {
                $where[] = ['u.id', 'not in', $front_user_ids];
            }
            $count = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->count('DISTINCT u.id');
        }
        return $count;
    }

    /**
     * 新增客户 - 列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function BaseDataOrderNewUserCountList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $not_front_where = empty($params['not_front_where']) ? [] : $params['not_front_where'];
        $field = empty($params['field']) ? 'u.*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'u.id desc' : $params['order_by'];

        // 如果没有指定时间则直接计算只有一次下单的客户
        if(empty($not_front_where))
        {
            $data = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field($field.', COUNT(u.id) AS count')->group('u.id')->having('count=1')->limit($m, $n)->order($order_by)->select()->toArray();
        } else {
            $front_user_ids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($not_front_where)->column('DISTINCT u.id');
            if(!empty($front_user_ids))
            {
                $where[] = ['u.id', 'not in', $front_user_ids];
            }
            $data = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->group('u.id')->field($field)->limit($m, $n)->order($order_by)->select()->toArray();
        }
        return DataReturn(MyLang('handle_success'), 0, self::TeamDataListHandle($data));
    }

    /**
     * 新增客户-有效 - 总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-15
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function BaseDataOrderNewValidUserCountTotal($where, $not_front_where)
    {
        $oids = [];
        if(empty($not_front_where))
        {
            $temp = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field('o.id, COUNT(u.id) AS count')->group('u.id')->having('count=1')->select()->toArray();
            if(!empty($temp))
            {
                $oids = array_unique(array_column($temp, 'id'));
            }
        } else {
            $front_user_ids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($not_front_where)->column('DISTINCT u.id');
            if(!empty($front_user_ids))
            {
                $where[] = ['u.id', 'not in', $front_user_ids];
            }
            $oids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->column('DISTINCT o.id');
        }
        if(empty($oids))
        {
            return 0;
        }

        // 新增客户-有效（以日期+用户id分组然后获取总数，然后筛选数量及总价）
        // 1. 首单必须是这个月的
        // 2. 按天分组计算，3天及以上并且金额大于等于1000则是有效客户
        return Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where([['o.id', 'in', $oids]])->field('u.id, COUNT( u.id) as count, SUM(o.total_price) as summary_total_price')->group('u.id')->having('count >= 3 AND summary_total_price >= 1000')->count();
    }

    /**
     * 新增客户-有效 - 列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function BaseDataOrderNewValidUserCountList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $not_front_where = empty($params['not_front_where']) ? [] : $params['not_front_where'];
        $field = empty($params['field']) ? 'u.*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'u.id desc' : $params['order_by'];

        $oids = [];
        if(empty($not_front_where))
        {
            $temp = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field('o.id, COUNT(u.id) AS count')->group('u.id')->having('count=1')->select()->toArray();
            if(!empty($temp))
            {
                $oids = array_unique(array_column($temp, 'id'));
            }
        } else {
            $front_user_ids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($not_front_where)->column('DISTINCT u.id');
            if(!empty($front_user_ids))
            {
                $where[] = ['u.id', 'not in', $front_user_ids];
            }
            $oids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->column('DISTINCT o.id');
        }
        if(!empty($oids))
        {
            // 新增客户-有效（以日期+用户id分组然后获取总数，然后筛选数量及总价）
            // 1. 首单必须是这个月的
            // 2. 按天分组计算，3天及以上并且金额大于等于1000则是有效客户
            $data = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where([['o.id', 'in', $oids]])->field($field.', u.id, COUNT( u.id) as count, SUM(o.total_price) as summary_total_price')->group('u.id')->having('count >= 3 AND summary_total_price >= 1000')->limit($m, $n)->order($order_by)->select()->toArray();
        } else {
            $data = [];
        }
        return DataReturn(MyLang('handle_success'), 0, self::TeamDataListHandle($data));
    }

    /**
     * 新增客户-需复购
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-15
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function BaseDataOrderNewValidUserNeedRepurchaseCountTotal($where, $not_front_where)
    {
        $oids = [];
        if(empty($not_front_where))
        {
            $temp = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field('o.id, COUNT(u.id) AS count')->group('u.id')->having('count=1')->select()->toArray();
            if(!empty($temp))
            {
                $oids = array_unique(array_column($temp, 'id'));
            }
        } else {
            $front_user_ids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($not_front_where)->column('DISTINCT u.id');
            if(!empty($front_user_ids))
            {
                $where[] = ['u.id', 'not in', $front_user_ids];
            }
            $oids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->column('DISTINCT o.id');
        }
        if(empty($oids))
        {
            return 0;
        }

        // 新增客户-有效（以日期+用户id分组然后获取总数，然后筛选数量及总价）
        // 1. 首单必须是这个月的
        // 2. 按天分组计算，3天及以上并且金额大于等于1000则是有效客户
        return Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where([['o.id', 'in', $oids]])->field('u.id, COUNT( u.id) as count, SUM(o.total_price) as summary_total_price')->group('u.id')->having('count < 3 OR summary_total_price < 1000')->count();
    }

    /**
     * 新增客户-需复购 - 列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function BaseDataOrderNewValidUserNeedRepurchaseCountList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $not_front_where = empty($params['not_front_where']) ? [] : $params['not_front_where'];
        $field = empty($params['field']) ? 'u.*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'u.id desc' : $params['order_by'];

        $oids = [];
        if(empty($not_front_where))
        {
            $temp = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field('o.id, COUNT(u.id) AS count')->group('u.id')->having('count=1')->select()->toArray();
            if(!empty($temp))
            {
                $oids = array_unique(array_column($temp, 'id'));
            }
        } else {
            $front_user_ids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($not_front_where)->column('DISTINCT u.id');
            if(!empty($front_user_ids))
            {
                $where[] = ['u.id', 'not in', $front_user_ids];
            }
            $oids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->column('DISTINCT o.id');
        }
        if(!empty($oids))
        {
            // 新增客户-有效（以日期+用户id分组然后获取总数，然后筛选数量及总价）
            // 1. 首单必须是这个月的
            // 2. 按天分组计算，3天及以上并且金额大于等于1000则是有效客户
            $data = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where([['o.id', 'in', $oids]])->field($field.', u.id, COUNT( u.id) as count, SUM(o.total_price) as summary_total_price')->group('u.id')->having('count < 3 OR summary_total_price < 1000')->limit($m, $n)->order($order_by)->select()->toArray();
            if(!empty($data))
            {
                $where = empty($params['where']) ? [] : $params['where'];
                foreach($data as &$v)
                {
                    $v['day_count'] = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where(array_merge($where, [['u.id', '=', $v['id']]]))->group('DATE_FORMAT(FROM_UNIXTIME(o.add_time), "%Y-%m-%d")')->count();
                }
            }
        } else {
            $data = [];
        }
        return DataReturn(MyLang('handle_success'), 0, self::TeamDataListHandle($data));
    }

    /**
     * 新增客户总额GMV - 总额
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-15
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function BaseDataOrderNewUserPriceGMVSummaryTotal($where, $not_front_where = [])
    {
        // 如果没有指定时间则直接计算只有一次下单的客户
        if(empty($not_front_where))
        {
            $temp = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field('SUM(o.total_price) AS total_price, COUNT(u.id) AS count')->group('o.id')->having('count=1')->select()->toArray();
            $total_price = empty($temp) ? 0 : array_sum(array_column($temp, 'total_price'));
        } else {
            $front_user_ids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($not_front_where)->column('DISTINCT u.id');
            if(!empty($front_user_ids))
            {
                $where[] = ['u.id', 'not in', $front_user_ids];
            }
            $total_price = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->sum('o.total_price');
        }
        return $total_price;
    }

    /**
     * 新增客户总额GMV - 总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-15
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function BaseDataOrderNewUserPriceGMVTotal($where, $not_front_where = [])
    {
        // 如果没有指定时间则直接计算只有一次下单的客户
        if(empty($not_front_where))
        {
            $count = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field('SUM(o.total_price) AS total_price, COUNT(u.id) AS count')->group('o.id')->having('count=1')->count();
        } else {
            $front_user_ids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($not_front_where)->column('DISTINCT u.id');
            if(!empty($front_user_ids))
            {
                $where[] = ['u.id', 'not in', $front_user_ids];
            }
            $count = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->count();
        }
        return $count;
    }

    /**
     * 新增客户总额GMV - 列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function BaseDataOrderNewUserPriceGMVList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $not_front_where = empty($params['not_front_where']) ? [] : $params['not_front_where'];
        $field = empty($params['field']) ? 'o.*, u.username,u.nickname,u.mobile,u.email,u.avatar' : $params['field'];
        $order_by = empty($params['order_by']) ? 'o.id desc' : $params['order_by'];

        // 如果没有指定时间则直接计算只有一次下单的客户
        if(empty($not_front_where))
        {
            $data = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field($field.', SUM(o.total_price) AS summary_total_price, COUNT(u.id) AS count')->group('o.id')->having('count=1')->limit($m, $n)->order($order_by)->select()->toArray();
        } else {
            $front_user_ids = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($not_front_where)->column('DISTINCT u.id');
            if(!empty($front_user_ids))
            {
                $where[] = ['u.id', 'not in', $front_user_ids];
            }
            $data = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field($field.', SUM(o.total_price) AS summary_total_price')->group('o.id')->limit($m, $n)->order($order_by)->select()->toArray();
        }
        return DataReturn(MyLang('handle_success'), 0, self::UserOrderListHandle($data));
    }

    /**
     * 订单总数 - 总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-16
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function BaseDataOrderUserCountTotal($where)
    {
        return Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->count('DISTINCT o.id');
    }

    /**
     * 订单总数 - 列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function BaseDataOrderUserCountList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'o.*, u.username,u.nickname,u.mobile,u.email,u.avatar' : $params['field'];
        $order_by = empty($params['order_by']) ? 'o.id desc' : $params['order_by'];
        $data = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->field($field)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::UserOrderListHandle($data));
    }

    /**
     * 订单总额GMV - 总额
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-16
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function BaseDataOrderUserPriceGMVSummaryTotal($where)
    {
        return Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->sum('o.total_price');
    }

    /**
     * 订单总额GMV - 总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-15
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function BaseDataOrderUserPriceGMVTotal($where)
    {
        return Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->where($where)->count();
    }

    /**
     * 订单总额GMV - 列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-14
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function BaseDataOrderUserPriceGMVList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'o.*, u.username,u.nickname,u.mobile,u.email,u.avatar' : $params['field'];
        $order_by = empty($params['order_by']) ? 'o.id desc' : $params['order_by'];
        $data = Db::name('Order')->alias('o')->join('user u', 'o.user_id=u.id')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::UserOrderListHandle($data));
    }
}
?>