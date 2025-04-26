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
namespace app\plugins\coupon\service;

use think\facade\Db;
use app\service\UserService;
use app\plugins\coupon\service\BaseService;
use app\plugins\coupon\service\CouponService;

/**
 * 用户优惠券服务层-用户
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class UserCouponService
{
    /**
     * 用户优惠券列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date    2019-08-08
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CouponUserList($params = [])
    {
        // 用户优惠券过期处理
        self::CouponUserExpireHandle();

        // 参数
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];

        // 获取数据列表
        $data = Db::name('PluginsCouponUser')->field($field)->where($where)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::CouponUserListHandle($data, $params));
    }

    /**
     * 列表数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-07
     * @desc    description
     * @param   [array]          $data   [数据列表]
     * @param   [array]          $params [输入参数]
     */
    public static function CouponUserListHandle($data, $params = [])
    {
        $result = [];
        if(!empty($data))
        {
            $coupons = [];
            $is_group = (isset($params['is_group']) && $params['is_group'] == 1);
            $user_type = isset($params['user_type']) ? $params['user_type'] : 'user';
            if($user_type == 'admin')
            {
                $user = UserService::GetUserViewInfo(array_column($data, 'user_id'));
            }
            $status_operable_list = [3=>MyLang('to_use_name'), 4=>MyLang('used_name'), 5=>MyLang('expired_name')];
            foreach($data as $v)
            {
                // 用户信息
                if($user_type == 'admin' && !empty($user))
                {
                    $v['user'] = array_key_exists($v['user_id'], $user) ? $user[$v['user_id']] : null;
                }

                // 优惠券信息
                if(!isset($coupons[$v['coupon_id']]))
                {
                    $coupons[$v['coupon_id']] = self::CouponData($v['coupon_id']);
                }
                $v['coupon'] = $coupons[$v['coupon_id']];

                // 状态  0可领取，1已领取，2已抢完，3去使用，4已使用，5已过期
                $v['status_type'] = ($v['is_use'] == 1) ? 4 : (($v['is_expire'] == 1) ? 5 : 3);
                $v['status_operable_name'] = isset($status_operable_list[$v['status_type']]) ? $status_operable_list[$v['status_type']] : '';

                // 使用时间
                $v['use_time'] = empty($v['use_time']) ? '' : date('Y-m-d H:i', $v['use_time']);
                // 过期提示
                $v['expire_tips'] = empty($v['use_time']) ? ($v['time_end']-time() <= 60*60*24 ? MyLang('fast_expire_tips') : '') : '';

                // 有效时间
                $v['time_start_show_text'] = date('m.d H:i', $v['time_start']);
                $v['time_end_show_text'] = date('m.d H:i', $v['time_end']);
                $v['time_start'] = date('Y-m-d H:i:s', $v['time_start']);
                $v['time_end'] = date('Y-m-d H:i:s', $v['time_end']);

                // 时间
                $v['add_time'] = empty($v['add_time']) ? '' : date('Y-m-d H:i:s', $v['add_time']);
                $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);

                // 按照类型分组
                if($is_group)
                {
                    $result[self::CouponTabGroup($v)][] = $v;
                } else {
                    $result[] = $v;
                }
            }
        }
        return $result;
    }

    /**
     * 优惠券分组
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]          $data [优惠券信息]
     */
    private static function CouponTabGroup($data)
    {
        // not_use 未使用, already_use 已使用, already_expire 已过期
        $value = 'not_use';
        if($data['is_use'] == 1)
        {
            $value = 'already_use';
        } else {
            if($data['is_expire'] == 1)
            {
                $value = 'already_expire';
            }
        }
        return $value;
    }

    /**
     * 获取优惠券信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [int]          $coupon_id [优惠券id]
     */
    private static function CouponData($coupon_id)
    {
        $data = Db::name('PluginsCoupon')->field('id,shop_id,name,desc,type,bg_color,expire_type,discount_value,use_limit_type,use_value_ids,where_order_price')->find($coupon_id);
        if(!empty($data))
        {
            // 静态资源
            $coupon_type_list = BaseService::ConstData('coupon_type_list');
            $coupon_bg_color_list = BaseService::ConstData('coupon_bg_color_list');
            $coupon_expire_type_list = BaseService::ConstData('coupon_expire_type_list');
            $coupon_use_limit_type_list = BaseService::ConstData('coupon_use_limit_type_list');

            // 优惠券类型
            $data['type_name'] = (isset($data['type']) && isset($coupon_type_list[$data['type']])) ? $coupon_type_list[$data['type']]['name'] : MyLang('unknow_text');
            $data['type_unit'] = (!isset($data['type']) || $data['type'] == 0) ? MyLang('price_unit_text') : MyLang('break_name');

            // 背景色
            if((isset($data['bg_color']) && isset($coupon_bg_color_list[$data['bg_color']])))
            {
                $data['bg_color_name'] = $coupon_bg_color_list[$data['bg_color']]['name'];
                $data['bg_color_value'] = $coupon_bg_color_list[$data['bg_color']]['color'];
            } else {
                $data['bg_color_name'] = MyLang('unknow_text');
                $data['bg_color_value'] = '#D2364C';
            }

            // 过期类型
            $data['expire_type_name'] = (isset($data['expire_type']) && isset($coupon_expire_type_list[$data['expire_type']])) ? $coupon_expire_type_list[$data['expire_type']]['name'] : MyLang('unknow_text');

            // 使用限制类型
            $data['use_limit_type_name'] = (isset($data['use_limit_type']) && isset($coupon_use_limit_type_list[$data['use_limit_type']])) ? $coupon_use_limit_type_list[$data['use_limit_type']]['name'] : MyLang('unknow_text');

            // 限制条件值
            $data['use_value_ids_all'] = empty($data['use_value_ids']) ? [] : json_decode($data['use_value_ids'], true);

            // 优惠金额/折扣美化
            $data['discount_value'] = PriceBeautify($data['discount_value']);
        }
        return $data;
    }

    /**
     * 用户优惠券过期处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     */
    public static function CouponUserExpireHandle()
    {
        $where = [
            ['is_use', '=', 0],
            ['is_expire', '=', 0],
            ['time_end', '<', time()],
        ];
        $count = Db::name('PluginsCouponUser')->where($where)->update(['is_expire'=>1, 'upd_time'=>time()]);
        return DataReturn(MyLang('handle_success'), 0, $count);
    }

    /**
     * 用户优惠券使用状态更新
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-15
     * @desc    description
     * @param   [string|array]     $extension_data [订单扩展数据]
     * @param   [int]              $status         [状态值 0|1]
     * @param   [int]              $use_order_id   [使用的订单id]
     */
    public static function UserCouponUseStatusUpdate($extension_data, $status, $use_order_id = 0)
    {
        $fail = 0;
        $success = 0;
        if(!empty($extension_data))
        {
            if(is_string($extension_data))
            {
                $extension_data = json_decode($extension_data, true);
            }
            if(!empty($extension_data) && is_array($extension_data))
            {
                foreach($extension_data as $ext)
                {
                    if(isset($ext['business']) && $ext['business'] == 'plugins-coupon' && !empty($ext['ext']) && !empty($ext['ext']['id']))
                    {
                        $data = [
                            'is_use'    => $status,
                            'upd_time'  => time(),
                        ];
                        if($status == 1)
                        {
                            $data['use_time'] = time();
                            $data['use_order_id'] = $use_order_id;
                        } else {
                            $data['use_time'] = 0;
                            $data['use_order_id'] = 0;
                        }
                        if(Db::name('PluginsCouponUser')->where(['id'=>intval($ext['ext']['id'])])->update($data))
                        {
                            $success++;
                        }
                        $fail++;
                    }
                }
            }
        }
        return DataReturn(MyLang('update_success'), 0, ['success'=>$success, 'fail'=>$fail]);
    }

    /**
     * 注册赠送优惠券
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-15
     * @desc    description
     * @param   [int]          $user_id [用户id]
     */
    public static function UserRegisterGive($user_id)
    {
        // 获取已启用/可注册领取优惠券列表
        $where = [
            'is_enable'         => 1,
            'is_regster_send'   => 1,
        ];
        $coupon = Db::name('PluginsCoupon')->where($where)->field('id,is_repeat_receive')->select()->toArray();
        if(!empty($coupon))
        {
            // 循环发放优惠券
            $fail = 0;
            $success = 0;
            foreach($coupon as $v)
            {
                // 是否已领取过
                if($v['is_repeat_receive'] != 1)
                {
                    $temp = Db::name('PluginsCouponUser')->where(['coupon_id'=>$v['id'], 'user_id'=>$user_id])->find();
                    if(!empty($temp))
                    {
                        continue;
                    }
                }

                // 用户优惠券发放
                $coupon_params = [
                    'user_ids'          => [$user_id],
                    'coupon_id'         => $v['id'],
                    'is_regster_send'   => 1,
                ];
                $ret = CouponService::CouponSend($coupon_params);
                if($ret['code'] == 0)
                {
                    $success++;
                } else {
                    $fail++;
                }
            }
            return DataReturn(MyLang('grant_success'), 0, ['success'=>$success, 'fail'=>$fail]);
        }
        return DataReturn(MyLang('not_use_coupon_message'), -1);
    }
}
?>