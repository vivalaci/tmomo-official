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
use app\service\SystemService;
use app\service\SystemBaseService;
use app\service\PluginsService;
use app\service\ResourcesService;
use app\service\UserService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\plugins\distribution\service\LevelService;

/**
 * 分销 - 基础服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [
        'default_level_images',
        'default_qrcode_logo',
    ];

    // 消息类型
    public static $message_business_type = '分销';

    // 是否开启
    public static $distribution_is_enable_list = [
        0 => ['value' => 0, 'name' => '关闭', 'checked' => true],
        1 => ['value' => 1, 'name' => '开启'],
    ];

    // 分销层级
    public static $distribution_level_list = [
        0 => ['value' => 0, 'name' => '一级分销'],
        1 => ['value' => 1, 'name' => '二级分销'],
        2 => ['value' => 2, 'name' => '三级分销', 'checked' => true],
    ];

    // 边框样式
    public static $distribution_border_style_list = [
        0 => ['value' => 0, 'name' => '正方形', 'class' => ''],
        1 => ['value' => 1, 'name' => '圆角', 'class' => 'am-radius'],
        2 => ['value' => 2, 'name' => '圆形', 'class' => 'am-circle'],
    ];

    // 用户名称
    public static $distribution_userinfo_list = [
        0 => ['value' => 0, 'name' => '昵称'],
        1 => ['value' => 1, 'name' => '手机'],
        2 => ['value' => 2, 'name' => '邮箱'],
        3 => ['value' => 3, 'name' => '用户ID'],
        4 => ['value' => 4, 'name' => '会员码'],
        5 => ['value' => 5, 'name' => '推荐码'],
    ];

    // 取货点状态
    public static $distribution_extraction_status_list = [
        0 => ['value' => 0, 'name' => '待审核', 'checked' => true],
        1 => ['value' => 1, 'name' => '已通过'],
        2 => ['value' => 2, 'name' => '已拒绝'],
        3 => ['value' => 3, 'name' => '已解约'],
    ];

    // 取货点返佣上级
    public static $distribution_extraction_profit_level_list = [
        0 => ['value' => 0, 'name' => '关闭', 'checked' => true],
        1 => ['value' => 1, 'name' => '上一级'],
        2 => ['value' => 2, 'name' => '上二级'],
    ];

    // 订单返佣类型
    public static $distribution_order_profit_type_list = [
        0 => ['value' => 0, 'name' => '所有订单', 'checked' => true],
        1 => ['value' => 1, 'name' => '首单'],
    ];

    // 级别
    public static $level_name_list = [
        0 => ['value' => 0, 'name' => '向下'],
        1 => ['value' => 1, 'name' => '一级'],
        2 => ['value' => 2, 'name' => '二级'],
        3 => ['value' => 3, 'name' => '三级'],
        4 => ['value' => 4, 'name' => '内购'],
        5 => ['value' => 5, 'name' => '自提点一级'],
        6 => ['value' => 6, 'name' => '自提点二级'],
        7 => ['value' => 7, 'name' => '自提点三级'],
        8 => ['value' => 8, 'name' => '指定商品返现'],
        9 => ['value' => 9, 'name' => '指定商品销售返佣'],
        10 => ['value' => 10, 'name' => '指定商品阶梯返佣'],
    ];

    // 返佣类型
    public static $profit_profit_type_list = [
        0 => ['value' => 0, 'name' => '金额', 'checked' => true],
        1 => ['value' => 1, 'name' => '积分'],
    ];

    // 收益结算状态（0待生效, 1待结算, 2已结算, 3已失效）
    public static $profit_status_list = [
        0 => ['value' => 0, 'name' => '待生效', 'checked' => true],
        1 => ['value' => 1, 'name' => '待结算'],
        2 => ['value' => 2, 'name' => '已结算'],
        3 => ['value' => 3, 'name' => '已失效'],
    ];

    // 积分发放状态（0待生效, 1待结算, 2已结算, 3已失效）
    public static $integral_status_list = [
        0 => ['value' => 0, 'name' => '待发放', 'checked' => true],
        1 => ['value' => 1, 'name' => '已发放'],
        2 => ['value' => 2, 'name' => '已退回'],
    ];

    // 自提订单状态（0待处理, 1已处理）
    public static $order_status_list = [
        0 => ['value' => 0, 'name' => '待处理', 'checked' => true],
        1 => ['value' => 1, 'name' => '已处理'],
    ];

    // 自动升级分销等级类型
    public static $auto_level_type_list = [
        0 => ['value' => 0, 'name' => '满足以下任意条件', 'checked' => true],
        1 => ['value' => 1, 'name' => '满足以下全部条件'],
    ];

    // 自动升级分销等级类型条件
    public static $auto_level_type_where_list = [
        0 => ['value' => 0, 'name' => '自消费总额(已完成)'],
        1 => ['value' => 1, 'name' => '自消费单数(已完成)'],
        2 => ['value' => 2, 'name' => '推广收益总额(已结算)'],
        3 => ['value' => 3, 'name' => '推广收益单数(已结算)'],
        4 => ['value' => 4, 'name' => '推广下级人数'],
        5 => ['value' => 5, 'name' => '推广下级消费人数(已完成)'],
        6 => ['value' => 6, 'name' => '有效积分'],
    ];

    // 字体地址
    public static $font_path = ROOT.'public'.DS.'static'.DS.'common'.DS.'typeface'.DS.'Alibaba-PuHuiTi-Regular.ttf';

    /**
     * 海报数据保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function PosterDataSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'backdrop',
                'error_msg'         => '请上传海报背景图片',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'avatar_width',
                'error_msg'         => '请设置头像宽度',
            ],
            [
                'checked_type'      => 'min',
                'key_name'          => 'avatar_width',
                'checked_data'      => 30,
                'error_msg'         => '头像宽度尺寸 30~300 之间',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'avatar_width',
                'checked_data'      => 300,
                'error_msg'         => '头像宽度尺寸 30~300 之间',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'avatar_border_style',
                'checked_data'      => array_column(self::$distribution_border_style_list, 'value'),
                'error_msg'         => '头像样式数据值有误',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'qrcode_width',
                'error_msg'         => '请设置二维码宽度尺寸',
            ],
            [
                'checked_type'      => 'min',
                'key_name'          => 'qrcode_width',
                'checked_data'      => 30,
                'error_msg'         => '二维码宽度尺寸 30~300 之间',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'qrcode_width',
                'checked_data'      => 300,
                'error_msg'         => '二维码宽度尺寸 30~300 之间',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'qrcode_border_style',
                'checked_data'      => array_column(self::$distribution_border_style_list, 'value'),
                'error_msg'         => '二维码样式数据值有误',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'userinfo_size',
                'error_msg'         => '请设置用户字体大小最大',
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'userinfo_size',
                'checked_data'      => 80,
                'error_msg'         => '用户字体大小最大 80',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据字段
        $data_field = 'poster_data';

        // 附件
        $data_fields = ['backdrop'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'backdrop'              => $attachment['data']['backdrop'],
            'avatar_width'          => empty($params['avatar_width']) ? 30 : intval($params['avatar_width']),
            'qrcode_width'          => empty($params['qrcode_width']) ? 110 : intval($params['qrcode_width']),

            'avatar_top'            => empty($params['avatar_top']) ? 12 : intval($params['avatar_top']),
            'avatar_left'           => empty($params['avatar_left']) ? 119 : intval($params['avatar_left']),

            'userinfo_type'          => empty($params['userinfo_type']) ? 0 : intval($params['userinfo_type']),
            'userinfo_size'          => empty($params['userinfo_size']) ? 14 : intval($params['userinfo_size']),
            'userinfo_top'          => empty($params['userinfo_top']) ? 72 : intval($params['userinfo_top']),
            'userinfo_left'         => empty($params['userinfo_left']) ? 113 : intval($params['userinfo_left']),

            'qrcode_top'            => empty($params['qrcode_top']) ? 96 : intval($params['qrcode_top']),
            'qrcode_left'           => empty($params['qrcode_left']) ? 94 : intval($params['qrcode_left']),

            'avatar_border_style'   => isset($params['avatar_border_style']) ? intval($params['avatar_border_style']) : 2,
            'qrcode_border_style'   => isset($params['qrcode_border_style']) ? intval($params['qrcode_border_style']) : 0,

            'userinfo_color'        => empty($params['userinfo_color']) ? '#666' : $params['userinfo_color'],
            'userinfo_auto_center'  => isset($params['userinfo_auto_center']) ? intval($params['userinfo_auto_center']) : 1,
            'operation_time'        => time(),
        ];

        // 原有数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field, false);

        // 保存
        $ret['data'][$data_field] = $data;
        return PluginsService::PluginsDataSave(['plugins'=>'distribution', 'data'=>$ret['data']]);
    }

    /**
     * 分享海报数据
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function PosterData($params = [])
    {
        // 数据字段
        $data_field = 'poster_data';

        // 获取数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field);
        $data = (empty($ret['data']) || empty($ret['data'][$data_field])) ? [] : $ret['data'][$data_field];

        // 数据处理
        if(isset($params['is_handle_data']) && $params['is_handle_data'] == 1)
        {
            // 背景图片地址
            $data['backdrop_old'] = empty($data['backdrop']) ? '/static/plugins/distribution/images/default-backdrop.png' : $data['backdrop'];
            $data['backdrop'] = ResourcesService::AttachmentPathViewHandle($data['backdrop_old']);

            // 头像
            $data['avatar_width'] = empty($data['avatar_width']) ? 30 : intval($data['avatar_width']);
            $data['avatar_top'] = empty($data['avatar_top']) ? 12 : intval($data['avatar_top']);
            $data['avatar_left'] = empty($data['avatar_left']) ? 119 : intval($data['avatar_left']);
            $data['avatar_border_style'] = isset($data['avatar_border_style']) ? intval($data['avatar_border_style']) : 2;

            // 昵称
            $data['userinfo_type'] = empty($data['userinfo_type']) ? 0 : $data['userinfo_type'];
            $data['userinfo_size'] = empty($data['userinfo_size']) ? 14 : $data['userinfo_size'];
            $data['userinfo_color'] = empty($data['userinfo_color']) ? '#666' : $data['userinfo_color'];
            $data['userinfo_top'] = empty($data['userinfo_top']) ? 72 : intval($data['userinfo_top']);
            $data['userinfo_left'] = empty($data['userinfo_left']) ? 113 : intval($data['userinfo_left']);
            $data['userinfo_auto_center'] = isset($data['userinfo_auto_center']) ? intval($data['userinfo_auto_center']) : 1;

            // 二维码
            $data['qrcode_width'] = empty($data['qrcode_width']) ? 110 : intval($data['qrcode_width']);
            $data['qrcode_top'] = empty($data['qrcode_top']) ? 96 : intval($data['qrcode_top']);
            $data['qrcode_left'] = empty($data['qrcode_left']) ? 94 : intval($data['qrcode_left']);
            $data['qrcode_border_style'] = isset($data['qrcode_border_style']) ? intval($data['qrcode_border_style']) : 0;
        }

        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 获取用户分销数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-05
     * @desc    description
     * @param   [int]     $user_id      [用户id]
     * @param   [array]   $config       [配置信息]
     */
    public static function UserDistributionLevel($user_id, $config = [])
    {
        // 用户等级静态类似存储
        static $plugins_distribution_user_level = [];
        if(array_key_exists($user_id, $plugins_distribution_user_level))
        {
            $level = $plugins_distribution_user_level[$user_id];
        } else {
            $level = [];
            // 基础配置
            if(empty($config))
            {
                $base = self::BaseConfig();
                $config = $base['data'];
            }

            // 等级列表数据处理
            $level_list = LevelService::DataList(['where'=>['is_enable'=>1]]);
            if(!empty($level_list['data']))
            {
                // 等级根据id分组
                $level_id_group = array_column($level_list['data'], null, 'id');

                // 用户是否配置了自定义等级
                // 没有自定义的使用自动模式分配分销等级
                $user_level_id = Db::name('User')->where(['id'=>$user_id])->value('plugins_distribution_level');
                if(!empty($user_level_id) && array_key_exists($user_level_id, $level_id_group))
                {
                    $level = $level_id_group[$user_level_id];
                }

                // 指定商品购买赠送等级
                if(empty($level) && !empty($config['is_appoint_appoint_level']) && $config['is_appoint_appoint_level'] == 1 && !empty($config['appoint_level_buy_data']) && is_array($config['appoint_level_buy_data']))
                {
                    foreach($config['appoint_level_buy_data'] as $lv)
                    {
                        if(!empty($lv['id']) && !empty($lv['goods_ids']) && is_array($lv['goods_ids']) && array_key_exists($lv['id'], $level_id_group))
                        {
                            // 获取用户已完成订单商品总数
                            $order_data = Db::name('Order')->alias('o')->join('order_detail od', 'o.id=od.order_id')->where(['o.user_id'=>$user_id, 'o.status'=>4, 'od.goods_id'=>$lv['goods_ids']])->field('o.id,od.goods_id,od.buy_number')->select()->toArray();
                            if(!empty($order_data))
                            {
                                // 订单数据集合处理
                                $temp_order = [];
                                foreach($order_data as $ov)
                                {
                                    if(!array_key_exists($ov['goods_id'], $temp_order))
                                    {
                                        $temp_order[$ov['goods_id']] = [
                                            'goods_count'   => 0,
                                            'goods_ids'     => [],
                                            'order_ids'     => [],
                                        ];
                                    }
                                    $temp_order[$ov['goods_id']]['goods_count'] += $ov['buy_number'];
                                    $temp_order[$ov['goods_id']]['goods_ids'][] = $ov['goods_id'];
                                    $temp_order[$ov['goods_id']]['order_ids'][] = $ov['id'];
                                }

                                // 订单最低数量和商品最低数量
                                $order_min_number = empty($lv['order_min_number']) ? 1 : intval($lv['order_min_number']);
                                $goods_min_number = empty($lv['goods_min_number']) ? 1 : intval($lv['goods_min_number']);
                                foreach($lv['goods_ids'] as $gid)
                                {
                                    if(array_key_exists($gid, $temp_order))
                                    {
                                        $goods_count = $temp_order[$gid]['goods_count'];
                                        $order_count = count(array_unique($temp_order[$gid]['order_ids']));
                                        if($order_count >= $order_min_number && $goods_count >= $goods_min_number)
                                        {
                                            $level = $level_id_group[$lv['id']];
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                // 自动分配
                if(empty($level))
                {
                    // 匹配相应的等级
                    foreach($level_list['data'] as $v)
                    {
                        if(isset($v['is_enable']) && $v['is_enable'] == 1 && isset($v['is_level_auto']) && $v['is_level_auto'] == 1)
                        {
                            // 状态
                            $status = 1;
                            if(!empty($v['auto_level_type_where']))
                            {
                                // 规则集合
                                $rules = [];

                                // 自消费总额(已完成)
                                if(in_array(0, $v['auto_level_type_where']))
                                {
                                    // 订单状态（0待确认, 1已确认/待支付, 2已支付/待发货, 3已发货/待收货, 4已完成, 5已取消, 6已关闭）
                                    $where = [
                                        ['user_id', '=', $user_id],
                                        ['status', '=', 4],
                                    ];
                                    $res = Db::name('Order')->where($where)->field('`pay_price`-`refund_price` AS total')->select()->toArray();
                                    $value = empty($res) ? 0 : array_sum(array_column($res, 'total'));
                                    $rules[] = self::UserLevelRulesHandle($v['auto_level_self_consume_price_rules_min'], $v['auto_level_self_consume_price_rules_max'], $value);
                                }
                                // 自消费单数(已完成)
                                if(in_array(1, $v['auto_level_type_where']))
                                {
                                    // 订单状态（0待确认, 1已确认/待支付, 2已支付/待发货, 3已发货/待收货, 4已完成, 5已取消, 6已关闭）
                                    $where = [
                                        ['user_id', '=', $user_id],
                                        ['status', '=', 4],
                                    ];
                                    $value = Db::name('Order')->where($where)->count();
                                    $rules[] = self::UserLevelRulesHandle($v['auto_level_self_consume_number_rules_min'], $v['auto_level_self_consume_number_rules_max'], $value);
                                }

                                // 推广收益总额(已完成)
                                if(in_array(2, $v['auto_level_type_where']))
                                {
                                    // 结算状态（0待生效, 1待结算, 2已结算, 3已失效）
                                    $where = [
                                        ['user_id', '=', $user_id],
                                        ['status', '=', 2],
                                    ];
                                    $value = Db::name('PluginsDistributionProfitLog')->where($where)->sum('profit_price');
                                    $rules[] = self::UserLevelRulesHandle($v['auto_level_promotion_income_order_price_rules_min'], $v['auto_level_promotion_income_order_price_rules_max'], $value);
                                }
                                // 推广收益单数(已完成)
                                if(in_array(3, $v['auto_level_type_where']))
                                {
                                    // 结算状态（0待生效, 1待结算, 2已结算, 3已失效）
                                    $where = [
                                        ['user_id', '=', $user_id],
                                        ['status', '=', 2],
                                    ];
                                    $value = Db::name('PluginsDistributionProfitLog')->where($where)->count();
                                    $rules[] = self::UserLevelRulesHandle($v['auto_level_promotion_income_order_number_rules_min'], $v['auto_level_promotion_income_order_number_rules_max'], $value);
                                }

                                // 推广下级人数
                                if(in_array(4, $v['auto_level_type_where']))
                                {
                                    // 邀请的正常状态用户
                                    $where = [
                                        ['referrer', '=', $user_id],
                                        ['status', '=', 0],
                                    ];
                                    $value = Db::name('User')->where($where)->count();
                                    $rules[] = self::UserLevelRulesHandle($v['auto_level_promotion_income_team_number_rules_min'], $v['auto_level_promotion_income_team_number_rules_max'], $value);
                                }

                                // 推广下级消费人数(已完成)
                                if(in_array(5, $v['auto_level_type_where']))
                                {
                                    // 邀请的正常状态用户、并且消费了
                                    $where = [
                                        ['u.referrer', '=', $user_id],
                                        ['u.status', '=', 0],
                                        ['o.status', '=', 4],
                                    ];
                                    $value = Db::name('User')->alias('u')->join('order o', 'u.id=o.user_id')->where($where)->count('DISTINCT u.id');
                                    $rules[] = self::UserLevelRulesHandle($v['auto_level_promotion_income_team_consume_rules_min'], $v['auto_level_promotion_income_team_consume_rules_max'], $value);
                                }

                                // 有效积分
                                if(in_array(6, $v['auto_level_type_where']))
                                {
                                    $where = [
                                        ['id', '=', $user_id],
                                    ];
                                    $value = Db::name('User')->where($where)->value('integral');
                                    $rules[] = self::UserLevelRulesHandle($v['auto_level_user_points_number_rules_min'], $v['auto_level_user_points_number_rules_max'], $value);
                                }

                                // 条件满足类型
                                $sum = array_sum($rules);
                                if($v['auto_level_type'] == 0)
                                {
                                    // 满足以下任意条件
                                    if($sum == 0)
                                    {
                                        $status = 0;
                                    }
                                } else {
                                    // 满足以下全部条件
                                    if($sum < count($rules))
                                    {
                                        $status = 0;
                                    }
                                }
                            }
                            // 匹配成功则赋值等级数据、可以继续往下匹配直到最后一个
                            if($status == 1)
                            {
                                $level = $v;
                            }
                        }
                    }

                    // 更新用户等级
                    Db::name('User')->where(['id'=>$user_id])->update(['plugins_distribution_auto_level'=>empty($level) ? 0 : $level['id'], 'upd_time'=>time()]);
                }
            }

            // 静态类似存储
            $plugins_distribution_user_level[$user_id] = $level;
        }
        if(empty($level))
        {
            return DataReturn('没有相关等级', -1);
        }

        // 返回等级数据
        return DataReturn(MyLang('handle_success'), 0, $level);
    }

    /**
     * 用户有效订单消费金额
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-05T22:08:34+0800
     * @param    [float|int]       $min     [最小数]
     * @param    [float|int]       $max     [最大数]
     * @param    [float|int]       $value   [对比值]
     */
    public static function UserLevelRulesHandle($min, $max, $value)
    {
        // 0-0
        if($min <= 0 && $max <= 0)
        {
            return 1;
        }
        // 0-*
        if($min <= 0 && $max > 0 && $value < $max)
        {
            return 1;
        }
        // *-*
        if($min > 0 && $max > 0 && $value >= $min && $value < $max)
        {
            return 1;
        }
        // *-0
        if($max <= 0 && $min > 0 && $value >= $min)
        {
            return 1;
        }
        return 0;
    }

    /**
     * 海报清空
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-12T20:36:38+0800
     * @param   [array]           $params [输入参数]
     */
    public static function PosterDelete($params = [])
    {
        $dir_all = ['poster', 'qrcode'];
        foreach($dir_all as $v)
        {
            $dir = ROOT.'public'.DS.'download'.DS.'plugins_distribution'.DS.$v;
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
     * 商品海报数据
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function PosterGoodsData($params = [])
    {
        // 数据字段
        $data_field = 'poster_goods_data';

        // 获取数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field);
        $data = (empty($ret['data']) || empty($ret['data'][$data_field])) ? [] : $ret['data'][$data_field];

        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 商品海报数据保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function PosterGoodsDataSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'bottom_left_text',
                'checked_data'      => '10',
                'is_checked'        => 1,
                'error_msg'         => '底部左侧文本不超过 10 个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'bottom_right_text',
                'checked_data'      => '6',
                'is_checked'        => 1,
                'error_msg'         => '底部右侧文本不超过 6 个字符',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据字段
        $data_field = 'poster_goods_data';

        // 数据
        $data = [
            'goods_title_text_color'    => empty($params['goods_title_text_color']) ? '' : $params['goods_title_text_color'],
            'goods_simple_text_color'   => empty($params['goods_simple_text_color']) ? '' : $params['goods_simple_text_color'],

            'price_custom_text'          => empty($params['price_custom_text']) ? '' : $params['price_custom_text'],
            'price_custom_text_color'    => empty($params['price_custom_text_color']) ? '' : $params['price_custom_text_color'],

            'bottom_left_text'          => empty($params['bottom_left_text']) ? '' : $params['bottom_left_text'],
            'bottom_left_text_color'    => empty($params['bottom_left_text_color']) ? '' : $params['bottom_left_text_color'],

            'bottom_right_text'         => empty($params['bottom_right_text']) ? '' : $params['bottom_right_text'],
            'bottom_right_text_color'   => empty($params['bottom_right_text_color']) ? '' : $params['bottom_right_text_color'],
        ];

        // 原有数据
        $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field, false);

        // 保存
        $ret['data'][$data_field] = $data;
        return PluginsService::PluginsDataSave(['plugins'=>'distribution', 'data'=>$ret['data']]);
    }

    /**
     * 商品海报清空
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-12T20:36:38+0800
     * @param    [array]           $params [输入参数]
     */
    public static function PosterGoodsDelete($params = [])
    {
        $path = ROOT.'public'.DS.'download'.DS.'plugins_distribution'.DS;
        $dir_all = ['poster_goods_qrcode', 'poster_goods'];
        foreach($dir_all as $v)
        {
            if(is_dir($path.$v))
            {
                // 是否有权限
                if(!is_writable($path.$v))
                {
                    return DataReturn('目录没权限['.$path.$v.']', -1);
                }

                // 删除目录
                \base\FileUtil::UnlinkDir($path.$v);
            }
        }
        return DataReturn(MyLang('operate_success'), 0);
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
        static $plugins_distribution_base_data = null;
        if($plugins_distribution_base_data === null)
        {
            $ret = PluginsService::PluginsData('distribution', self::$base_config_attachment_field, $is_cache);

            if(!empty($ret['data']))
            {
                // 用户海报页面顶部描述
                if(!empty($ret['data']['user_poster_top_desc']))
                {
                    $ret['data']['user_poster_top_desc'] = explode("\n", $ret['data']['user_poster_top_desc']);
                }

                // 等级介绍顶部描述
                if(!empty($ret['data']['user_center_level_desc']))
                {
                    $ret['data']['user_center_level_desc'] = explode("\n", $ret['data']['user_center_level_desc']);
                }

                // 自提取货点申请介绍
                if(!empty($ret['data']['self_extraction_apply_desc']))
                {
                    $ret['data']['self_extraction_apply_desc'] = explode("\n", $ret['data']['self_extraction_apply_desc']);
                }

                // 自提取货点顶部公告
                if(!empty($ret['data']['self_extraction_common_notice']))
                {
                    $ret['data']['self_extraction_common_notice'] = explode("\n", $ret['data']['self_extraction_common_notice']);
                }

                // 不符合分销条件描述
                if(!empty($ret['data']['non_conformity_desc']))
                {
                    $ret['data']['non_conformity_desc'] = explode("\n", $ret['data']['non_conformity_desc']);
                }

                // 分销中心公告
                if(!empty($ret['data']['user_center_notice']))
                {
                    $ret['data']['user_center_notice'] = explode("\n", $ret['data']['user_center_notice']);
                }

                // 指定商品数据查询
                $level_goods_ids = [];
                if(!empty($ret['data']['appoint_level_buy_data']) && is_array($ret['data']['appoint_level_buy_data']))
                {
                    foreach($ret['data']['appoint_level_buy_data'] as $k=>$v)
                    {
                        $ret['data']['appoint_level_buy_data'][$k]['goods_list'] = empty($v['goods_list']) ? [] : $v['goods_list'];
                        $ret['data']['appoint_level_buy_data'][$k]['goods_ids'] = empty($v['goods_ids']) ? [] : $v['goods_ids'];
                        $level_goods_ids = array_merge($level_goods_ids, $ret['data']['appoint_level_buy_data'][$k]['goods_ids']);
                    }
                }

                $ret['data']['appoint_only_join_profit_goods_ids'] = empty($ret['data']['appoint_only_join_profit_goods_ids']) ? [] : $ret['data']['appoint_only_join_profit_goods_ids'];
                $ret['data']['appoint_only_join_profit_goods_list'] = [];

                $ret['data']['appoint_not_join_profit_goods_ids'] = empty($ret['data']['appoint_not_join_profit_goods_ids']) ? [] : $ret['data']['appoint_not_join_profit_goods_ids'];
                $ret['data']['appoint_not_join_profit_goods_list'] = [];

                $ret['data']['appoint_profit_goods_ids'] = empty($ret['data']['appoint_profit_goods_ids']) ? [] : $ret['data']['appoint_profit_goods_ids'];
                $ret['data']['appoint_profit_goods_list'] = [];

                $ret['data']['appoint_sale_goods_ids'] = empty($ret['data']['appoint_sale_goods_ids']) ? [] : $ret['data']['appoint_sale_goods_ids'];
                $ret['data']['appoint_sale_goods_list'] = [];

                $ret['data']['appoint_ladder_goods_ids'] = empty($ret['data']['appoint_ladder_goods_ids']) ? [] : $ret['data']['appoint_ladder_goods_ids'];
                $ret['data']['appoint_ladder_goods_list'] = [];

                $ret['data']['appoint_repurchase_goods_ids'] = empty($ret['data']['appoint_repurchase_goods_ids']) ? [] : $ret['data']['appoint_repurchase_goods_ids'];
                $ret['data']['appoint_repurchase_goods_list'] = [];

                // 查询商品进行组装
                $goods_ids = array_merge($level_goods_ids, $ret['data']['appoint_only_join_profit_goods_ids'], $ret['data']['appoint_not_join_profit_goods_ids'], $ret['data']['appoint_profit_goods_ids'], $ret['data']['appoint_sale_goods_ids'], $ret['data']['appoint_ladder_goods_ids'], $ret['data']['appoint_repurchase_goods_ids']);
                if(!empty($goods_ids))
                {
                    $goods = Db::name('Goods')->where(['id'=>$goods_ids])->field('id,title,images')->select()->toArray();
                    if(!empty($goods))
                    {
                        foreach($goods as $g)
                        {
                            $g['goods_url'] = GoodsService::GoodsUrlCreate($g['id']);
                            if(!empty($ret['data']['appoint_level_buy_data']) && is_array($ret['data']['appoint_level_buy_data']))
                            {
                                foreach($ret['data']['appoint_level_buy_data'] as $k=>$v)
                                {
                                    if(in_array($g['id'], $v['goods_ids']))
                                    {
                                        $ret['data']['appoint_level_buy_data'][$k]['goods_list'][] = $g;
                                    }
                                }
                            }
                            if(in_array($g['id'], $ret['data']['appoint_only_join_profit_goods_ids']))
                            {
                                $ret['data']['appoint_only_join_profit_goods_list'][] = $g;
                            }
                            if(in_array($g['id'], $ret['data']['appoint_not_join_profit_goods_ids']))
                            {
                                $ret['data']['appoint_not_join_profit_goods_list'][] = $g;
                            }
                            if(in_array($g['id'], $ret['data']['appoint_profit_goods_ids']))
                            {
                                $ret['data']['appoint_profit_goods_list'][] = $g;
                            }
                            if(in_array($g['id'], $ret['data']['appoint_sale_goods_ids']))
                            {
                                $ret['data']['appoint_sale_goods_list'][] = $g;
                            }
                            if(in_array($g['id'], $ret['data']['appoint_ladder_goods_ids']))
                            {
                                $ret['data']['appoint_ladder_goods_list'][] = $g;
                            }
                            if(in_array($g['id'], $ret['data']['appoint_repurchase_goods_ids']))
                            {
                                $ret['data']['appoint_repurchase_goods_list'][] = $g;
                            }
                        }
                    }
                }
            }
            $plugins_distribution_base_data = $ret;
        } else {
            $ret = $plugins_distribution_base_data;
        }
        return $ret;
    }

    /**
     * 商品搜索 - 基础
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseGoodsSearchList($params = [])
    {
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

        // 指定字段
        $field = 'g.id,g.title,g.images,g.min_price,g.price,g.original_price,g.inventory,g.inventory_unit,g.is_exist_many_spec';

        // 获取数据
        return GoodsService::CategoryGoodsList(['where'=>$where, 'm'=>0, 'n'=>100, 'field'=>$field, 'is_spec'=>1, 'is_admin_access'=>1]);
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
     * 商品列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]         $goods_ids [商品id]
     * @param   [int]           $m         [分页起始值]
     * @param   [int]           $n         [分页数量]
     */
    public static function GoodsList($goods_ids = [], $m = 0, $n = 0)
    {
        $where = [
            ['is_delete_time', '=', 0],
            ['id', 'in', $goods_ids],
        ];
        return GoodsService::GoodsList(['where'=>$where, 'm'=>$m, 'n'=>$n]);
    }

    /**
     * 优惠价格计算
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-26
     * @desc    description
     * @param   [string]          $price        [商品展示金额]
     * @param   [int]             $discount     [折扣系数]
     */
    public static function PriceCalculate($price, $discount = 0)
    {
        // 折扣
        if($discount > 0)
        {
            if(stripos($price, '-') !== false)
            {
                $text = explode('-', $price);
                $min_price = $text[0]*$discount;
                $max_price = $text[1]*$discount;
                $price = ($min_price <= 0) ? '0.00' : PriceNumberFormat($min_price);
                $price .= '-'.(($max_price <= 0) ? '0.00' : PriceNumberFormat($max_price));
            } else {
                $price = (float) $price *$discount;
                $price = ($price <= 0) ? '0.00' : PriceNumberFormat($price);
            }
        }
        return $price;
    }

    /**
     * 用户是否复购该商品
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-10-23
     * @desc    description
     * @param   [array]          $goods_ids [商品id]
     */
    public static function IsUserRepurchaseGoods($goods_ids)
    {
        $result = [];
        $user = UserService::LoginUserInfo();
        if(!empty($user))
        {
            // 查询有效订单
            $result = Db::name('Order')->alias('o')->join('order_detail od', 'o.id=od.order_id')->where(['o.user_id'=>$user['id'], 'o.status'=>4, 'od.goods_id'=>$goods_ids])->column('DISTINCT od.goods_id');
        }
        return $result;
    }

    /**
     * 用户上级数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-24
     * @desc    description
     * @param   [array]          $user           [用户信息]
     * @param   [array]          $plugins_config [插件配置]
     */
    public static function UserSuperiorData($user, $plugins_config = [])
    {
        $result = [];
        if(!empty($user) && !empty($user['id']))
        {
            // 是否存在邀请人
            if(!isset($user['referrer']))
            {
                $user['referrer'] = Db::name('User')->where(['id'=>$user['id']])->value('referrer');
            }

            // 获取邀请人信息
            if(!empty($user['referrer']))
            {
                $temp = Db::name('User')->where(['id'=>$user['referrer']])->field('id,nickname,username,mobile,email,avatar,add_time')->find();
                if(!empty($temp))
                {
                    $result = UserService::UserHandle($temp);
                    unset($result['mobile'], $result['email'], $result['username'], $result['nickname'], $result['email_security'], $result['mobile_security']);

                    // 获取还可以修改的次数
                    if(!empty($plugins_config) && !empty($plugins_config['superior_modify_number_limit']))
                    {
                        $info = Db::name('PluginsDistributionSuperiorModify')->where(['user_id'=>$user['id']])->find();
                        $modify_number = empty($info) ? $plugins_config['superior_modify_number_limit'] : $plugins_config['superior_modify_number_limit']-$info['modify_number'];
                        if($modify_number < 0)
                        {
                            $modify_number = 0;
                        }
                        $result['can_modify_number_msg'] = '还可以修改'.$modify_number.'次';
                    }
                }
            }
        }
        return $result;
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
                'name'      => '分销等级',
                'control'   => 'level',
                'action'    => 'index',
            ],
            [
                'name'      => '推广海报',
                'control'   => 'poster',
                'action'    => 'index',
            ],
            [
                'name'      => '商品海报',
                'control'   => 'postergoods',
                'action'    => 'index',
            ],
            [
                'name'      => '分销用户',
                'control'   => 'team',
                'action'    => 'index',
            ],
            [
                'name'      => '取货点管理',
                'control'   => 'extraction',
                'action'    => 'index',
            ],
            [
                'name'      => '取货点订单',
                'control'   => 'extractionorder',
                'action'    => 'index',
            ],
            [
                'name'      => '客户订单',
                'control'   => 'order',
                'action'    => 'index',
            ],
            [
                'name'      => '客户拜访',
                'control'   => 'visit',
                'action'    => 'index',
            ],
            [
                'name'      => '推荐宝',
                'control'   => 'recommend',
                'action'    => 'index',
            ],
            [
                'name'      => '收益明细',
                'control'   => 'profit',
                'action'    => 'index',
            ],
            [
                'name'      => '积分明细',
                'control'   => 'integral',
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
        $host = SystemBaseService::AttachmentHost().'/static/plugins/distribution/images/web/';
        $data = [
            [
                'title'    => '分销中心',
                'icon'     => '',
                'plugins'  => 'distribution',
                'control'  => 'index',
                'action'   => 'index',
            ],
            [
                'title'    => '客户订单',
                'icon'     => $host.'order-icon.png',
                'plugins'  => 'distribution',
                'control'  => 'order',
                'action'   => 'index',
            ],
            [
                'title'    => '我的团队',
                'icon'     => $host.'team-icon.png',
                'plugins'  => 'distribution',
                'control'  => 'team',
                'action'   => 'index',
            ],
            // [
            //     'title'    => '客户分布',
            //     'icon'     => $host.'map-icon.png',
            //     'plugins'  => 'distribution',
            //     'control'  => 'map',
            //     'action'   => 'index',
            // ],
        ];

        // 是否开启客户拜访
        if(isset($config['is_enable_visit']) && $config['is_enable_visit'] == 1)
        {
            $data[] = [
                'title'    => '客户拜访',
                'icon'     => $host.'visit-icon.png',
                'plugins'  => 'distribution',
                'control'  => 'visit',
                'action'   => 'index',
            ];
        }

        // 是否开启推荐宝
        if(isset($config['is_enable_recommend']) && $config['is_enable_recommend'] == 1)
        {
            $data[] = [
                'title'    => '推荐宝',
                'icon'     => $host.'recommend-icon.png',
                'plugins'  => 'distribution',
                'control'  => 'recommend',
                'action'   => 'index',
            ];
        }

        $data = array_merge($data, [
            [
                'title'    => '推广奖励',
                'icon'     => $host.'poster-icon.png',
                'plugins'  => 'distribution',
                'control'  => 'poster',
                'action'   => 'index',
            ],
            [
                'title'    => '收益明细',
                'icon'     => $host.'profit-icon.png',
                'plugins'  => 'distribution',
                'control'  => 'profit',
                'action'   => 'index',
            ],
            [
                'title'    => '提现明细',
                'icon'     => $host.'cash-icon.png',
                'plugins'  => 'wallet',
                'control'  => 'cash',
                'action'   => 'index',
            ],
        ]);

        // 等级介绍
        if(isset($config['is_show_introduce']) && $config['is_show_introduce'] == 1)
        {
            $data[] = [
                'title'    => '等级介绍',
                'icon'     => $host.'introduce-icon.png',
                'plugins'  => 'distribution',
                'control'  => 'introduce',
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
        $host = SystemBaseService::AttachmentHost().'/static/plugins/distribution/images/app/center/';
        $data = [
            [
                'icon'  => $host.'order-icon.png',
                'title' => '客户订单',
                'url'   => '/pages/plugins/distribution/order/order',
            ],
            [
                'icon'  => $host.'team-icon.png',
                'title' => '我的团队',
                'url'   => '/pages/plugins/distribution/team/team',
            ],
            [
                'icon'  => $host.'map-icon.png',
                'title' => '客户分布',
                'url'   => '/pages/plugins/distribution/map/map',
            ],
        ];

        // 是否开启客户拜访
        if(isset($config['is_enable_visit']) && $config['is_enable_visit'] == 1)
        {
            $data[] = [
                'icon'  => $host.'visit-icon.png',
                'title' => '客户拜访',
                'url'   => '/pages/plugins/distribution/visit-list/visit-list',
            ];
        }

        // 是否开启推荐宝
        if(isset($config['is_enable_recommend']) && $config['is_enable_recommend'] == 1)
        {
            $data[] = [
                'icon'  => $host.'recommend-icon.png',
                'title' => '推荐宝',
                'url'   => '/pages/plugins/distribution/recommend-list/recommend-list',
            ];
        }

        $data = array_merge($data, [
            [
                'icon'  => $host.'poster-icon.png',
                'title' => '推广奖励',
                'url'   => '/pages/plugins/distribution/poster/poster',
            ],
            [
                'icon'  => $host.'profit-icon.png',
                'title' => '收益明细',
                'url'   => '/pages/plugins/distribution/profit/profit',
            ],
        ]);

        // 等级介绍
        if(isset($config['is_show_introduce']) && $config['is_show_introduce'] == 1)
        {
            $data[] = [
                'icon'  => $host.'introduce-icon.png',
                'title' => '等级介绍',
                'url'   => '/pages/plugins/distribution/introduce/introduce',
            ];
        }

        return $data;
    }

    /**
     * 用户中心推广用户菜单
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-18
     * @desc    description
     * @param   [array]          $config [配置信息]
     */
    public static function WebPromotionUserNav($config = [])
    {
        return [
            [
                'title'    => '推广用户',
                'plugins'  => 'distribution',
                'control'  => 'promotionuser',
                'action'   => 'index',
            ],
            [
                'title'    => '已消费用户',
                'plugins'  => 'distribution',
                'control'  => 'promotionuser',
                'action'   => 'index',
            ],
            [
                'title'    => '未消费用户',
                'plugins'  => 'distribution',
                'control'  => 'promotionuser',
                'action'   => 'index',
            ],
            [
                'title'    => '新增客户',
                'plugins'  => 'distribution',
                'control'  => 'promotionuser',
                'action'   => 'index',
            ],
            [
                'title'    => '新增客户(有效)',
                'plugins'  => 'distribution',
                'control'  => 'promotionuser',
                'action'   => 'index',
            ],
            [
                'title'    => '新增客户(需复购)',
                'plugins'  => 'distribution',
                'control'  => 'promotionuser',
                'action'   => 'index',
            ],
        ];
    }

    /**
     * 用户中心推广订单菜单
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-18
     * @desc    description
     * @param   [array]          $config [配置信息]
     */
    public static function WebPromotionOrderNav($config = [])
    {
        return [
            [
                'title'    => '新增客户总GMV',
                'plugins'  => 'distribution',
                'control'  => 'promotionorder',
                'action'   => 'index',
            ],
            [
                'title'    => '订单总数',
                'plugins'  => 'distribution',
                'control'  => 'promotionorder',
                'action'   => 'index',
            ],
            [
                'title'    => '订单总GMV',
                'plugins'  => 'distribution',
                'control'  => 'promotionorder',
                'action'   => 'index',
            ],
        ];
    }

    /**
     * 指定商品购买阶梯返佣级别计算
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-03
     * @desc    description
     * @param   [array]          $config    [基础配置]
     * @param   [int]            $user_id   [用户id]
     * @param   [array]          $goods_ids [商品id]
     */
    public static function AppointProfitLadderOrderLevel($config, $user_id, $goods_ids = [])
    {
        if(!empty($config['appoint_goods_ladder_config']) && !empty($config['appoint_goods_ladder_config']['rate']) && !empty($config['appoint_goods_ladder_config']['price']) && count($config['appoint_goods_ladder_config']['rate']) == count($config['appoint_goods_ladder_config']['price']))
        {
            // 没指定商品id则使用配置的商品
            if(empty($goods_ids) && !empty($config['appoint_ladder_goods_ids']))
            {
                $goods_ids = $config['appoint_ladder_goods_ids'];
            }
            if(!empty($goods_ids))
            {
                // 阶梯分割时间
                $interval_time = empty($config['appoint_goods_ladder_interval_time']) ? 0 : intval($config['appoint_goods_ladder_interval_time'])*60;

                // 获取日志记录
                $where = [
                    ['pg.goods_id', 'in', $goods_ids],
                    ['p.user_id', '=', $user_id],
                ];
                $info = Db::name('PluginsDistributionAppointLadderLog')->alias('p')->join('plugins_distribution_appoint_ladder_log_goods pg', 'p.id=pg.log_id')->where($where)->group('p.id')->field('p.*')->order('p.id desc')->find();
                $level = 1;
                $count = count($config['appoint_goods_ladder_config']['rate']);
                if(!empty($info))
                {
                    if($interval_time <= 0 || $info['add_time']+$interval_time >= time())
                    {
                        // 匹配等级
                        if($count > $info['level'])
                        {
                            $level = $info['level']+1;
                        }
                    }
                }

                // 下一个阶梯返佣截止时间
                $temp = ($level > 1 && $level <= $count && !empty($info)) ? $info['add_time']+$interval_time : '';
                $time = (!empty($temp) && $temp >= time()) ? date('Y-m-d H:i:s', $temp) : '';

                // 当返佣规则
                $current = [
                    'rate'  => intval($config['appoint_goods_ladder_config']['rate'][$level-1]),
                    'price' => floatval($config['appoint_goods_ladder_config']['price'][$level-1]),
                ];

                // 实际返佣值
                $profit = ($current['price'] > 0) ? $current['price'] : $current['rate'].'%';

                // 下一个返佣规则
                if($level > 1 && $level <= $count)
                {
                    $is_valid = 1;
                    $msg = '继续分享在截止时间前('.$time.')让更多人购买助力、获得（'.$profit.'）高返佣！';
                } else {
                    $is_valid = 0;
                    $msg = '分享让更多人购买助力、获得('.$profit.')丰厚返佣！';
                }
                return [
                    'level'     => $level,
                    'count'     => $count,
                    'time'      => $time,
                    'current'   => $current,
                    'msg'       => $msg,
                    'is_valid'  => $is_valid,
                ];
            }
        }
        return [];
    }

    /**
     * 获取H5地址
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-01-23
     * @desc    description
     * @param   [array]          $config [当前插件配置]
     */
    public static function H5Url($config)
    {
        return empty($config['h5_url']) ? MyC('common_app_h5_url') : $config['h5_url'];
    }

    /**
     * 分销用户搜索
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-12-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function DistributionSearchUser($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'keywords',
                'error_msg'         => '请输入用户码/名/昵称/手机/邮箱',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        $where = [
            ['status', '=', 0],
            ['plugins_distribution_level', '=', 0],
        ];
        // 是否为会员码
        if(is_numeric($params['keywords']) && strlen($params['keywords']) == 10)
        {
            $where[] = ['number_code', '=', $params['keywords']];
        } else {
            $where[] = ['number_code|username|nickname|mobile|email', 'like', '%'.$params['keywords'].'%'];
        }
        $user = Db::name('User')->where($where)->field('id,username,nickname,mobile,email,avatar')->group('id')->limit(50)->select()->toArray();
        if(!empty($user))
        {
            foreach($user as &$v)
            {
                $v = UserService::UserHandle($v);
            }
            return DataReturn('success', 0, $user);
        }
        return DataReturn('没有相关用户或已存在', -1);
    }

    /**
     * 全部用户查询
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-10-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UserQuery($params = [])
    {
        // 数据验证
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'keywords',
                'error_msg'         => '请输入用户信息',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 是否已开启拜访
        if(empty($params['plugins_config']) || ((!isset($params['plugins_config']['is_enable_visit']) || $params['plugins_config']['is_enable_visit'] != 1) && (!isset($params['plugins_config']['is_modify_superior']) || $params['plugins_config']['is_modify_superior'] != 1)))
        {
            return DataReturn('管理员未开启拜访或修改上级功能', -1);
        }

        // 查询用户、整数则加上id字段
        $id_field = is_numeric($params['keywords']) ? 'id|' : '';
        $where = [
            [$id_field.'number_code|username|nickname|mobile|email', '=', $params['keywords']],
            ['status', '=', 0],
            ['is_delete_time', '=', 0],
            ['is_logout_time', '=', 0],
        ];
        $user = Db::name('User')->where($where)->find();
        if(empty($user))
        {
            return DataReturn('用户不存在', -1);
        }
        // 移除用户敏感数据
        unset($user['mobile'], $user['email']);
        return DataReturn('success', 0, UserService::UserHandle($user));
    }

    /**
     * 上级用户保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-10-23
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SuperiorSave($params = [])
    {
        // 数据验证
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'superior_id',
                'error_msg'         => '请先搜索用户',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 插件配置
        $plugins_config = empty($params['plugins_config']) ? [] : $params['plugins_config'];

        // 不能和当前用户相同
        if($params['user']['id'] == $params['superior_id'])
        {
            return DataReturn('上级用户不能是自己！', -1);
        }

        // 是否还是原来的
        if($params['user']['referrer'] == $params['superior_id'])
        {
            return DataReturn('上级已经是该用户、不能重复修改！', -1);
        }

        // 是否有修改次数限制
        if(!empty($plugins_config) && !empty($plugins_config['superior_modify_number_limit']))
        {
            $info = Db::name('PluginsDistributionSuperiorModify')->where(['user_id'=>$params['user']['id']])->find();
            if(!empty($info) && $info['modify_number'] >= $plugins_config['superior_modify_number_limit'])
            {
                return DataReturn('已修改达到('.$plugins_config['superior_modify_number_limit'].')次限制、不能修改！', -1);
            }
        }

        // 更新用户信息
        $ret = UserService::UserUpdateHandle(['referrer'=>intval($params['superior_id']), 'upd_time'=>time()], $params['user']['id']);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 重新更新用户缓存
        $user = UserService::UserHandle(UserService::UserInfo('id', $params['user']['id']));
        UserService::UserLoginRecord($params['user']['id'], $user);
        if(!empty($user['token']))
        {
            MyCache(SystemService::CacheKey('shopxo.cache_user_info').$user['token'], $user);
        }

        // 记录更新次数
        if(empty($info))
        {
            $insert_data = [
                'user_id'        => $params['user']['id'],
                'modify_number'  => 1,
                'add_time'       => time(),
            ];
            if(Db::name('PluginsDistributionSuperiorModify')->insertGetId($insert_data) <= 0)
            {
                return DataReturn('记录数据添加失败', -1);
            }
        } else {
            $update_data = [
                'modify_number'  => $info['modify_number']+1,
                'upd_time'       => time(),
            ];
            if(!Db::name('PluginsDistributionSuperiorModify')->where(['id'=>$info['id']])->update($update_data))
            {
                return DataReturn('记录数据更新失败', -1);
            }
        }

        // 返回上级用户
        $params['user']['referrer'] = intval($params['superior_id']);
        return DataReturn(MyLang('operate_success'), 0, self::UserSuperiorData($params['user'], $plugins_config));
    }

    /**
     * 获取店铺id
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-08
     * @desc    description
     */
    public static function ShopID()
    {
        return CallPluginsServiceMethod('shop', 'ShopService', 'CurrentUserShopID', true);
    }

    /**
     * 获取店铺信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-12-04
     * @desc    description
     * @param   [int]          $shop_id [店铺id]
     */
    public static function ShopInfo($shop_id)
    {
        return CallPluginsServiceMethod('shop', 'ShopService', 'UserShopInfo', $shop_id, 'id');
    }
}
?>