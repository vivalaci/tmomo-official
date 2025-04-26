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
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\service\UserService;
use app\service\ResourcesService;
use app\plugins\coupon\service\BaseService;
use app\plugins\coupon\service\UserNoticeService;

/**
 * 优惠券服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class CouponService
{
    /**
     * 商品搜索
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2019-08-07T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function GoodsSearchList($params = [])
    {
        // 条件
        $where = array_merge([
            ['g.is_delete_time', '=', 0],
            ['g.is_shelves', '=', 1]
        ], empty($params['where']) ? [] : $params['where']);

        // 关键字
        if(!empty($params['keywords']))
        {
            $where[] = ['g.title', 'like', '%'.$params['keywords'].'%'];
        }

        // 是否店铺
        if(empty($params['shop_id']))
        {
            // 系统商品分类id
            if(!empty($params['category_id']))
            {
                $category_ids = GoodsCategoryService::GoodsCategoryItemsIds([$params['category_id']], 1);
                $category_ids[] = $params['category_id'];
                $where[] = ['gci.category_id', 'in', $category_ids];
            }
        } else {
            $where[] = ['g.shop_id', '=', intval($params['shop_id'])];
            // 店铺商品分类
            if(!empty($params['category_id']))
            {
                $where[] = ['g.shop_category_id', '=', intval($params['category_id'])];
            }
        }

        // 商品id
        if(!empty($params['goods_ids']))
        {
            $goods_ids = is_array($params['goods_ids']) ? $params['goods_ids'] : explode(',', $params['goods_ids']);
            $where[] = ['g.id', 'in', $goods_ids];
        }

        // 获取数据
        return GoodsService::CategoryGoodsList(['where'=>$where, 'm'=>0, 'n'=>300, 'is_admin_access'=>1]);
    }

    /**
     * 优惠券保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-08
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CouponSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '1,30',
                'error_msg'         => MyLang('name_length_max_30_message'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'desc',
                'checked_data'      => '60',
                'error_msg'         => MyLang('desc_length_max_60_message'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'bg_color',
                'checked_data'      => array_keys(BaseService::ConstData('coupon_bg_color_list')),
                'error_msg'         => MyLang('coupon_color_error_message'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'sort',
                'checked_data'      => '3',
                'error_msg'         => MyLang('form_sort_message'),
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'sort',
                'checked_data'      => 255,
                'error_msg'         => MyLang('order_not_greate_255_message'),
            ],
        ];
        
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 是否编辑
        $shop_id = empty($params['shop_id']) ? 0 : intval($params['shop_id']);
        if(!empty($params['id']))
        {
            $coupon = Db::name('PluginsCoupon')->where(['shop_id'=>$shop_id, 'id'=>intval($params['id'])])->find();
        }

        // 非编辑或者已发放数量为0则需要校验核心数据
        if(empty($coupon) || $coupon['already_send_count'] <= 0)
        {
            $p[] = [
                'checked_type'      => 'in',
                'key_name'          => 'type',
                'checked_data'      => array_keys(BaseService::ConstData('coupon_type_list')),
                'error_msg'         => MyLang('coupon_type_error_message'),
            ];
            $p[] = [
                'checked_type'      => 'in',
                'key_name'          => 'expire_type',
                'checked_data'      => array_keys(BaseService::ConstData('coupon_expire_type_list')),
                'error_msg'         => MyLang('expire_type_error_message'),
            ];
            $p[] = [
                'checked_type'      => 'isset',
                'key_name'          => 'where_order_price',
                'error_msg'         => MyLang('order_min_price_error_message'),
            ];
            $p[] = [
                'checked_type'      => 'in',
                'key_name'          => 'use_limit_type',
                'checked_data'      => array_keys(BaseService::ConstData('coupon_use_limit_type_list')),
                'error_msg'         => MyLang('use_limit_error_message'),
            ];
            if(isset($params['type']) && $params['type'] == 1)
            {
                $p[] = [
                    'checked_type'      => 'isset',
                    'key_name'          => 'discount_price',
                    'error_msg'         => MyLang('exemption_price_error_message'),
                ];
            } else {
                $p[] = [
                    'checked_type'      => 'isset',
                    'key_name'          => 'discount_rate',
                    'error_msg'         => MyLang('discount_error_message'),
                ];
            }
        }

        // 使用限制值
        $use_value_ids = '';
        if(isset($params['use_limit_type']))
        {
            if($params['use_limit_type'] == 1 && !empty($params['category_ids']))
            {
                $use_value_ids = json_encode(explode(',', $params['category_ids']));
            } else if($params['use_limit_type'] == 2 && !empty($params['goods_ids']))
            {
                $use_value_ids = json_encode(explode(',', $params['goods_ids']));
            }
        }

        // 数据
        $data = [
            'shop_id'           => $shop_id,
            'name'              => $params['name'],
            'desc'              => $params['desc'],
            'bg_color'          => $params['bg_color'],
            'sort'              => intval($params['sort']),
            'is_user_receive'   => isset($params['is_user_receive']) ? intval($params['is_user_receive']) : 0,
            'is_repeat_receive'  => isset($params['is_repeat_receive']) ? intval($params['is_repeat_receive']) : 0,
            'is_regster_send'   => isset($params['is_regster_send']) ? intval($params['is_regster_send']) : 0,
            'is_enable'         => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
        ];

        // 非编辑或者已发放数量为0则需要校验核心数据
        if(empty($coupon) || $coupon['already_send_count'] <= 0)
        {
            $data['type'] = intval($params['type']);
            $data['expire_type'] = intval($params['expire_type']);
            $data['expire_hour'] = ($params['expire_type'] == 0 && isset($params['expire_hour'])) ? intval($params['expire_hour']) : 0;
            $data['use_limit_type'] = isset($params['use_limit_type']) ? intval($params['use_limit_type']) : 0;
            $data['fixed_time_start'] = empty($params['fixed_time_start']) ? 0 : strtotime($params['fixed_time_start']);
            $data['fixed_time_end'] = empty($params['fixed_time_end']) ? 0 : strtotime($params['fixed_time_end']);
            $data['where_order_price'] = PriceNumberFormat($params['where_order_price']);
            $data['use_value_ids'] = $use_value_ids;
            $data['discount_value'] = ($params['type'] == 1) ? PriceNumberFormat($params['discount_rate']) : PriceNumberFormat($params['discount_price']);
            $data['limit_send_count'] = isset($params['limit_send_count']) ? intval($params['limit_send_count']) : 0;
        }

        if(empty($coupon))
        {
            $data['add_time'] = time();
            if(Db::name('PluginsCoupon')->insertGetId($data) > 0)
            {
                return DataReturn(MyLang('insert_success'), 0);
            }
            return DataReturn(MyLang('insert_fail'), -100);
        } else {
            $data['upd_time'] = time();
            if(Db::name('PluginsCoupon')->where(['id'=>$coupon['id']])->update($data))
            {
                return DataReturn(MyLang('edit_success'), 0);
            }
            return DataReturn(MyLang('edit_fail'), -100); 
        }
    }

    /**
     * 优惠券列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date    2019-08-08
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CouponList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'sort asc,id desc' : $params['order_by'];

        // 获取数据列表
        $data = Db::name('PluginsCoupon')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::CouponListHandle($data, $params));
    }

    /**
     * 优惠券总数
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-09
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function CouponTotal($where)
    {
        return (int) Db::name('PluginsCoupon')->where($where)->count();
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
    public static function CouponListHandle($data, $params = [])
    {
        // 是否处理数据
        $is_handle = isset($params['is_handle']) ? intval($params['is_handle']) : 1;
        if(!empty($data) && $is_handle == 1)
        {
            // 静态资源
            $coupon_type_list = BaseService::ConstData('coupon_type_list');
            $coupon_bg_color_list = BaseService::ConstData('coupon_bg_color_list');
            $coupon_expire_type_list = BaseService::ConstData('coupon_expire_type_list');
            $coupon_use_limit_type_list = BaseService::ConstData('coupon_use_limit_type_list');

            // 价格符号
            $currency_symbol = ResourcesService::CurrencyDataSymbol();

            // 校验用户是否已领取
            $is_sure_receive = isset($params['is_sure_receive']) ? intval($params['is_sure_receive']) : 0;

            foreach($data as &$v)
            {
                // url
                if(array_key_exists('id', $v))
                {
                    $v['url'] = (APPLICATION == 'web') ? PluginsHomeUrl('coupon', 'index', 'detail', ['id'=>$v['id']]) : '/pages/plugins/coupon/detail/detail?id='.$v['id'];
                }

                // 该优惠券是否可以操作
                // 0可领取，1已领取，2已抢完，3去使用，4已使用，5已过期
                $v['status_type'] = 0;
                // 展示操作名称
                $v['status_operable_name'] = MyLang('receive_text');

                // 校验用户是否已领取
                // 不允许重复领取
                // 用户已登录
                // 达到以上三个条件则校验当前登录用户是否还可以领取当前优惠券
                if($is_sure_receive == 1 && $v['is_repeat_receive'] != 1 && !empty($params['user']))
                {
                    $temp = Db::name('PluginsCouponUser')->where(['coupon_id'=>$v['id'], 'user_id'=>$params['user']['id']])->find();
                    if(!empty($temp))
                    {
                        $v['status_type'] = 1;
                        $v['status_operable_name'] = MyLang('received_text');
                    }
                }

                // 是否已过期
                if($v['status_type'] == 0 && isset($v['expire_type']) && $v['expire_type'] == 1 && isset($v['fixed_time_end']) && $v['fixed_time_end'] < time())
                {
                    $v['status_type'] = 5;
                    $v['status_operable_name'] = MyLang('expired_name');
                }

                // 是否超限
                if($v['status_type'] == 0 && isset($v['limit_send_count']) && isset($v['already_send_count']) && $v['limit_send_count'] > 0 && $v['limit_send_count'] <= $v['already_send_count'])
                {
                    $v['status_type'] = 2;
                    $v['status_operable_name'] = MyLang('received_over_text');
                }

                // 优惠券类型
                $v['type_name'] = (isset($v['type']) && isset($coupon_type_list[$v['type']])) ? $coupon_type_list[$v['type']]['name'] : MyLang('unknow_text');
                $v['type_first'] = (!isset($v['type']) || $v['type'] == 0) ? $currency_symbol : '';
                $v['type_unit'] = (!isset($v['type']) || $v['type'] == 0) ? MyLang('price_unit_text') : MyLang('break_name');

                // 背景色
                if((isset($v['bg_color']) && isset($coupon_bg_color_list[$v['bg_color']])))
                {
                    $v['bg_color_name'] = $coupon_bg_color_list[$v['bg_color']]['name'];
                    $v['bg_color_value'] = $coupon_bg_color_list[$v['bg_color']]['color'];
                } else {
                    $v['bg_color_name'] = MyLang('unknow_text');
                    $v['bg_color_value'] = '#D2364C';
                }

                // 过期类型
                $v['expire_type_name'] = (isset($v['expire_type']) && isset($coupon_expire_type_list[$v['expire_type']])) ? $coupon_expire_type_list[$v['expire_type']]['name'] : MyLang('unknow_text');

                // 使用限制类型
                $v['use_limit_type_name'] = (isset($v['use_limit_type']) && isset($coupon_use_limit_type_list[$v['use_limit_type']])) ? $coupon_use_limit_type_list[$v['use_limit_type']]['name'] : MyLang('unknow_text');

                // 优惠使用限制关联id
                $v['use_value_ids_all'] = empty($v['use_value_ids']) ? [] : json_decode($v['use_value_ids'], true);
                $v['use_value_ids_str'] = empty($v['use_value_ids_all']) ? '' : implode(',', $v['use_value_ids_all']);

                // 过期时间
                $v['fixed_time_start'] = empty($v['fixed_time_start']) ? '' : date('m-d H:i:s', $v['fixed_time_start']);
                $v['fixed_time_end'] = empty($v['fixed_time_end']) ? '' : date('m-d H:i:s', $v['fixed_time_end']);

                // 优惠金额/折扣美化
                $v['discount_value'] = PriceBeautify($v['discount_value']);

                // 领取进度
                $process_data = ['type'=>0, 'msg'=>MyLang('unlimited_text')];
                if($v['limit_send_count'] > 0)
                {
                    $value = intval(($v['already_send_count']/$v['limit_send_count'])*100);
                    $process_data = [
                        'type'   => 1,
                        'value'  => $value,
                        'msg'    => MyLang('received_process_msg', ['value'=>$value]),
                    ];
                }
                $v['process_data'] = $process_data;

                // 使用限制
                if(!empty($v['use_value_ids_all']))
                {
                    // 商品分类
                    if($v['use_limit_type'] == 1)
                    {
                        $v['category_names'] = empty($v['shop_id']) ? Db::name('GoodsCategory')->where('id', 'in', $v['use_value_ids_all'])->column('name') : Db::name('PluginsShopGoodsCategory')->where('id', 'in', $v['use_value_ids_all'])->column('name');

                    // 商品
                    } else if($v['use_limit_type'] == 2)
                    {
                        $goods = self::GoodsSearchList(['goods_ids'=>$v['use_value_ids_all']]);
                        if(isset($goods['data']))
                        {
                            $v['goods_items'] = $goods['data'];
                        }
                    }
                }

                // 时间
                $v['add_time'] = empty($v['add_time']) ? '' : date('Y-m-d H:i:s', $v['add_time']);
                $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
            }
        }
        return $data;
    }

    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function StatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => MyLang('operate_field_error_tips'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => MyLang('form_status_range_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 操作条件
        $where = [
            ['id', '=', intval($params['id'])],
        ];
        // 是否指定店铺id
        if(!empty($params['shop_id']))
        {
            $where[] = ['shop_id', '=', intval($params['shop_id'])];
        }

        // 数据更新
        if(Db::name('PluginsCoupon')->where($where)->update([$params['field']=>intval($params['state'])]))
        {
           return DataReturn(MyLang('edit_success'), 0);
        }
        return DataReturn(MyLang('edit_fail'), -100);
    }

    /**
     * 删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function Delete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn(MyLang('data_id_error_tips'), -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 操作条件
        $where = [
            ['id', 'in', $params['ids']],
            ['already_send_count', '<=', 0],
        ];
        // 是否指定店铺id
        if(!empty($params['shop_id']))
        {
            $where[] = ['shop_id', '=', intval($params['shop_id'])];
        }

        // 删除操作
        if(Db::name('PluginsCoupon')->where($where)->delete())
        {
            return DataReturn(MyLang('delete_success'), 0);
        }
        return DataReturn(MyLang('delete_fail'), -100);
    }

    /**
     * 用户搜索
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-10T17:29:34+0800
     * @param    [array]             $params [输入参数]
     */
    public static function UserSearchList($params = [])
    {
        $where = [];
        $keywords = [];
        // 会员等级
        if(!empty($params['level_id']))
        {
            $where[] = ['plugins_user_auto_level', '=', intval($params['level_id'])];
        }
        // 关键字条件
        if(!empty($params['keywords']))
        {
            foreach(explode(' ', $params['keywords']) as $wd)
            {
                $keywords[] = ['number_code|username|nickname|mobile|email', 'like', '%'.$wd.'%'];
            }
        }
        $data = Db::name('User')->where($where)->where(function($query) use($keywords) {
            $query->whereOr($keywords);
        })->field('id,username,nickname,mobile,email,avatar')->limit(100)->select()->toArray();
        if(!empty($data))
        {
            foreach($data as &$user)
            {
                $user = UserService::UserHandle($user);
            }
        }
        return DataReturn(MyLang('get_success'), 0, $data);
    }

    /**
     * 用户优惠券发放
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-10T18:30:52+0800
     * @param    [array]           $params [输入参数]
     */
    public static function CouponSend($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'coupon_id',
                'error_msg'         => MyLang('coupon_id_error_message'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user_ids',
                'error_msg'         => MyLang('designated_distribution_user_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        if(!is_array($params['user_ids']))
        {
            $params['user_ids'] = explode(',', $params['user_ids']);
        }

        // 获取优惠券
        $where = [
            ['id', '=', intval($params['coupon_id'])],
        ];
        // 是否指定店铺id
        if(!empty($params['shop_id']))
        {
            $where[] = ['shop_id', '=', intval($params['shop_id'])];
        }
        $coupon = Db::name('PluginsCoupon')->where($where)->find();
        if(empty($coupon))
        {
            return DataReturn(MyLang('coupon_absent_or_del_message'), -1);
        }

        // 基础判断
        if($coupon['is_enable'] != 1)
        {
            return DataReturn(MyLang('coupon_not_use_message').'['.$coupon['name'].']', -1);
        }
        if($coupon['limit_send_count'] > 0 && $coupon['already_send_count'] >= $coupon['limit_send_count'])
        {
            return DataReturn(MyLang('coupon_grant_num_exceeded_limit_message').'['.$coupon['name'].']', -1);
        }

        // 用户领取
        if(isset($params['is_user_receive']) && $params['is_user_receive'] == 1 && $coupon['is_user_receive'] != 1)
        {
            return DataReturn(MyLang('not_open_receive_message').'['.$coupon['name'].']', -1);
        }

        // 注册发放
        if(isset($params['is_regster_send']) && $params['is_regster_send'] == 1 && $coupon['is_regster_send'] != 1)
        {
            return DataReturn(MyLang('not_support_regist_grant_message').'['.$coupon['name'].']', -1);
        }

        // 是否已过期
        switch($coupon['expire_type'])
        {
            // 领取生效
            case 0 :
                if($coupon['expire_hour'] <= 0)
                {
                    return DataReturn(MyLang('coupon_time_error_message').'['.$coupon['name'].']', -1);
                }
                break;

            // 固定日期
            case 1 :
                if($coupon['fixed_time_end'] < time())
                {
                    return DataReturn(MyLang('coupon_expired_message').'['.$coupon['name'].']', -1);
                }
                break;

            default :
                return DataReturn(MyLang('coupon_exprire_type_error_message').'['.$coupon['name'].']', -1);
        }

        // 过期时间计算
        switch($coupon['expire_type'])
        {
            // 领取生效
            case 0 :
                $time_start = time();
                $time_end = time()+(3600*$coupon['expire_hour']);
                break;

            // 固定日期
            case 1 :
                $time_start = $coupon['fixed_time_start'];
                $time_end = $coupon['fixed_time_end'];
                break;
        }

        // 添加优惠券
        $data = [];
        $add_time = time();
        $send_number = empty($params['send_number']) ? 1 : intval($params['send_number']);
        foreach($params['user_ids'] as $user_id)
        {
            for($i=0; $i<$send_number; $i++)
            {
                $data[] = [
                    'coupon_id'     => $coupon['id'],
                    'user_id'       => $user_id,
                    'is_valid'      => 1,
                    'time_start'    => $time_start,
                    'time_end'      => $time_end,
                    'add_time'      => $add_time,
                ];
            }
        }

        // 添加用户优惠券
        $count = count($data);
        if(Db::name('PluginsCouponUser')->insertAll($data) >= $count)
        {
            // 更新发放条数
            if(Db::name('PluginsCoupon')->where(['id'=>$coupon['id']])->inc('already_send_count', $count)->update())
            {
                // 是否需要通知
                UserNoticeService::Send($data, $coupon);

                return DataReturn(MyLang('grant_success'), 0);
            }
        }
        return DataReturn(MyLang('grant_fail'), -100);
    }

    /**
     * 用户领取优惠券
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-11T15:36:19+0800
     * @param    [array]          $params [输入参数]
     */
    public static function UserReceiveCoupon($params = [])
    {
        // 优惠券id是否正常
        if(empty($params['coupon_id']))
        {
            return DataReturn(MyLang('coupon_id_error_message'), -1);
        }

        // 是否登录
        $user = UserService::LoginUserInfo();
        if(empty($user))
        {
            return DataReturn(MyLang('user_no_login_tips'), -400);
        }
        $coupon_id = intval($params['coupon_id']);

        // 是否允许重复领取
        // 是否已领取过
        $is_repeat_receive = Db::name('PluginsCoupon')->where(['id'=>$coupon_id])->value('is_repeat_receive');
        if($is_repeat_receive != 1)
        {
            $temp = Db::name('PluginsCouponUser')->where(['coupon_id'=>$coupon_id, 'user_id'=>$user['id']])->find();
            if(!empty($temp))
            {
                return DataReturn(MyLang('coupon_received_not_receive_message'), -1);
            }
        }

        // 领取优惠券
        $coupon_params = [
            'user_ids'          => [$user['id']],
            'coupon_id'         => $coupon_id,
            'is_user_receive'   => 1,
        ];
        $ret = self::CouponSend($coupon_params);
        if($ret['code'] == 0)
        {
            $res = self::CouponList(['where'=>['id'=>$coupon_id]]);
            $coupon = $res['data'][0];
            return DataReturn(MyLang('take_success'), 0, [
                    'is_repeat_receive'     => $is_repeat_receive,
                    'already_receive_text'  => $coupon['status_operable_name'],
                    'coupon'                => $coupon,
                ]);
        }
        return $ret;
    }

    /**
     * 用户领取优惠券列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UserReceiveCouponList($params = [])
    {
        // 指定条件
        $where = empty($params['where']) ? [] : $params['where'];
        // 是否指定类型
        if(isset($params['type']) && $params['type'] !== '')
        {
            $where[] = ['type', 'in', is_array($params['type']) ? $params['type'] : explode(',', $params['type'])];
        }
        // 指定优惠券id
        $coupon_ids = empty($params['coupon_ids']) ? '' : $params['coupon_ids'];
        if(!empty($coupon_ids))
        {
            if(!is_array($coupon_ids))
            {
                $coupon_ids = explode(',', $coupon_ids);
            }
            $where[] = ['id', 'in', $coupon_ids];
        }

        // 查询数据
        $data_params = [
            'where'             => array_merge($where, [
                'is_enable'         => 1,
                'is_user_receive'   => 1,
            ]),
            'm'                 => 0,
            'n'                 => empty($params['number']) ? 0 : intval($params['number']),
            'is_sure_receive'   => 1,
            'user'              => empty($params['user']) ? null : $params['user'],
        ];
        $ret = CouponService::CouponList($data_params);
        if(!empty($ret['data']))
        {
            // 是否存在指定优惠券id则按照指定的顺序返回数据
            if(empty($coupon_ids))
            {
                $result = $ret['data'];
            } else {
                $result = [];
                $temp = array_column($ret['data'], null, 'id');
                foreach($coupon_ids as $id)
                {
                    if(array_key_exists($id, $temp))
                    {
                        $result[] = $temp[$id];
                    }
                }
            }
            return $result;
        }
        return [];
    }

    /**
     * 用户领取优惠券数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UserReceiveCouponData($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('coupon_id_error_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 获取优惠券数据
        $res = self::UserReceiveCouponList(array_merge($params, ['coupon_ids'=>$params['id']]));
        if(empty($res) || empty($res[0]))
        {
            return DataReturn(MyLang('coupon_absent_or_del_message'), -1);
        }
        return DataReturn('success', 0, $res[0]);
    }

    /**
     * 指定读取优惠券列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]         $params   [输入参数]
     */
    public static function AppointCouponList($params = [])
    {
        $result = [];
        if(!empty($params['coupon_ids']))
        {
            // 非数组则转为数组
            if(!is_array($params['coupon_ids']))
            {
                $params['coupon_ids'] = explode(',', $params['coupon_ids']);
            }

            // 基础条件
            $where = [
                ['is_enable', '=', 1],
                ['is_user_receive', '=', 1],
                ['id', 'in', array_unique($params['coupon_ids'])]
            ];

            // 获取数据
            $ret = self::CouponList([
                'where'            => $where,
                'm'                => 0,
                'n'                => 0,
                'is_sure_receive'  => 1,
                'user'             => empty($params['user']) ? null : $params['user'],
            ]);
            if(!empty($ret['data']))
            {
                $temp = array_column($ret['data'], null, 'id');
                foreach($params['coupon_ids'] as $id)
                {
                    if(!empty($id) && array_key_exists($id, $temp))
                    {
                        $result[] = $temp[$id];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 自动读取优惠券列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]         $params [输入参数]
     */
    public static function AutoCouponList($params = [])
    {
        // 基础条件
        $where = [
            ['is_enable', '=', 1],
            ['is_user_receive', '=', 1],
        ];

        // 关键字
        $keywords = empty($params['coupon_keywords']) ? (empty($params['keywords']) ? '' : $params['keywords']) : $params['coupon_keywords'];
        if(!empty($keywords))
        {
            $where[] = ['name|desc', 'like', '%'.$keywords.'%'];
        }

        // 类型
        $type_ids = empty($params['type_ids']) ? (empty($params['type']) ? '' : $params['type']) : $params['type_ids'];
        if($type_ids !== '')
        {
            if(!is_array($type_ids))
            {
                $type_ids = explode(',', $type_ids);
            }
            $where[] = ['type', 'in', $type_ids];
        }

        // 到期类型
        $expire_type_ids = empty($params['expire_type_ids']) ? (empty($params['expire_type']) ? '' : $params['expire_type']) : $params['expire_type_ids'];
        if($expire_type_ids !== '')
        {
            if(!is_array($expire_type_ids))
            {
                $expire_type_ids = explode(',', $expire_type_ids);
            }
            $where[] = ['expire_type', 'in', $expire_type_ids];
        }

        // 使用限制
        $use_limit_type_ids = empty($params['use_limit_type_ids']) ? (empty($params['use_limit_type']) ? '' : $params['use_limit_type']) : $params['use_limit_type_ids'];
        if($use_limit_type_ids !== '')
        {
            if(!is_array($use_limit_type_ids))
            {
                $use_limit_type_ids = explode(',', $use_limit_type_ids);
            }
            $where[] = ['use_limit_type', 'in', $use_limit_type_ids];
        }

        // 是否允许重复领取
        if((isset($params['coupon_is_repeat_receive']) && $params['coupon_is_repeat_receive'] == 1) || (isset($params['is_repeat_receive']) && $params['is_repeat_receive'] == 1))
        {
            $where[] = ['is_repeat_receive', '=', 1];
        }

        // 排序
        $order_by_type_list = BaseService::ConstData('coupon_order_by_type_list');
        $order_by_rule_list = MyConst('common_data_order_by_rule_list');
        $order_by_type = !isset($params['coupon_order_by_type']) || !array_key_exists($params['coupon_order_by_type'], $order_by_type_list) ? $order_by_type_list[0]['value'] : $order_by_type_list[$params['coupon_order_by_type']]['value'];
        $order_by_rule = !isset($params['coupon_order_by_rule']) || !array_key_exists($params['coupon_order_by_rule'], $order_by_rule_list) ? $order_by_rule_list[0]['value'] : $order_by_rule_list[$params['coupon_order_by_rule']]['value'];
        $order_by = $order_by_type.' '.$order_by_rule;

        // 获取数据
        $ret = self::CouponList([
            'where'            => $where,
            'm'                => 0,
            'n'                => empty($params['coupon_number']) ? (empty($params['number']) ? 10 : intval($params['number'])) : intval($params['coupon_number']),
            'order_by'         => $order_by,
            'is_sure_receive'  => 1,
            'user'             => empty($params['user']) ? null : $params['user'],
        ]);
        return empty($ret['data']) ? [] : $ret['data'];
    }
}
?>