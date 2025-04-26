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
namespace app\plugins\membershiplevelvip\service;

use think\facade\Db;
use app\service\PluginsService;
use app\service\ResourcesService;
use app\service\UserService;
use app\service\PaymentService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\plugins\membershiplevelvip\service\BusinessService;

/**
 * 会员等级服务层 - 基础
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class BaseService
{
    // 类型名称
    public static $business_type_name = 'member';

    // 基础数据附件字段
    public static $base_config_attachment_field = [
        'default_level_images',
        'user_poster_share_images',
        'default_bg_images',
        'default_btn_bg',
        'default_logo'
    ];

    // 等级规则
    public static $members_level_rules_list = [
        0 => ['value' => 0, 'name' => '积分（可用积分）', 'checked' => true],
        1 => ['value' => 1, 'name' => '积分（总积分）'],
        2 => ['value' => 2, 'name' => '消费总额（已完成订单）'],
    ];

    // 开通会员订单状态
    // 订单状态（0待支付, 1已支付, 2已取消, 3已关闭）
    public static $payment_user_order_status_list = [
        0 => ['value' => 0, 'name' => '待支付', 'checked' => true],
        1 => ['value' => 1, 'name' => '已支付'],
        2 => ['value' => 2, 'name' => '已取消'],
        3 => ['value' => 3, 'name' => '已关闭'],
    ];

    // 结算状态（0待结算, 1结算中, 2已结算）
    public static $payment_user_order_settlement_status_list = [
        0 => ['value' => 0, 'name' => '待结算', 'checked' => true],
        1 => ['value' => 1, 'name' => '结算中'],
        2 => ['value' => 2, 'name' => '已结算'],
    ];

    // 收益结算状态（0待结算, 1已结算, 2已失效）
    public static $payment_user_profit_status_list = [
        0 => ['value' => 0, 'name' => '待结算', 'checked' => true],
        1 => ['value' => 1, 'name' => '已结算'],
        2 => ['value' => 2, 'name' => '已失效'],
    ];

    // 订单类型（0正常购买, 1续费）
    public static $payment_user_order_type_list = [
        0 => ['value' => 0, 'name' => '购买', 'checked' => true],
        1 => ['value' => 1, 'name' => '续费'],
    ];

    // 级别
    public static $level_name_list = [
        1 => ['value' => 1, 'name' => '一级'],
        2 => ['value' => 2, 'name' => '二级'],
        3 => ['value' => 3, 'name' => '三级'],
    ];

    // 会员VIP信息缓存key
    public static $user_vip_data_key = 'plugins_membershiplevelvip_user_vip_';

    /**
     * 获取用户列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $data   [数据列表]
     * @param    [array]          $params [输入参数]
     */
    public static function UserPaymentListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                // 用户信息处理
                $v['user'] = UserService::GetUserViewInfo($v['user_id']);

                // 订单有效金额
                $order_where = [
                    ['user_id', '=', $v['user_id']],
                    ['status', '=', 1],
                ];
                $order_total = PriceNumberFormat(Db::name('PluginsMembershiplevelvipPaymentUserOrder')->where($order_where)->sum('pay_price'));
                $v['order_total'] = ($order_total <= 0) ? '' : $order_total;

                // 更新时间
                $v['expire_time'] = empty($v['expire_time']) ? '' : (stripos($v['expire_time'], '-') === false ? date('Y-m-d H:i:s', $v['expire_time']) : $v['expire_time']);

                // 更新时间
                $v['upd_time'] = empty($v['upd_time']) ? '' : (stripos($v['upd_time'], '-') === false ? date('Y-m-d H:i:s', $v['upd_time']) : $v['upd_time']);
            }
        }
        return $data;
    }

    /**
     * 用户购买会员订单列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-04-30T00:13:14+0800
     * @param   [array]          $params [输入参数]
     */
    public static function UserPayOrderList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];

        // 获取数据列表
        $data = Db::name('PluginsMembershiplevelvipPaymentUserOrder')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                // 用户信息
                $v['user'] = UserService::GetUserViewInfo($v['user_id']);

                // 类型
                $v['type_name'] = isset(self::$payment_user_order_type_list[$v['type']]) ? self::$payment_user_order_type_list[$v['type']]['name'] : '';

                // 结算状态
                $v['settlement_status_name'] = isset(self::$payment_user_order_settlement_status_list[$v['settlement_status']]) ? self::$payment_user_order_settlement_status_list[$v['settlement_status']]['name'] : '';

                // 返佣总额
                $v['profit_price_total'] = Db::name('PluginsMembershiplevelvipUserProfit')->where(['payment_user_order_id'=>$v['id']])->sum('profit_price');

                // 购买信息
                if(empty($v['number']))
                {
                    $value = '终身';
                    $unit = '';
                } else {
                    $value_uint = BusinessService::UserExpireTimeValueUnit($v['number']);
                    $value = $value_uint['value'];
                    $unit = $value_uint['unit'];
                }
                $v['period_value'] = $value;
                $v['period_unit'] = $unit;

                // 支付状态
                $v['status_name'] = isset($v['status']) ? self::$payment_user_order_status_list[$v['status']]['name'] : '';

                // 支付时间
                $v['pay_time'] = empty($v['pay_time']) ? '' : date('Y-m-d H:i:s', $v['pay_time']);

                // 创建时间
                $v['add_time'] = empty($v['add_time']) ? '' : date('Y-m-d H:i:s', $v['add_time']);

                // 更新时间
                $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 用户购买会员订单列表总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function UserPayOrderTotal($where = [])
    {
        return (int) Db::name('PluginsMembershiplevelvipPaymentUserOrder')->where($where)->count();
    }

    /**
     * 用户购买会员订单列表条件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UserPayOrderWhere($params = [])
    {
        $where = [];

        // 用户id
        if(!empty($params['user']))
        {
            $where[] = ['user_id', '=', $params['user']['id']];
        }

        // 指定用户id
        if(!empty($params['uid']))
        {
            $where[] = ['user_id', '=', intval($params['uid'])];
        }

        // id
        if(!empty($params['id']))
        {
            $where[] = ['id', '=', intval($params['id'])];
        }
        // 订单号
        if(!empty($params['orderno']))
        {
            $where[] = ['payment_user_order_no', '=', trim($params['orderno'])];
        }

        // 关键字根据用户筛选
        if(!empty($params['keywords']))
        {
            $user_ids = Db::name('User')->where('number_code|username|nickname|mobile|email', '=', $params['keywords'])->column('id');
            if(!empty($user_ids))
            {
                $where[] = ['user_id', 'in', $user_ids];
            } else {
                // 无数据条件，走单号条件
                $where[] = ['payment_user_order_no', '=', $params['keywords']];
            }
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 订单状态
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['status', '=', $params['status']];
            }

            // 结算状态
            if(isset($params['settlement_status']) && $params['settlement_status'] > -1)
            {
                $where[] = ['settlement_status', '=', $params['settlement_status']];
            }
        }

        return $where;
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
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        $user_type = isset($params['user_type']) ? $params['user_type'] : 'user';

        $data = Db::name('PluginsMembershiplevelvipUserProfit')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        if(!empty($data))
        {
            $common_platform_type = MyConst('common_platform_type');
            $order_status_list = MyConst('common_order_status');
            $order_pay_status = MyConst('common_order_pay_status');
            $status_list = self::$payment_user_profit_status_list;
            foreach($data as &$v)
            {
                // 用户信息
                $v['user'] = ($user_type == 'admin') ? UserService::GetUserViewInfo($v['user_id']) : [];

                // 级别
                $v['level_name'] = isset(self::$level_name_list[$v['level']]) ? self::$level_name_list[$v['level']]['name'] : '未知';

                // 佣金状态
                $v['status_name'] = isset($status_list[$v['status']]) ? $status_list[$v['status']]['name'] : '未知';

                // 创建时间
                $v['add_time'] = empty($v['add_time']) ? '' : date('Y-m-d H:i:s', $v['add_time']);

                // 更新时间
                $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
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
            $order_ids = Db::name('PluginsMembershiplevelvipPaymentUserOrder')->where('payment_user_order_no', '=', $params['keywords'])->column('id');
            if(!empty($order_ids))
            {
                $where[] = ['payment_user_order_id', 'in', $order_ids];
            } else {
                $user_ids = Db::name('User')->where('number_code|username|nickname|mobile|email', '=', $params['keywords'])->column('id');
                if(!empty($user_ids))
                {
                    $where[] = ['user_id', 'in', $user_ids];
                } else {
                    // 无数据条件，避免搜索条件没有数据造成的错觉
                    $where[] = ['id', '=', 0];
                }
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
            $where[] = ['user_id', '=', intval($params['uid'])];
        }

        // id
        if(!empty($params['id']))
        {
            $where[] = ['id', '=', intval($params['id'])];
        }

        // 更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 是否有退款
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['status', '=', intval($params['status'])];
            }

            // 级别
            if(isset($params['level']) && $params['level'] > 0)
            {
                $where[] = ['level', '=', intval($params['level'])];
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
     * 用户收益明细总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function UserProfitTotal($where)
    {
        return (int) Db::name('PluginsMembershiplevelvipUserProfit')->where($where)->count();
    }

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
        $is_privacy = isset($params['is_privacy']) ? (boolean) $params['is_privacy'] : true;

        $data = Db::name('User')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                // 用户信息处理
                $v = UserService::GetUserViewInfo($v['id'], $v, $is_privacy);

                // 等级信息
                $v['membershiplevelvip_auto_level_data'] = BusinessService::UserVip($v['id']);

                // 当前用户下级用户id列表
                $user_find_ids = Db::name('User')->where(['referrer'=>$v['id']])->column('id');

                // 当前用户下一级总数
                $v['referrer_count'] = empty($user_find_ids) ? '' : count($user_find_ids);

                // 当前用户下一级消费总金额
                if(empty($user_find_ids))
                {
                    $v['find_order_total'] = '';
                } else {
                    $find_where = [
                        ['user_id', 'in', $user_find_ids],
                        ['status', '=', 1],
                    ];
                    $find_order_total = Db::name('PluginsMembershiplevelvipPaymentUserOrder')->where($find_where)->sum('pay_price');
                    $v['find_order_total'] = ($find_order_total <= 0) ? '' : PriceNumberFormat($find_order_total);
                }

                // 订单有效金额
                $order_where = [
                    ['user_id', '=', $v['id']],
                    ['status', '=', 1],
                ];
                $order_total = Db::name('PluginsMembershiplevelvipPaymentUserOrder')->where($order_where)->sum('pay_price');
                $v['order_total'] = ($order_total <= 0) ? '' : PriceNumberFormat($order_total);

                // 创建时间
                $v['add_time'] = empty($v['add_time']) ? '' : date('Y-m-d H:i:s', $v['add_time']);

                // 更新时间
                $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $data);
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
        $where = [
            ['is_delete_time', '=', 0],
        ];

        // 关键字筛选
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

        // 用户
        if(!empty($params['user']))
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
     * 用户团队总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function UserTeamTotal($where)
    {
        return (int) Db::name('User')->where($where)->count();
    }

    /**
     * 支付方式获取
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-19
     * @desc    description
     * @param  [string]          $config [插件配置]
     */
    public static function HomeBuyPaymentList($config = [])
    {
        // 排除线下支付
        $not = MyConfig('shopxo.under_line_list');
        $where = [
            ['is_enable', '=', 1],
            ['is_open_user', '=', 1],
            ['payment', 'not in', $not],
        ];
        $data = PaymentService::BuyPaymentList(['where'=>$where]);

        // 是否存在支付方式限制
        if(!empty($data) && !empty($config['user_buy_can_payment']) && is_array($config['user_buy_can_payment']))
        {
            foreach($data as $k=>$v)
            {
                if(!in_array($v['payment'], $config['user_buy_can_payment']))
                {
                    unset($data[$k]);
                }
            }
        }

        return empty($data) ? [] : array_values($data);
    }

    /**
     * 基础配置信息保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseConfigSave($params = [])
    {
        // 会员首页描述
        if(!empty($params['banner_bottom_content']))
        {
            $params['banner_bottom_content'] = ResourcesService::ContentStaticReplace(htmlspecialchars_decode($params['banner_bottom_content']), 'add');
        }

        return PluginsService::PluginsDataSave(['plugins'=>'membershiplevelvip', 'data'=>$params], self::$base_config_attachment_field);
    }

    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [boolean]          $is_cache [是否缓存中读取]
     */
    public static function BaseConfig($is_cache = true)
    {
        $ret = PluginsService::PluginsData('membershiplevelvip', self::$base_config_attachment_field, $is_cache);
        if(!empty($ret['data']))
        {
            // 未开通会员介绍
            if(!empty($ret['data']['not_opening_vip_desc']))
            {
                $ret['data']['not_opening_vip_desc'] = explode("\n", $ret['data']['not_opening_vip_desc']);
            }

            // 会员中心公告
            if(!empty($ret['data']['user_vip_center_notice']))
            {
                $ret['data']['user_vip_center_notice'] = explode("\n", $ret['data']['user_vip_center_notice']);
            }

            // 会员首页描述
            if(!empty($ret['data']['banner_bottom_content']))
            {
                $ret['data']['banner_bottom_content'] = ResourcesService::ContentStaticReplace($ret['data']['banner_bottom_content'], 'get');
            }

            // 充值可选支付方式
            if(!empty($ret['data']['user_buy_can_payment']))
            {
                $ret['data']['user_buy_can_payment'] = explode(',', $ret['data']['user_buy_can_payment']);
            }

            // 商品处理
            $ret['data']['appoint_only_join_vip_discount_goods_ids'] = empty($ret['data']['appoint_only_join_vip_discount_goods_ids']) ? [] : $ret['data']['appoint_only_join_vip_discount_goods_ids'];
            $ret['data']['appoint_only_join_vip_discount_goods_list'] = [];

            $ret['data']['appoint_not_join_vip_discount_goods_ids'] = empty($ret['data']['appoint_not_join_vip_discount_goods_ids']) ? [] : $ret['data']['appoint_not_join_vip_discount_goods_ids'];
            $ret['data']['appoint_not_join_vip_discount_goods_list'] = [];

        // 查询商品进行组装
            $goods_ids = array_merge($ret['data']['appoint_only_join_vip_discount_goods_ids'], $ret['data']['appoint_not_join_vip_discount_goods_ids']);
            if(!empty($goods_ids))
            {
                $goods = Db::name('Goods')->where(['id'=>$goods_ids])->field('id,title,images')->select()->toArray();
                if(!empty($goods))
                {
                    foreach($goods as $g)
                    {
                        $g['goods_url'] = GoodsService::GoodsUrlCreate($g['id']);
                        if(in_array($g['id'], $ret['data']['appoint_only_join_vip_discount_goods_ids']))
                        {
                            $ret['data']['appoint_only_join_vip_discount_goods_list'][] = $g;
                        }
                        if(in_array($g['id'], $ret['data']['appoint_not_join_vip_discount_goods_ids']))
                        {
                            $ret['data']['appoint_not_join_vip_discount_goods_list'][] = $g;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 默认图片数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-07-27
     * @desc    description
     * @param   [array]          $config [配置信息]
     */
    public static function DefaultImagesData($config = [])
    {
        return [
            'default_bg_images'   => empty($config['default_bg_images']) ? StaticAttachmentUrl('index-bg.png') : $config['default_bg_images'],
            'default_btn_bg'      => empty($config['default_btn_bg']) ? StaticAttachmentUrl('btn.png') : $config['default_btn_bg'],
            'banner_middle_name'  => empty($config['banner_middle_name']) ? '加入会员' : $config['banner_middle_name'],
            'default_logo'        => empty($config['default_logo']) ? StaticAttachmentUrl('logo.png') : $config['default_logo'],
        ];
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
                'name'      => '会员等级',
                'control'   => 'level',
                'action'    => 'index',
            ],
            [
                'name'      => '付费会员',
                'control'   => 'user',
                'action'    => 'index',
            ],
            [
                'name'      => '会员订单',
                'control'   => 'order',
                'action'    => 'index',
            ],
            [
                'name'      => '分销员管理',
                'control'   => 'team',
                'action'    => 'index',
            ],
            [
                'name'      => '收益明细',
                'control'   => 'profit',
                'action'    => 'index',
            ],
            [
                'name'      => '首页会员介绍',
                'control'   => 'introduce',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 用户中心菜单 - web端
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-18
     * @desc    description
     * @param   [array]          $config [配置信息]
     */
    public static function WebUserCenterNav($config = [])
    {
        $data = [];
        if(isset($config['is_user_buy']) && $config['is_user_buy'] == 1)
        {
            $data = [
                [
                    'title'    => '我的会员',
                    'icon'     => '',
                    'plugins'  => 'membershiplevelvip',
                    'control'  => 'vip',
                    'action'   => 'index',
                ],
                [
                    'title'    => '开通订单',
                    'icon'     => StaticAttachmentUrl('web/order-icon.png'),
                    'plugins'  => 'membershiplevelvip',
                    'control'  => 'order',
                    'action'   => 'index',
                ],
            ];

            // 推广收益
            if(isset($config['is_commission']) && $config['is_commission'] == 1)
            {
                $data[] = [
                    'title'    => '收益明细',
                    'icon'     => StaticAttachmentUrl('web/profit-icon.png'),
                    'plugins'  => 'membershiplevelvip',
                    'control'  => 'profit',
                    'action'   => 'index',
                ];
                $data[] = [
                    'title'    => '提现明细',
                    'icon'     => StaticAttachmentUrl('web/cash-icon.png'),
                    'plugins'  => 'wallet',
                    'control'  => 'cash',
                    'action'   => 'index',
                ];
            }

            // 我的团队
            if(isset($config['is_propaganda']) && $config['is_propaganda'] == 1)
            {
                $data[] = [
                    'title'    => '我的团队',
                    'icon'     => StaticAttachmentUrl('web/team-icon.png'),
                    'plugins'  => 'membershiplevelvip',
                    'control'  => 'team',
                    'action'   => 'index',
                ];
                $data[] = [
                    'title'    => '推广奖励',
                    'icon'     => StaticAttachmentUrl('web/poster-icon.png'),
                    'plugins'  => 'membershiplevelvip',
                    'control'  => 'poster',
                    'action'   => 'index',
                ];
            }

            // 会员首页
            $data[] = [
                'title'    => '会员首页',
                'icon'     => StaticAttachmentUrl('web/home-icon.png'),
                'plugins'  => 'membershiplevelvip',
                'control'  => 'index',
                'action'   => 'index',
            ];
        }

        return $data;
    }

    /**
     * 用户中心菜单 - app端
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-18
     * @desc    description
     * @param   [array]          $config [配置信息]
     */
    public static function AppUserCenterNav($config = [])
    {
        $data = [];
        if(isset($config['is_user_buy']) && $config['is_user_buy'] == 1)
        {
            $data = [
                [
                    'icon'  => StaticAttachmentUrl('app/center/order-icon.png'),
                    'title' => '开通订单',
                    'url'   => '/pages/plugins/membershiplevelvip/order/order',
                ]
            ];

            // 开启返佣
            if(isset($config['is_commission']) && $config['is_commission'] == 1)
            {
                $data[] = [
                    'icon'  => StaticAttachmentUrl('app/center/profit-icon.png'),
                    'title' => '收益明细',
                    'url'   => '/pages/plugins/membershiplevelvip/profit/profit',
                ];
            }

            // 开启推广
            if(isset($config['is_propaganda']) && $config['is_propaganda'] == 1)
            {
                $data[] = [
                    'icon'  => StaticAttachmentUrl('app/center/team-icon.png'),
                    'title' => '我的团队',
                    'url'   => '/pages/plugins/membershiplevelvip/team/team',
                ];
                $data[] = [
                    'icon'  => StaticAttachmentUrl('app/center/poster-icon.png'),
                    'title' => '推广奖励',
                    'url'   => '/pages/plugins/membershiplevelvip/poster/poster',
                ];
            }

            // 会员首页
            $data[] = [
                'icon'  => StaticAttachmentUrl('app/center/index-icon.png'),
                'title' => '会员首页',
                'url'   => '/pages/plugins/membershiplevelvip/index/index',
            ];
        }
        return $data;
    }

    /**
     * 二维码清空
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-12T20:36:38+0800
     * @param   [array]           $params [输入参数]
     */
    public static function QrcodeDelete($params = [])
    {
        $dir_all = ['qrcode'];
        foreach($dir_all as $v)
        {
            $dir = 'download'.DS.'plugins_membershiplevelvip'.DS.$v;
            if(is_dir($dir))
            {
                // 是否有权限
                if(!is_writable($dir))
                {
                    return DataReturn('目录没权限', -1);
                }

                // 删除目录
                \base\FileUtil::UnlinkDir($dir);
            }
        }

        return DataReturn(MyLang('operate_success'), 0);
    }

    /**
     * 商品是否可以优惠
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-11-05
     * @desc    description
     * @param   [int]          $goods_id [商品id]
     * @param   [array]        $config   [配置信息]
     */
    public static function IsGoodsDiscountConfig($goods_id, $config)
    {
        // 排除不参加统一折扣的商品
        if(isset($config['is_not_join_vip_discount_goods']) && $config['is_not_join_vip_discount_goods'] == 1 && !empty($config['appoint_not_join_vip_discount_goods_ids']) && is_array($config['appoint_not_join_vip_discount_goods_ids']) && in_array($goods_id, $config['appoint_not_join_vip_discount_goods_ids']))
        {
            return false;
        }

        // 仅参数的统一折扣商品
        if(isset($config['is_only_join_vip_discount_goods']) && $config['is_only_join_vip_discount_goods'] == 1 && !empty($config['appoint_only_join_vip_discount_goods_ids']) && is_array($config['appoint_only_join_vip_discount_goods_ids']) && !in_array($goods_id, $config['appoint_only_join_vip_discount_goods_ids']))
        {
            return false;
        }
        return true;
    }

    /**
     * 商品搜索 - 分页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-13
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function LimitGoodsSearchList($params = [])
    {
        // 返回数据
        $result = [
            'page_total'    => 0,
            'page_size'     => 20,
            'page'          => max(1, isset($params['page']) ? intval($params['page']) : 1),
            'total'         => 0,
            'data'          => [],
        ];

        // 条件
        $where = [
            ['g.is_delete_time', '=', 0],
            ['g.is_shelves', '=', 1]
        ];

        // 关键字
        if(!empty($params['keywords']))
        {
            $where[] = ['g.title', 'like', '%'.$params['keywords'].'%'];
        }

        // 分类id
        if(!empty($params['category_id']))
        {
            $category_ids = GoodsCategoryService::GoodsCategoryItemsIds([$params['category_id']], 1);
            $category_ids[] = $params['category_id'];
            $where[] = ['gci.category_id', 'in', $category_ids];
        }

        // 获取商品总数
        $result['total'] = GoodsService::CategoryGoodsTotal($where);

        // 获取商品列表
        if($result['total'] > 0)
        {
            // 基础参数
            $field = 'g.id,g.title,g.images';
            $order_by = 'g.id desc';

            // 分页计算
            $m = intval(($result['page']-1)*$result['page_size']);
            $goods = GoodsService::CategoryGoodsList(['where'=>$where, 'm'=>$m, 'n'=>$result['page_size'], 'field'=>$field, 'order_by'=>$order_by]);
            $result['data'] = $goods['data'];
            $result['page_total'] = ceil($result['total']/$result['page_size']);
             // 数据处理
            if(!empty($result['data']) && is_array($result['data']) && !empty($params['goods_ids']) && is_array($params['goods_ids']))
            {
                foreach($result['data'] as &$v)
                {
                    // 是否已添加
                    $v['is_exist'] = in_array($v['id'], $params['goods_ids']) ? 1 : 0;
                }
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 优惠券列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-06-25
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function CouponList($params = [])
    {
        // 优惠券
        $data_params = [
            'm'      => 0,
            'n'      => 0,
            'where'  => ['is_enable' => 1],
        ];
        $coupon = CallPluginsServiceMethod('coupon', 'CouponService', 'CouponList', $data_params);
        return empty($coupon['data']) ? [] : $coupon['data'];
    }
}
?>