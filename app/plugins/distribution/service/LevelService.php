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
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\BusinessService;
use app\plugins\shop\service\ShopService;

/**
 * 会员等级服务层 - 会员等级
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class LevelService
{
    /**
     * 获取多商户分销等级数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ShopLevelDataList($params = [])
    {
        // 固定返回格式
        $result = [
            'data'  => [],
            'level' => [],
        ];

        // 用户店铺
        if(!empty($params['shop_id']))
        {
            // 分销等级列表
            $where = [
                ['is_enable', '=', 1],
            ];
            $ret = self::DataList(['where'=>$where]);
            if(!empty($ret['data']))
            {
                // 店铺等级配置
                $where = [
                    ['shop_id', '=', $params['shop_id']],
                ];
                $result['data'] = Db::name('PluginsDistributionLevelShop')->where($where)->find();

                // 店铺返佣配置
                $config = (empty($result['data']) || empty($result['data']['config'])) ? [] : json_decode($result['data']['config'], true);

                // 将店铺设置的返佣配置覆盖到已有配置
                foreach($ret['data'] as &$v)
                {
                    // 店铺等级返佣配置处理
                    $v = self::ShopLevelItemDataHandle($v, $config);
                }
                $result['level'] = $ret['data'];
            }
        }
        return DataReturn('success', 0, $result);
    }

    /**
     * 店铺等级返佣配置信息处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-02
     * @desc    description
     * @param   [array]          $data   [等级数据]
     * @param   [array]          $config [配置信息]
     */
    public static function ShopLevelItemDataHandle($data, $config)
    {
        // 字段数据处理
        $fields = [
            'level_rate_one',
            'level_rate_two',
            'level_rate_three',
            'down_return_rate',
            'self_buy_rate',
        ];

        // 店铺等级返佣配置
        $temp_config = (!empty($data['id']) && !empty($config) && is_array($config) && array_key_exists($data['id'], $config)) ? $config[$data['id']] : [];
        // 返佣比例
        foreach($fields as $fd)
        {
            $data[$fd] = array_key_exists($fd, $temp_config) ? PriceBeautify(PriceNumberFormat($temp_config[$fd])) : 0;
        }
        return $data;
    }

    /**
     * 获取分销等级数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function DataList($params = [])
    {
        // 参数
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id asc' : trim($params['order_by']);

        // 获取数据
        $data = Db::name('PluginsDistributionLevel')->field($field)->where($where)->order($order_by)->select()->toArray();

        // 数据处理
        return self::DataHandle($data, $params);
    }

    /**
     * 等级数据列表处理
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-04-27T01:08:23+0800
     * @param    [array]                   $data   [等级数据]
     * @param    [array]                   $params [输入参数]
     */
    public static function DataHandle($data, $params = [])
    {
        if(!empty($data))
        {
            $common_is_enable_tips = MyConst('common_is_enable_tips');
            $auto_level_type_list = BaseService::$auto_level_type_list;
            $auto_level_type_where_list = BaseService::$auto_level_type_where_list;
            foreach($data as &$v)
            {
                // 图片地址,不存在使用默认配置或系统默认
                if(empty($v['images_url']))
                {
                    $v['images_url'] = empty($base['default_level_images']) ? StaticAttachmentUrl('default-level.png') : $base['default_level_images'];
                } else {
                    $v['images_url'] = ResourcesService::AttachmentPathViewHandle($v['images_url']);
                }

                // 条件类型
                if(array_key_exists('auto_level_type_where', $v))
                {
                    $rules_msg_list = [
                        'name'  => $auto_level_type_list[$v['auto_level_type']]['name'],
                        'data'  => [],
                    ];
                    $v['auto_level_type_where'] = empty($v['auto_level_type_where']) ? [] : json_decode($v['auto_level_type_where'], true);
                    if(!empty($v['auto_level_type_where']))
                    {
                        foreach($v['auto_level_type_where'] as $rk=>$rv)
                        {
                            if(isset($auto_level_type_where_list[$rv]))
                            {
                                $name = $auto_level_type_where_list[$rv]['name'];
                                $value = '';
                                switch($rv)
                                {
                                    case 0 :
                                        if(isset($v['auto_level_self_consume_price_rules_min']) && isset($v['auto_level_self_consume_price_rules_max']))
                                        {
                                            if($v['auto_level_self_consume_price_rules_min'] > 0 && $v['auto_level_self_consume_price_rules_max'] > 0)
                                            {
                                                $value = PriceBeautify($v['auto_level_self_consume_price_rules_min']).'-'.PriceBeautify($v['auto_level_self_consume_price_rules_max']);
                                            } else if($v['auto_level_self_consume_price_rules_min'] > 0)
                                            {
                                                $value = '>='.PriceBeautify($v['auto_level_self_consume_price_rules_min']);
                                            } else if($v['auto_level_self_consume_price_rules_max'] > 0)
                                            {
                                                $value = '<'.PriceBeautify($v['auto_level_self_consume_price_rules_max']);
                                            }
                                        }
                                        break;
                                    case 1 :
                                        if(isset($v['auto_level_self_consume_number_rules_min']) && isset($v['auto_level_self_consume_number_rules_max']))
                                        {
                                            if($v['auto_level_self_consume_number_rules_min'] > 0 && $v['auto_level_self_consume_number_rules_max'] > 0)
                                            {
                                                $value = PriceBeautify($v['auto_level_self_consume_number_rules_min']).'-'.PriceBeautify($v['auto_level_self_consume_number_rules_max']);
                                            } else if($v['auto_level_self_consume_number_rules_min'] > 0)
                                            {
                                                $value = '>='.PriceBeautify($v['auto_level_self_consume_number_rules_min']);
                                            } else if($v['auto_level_self_consume_number_rules_max'] > 0)
                                            {
                                                $value = '<'.PriceBeautify($v['auto_level_self_consume_number_rules_max']);
                                            }
                                        }
                                        break;
                                    case 2 :
                                        if(isset($v['auto_level_promotion_income_order_price_rules_min']) && isset($v['auto_level_promotion_income_order_price_rules_max']))
                                        {
                                            if($v['auto_level_promotion_income_order_price_rules_min'] > 0 && $v['auto_level_promotion_income_order_price_rules_max'] > 0)
                                            {
                                                $value = PriceBeautify($v['auto_level_promotion_income_order_price_rules_min']).'-'.PriceBeautify($v['auto_level_promotion_income_order_price_rules_max']);
                                            } else if($v['auto_level_promotion_income_order_price_rules_min'] > 0)
                                            {
                                                $value = '>='.PriceBeautify($v['auto_level_promotion_income_order_price_rules_min']);
                                            } else if($v['auto_level_promotion_income_order_price_rules_max'] > 0)
                                            {
                                                $value = '<'.PriceBeautify($v['auto_level_promotion_income_order_price_rules_max']);
                                            }
                                        }
                                    break;
                                    case 3 :
                                        if(isset($v['auto_level_promotion_income_order_number_rules_min']) && isset($v['auto_level_promotion_income_order_number_rules_max']))
                                        {
                                            if($v['auto_level_promotion_income_order_number_rules_min'] > 0 && $v['auto_level_promotion_income_order_number_rules_max'] > 0)
                                            {
                                                $value = PriceBeautify($v['auto_level_promotion_income_order_number_rules_min']).'-'.PriceBeautify($v['auto_level_promotion_income_order_number_rules_max']);
                                            } else if($v['auto_level_promotion_income_order_number_rules_min'] > 0)
                                            {
                                                $value = '>='.PriceBeautify($v['auto_level_promotion_income_order_number_rules_min']);
                                            } else if($v['auto_level_promotion_income_order_number_rules_max'] > 0)
                                            {
                                                $value = '<'.PriceBeautify($v['auto_level_promotion_income_order_number_rules_max']);
                                            }
                                        }
                                    break;
                                    case 4 :
                                        if(isset($v['auto_level_promotion_income_team_number_rules_min']) && isset($v['auto_level_promotion_income_team_number_rules_max']))
                                        {
                                            if($v['auto_level_promotion_income_team_number_rules_min'] > 0 && $v['auto_level_promotion_income_team_number_rules_max'] > 0)
                                            {
                                                $value = PriceBeautify($v['auto_level_promotion_income_team_number_rules_min']).'-'.PriceBeautify($v['auto_level_promotion_income_team_number_rules_max']);
                                            } else if($v['auto_level_promotion_income_team_number_rules_min'] > 0)
                                            {
                                                $value = '>='.PriceBeautify($v['auto_level_promotion_income_team_number_rules_min']);
                                            } else if($v['auto_level_promotion_income_team_number_rules_max'] > 0)
                                            {
                                                $value = '<'.PriceBeautify($v['auto_level_promotion_income_team_number_rules_max']);
                                            }
                                        }
                                    break;
                                    case 5 :
                                        if(isset($v['auto_level_promotion_income_team_consume_rules_min']) && isset($v['auto_level_promotion_income_team_consume_rules_max']))
                                        {
                                            if($v['auto_level_promotion_income_team_consume_rules_min'] > 0 && $v['auto_level_promotion_income_team_consume_rules_max'] > 0)
                                            {
                                                $value = PriceBeautify($v['auto_level_promotion_income_team_consume_rules_min']).'-'.PriceBeautify($v['auto_level_promotion_income_team_consume_rules_max']);
                                            } else if($v['auto_level_promotion_income_team_consume_rules_min'] > 0)
                                            {
                                                $value = '>='.PriceBeautify($v['auto_level_promotion_income_team_consume_rules_min']);
                                            } else if($v['auto_level_promotion_income_team_consume_rules_max'] > 0)
                                            {
                                                $value = '<'.PriceBeautify($v['auto_level_promotion_income_team_consume_rules_max']);
                                            }
                                        }
                                    break;
                                    case 6 :
                                        if(isset($v['auto_level_user_points_number_rules_min']) && isset($v['auto_level_user_points_number_rules_max']))
                                        {
                                            if($v['auto_level_user_points_number_rules_min'] > 0 && $v['auto_level_user_points_number_rules_max'] > 0)
                                            {
                                                $value = PriceBeautify($v['auto_level_user_points_number_rules_min']).'-'.PriceBeautify($v['auto_level_user_points_number_rules_max']);
                                            } else if($v['auto_level_user_points_number_rules_min'] > 0)
                                            {
                                                $value = '>='.PriceBeautify($v['auto_level_user_points_number_rules_min']);
                                            } else if($v['auto_level_user_points_number_rules_max'] > 0)
                                            {
                                                $value = '<'.PriceBeautify($v['auto_level_user_points_number_rules_max']);
                                            }
                                        }
                                    break;
                                }
                                $rules_msg_list['data'][] = ['name'=>$name, 'value'=>$value];
                            }
                        }
                    }
                    $v['rules_msg_list'] = $rules_msg_list;
                }

                // 返佣
                if(array_key_exists('level_rate_one', $v))
                {
                    $v['level_rate_one'] = PriceBeautify($v['level_rate_one']);
                }
                if(array_key_exists('level_rate_two', $v))
                {
                    $v['level_rate_two'] = PriceBeautify($v['level_rate_two']);
                }
                if(array_key_exists('level_rate_three', $v))
                {
                    $v['level_rate_three'] = PriceBeautify($v['level_rate_three']);
                }
                if(array_key_exists('self_buy_rate', $v))
                {
                    $v['self_buy_rate'] = PriceBeautify($v['self_buy_rate']);
                }
                if(array_key_exists('force_current_user_rate_one', $v))
                {
                    $v['force_current_user_rate_one'] = PriceBeautify($v['force_current_user_rate_one']);
                }
                if(array_key_exists('force_current_user_rate_two', $v))
                {
                    $v['force_current_user_rate_two'] = PriceBeautify($v['force_current_user_rate_two']);
                }
                if(array_key_exists('force_current_user_rate_three', $v))
                {
                    $v['force_current_user_rate_three'] = PriceBeautify($v['force_current_user_rate_three']);
                }

                // 时间
                if(array_key_exists('add_time', $v))
                {
                    $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                if(array_key_exists('upd_time', $v))
                {
                    $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
                }
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 获取等级数据保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function DataSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '1,30',
                'error_msg'         => '名称长度 1~30 个字符',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'level_rate_one',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '一级返佣比例 0~100 之间的数字',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'level_rate_two',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '二级返佣比例 0~100 之间的数字',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'level_rate_three',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '三级返佣比例 0~100 之间的数字',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'down_return_rate',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '向下返佣比例 0~100 之间的数字',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'self_buy_rate',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '自购返佣比例 0~100 之间的数字',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'force_current_user_rate_one',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '强制返佣至取货点一级返佣比例 0~100 之间的数字',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'force_current_user_rate_two',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '强制返佣至取货点二级返佣比例 0~100 之间的数字',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'force_current_user_rate_three',
                'checked_data'      => 100,
                'is_checked'        => 1,
                'error_msg'         => '强制返佣至取货点三级返佣比例 0~100 之间的数字',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 附件
        $data_fields = ['images_url'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'name'                                                => $params['name'],
            'images_url'                                          => $attachment['data']['images_url'],
            'is_enable'                                           => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
            'is_level_auto'                                       => isset($params['is_level_auto']) ? intval($params['is_level_auto']) : 0,
            // 条件类型
            'auto_level_type'                                     => isset($params['auto_level_type']) ? intval($params['auto_level_type']) : 0,
            'auto_level_type_where'                               => isset($params['auto_level_type_where']) ? json_encode(explode(',', $params['auto_level_type_where'])) : '',
            // 自消费总额
            'auto_level_self_consume_price_rules_min'             => isset($params['auto_level_self_consume_price_rules_min']) ? (float) $params['auto_level_self_consume_price_rules_min'] : 0.00,
            'auto_level_self_consume_price_rules_max'             => isset($params['auto_level_self_consume_price_rules_max']) ? (float) $params['auto_level_self_consume_price_rules_max'] : 0.00,
            // 自消费单数
            'auto_level_self_consume_number_rules_min'            => isset($params['auto_level_self_consume_number_rules_min']) ? intval($params['auto_level_self_consume_number_rules_min']) : 0,
            'auto_level_self_consume_number_rules_max'            => isset($params['auto_level_self_consume_number_rules_max']) ? intval($params['auto_level_self_consume_number_rules_max']) : 0,
            // 推广收益总额
            'auto_level_promotion_income_order_price_rules_min'   => isset($params['auto_level_promotion_income_order_price_rules_min']) ? (float) $params['auto_level_promotion_income_order_price_rules_min'] : 0.00,
            'auto_level_promotion_income_order_price_rules_max'   => isset($params['auto_level_promotion_income_order_price_rules_max']) ? (float) $params['auto_level_promotion_income_order_price_rules_max'] : 0.00,
            // 推广收益单数
            'auto_level_promotion_income_order_number_rules_min'  => isset($params['auto_level_promotion_income_order_number_rules_min']) ? intval($params['auto_level_promotion_income_order_number_rules_min']) : 0,
            'auto_level_promotion_income_order_number_rules_max'  => isset($params['auto_level_promotion_income_order_number_rules_max']) ? intval($params['auto_level_promotion_income_order_number_rules_max']) : 0,
            // 推广人数
            'auto_level_promotion_income_team_number_rules_min'   => isset($params['auto_level_promotion_income_team_number_rules_min']) ? intval($params['auto_level_promotion_income_team_number_rules_min']) : 0,
            'auto_level_promotion_income_team_number_rules_max'   => isset($params['auto_level_promotion_income_team_number_rules_max']) ? intval($params['auto_level_promotion_income_team_number_rules_max']) : 0,
            // 推广消费人数
            'auto_level_promotion_income_team_consume_rules_min'             => isset($params['auto_level_promotion_income_team_consume_rules_min']) ? intval($params['auto_level_promotion_income_team_consume_rules_min']) : 0,
            'auto_level_promotion_income_team_consume_rules_max'             => isset($params['auto_level_promotion_income_team_consume_rules_max']) ? intval($params['auto_level_promotion_income_team_consume_rules_max']) : 0,
            // 有效积分
            'auto_level_user_points_number_rules_min'             => isset($params['auto_level_user_points_number_rules_min']) ? intval($params['auto_level_user_points_number_rules_min']) : 0,
            'auto_level_user_points_number_rules_max'             => isset($params['auto_level_user_points_number_rules_max']) ? intval($params['auto_level_user_points_number_rules_max']) : 0,
            // 返佣配置
            'level_rate_one'                                      => isset($params['level_rate_one']) ? PriceBeautify(PriceNumberFormat($params['level_rate_one'])) : 0,
            'level_rate_two'                                      => isset($params['level_rate_two']) ? PriceBeautify(PriceNumberFormat($params['level_rate_two'])) : 0,
            'level_rate_three'                                    => isset($params['level_rate_three']) ? PriceBeautify(PriceNumberFormat($params['level_rate_three'])) : 0,
            'down_return_rate'                                    => isset($params['down_return_rate']) ? PriceBeautify(PriceNumberFormat($params['down_return_rate'])) : 0,
            'self_buy_rate'                                       => isset($params['self_buy_rate']) ? PriceBeautify(PriceNumberFormat($params['self_buy_rate'])) : 0,
            'force_current_user_rate_one'                         => isset($params['force_current_user_rate_one']) ? PriceBeautify(PriceNumberFormat($params['force_current_user_rate_one'])) : 0,
            'force_current_user_rate_two'                         => isset($params['force_current_user_rate_two']) ? PriceBeautify(PriceNumberFormat($params['force_current_user_rate_two'])) : 0,
            'force_current_user_rate_three'                       => isset($params['force_current_user_rate_three']) ? PriceBeautify(PriceNumberFormat($params['force_current_user_rate_three'])) : 0,
        ];

        // 不存在更新 则添加
        if(empty($params['id']))
        {
            $data['add_time'] = time();
            if(Db::name('PluginsDistributionLevel')->insertGetId($data) > 0)
            {
                return DataReturn(MyLang('insert_success'), 0);
            }
            return DataReturn(MyLang('insert_fail'), -100);
        } else {
            $data['upd_time'] = time();
            if(Db::name('PluginsDistributionLevel')->where(['id'=>intval($params['id'])])->update($data))
            {
                return DataReturn(MyLang('edit_success'), 0);
            }
            return DataReturn(MyLang('edit_fail'), -100);
        }
    }

    /**
     * 多商户分销等级配置保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ShopDataSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user_id',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'shop_id',
                'error_msg'         => '店铺id有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据
        $data = [
            'user_id'   => $params['user_id'],
            'shop_id'   => $params['shop_id'],
            'is_enable' => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
            'config'    => empty($params['config']) ? '' : json_encode($params['config'], JSON_UNESCAPED_UNICODE),
        ];

        // 不存在更新 则添加
        $info = Db::name('PluginsDistributionLevelShop')->where(['user_id'=>$data['user_id'], 'shop_id'=>$data['shop_id']])->find();
        if(empty($info))
        {
            $data['add_time'] = time();
            if(Db::name('PluginsDistributionLevelShop')->insertGetId($data) <= 0)
            {
                return DataReturn(MyLang('insert_fail'), -100);
            }
        } else {
            $data['upd_time'] = time();
            if(!Db::name('PluginsDistributionLevelShop')->where(['id'=>$info['id']])->update($data))
            {
                return DataReturn(MyLang('edit_fail'), -100);
            }
        }
        return DataReturn(MyLang('operate_success'), 0);
    }

    /**
     * 数据删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function DataDelete($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 删除操作
        if(Db::name('PluginsDistributionLevel')->where(['id'=>intval($params['id'])])->delete())
        {
            return DataReturn(MyLang('delete_success'), 0);
        }
        return DataReturn(MyLang('delete_fail'), -100);
    }

    /**
     * 数据状态更新
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function DataStatusUpdate($params = [])
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

        // 数据更新
        if(Db::name('PluginsDistributionLevel')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
            return DataReturn(MyLang('edit_success'), 0);
        }
        return DataReturn(MyLang('edit_fail'), -100);
    }
}
?>