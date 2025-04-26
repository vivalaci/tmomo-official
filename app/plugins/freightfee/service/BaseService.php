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
namespace app\plugins\freightfee\service;

use think\facade\Db;
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\service\RegionService;
use app\service\ResourcesService;
use app\service\PluginsService;
/**
 * 运费设置服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class BaseService
{
    // 计件方式
    public static $is_whether_list = [
        0 => array('id' => 0, 'name' => '按件数', 'checked' => true),
        1 => array('id' => 1, 'name' => '按重量(kg)'),
        2 => array('id' => 2, 'name' => '按体积(m³)'),
    ];

    // 续费计算方式
    public static $is_continue_type_list = [
        0 => array('id' => 0, 'name' => '四舍五入取整', 'checked' => true),
        1 => array('id' => 1, 'name' => '向上取整（有小数就加1）'),
        2 => array('id' => 2, 'name' => '向下取整（有小数就忽略、直接取整）'),
    ];

    // 基础数据附件字段
    public static $base_config_attachment_field = [];

    /**
     * 基础配置信息保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseConfigSave($params = [])
    {
        return PluginsService::PluginsDataSave(['plugins'=>'freightfee', 'data'=>$params], self::$base_config_attachment_field);
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
        return PluginsService::PluginsData('freightfee', self::$base_config_attachment_field, $is_cache);
    }

    /**
     * 数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function WarehouseFeeList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'ft.*,w.id as warehouse_id,w.name as warehouse_name,w.alias as warehouse_alias' : $params['field'];
        $order_by = empty($params['order_by']) ? 'w.level desc, w.id desc' : trim($params['order_by']);
        $data = Db::name('PluginsFreightfeeTemplate')->alias('ft')->rightJoin('warehouse w', 'ft.warehouse_id=w.id')->field($field)->where($where)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::DataHandle($data));
    }

    /**
     * 数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-18
     * @desc    description
     * @param   [array]          $data [仓库数据]
     */
    public static function DataHandle($data)
    {
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                // 支付方式
                if(empty($v['payment']))
                {
                    $v['payment'] = [];
                    $v['payment_names'] = [];
                } else {
                    $v['payment'] = explode(',', $v['payment']);
                    $v['payment_names'] = array_map(function($v){return mb_substr($v, strrpos($v, '-')+1, null, 'utf-8');}, $v['payment']);
                }

                // 地区
                if(!empty($v['data']))
                {
                    if(!is_array($v['data']))
                    {
                        $v['data'] = json_decode($v['data'], true);
                    }
                    foreach($v['data'] as &$vs)
                    {
                        $vs['region_names'] = (empty($vs['region_show']) || $vs['region_show'] == 'default') ? '' : implode('、', Db::name('Region')->where('id', 'in', explode('-', $vs['region_show']))->column('name'));
                    }
                }

                // 商品列表
                $v['goods_list'] = empty($v['goods_ids']) ? [] : self::GoodsList($v['goods_ids']);

                // 商品分类追加运费
                $v['goods_category_append'] = empty($v['goods_category_append']) ? '' : json_decode($v['goods_category_append'], true);
            }
        }
        return $data;
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-07
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function WarehouseFeeSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'warehouse_id',
                'error_msg'         => '仓库id有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'show_name',
                'error_msg'         => '运费展示名称不能为空',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'show_name',
                'checked_data'      => '0,16',
                'error_msg'         => '运费展示名称格式最多 16 个字符',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'valuation',
                'checked_data'      => array_column(self::$is_whether_list, 'id'),
                'error_msg'         => '计价方式范围值有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'is_insufficient_first_price',
                'checked_data'      => [0,1],
                'is_checked'        => 2,
                'error_msg'         => '不满足按首费计算范围值有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'is_continue_type',
                'checked_data'      => array_column(self::$is_continue_type_list, 'id'),
                'error_msg'         => '计价方式范围值有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'data',
                'error_msg'         => '运费规则不能为空',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 运费规则
        if(!empty($params['data']) && is_array($params['data']))
        {
            $params['data'] = array_values($params['data']);
            foreach($params['data'] as &$dv)
            {
                if(!empty($dv['fee_list']) && is_array($dv['fee_list']))
                {
                    $dv['fee_list'] = array_values($dv['fee_list']);
                }
            }
        }

        // 操作数据
        $data = [
            'warehouse_id'                  => intval($params['warehouse_id']),
            'show_name'                     => trim($params['show_name']),
            'valuation'                     => intval($params['valuation']),
            'is_insufficient_first_price'   => isset($params['is_insufficient_first_price']) ?  intval($params['is_insufficient_first_price']) : 0,
            'is_continue_type'              => intval($params['is_continue_type']),
            'data'                          => empty($params['data']) ? '' : json_encode($params['data']),
            'payment'                       => empty($params['payment']) ? '' : $params['payment'],
            'goods_ids'                     => empty($params['goods_ids']) ? '' : $params['goods_ids'],
            'goods_category_append'         => empty($params['goods_category_append']) ? '' : json_encode($params['goods_category_append'], JSON_UNESCAPED_UNICODE),
            'is_enable'                     => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
        ];

        // 查询数据
        $info = Db::name('PluginsFreightfeeTemplate')->where(['warehouse_id'=>$data['warehouse_id']])->find();

        // 添加/更新数据
        if(empty($info))
        {
            $data['add_time'] = time();
            if(Db::name('PluginsFreightfeeTemplate')->insertGetId($data) <= 0)
            {
                return DataReturn(MyLang('insert_fail'), -100);
            }
        } else {
            $data['upd_time'] = time();
            if(!Db::name('PluginsFreightfeeTemplate')->where(['id'=>$info['id']])->update($data))
            {
                return DataReturn(MyLang('update_fail'), -100);
            }
        }
        return DataReturn(MyLang('operate_success'), 0);
    }

    /**
     * 状态更新
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function WarehouseFeeStatusUpdate($params = [])
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
        if(Db::name('PluginsFreightfeeTemplate')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
            return DataReturn(MyLang('edit_success'), 0);
        }
        return DataReturn(MyLang('edit_fail'), -100);
    }

    /**
     * 商品搜索
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function GoodsSearchList($params = [])
    {
        // 条件
        $where = empty($params['where']) ? [] : $params['where'];
        $where = array_merge($where, [
            ['g.is_delete_time', '=', 0],
            ['g.is_shelves', '=', 1]
        ]);

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
        $field = 'g.id,g.title';

        // 获取数据
        return GoodsService::CategoryGoodsList(['where'=>$where, 'm'=>0, 'n'=>100, 'field'=>$field, 'is_admin_access'=>1]);
    }

    /**
     * 商品列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [string]          $goods_ids [商品id]
     */
    public static function GoodsList($goods_ids = [])
    {
        // 商品id
        $goods_ids = empty($goods_ids) ? [] : explode(',', $goods_ids);

        // 条件
        $where = [
            ['g.is_delete_time', '=', 0],
            ['g.is_shelves', '=', 1],
            ['g.id', 'in', $goods_ids],
        ];

        // 指定字段
        $field = 'g.id,g.title,g.images,g.price';

        // 获取数据
        $ret = GoodsService::CategoryGoodsList(['where'=>$where, 'm'=>0, 'n'=>0, 'field'=>$field]);
        return $ret['data'];
    }

    /**
     * 地区列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-22
     * @desc    description
     */
    public static function RegionList()
    {
        // 地区
        $region = RegionService::RegionItems(['pid'=>0, 'field'=>'id,name']);
        if(!empty($region))
        {
            $region = array_map(function($v)
            {
                $v['items'] = RegionService::RegionItems(['pid'=>$v['id'], 'field'=>'id,name']);
                return $v;
            }, $region);
        }
        return $region;
    }

    /**
     * 运费计算数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-02-13
     * @desc    description
     * @param   [array]          &$order_data [订单数据]
     * @param   [array]           $params     [输入参数]
     */
    public static function FreightFeeCalculateDataHandle(&$order_data, $params = [])
    {
        $rules = null;
        if(!empty($order_data))
        {
            // 获取运费模板
            $fee_template = BaseService::DataHandle(Db::name('PluginsFreightfeeTemplate')->where([
                'warehouse_id'  => array_column($order_data, 'id'),
                'is_enable'     => 1,
            ])->select()->toArray());
            if(!empty($fee_template))
            {
                // 运费模板分组
                $group = [];
                foreach($fee_template as $fv)
                {
                    $group[$fv['warehouse_id']] = $fv;
                }

                // 货币符号
                $currency_symbol = ResourcesService::CurrencyDataSymbol();

                // 当前选中的支付方式
                $payment_id = (isset($params['params']) && isset($params['params']['params']) && isset($params['params']['params']['payment_id'])) ? $params['params']['params']['payment_id'] : 0;

                // 运费计算
                foreach($order_data as &$v)
                {
                    // 订单模式（0快递, 1展示型, 2自提点, 3虚拟销售）
                    // 仓库是否存在运费模板
                    if(empty($group[$v['id']]))
                    {
                        continue;
                    }
                    $template = $group[$v['id']];

                    // 计算运费
                    // 支付方式免运费
                    $is_payment = false;
                    if(!empty($template['payment']) && !empty($payment_id))
                    {
                        if(!is_array($template['payment']))
                        {
                            $template['payment'] = explode(',', $template['payment']);
                        }
                        $payment = array_map(function($v){return explode('-', $v);}, $template['payment']);
                        if(!empty($payment) && is_array($payment))
                        {
                            foreach($payment as $pv)
                            {
                                if(isset($pv[0]) && $pv[0] == $payment_id)
                                {
                                    $is_payment = true;
                                    break;
                                }
                            }
                        }
                    }
                    if($is_payment === false)
                    {
                        // 免运费商品
                        $free_goods = self::FreeShippingGoods(empty($template['goods_ids']) ? '' : $template['goods_ids'], $v['goods_items']);
                        $buy_count = $v['order_base']['buy_count']-$free_goods['buy_count'];
                        $spec_weight_total = $v['order_base']['spec_weight_total']-$free_goods['spec_weight'];
                        $spec_volume_total = $v['order_base']['spec_volume_total']-$free_goods['spec_volume'];

                        // 是否设置运费数据
                        if(!empty($template['data']) && ($buy_count > 0 || $spec_weight_total > 0 || $spec_volume_total > 0))
                        {
                            // 当前默认模板
                            $default_fee = null;
                            // 规则
                            $rules = self::RulesHandle($template['data'], $v['order_base']['address']);
                            if(!empty($rules) && !empty($rules['fee_list']))
                            {
                                // 运费
                                $price = 0;

                                // 指定运费
                                $key = 'freightfee_id_'.$v['id'];
                                $freightfee_id = empty($params['params']) ? (isset($params[$key]) ? intval($params[$key]) : 0) : (empty($params['params']['params']) ? (isset($params['params'][$key]) ? intval($params['params'][$key]) : 0) : (isset($params['params']['params'][$key]) ? intval($params['params']['params'][$key]) : 0));

                                // 运费模板
                                $fee = array_key_exists($freightfee_id, $rules['fee_list']) ? $rules['fee_list'][$freightfee_id] : [];
                                if(!empty($fee))
                                {
                                    // 订单金额满免运费
                                    if(empty($rules['free_shipping_price']) || $v['order_base']['total_price'] < $rules['free_shipping_price'])
                                    {
                                        // 根据规则计算运费
                                        switch($template['valuation'])
                                        {
                                            // 按件
                                            case 0 :
                                                if($buy_count > 0)
                                                {
                                                    $price = self::FreightTypeCalculate($rules, $buy_count, $template, $fee);
                                                }
                                                break;

                                            // 按重量
                                            case 1 :
                                                if($spec_weight_total > 0)
                                                {
                                                    $price = self::FreightTypeCalculate($rules, $spec_weight_total, $template, $fee);
                                                }
                                                break;

                                            // 按体积
                                            case 2 :
                                                if($spec_volume_total > 0)
                                                {
                                                    $price = self::FreightTypeCalculate($rules, $spec_volume_total, $template, $fee);
                                                }
                                                break;
                                        }
                                    }

                                    // 运费类型数据名称处理
                                    $valuation_unit = ($template['valuation'] == 0) ? '件' : (($template['valuation'] == 1) ? 'kg' : 'm³');
                                    foreach($rules['fee_list'] as $rk=>&$rv)
                                    {
                                        // 名称拼接
                                        $temp = '';
                                        if(isset($rules['first']) && $rules['first'] > 0 && isset($rv['first_price']) && $rv['first_price'] > 0)
                                        {
                                            $temp .= '首'.$rules['first'].$valuation_unit.'/'.$currency_symbol.$rv['first_price'];
                                        }
                                        if(isset($rules['continue']) && $rules['continue'] > 0 && isset($rv['continue_price']) && $rv['continue_price'] > 0)
                                        {
                                            if(!empty($temp))
                                            {
                                                $temp .= '、';
                                            }
                                            $temp .= '续'.$rules['continue'].$valuation_unit.'/'.$currency_symbol.$rv['continue_price'];
                                        }
                                        $rv['fee_name'] .= empty($temp) ? '' : '('.$temp.')';

                                        // 选中处理
                                        $rv['key'] = $rk;
                                        if($rk == $freightfee_id)
                                        {
                                            $default_fee = $rv;
                                            $rv['active'] = 1;
                                        } else {
                                            $rv['active'] = 0;
                                        }
                                    }
                                }

                                // 扩展展示数据
                                if($price > 0)
                                {
                                    $v['order_base']['extension_data'][] = [
                                        'name'      => (empty($template['show_name']) ? '运费' : $template['show_name']).'('.$fee['fee_name'].')',
                                        'price'     => $price,
                                        'type'      => 1,
                                        'business'  => 'plugins-freightfee',
                                        'tips'      => '+'.$currency_symbol.$price,
                                    ];
                                }

                                // 运费选择数据
                                if(!empty($rules['fee_list']) && count($rules['fee_list']) > 1)
                                {
                                    $v['plugins_freightfee_data'] = [
                                        'fee_list'  => $rules['fee_list'],
                                        'default'   => $default_fee,
                                    ];
                                }
                            }
                        }

                        // 分类特定运费
                        if(!empty($template['goods_category_append']) && is_array($template['goods_category_append']))
                        {
                            $goods_category_ids = Db::name('GoodsCategoryJoin')->where(['goods_id'=>array_column($v['goods_items'], 'goods_id')])->column('category_id');
                            if(!empty($goods_category_ids))
                            {
                                $parent_ids = GoodsCategoryService::GoodsCategoryParentIds($goods_category_ids);
                                if(!empty($parent_ids))
                                {
                                    foreach($template['goods_category_append'] as $gca)
                                    {
                                        if(!empty($gca['price']) && !empty($gca['name']) && !empty($gca['id']) && in_array($gca['id'], $parent_ids))
                                        {
                                            $v['order_base']['extension_data'][] = [
                                                'name'      => empty($gca['icon']) ? '【'.$gca['name'].'】额外运费' : $gca['icon'],
                                                'price'     => $gca['price'],
                                                'type'      => 1,
                                                'business'  => 'plugins-freightfee',
                                                'tips'      => '+'.$currency_symbol.$gca['price'],
                                                'ext'       => 'special',
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return [
            'order_data'  => $order_data,
            'rules'       => $rules,
        ];
    }

    /**
     * 运费计费
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-21
     * @desc    description
     * @param   [array]    $rules   [规则]
     * @param   [array]    $value   [数据值( 数量 buy_count , 重量 spec_weight_total , 体积 spec_volume_total )]
     * @param   [array]    $config  [插件配置]
     * @param   [array]    $fee     [运费数据]
     */
    public static function FreightTypeCalculate($rules, $value, $config, $fee)
    {
        // 运费金额
        $price = 0;

        // 首重
        if($fee['first_price'] > 0)
        {
            // 首件不满足也加首费 或者 满足加首费
            if((isset($config['is_insufficient_first_price']) && $config['is_insufficient_first_price'] == 1) || $value >= $rules['first'])
            {
                $price = $fee['first_price'];
            }
        }

        // 续重
        if($fee['continue_price'] > 0 && $value > $rules['first'])
        {
            $is_continue_type = isset($config['is_continue_type']) ? intval($config['is_continue_type']) : 0;
            switch($is_continue_type)
            {
                // 向上取整（有小数就加1）
                case 1 :
                    $number = ceil(($value-$rules['first'])/$rules['continue']);
                    break;

                // 向下取整（有小数就忽略）
                case 2 :
                    $number = floor(($value-$rules['first'])/$rules['continue']);
                    break;

                // 四舍五入取整 默认
                default :
                    $number = round(($value-$rules['first'])/$rules['continue']);
            }
            if($number > 0)
            {
                $price += PriceNumberFormat($fee['continue_price']*$number);
            }
        }
        return $price;
    }

    /**
     * 运费规则匹配
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-21
     * @desc    description
     * @param   [array]          $rules   [运费规则列表]
     * @param   [array]          $address [用户地址]
     */
    public static function RulesHandle($rules, $address)
    {
        // 规则数据类型
        if(!empty($rules) && !is_array($rules))
        {
            $rules = json_decode($rules, true);
        }

        // 大于一个规则
        if(count($rules) > 1 && !empty($address))
        {
            $data = [
                'province'  => ['rules' => [], 'number' => 0],
                'city'      => ['rules' => [], 'number' => 0],
            ];
            foreach($rules as $k=>$v)
            {
                if($k != 0)
                {
                    $region = explode('-', $v['region']);
                    if(!empty($region))
                    {
                        if(in_array($address['province'], $region))
                        {
                            $data['province']['rules'] = $v;
                            $data['province']['number']++;
                        }
                        if(in_array($address['city'], $region))
                        {
                            $data['city']['rules'] = $v;
                            $data['city']['number']++;
                        }
                    }
                }
            }
            if($data['city']['number'] > 0)
            {
                if($data['province']['number'] > $data['city']['number'])
                {
                    return $data['province']['rules'];
                }
                return $data['city']['rules'];
            } else {
                if($data['province']['number'] > 0)
                {
                    return $data['province']['rules'];
                }
            }
        }
        return  isset($rules[0]) ? $rules[0] : [];
    }

    /**
     * 免运费商品
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-26
     * @desc    description
     * @param   [string]         $goods_ids [商品ids]
     * @param   [array]          $goods     [商品]
     */
    public static function FreeShippingGoods($goods_ids, $goods)
    {
        $result = [
            'buy_count'     => 0,
            'spec_weight'   => 0,
            'spec_volume'   => 0,
        ];
        if(!empty($goods_ids))
        {
            if(!is_array($goods_ids))
            {
                $goods_ids = explode(',', $goods_ids);
            }
            foreach($goods as $v)
            {
                if(in_array($v['goods_id'], $goods_ids))
                {
                    $result['buy_count'] += $v['stock'];
                    $result['spec_weight'] += $v['stock']*$v['spec_weight'];
                    $result['spec_volume'] += $v['stock']*$v['spec_volume'];
                }
            }
        }

        return $result;
    }
}
?>