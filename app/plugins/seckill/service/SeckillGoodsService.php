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
namespace app\plugins\seckill\service;

use think\facade\Db;
use app\service\GoodsService;
use app\service\ResourcesService;
use app\plugins\seckill\service\BaseService;
use app\plugins\seckill\service\PeriodsService;

/**
 * 限时秒杀 - 商品服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-29
 * @desc    description
 */
class SeckillGoodsService
{
    /**
     * 数据列表处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-11
     * @desc    description
     * @param   [array]          $data   [数据列表]
     * @param   [array]          $params [输入参数]
     */
    public static function SeckillGoodsListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            // 审核状态
            $status_list = array_column(BaseService::$plugins_goods_audit_status_list, 'name', 'value');
            foreach($data as &$v)
            {
                // 审核状态
                $v['status_name'] = (array_key_exists('status', $v) && array_key_exists($v['status'], $status_list)) ? $status_list[$v['status']] : '';

                // 秒杀价
                $v['seckill_price'] = BaseService::PriceCalculate($v['price'], $v['discount_rate'], $v['dec_price']);

                // 折扣美化
                $v['discount_rate'] = PriceBeautify($v['discount_rate']);
                $v['dec_price'] = PriceBeautify($v['dec_price']);

                $v['time_start'] = empty($v['time_start']) ? '' : date('Y-m-d', $v['time_start']);
                $v['time_end'] = empty($v['time_end']) ? '' : date('Y-m-d', $v['time_end']);
            }
        }
        return GoodsService::GoodsDataHandle($data, $params);
    }

    /**
     * 秒杀信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-28
     * @desc    description
     * @param   [array]          $config [插件配置]
     * @param   [array]          $params [输入参数]
     */
    public static function SeckillData($config, $params = [])
    {
        // 临时记录数据
        $current = [];
        $last_end = [];
        $data_list = [];

        // 时段数据
        $periods_list = PeriodsService::PeriodsDataList(['field'=>'id,name,start_hour,continue_hour']);
        if(!empty($periods_list))
        {
            // 商品数据
            $params['where'] = [
                ['status', '=', 1],
                ['periods_id', 'in', array_column($periods_list, 'id')],
                ['time_start', '<=', time()],
                ['time_end', '>=', time()],
            ];
            // 是否推荐
            if(isset($params['is_recommend']))
            {
                $params['where'][] = ['is_recommend', '=', 1];
            }
            // 是否指定商品
            if(!empty($params['goods_ids']) && is_array($params['goods_ids']))
            {
                if(!is_array($params['goods_ids']))
                {
                    $params['goods_ids'] = explode(',', $params['goods_ids']);
                }
                $params['where'][] = ['goods_id', 'in', $params['goods_ids']];
            }
            $goods = self::SeckillGoodsList($params);
            $goods_group = [];
            if(!empty($goods['data']))
            {
                foreach($goods['data'] as $g)
                {
                    if(!array_key_exists($g['periods_id'], $goods_group))
                    {
                        $goods_group[$g['periods_id']] = [];
                    }
                    $goods_group[$g['periods_id']][] = $g;
                }
            }

            // 循环处理每个时段数据
            foreach($periods_list as &$v)
            {
                // 时段下的商品
                $v['goods'] = (!empty($goods_group) && array_key_exists($v['id'], $goods_group)) ? $goods_group[$v['id']] : [];

                // 秒杀时间
                $v['time_start'] = date('Y-m-d '.(strlen($v['start_hour']) == 1 ? '0' : '').$v['start_hour'].':00:00');
                $temp_hour = $v['start_hour']+$v['continue_hour'];
                $v['time_end'] = date('Y-m-d '.(strlen($temp_hour) == 1 ? '0' : '').$temp_hour.':00:00');
                $v['time_start_number'] = strtotime($v['time_start']);
                $v['time_end_number'] = strtotime($v['time_end']);
                $v['time'] = BaseService::TimeCalculate($v['time_start_number'], $v['time_end_number']);
                if(in_array($v['time']['status'], [0,1]))
                {
                    // 加入列表
                    $data_list[] = $v;

                    // 当前时段
                    if($v['time']['status'] == 1)
                    {
                        $current = $v;
                    }

                    // 记录最后一个结束的时段
                    if($v['time']['status'] == 2)
                    {
                        $last_end = $v;
                    }
                }
            }
            // 如果没有数据则默认取第一条
            if(empty($current))
            {
                $current = empty($last_end) ? (empty($data_list[0]) ? [] : $data_list[0]) : $last_end;
            }

            // 当前秒杀新增基础字段
            // 头部logo
            $header_logo = empty($config['header_logo']) ? StaticAttachmentUrl('header-logo.png') : $config['header_logo'];
            // 头部背景
            $header_bg = empty($config['header_bg']) ? StaticAttachmentUrl('header-bg.png') : $config['header_bg'];
            // 首页标题图标
            $home_title_icon = empty($config['home_title_icon']) ? StaticAttachmentUrl('home-title-icon.png') : $config['home_title_icon'];
            // 首页背景
            $home_bg = empty($config['home_bg']) ? StaticAttachmentUrl('home-bg.png') : $config['home_bg'];
            // 商品详情页面icon信息
            $current['goods_detail_icon']    = empty($config['goods_detail_icon']) ? '秒杀价' : $config['goods_detail_icon'];
            $current['goods_detail_title']   = empty($config['goods_detail_title']) ? '限时秒杀' : $config['goods_detail_title'];
            $current['goods_detail_header']  = StaticAttachmentUrl('goods-detail-header-'.APPLICATION.'.png');
            $current['header_logo']          = $header_logo;
            $current['header_bg']            = $header_bg;
            $current['home_title_icon']      = $home_title_icon;
            $current['home_bg']              = $home_bg;
        }
        $result = [
            'periods_list'  => $data_list,
            'current'       => $current,
            'config'        => $config,
        ];
        return DataReturn(MyLang('operate_success'), 0, $result);
    }

    /**
     * 商品列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function SeckillGoodsList($params = [])
    {
        // 获取商品id
        $where = empty($params['where']) ? [] : $params['where'];
        $goods = Db::name('PluginsSeckillGoods')->where($where)->order('id asc')->column('goods_id,shop_id,status,periods_id,discount_rate,dec_price,time_start,time_end,is_recommend', 'goods_id');
        if(empty($goods))
        {
            return DataReturn('没有商品', 0);
        }

        // 是否需要处理商品
        if(!isset($params['is_goods_handle']) || $params['is_goods_handle'] == 1)
        {
            // 条件
            $goods_ids = array_column($goods, 'goods_id');
            $where = [
                ['g.is_delete_time', '=', 0],
                ['g.is_shelves', '=', 1],
                ['g.id', 'in', $goods_ids],
            ];
            // 是否多商户
            if(!empty($params['shop_id']))
            {
                $where[] = ['g.shop_id', '=', intval($params['shop_id'])];
            }

            // 获取数据
            $ret = GoodsService::CategoryGoodsList(['where'=>$where, 'm'=>0, 'n'=>0, 'is_spec'=>1, 'is_cart'=>1]);
            if(!empty($ret['data']))
            {
                $result = [];
                $data = array_column($ret['data'], null, 'id');
                foreach($goods_ids as $gid)
                {
                    if(isset($data[$gid]))
                    {
                        // 数据合并
                        $temp = array_merge($data[$gid], $goods[$gid]);
                        // 秒杀价
                        $temp['seckill_price'] = BaseService::PriceCalculate($temp['price_container']['price'], $temp['discount_rate'], $temp['dec_price']);
                        $temp['seckill_min_price'] = BaseService::PriceCalculate($temp['price_container']['min_price'], $temp['discount_rate'], $temp['dec_price']);
                        // 活动时间
                        $temp['time_start'] = empty($temp['time_start']) ? '' : date('Y-m-d', $temp['time_start']);
                        $temp['time_end'] = empty($temp['time_end']) ? '' : date('Y-m-d', $temp['time_end']);
                        $result[] = $temp;
                    }
                }
                $ret['data'] = $result;
            }
        } else {
            $ret = DataReturn('success', 0, $goods);
        }
        return $ret;
    }

    /**
     * 关联商品保存 - 管理员
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-07
     * @desc    description
     * @param    [array]          $config [插件配置]
     * @param    [array]          $params [输入参数]
     */
    public static function AdminSeckillGoodsSave($config, $params = [])
    {
        // 商品多商户id
        $shop_goods = [];
        if(isset($config['is_shop_seckill']) && $config['is_shop_seckill'] == 1 && !empty($params['data']) && is_array($params['data']))
        {
            $shop_goods = Db::name('Goods')->where(['id'=>array_column($params['data'], 'goods_id')])->column('shop_id', 'id');
        }

        // 原有数据
        $list = Db::name('PluginsSeckillGoods')->column('*', 'goods_id');

        // 清除商品id
        Db::name('PluginsSeckillGoods')->where('id', '>', 0)->delete();

        // 写入商品id
        if(!empty($params['data']))
        {
            $sucs = 0;
            $fail = 0;
            foreach($params['data'] as &$v)
            {
                // 数据是否存在、则添加
                if(!empty($list) && array_key_exists($v['goods_id'], $list))
                {
                    $temp = $list[$v['goods_id']];
                    $v['shop_id'] = isset($temp['shop_id']) ? $temp['shop_id'] : 0;
                    $v['status'] = isset($temp['status']) ? $temp['status'] : 0;
                    $v['refuse_reason'] = empty($temp['refuse_reason']) ? '' : $temp['refuse_reason'];
                    $v['add_time'] = empty($temp['add_time']) ? time() : $temp['add_time'];
                    $v['upd_time'] = time();
                } else {
                    $v['add_time'] = time();
                }
                $v['discount_rate'] = empty($v['discount_rate']) ? 0.00 : floatval($v['discount_rate']);
                $v['dec_price'] = empty($v['dec_price']) ? 0.00 : floatval($v['dec_price']);
                $v['time_start'] = empty($v['time_start']) ? 0 : strtotime($v['time_start']);
                $v['time_end'] = empty($v['time_end']) ? 0 : strtotime($v['time_end']);

                // 非店铺操作、无状态字段则添加已审核状态
                if(!array_key_exists('status', $v))
                {
                    $v['status'] = 1;
                }

                // 店铺id
                if(!empty($shop_goods) && !empty($shop_goods[$v['goods_id']]))
                {
                    $v['shop_id'] = $shop_goods[$v['goods_id']];
                }
                if(Db::name('PluginsSeckillGoods')->insertGetId($v) > 0)
                {
                    $sucs++;
                } else {
                    $fail++;
                }
            }
            if($sucs > 0 && $fail > 0)
            {
                return DataReturn('操作成功('.$sucs.'条),失败('.$fail.'条)', 0);
            }
            if($sucs == 0 && $fail > 0)
            {
                return DataReturn(MyLang('operate_fail'), -100);
            }
        }
        return DataReturn(MyLang('operate_success'), 0);
    }

    /**
     * 关联商品保存 - 店铺
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-07
     * @desc    description
     * @param    [array]          $config [插件配置]
     * @param    [array]          $params [输入参数]
     */
    public static function ShopSeckillGoodsSave($config, $params = [])
    {
        // 请求参数
        $p = [
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
        $shop_id = intval($params['shop_id']);

        // 商品多商户id
        $shop_goods = [];
        if(isset($config['is_shop_seckill']) && $config['is_shop_seckill'] == 1 && !empty($params['data']) && is_array($params['data']))
        {
            $shop_goods = Db::name('Goods')->where(['id'=>array_column($params['data'], 'goods_id')])->column('shop_id', 'id');
        }

        // 原有数据
        $list = Db::name('PluginsSeckillGoods')->where(['shop_id'=>$shop_id])->column('*', 'goods_id');

        // 数据处理
        $del_data = [];
        $insert_data = [];
        $update_data = [];
        if(!empty($params['data']) && !empty($shop_goods))
        {
            foreach($params['data'] as &$v)
            {
                // 数据必须存在店铺商品中
                if(!empty($shop_goods[$v['goods_id']]))
                {
                    // 数据是否存在、则添加
                    if(!empty($list) && array_key_exists($v['goods_id'], $list))
                    {
                        $temp = $list[$v['goods_id']];
                        $v['is_recommend'] = isset($temp['is_recommend']) ? $temp['is_recommend'] : 0;
                        $v['status'] = isset($temp['status']) ? $temp['status'] : 0;
                        $v['refuse_reason'] = empty($temp['refuse_reason']) ? '' : $temp['refuse_reason'];
                        $v['upd_time'] = time();
                    } else {
                        $v['is_recommend'] = 0;
                        $v['status'] = 0;
                        $v['add_time'] = time();
                    }
                    $v['discount_rate'] = empty($v['discount_rate']) ? 0.00 : floatval($v['discount_rate']);
                    $v['dec_price'] = empty($v['dec_price']) ? 0.00 : floatval($v['dec_price']);
                    $v['time_start'] = empty($v['time_start']) ? 0 : strtotime($v['time_start']);
                    $v['time_end'] = empty($v['time_end']) ? 0 : strtotime($v['time_end']);
                    $v['shop_id'] = $shop_goods[$v['goods_id']];

                    // 加入数据
                    if(!empty($list) && array_key_exists($v['goods_id'], $list))
                    {
                        $update_data[$v['goods_id']] = $v;
                    } else {
                        $insert_data[$v['goods_id']] = $v;
                    }
                }
            }
        }
        // 删除
        if(!empty($list))
        {
            foreach($list as $lv)
            {
                if(!array_key_exists($lv['goods_id'], $insert_data) && !array_key_exists($lv['goods_id'], $update_data))
                {
                    $del_data[] = $lv['goods_id'];
                }
            }
            if(!empty($del_data))
            {
                $where = [
                    ['goods_id', 'in', $del_data],
                    ['shop_id', '=', $shop_id],
                ];
                Db::name('PluginsSeckillGoods')->where($where)->delete();
            }
        }

        // 更新
        if(!empty($update_data))
        {
            foreach($update_data as $uv)
            {
                Db::name('PluginsSeckillGoods')->where(['goods_id'=>$uv['goods_id'], 'shop_id'=>$uv['shop_id']])->update($uv);
            }
        }
        // 添加
        if(!empty($insert_data))
        {
            foreach($insert_data as $uv)
            {
                Db::name('PluginsSeckillGoods')->insertGetId($uv);
            }
        }
        return DataReturn(MyLang('operate_success'), 0);
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
    public static function SeckillGoodsDelete($params = [])
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

        // 店铺验证
        $check = self::ShopParamsCheck('ids', $params);
        if($check['code'] != 0)
        {
            return $check;
        }

        // 删除操作
        if(Db::name('PluginsSeckillGoods')->where(['id'=>$check['data']])->delete())
        {
            return DataReturn(MyLang('delete_success'), 0);
        }
        return DataReturn(MyLang('delete_fail'), -100);
    }

    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function SeckillGoodsStatusUpdate($params = [])
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

        // 店铺验证
        $check = self::ShopParamsCheck('id', $params);
        if($check['code'] != 0)
        {
            return $check;
        }

        // 数据更新
        if(Db::name('PluginsSeckillGoods')->where(['id'=>intval($check['data'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
           return DataReturn(MyLang('edit_success'), 0);
        }
        return DataReturn(MyLang('edit_fail'), -100);
    }

    /**
     * 店铺参数验证
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-11
     * @desc    description
     * @param   [string]          $field  [数据字段]
     * @param   [array]           $params [输入参数]
     */
    public static function ShopParamsCheck($field, $params = [])
    {
        // 是否多商户
        $value = isset($params[$field]) ? $params[$field] : '';
        if(!empty($params['shop_id']) && !empty($value))
        {
            if(!is_array($value))
            {
                $value = explode(',', $value);
            }
            $where = [
                ['g.shop_id', '=', intval($params['shop_id'])],
                ['pg.id', 'in', $value],
            ];
            $value = Db::name('PluginsSeckillGoods')->alias('pg')->join('goods g', 'g.id=pg.goods_id')->where($where)->column('pg.id');
            if(empty($value))
            {
                return DataReturn(MyLang('no_data'), -1);
            }
        }
        return DataReturn('success', 0, $value);
    }

    /**
     * 管理员商品审核
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SeckillGoodsAudit($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'opt_type',
                'checked_data'      => [0,1],
                'error_msg'         => '操作范围值有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 获取商品
        $where = [
            ['id', '=', intval($params['id'])],
        ];
        $info = Db::name('PluginsSeckillGoods')->where($where)->find();
        if(empty($info))
        {
            return DataReturn('商品不存在', -1);
        }
        if($info['status'] == 1)
        {
            return DataReturn(MyLang('status_not_can_operate_tips').'['.BaseService::$plugins_goods_audit_status_list[$info['status']]['name'].']', -1);
        }

        // 操作类型，0拒绝、1通过
        if($params['opt_type'] == 1)
        {
            // 更新数据
            $data = [
                'refuse_reason' => '',
                'status'        => 1,
                'is_recommend'  => isset($params['is_recommend']) ? intval($params['is_recommend']) : 0,
                'upd_time'      => time(),
            ];
        } else {
            // 请求参数
            $p = [
                [
                    'checked_type'      => 'length',
                    'key_name'          => 'msg',
                    'checked_data'      => '200',
                    'error_msg'         => '拒绝原因格式 最多200个字符',
                ],
            ];
            $ret = ParamsChecked($params, $p);
            if($ret !== true)
            {
                return DataReturn($ret, -1);
            }
            // 更新数据
            $data = [
                'refuse_reason' => $params['msg'],
                'status'        => 2,
                'upd_time'      => time(),
            ];
        }

        // 更新商品
        if(Db::name('PluginsSeckillGoods')->where(['id'=>$info['id']])->update($data))
        {
            return DataReturn(MyLang('operate_success'), 0);
        }
        return DataReturn(MyLang('operate_fail'), -1);
    }
}
?>