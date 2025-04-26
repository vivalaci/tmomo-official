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
namespace app\plugins\intellectstools\service;

use think\facade\Db;
use app\service\GoodsService;
use app\service\WarehouseService;
use app\service\WarehouseGoodsService;

/**
 * 智能工具箱 - 商品库存服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class GoodsInventoryService
{
    /**
     * 商品库存数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GoodsInventoryData($params = [])
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

        // 获取商品规格
        $goods_id = intval($params['id']);
        $res = GoodsService::GoodsSpecificationsActual($goods_id);
        $inventory_spec = [];
        if(!empty($res['value']) && is_array($res['value']))
        {
            // 获取当前配置的库存
            $spec = array_column($res['value'], 'value');
            foreach($spec as $v)
            {
                $arr = explode(GoodsService::$goods_spec_to_string_separator, $v);
                $inventory_spec[] = [
                    'name'      => implode(' / ', $arr),
                    'spec'      => json_encode(WarehouseGoodsService::GoodsSpecMuster($v, $res['title']), JSON_UNESCAPED_UNICODE),
                    'md5_key'   => md5(implode('', $arr)),
                    'inventory' => 0,
                ];
            }
        } else {
            // 没有规格则处理默认规格 default
            $str = 'default';
            $inventory_spec[] = [
                'name'      => '默认规格',
                'spec'      => $str,
                'md5_key'   => md5($str),
                'inventory' => 0,
            ];
        }

        // 获取仓库
        $warehouse = WarehouseService::WarehouseList(['field'=>'id,name,alias,is_enable', 'where'=>['is_delete_time'=>0]]);
        if(!empty($warehouse['data']))
        {
            // 获取仓库商品
            $warehouse_goods = Db::name('WarehouseGoods')->where(['warehouse_id'=>array_column($warehouse['data'], 'id'), 'goods_id'=>$goods_id])->column('*', 'warehouse_id');
            foreach($warehouse['data'] as &$v)
            {
                // 仓库商品规格库存
                $v['inventory_spec'] = $inventory_spec;

                // 获取库存
                if(!empty($warehouse_goods) && array_key_exists($v['id'], $warehouse_goods))
                {
                    $keys = array_column($inventory_spec, 'md5_key');
                    $where = [
                        'md5_key'               => $keys,
                        'warehouse_goods_id'    => $warehouse_goods[$v['id']]['id'],
                        'warehouse_id'          => $warehouse_goods[$v['id']]['warehouse_id'],
                        'goods_id'              => $warehouse_goods[$v['id']]['goods_id'],
                    ];
                    $inventory_data = Db::name('WarehouseGoodsSpec')->where($where)->column('inventory', 'md5_key');
                    if(!empty($inventory_data))
                    {
                        foreach($v['inventory_spec'] as &$iv)
                        {
                            if(array_key_exists($iv['md5_key'], $inventory_data))
                            {
                                $iv['inventory'] = $inventory_data[$iv['md5_key']];
                                $iv['is_enable'] = $warehouse_goods[$v['id']]['is_enable'];
                            }
                        }
                    }
                }
            }
        }
        return $warehouse;
    }

    /**
     * 商品库存保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GoodsInventorySave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'goods_id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'inventory',
                'error_msg'         => '库存数据有误',
            ],
            [
                'checked_type'      => 'is_array',
                'key_name'          => 'inventory',
                'error_msg'         => '库存数据有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'md5_key',
                'error_msg'         => '库存唯一值有误',
            ],
            [
                'checked_type'      => 'is_array',
                'key_name'          => 'md5_key',
                'error_msg'         => '库存唯一值有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'spec',
                'error_msg'         => '库存规格有误',
            ],
            [
                'checked_type'      => 'is_array',
                'key_name'          => 'spec',
                'error_msg'         => '库存规格有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据处理
        $data = [];
        $goods_id = intval($params['goods_id']);
        foreach($params['spec'] as $wid=>$spec)
        {
            // 仓库id必须存在
            if(!array_key_exists($wid, $params['md5_key']) || !array_key_exists($wid, $params['inventory']))
            {
                continue;
            }

            if(!empty($spec) && is_array($spec))
            {
                foreach($spec as $ks=>$vs)
                {
                    // 规格值,md5key,库存 必须存在
                    if(!array_key_exists($ks, $params['md5_key'][$wid]) || !array_key_exists($ks, $params['inventory'][$wid]))
                    {
                        continue;
                    }

                    // 库存数据
                    $inventory = intval($params['inventory'][$wid][$ks]);
                    if($inventory > 0)
                    {
                        $data[$wid][] = [
                            'warehouse_id'          => $wid,
                            'goods_id'              => $goods_id,
                            'md5_key'               => $params['md5_key'][$wid][$ks],
                            'spec'                  => htmlspecialchars_decode($vs),
                            'inventory'             => $inventory,
                            'add_time'              => time(),
                        ];
                    }
                }
            }
        }

        // 需要删除的数据
        $del = [];
        foreach($params['inventory'] as $wid=>$iv)
        {
            if(empty($iv) || array_sum(array_filter($iv)) <= 0)
            {
                $del[] = $wid;
            }
        }

        // 添加或删除
        if(!empty($data) || !empty($del))
        {
            // 启动事务
            Db::startTrans();

            // 捕获异常
            try {
                // 写入数据库
                if(!empty($data))
                {
                    // 获取仓库商品
                    $warehouse_goods = Db::name('WarehouseGoods')->where(['warehouse_id'=>array_keys($data), 'goods_id'=>$goods_id])->column('id', 'warehouse_id');
                    foreach($data as $wid=>$v)
                    {
                        // 库存商品不存在则增加
                        if(!array_key_exists($wid, $warehouse_goods))
                        {
                            $where = [
                                'warehouse_id'  => $wid,
                                'goods_id'      => $goods_id,
                            ];
                            $ret = WarehouseGoodsService::WarehouseGoodsAdd($where);
                            if($ret['code'] != 0)
                            {
                                throw new \Exception('仓库商品添加失败');
                            }
                            $warehouse_goods_id = Db::name('WarehouseGoods')->where($where)->value('id');
                        } else {
                            $warehouse_goods_id = $warehouse_goods[$wid];
                        }

                        // 删除原始数据
                        $where = [
                            'warehouse_id'  => $wid,
                            'goods_id'      => $goods_id,
                        ];
                        Db::name('WarehouseGoodsSpec')->where($where)->delete();

                        // 添加数据
                        array_walk($v, function(&$item, $key, $wgid)
                        {
                            $item['warehouse_goods_id'] = $wgid;
                        }, $warehouse_goods_id);
                        if(Db::name('WarehouseGoodsSpec')->insertAll($v) < count($v))
                        {
                            throw new \Exception('规格库存添加失败');
                        }

                        // 仓库商品更新
                        if(!Db::name('WarehouseGoods')->where(['id'=>$warehouse_goods_id])->update([
                            'inventory' => array_sum(array_column($v, 'inventory')),
                            'upd_time'  => time(),
                        ]))
                        {
                            throw new \Exception('库存商品更新失败');
                        }
                    }
                }

                // 删除数据
                if(!empty($del))
                {
                    // 仓库商品、仓库规格
                    $where = [
                        ['warehouse_id', 'in', $del],
                        ['goods_id', '=', $goods_id],
                    ];
                    Db::name('WarehouseGoods')->where($where)->delete();
                    Db::name('WarehouseGoodsSpec')->where($where)->delete();
                }

                // 同步商品库存
                $ret = WarehouseGoodsService::GoodsSpecInventorySync($goods_id);
                if($ret['code'] != 0)
                {
                    throw new \Exception($ret['msg']);
                }

                // 完成
                Db::commit();
            } catch(\Exception $e) {
                Db::rollback();
                return DataReturn($e->getMessage(), -1);
            }
        }
        return DataReturn(MyLang('operate_success'), 0);
    }

    /**
     * 仓库商品库存预警
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-06-28
     * @desc    description
     * @param   [array]           $config [插件配置]
     */
    public static function WarehouseGoodsInventoryEarlyWarning($config)
    {
        // 预警值、默认5
        $early_warning_number = empty($config['admin_warehouse_inventory_early_warning_number']) ? 5 : intval($config['admin_warehouse_inventory_early_warning_number']);
        // 库存数据读取最大数量、默认8
        $limit_number = empty($config['admin_warehouse_max_number']) ? 8 : intval($config['admin_warehouse_max_number']);
        // 获取预警值内的仓库商品总数
        $where = [
            ['w.is_enable', '=', 1],
            ['w.is_delete_time', '=', 0],
            ['wgs.inventory', '<=', $early_warning_number],
            ['wg.is_enable', '=', 1],
        ];
        $data = Db::name('Warehouse')->alias('w')->join('warehouse_goods wg', 'w.id=wg.warehouse_id')->join('warehouse_goods_spec wgs', 'wg.id=wgs.warehouse_goods_id')->where($where)->field('w.name as warehouse_name,wgs.warehouse_id,wgs.goods_id')->order('wgs.inventory asc')->select()->toArray();
        $result = [];
        if(!empty($data))
        {
            $group = [];
            foreach($data as $v)
            {
                if(array_key_exists($v['warehouse_id'], $group))
                {
                    // 循环，存在则加1
                    $group[$v['warehouse_id']]['goods_count'] += 1;
                } else {
                    // 时间
                    $v['time'] = date('Y-m-d H:i');

                    // 总库存
                    $v['inventory_total'] = Db::name('WarehouseGoods')->where(['is_enable'=>1, 'warehouse_id'=>$v['warehouse_id']])->sum('inventory');

                    // 默认1个商品
                    $v['goods_count'] = 1;

                    // 仓库地址
                    $v['warehouse_url'] = MyUrl('admin/warehouse/detail', ['id'=>$v['warehouse_id']]);

                    // 商品查看
                    $v['warehouse_goods_url'] = MyUrl('admin/warehousegoods/index', ['warehouse_id'=>$v['warehouse_id'], 'spec_inventory_max'=>$early_warning_number]);

                    // 加入分组
                    $group[$v['warehouse_id']] = $v;
                }
            }
            $group = array_values($group);
            $result = array_splice($group, 0, $limit_number);
        }
        return $result;
    }

    /**
     * 库存修改处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-08-31
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GoodsInventoryEditHandle($params = [])
    {
        // 参数校验
        $p = [
            [
                'checked_type'      => 'in',
                'key_name'          => 'export_key_type',
                'checked_data'      => array_keys(BaseService::$goods_export_key_type),
                'error_msg'         => '规格主键类型有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'warehouse_id',
                'error_msg'         => '请选择仓库',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 获取导入数据
        $ret = (new \base\Excel())->Import();
        if($ret['code'] != 0)
        {
            return $ret;
        }
        if(empty($ret['data']) || empty($ret['data']['title']) || empty($ret['data']['data']))
        {
            return DataReturn('导入数据为空', -1);
        }

        // 基础字段类型
        $base_key = in_array($ret['data']['title'][0], ['编码', 'coding', 'Coding', 'code', 'Code']) ? 'coding' : 'barcode';

        // 商品规格基础
        $base_where = array_filter(array_map(function($item)
        {
            return isset($item[0]) ? trim($item[0]) : '';
        }, $ret['data']['data']));
        $base_data = Db::name('GoodsSpecBase')->where([$base_key=>$base_where])->column('goods_id', $base_key);
        if(empty($base_data))
        {
            return DataReturn('没有相关商品基础信息', -1);
        }

        // 仓库商品
        $warehouse_id = intval($params['warehouse_id']);
        $warehouse_goods = Db::name('WarehouseGoods')->where(['goods_id'=>array_values($base_data), 'warehouse_id'=>$warehouse_id])->column('*', 'goods_id');

        // 捕获异常
        Db::startTrans();
        try {
            // 循环处理库存
            $goods_ids = [];
            foreach($ret['data']['data'] as $k=>$v)
            {
                if(!empty($v) && !empty($v[0]) && array_key_exists(trim($v[0]), $base_data) && count($v) == 3)
                {
                    // 基础信息
                    $key = '第'.($k+1).'行';
                    $goods_id = $base_data[trim($v[0])];
                    $inventory = empty($v[2]) ? 0 : intval($v[2]);
                    if(empty($v[1]))
                    {
                        $spec = 'default';
                    } else {
                        $temp_spec = explode(';', trim($v[1]));
                        $spec = array_filter(array_map(function($item)
                        {
                            $temp = explode(':', $item);
                            if(!empty($temp) && count($temp) == 2)
                            {
                                return ['type'=>$temp[0], 'value'=>$temp[1]];
                            }
                        }, $temp_spec));
                        if(count($temp_spec) != count($spec))
                        {
                            throw new \Exception($key.'规格格式有误');
                        }
                    }
                    $md5_key = md5(is_array($spec) ? implode('', array_column($spec, 'value')) : $spec);

                    // 仓库商品
                    $is_query_spec_exist = false;
                    if(array_key_exists($goods_id, $warehouse_goods))
                    {
                        $temp_warehouse_goods = $warehouse_goods[$goods_id];
                        $is_query_spec_exist = true;
                    } else {
                        $temp_warehouse_goods = [
                            'warehouse_id'  => $warehouse_id,
                            'goods_id'      => $goods_id,
                            'is_enable'     => 1,
                            'inventory'     => $inventory,
                            'add_time'      => time(),
                        ];
                        $temp_warehouse_goods['id'] = Db::name('WarehouseGoods')->insertGetId($temp_warehouse_goods);
                        if($temp_warehouse_goods['id'] <= 0)
                        {
                            throw new \Exception($key.'仓库商品添加失败');
                        }
                    }

                    // 仓库商品规格是否存在
                    $temp_warehouse_goods_spec = null;
                    if($is_query_spec_exist)
                    {
                        $temp_warehouse_goods_spec = Db::name('WarehouseGoodsSpec')->where(['goods_id'=>$goods_id, 'warehouse_id'=>$warehouse_id, 'warehouse_goods_id'=>$temp_warehouse_goods['id'], 'md5_key'=>$md5_key])->find();
                    }
                    if(empty($temp_warehouse_goods_spec))
                    {
                        $temp_warehouse_goods_spec = [
                            'goods_id'            => $goods_id,
                            'warehouse_id'        => $warehouse_id,
                            'warehouse_goods_id'  => $temp_warehouse_goods['id'],
                            'md5_key'             => $md5_key,
                            'spec'                => is_array($spec) ? json_encode($spec, JSON_UNESCAPED_UNICODE) : $spec,
                            'inventory'           => $inventory,
                            'add_time'            => time(),
                        ];
                        $temp_warehouse_goods_spec['id'] = Db::name('WarehouseGoodsSpec')->insertGetId($temp_warehouse_goods_spec);
                        if($temp_warehouse_goods_spec['id'] <= 0)
                        {
                            throw new \Exception($key.'仓库商品规格添加失败');
                        }
                    } else {
                        if(Db::name('WarehouseGoodsSpec')->where(['id'=>$temp_warehouse_goods_spec['id']])->update(['inventory'=>$inventory]) === false)
                        {
                            throw new \Exception($key.'仓库商品规格更新失败');
                        }
                    }
                    $goods_ids[] = $goods_id;
                }
            }

            // 库存同步处理
            if(!empty($goods_ids))
            {
                foreach(array_unique($goods_ids) as $gid)
                {
                    // 同步商品库存
                    $ret = WarehouseGoodsService::GoodsSpecInventorySync($gid);
                    if($ret['code'] != 0)
                    {
                        throw new \Exception($ret['msg']);
                    }
                }
            }

            // 完成
            Db::commit();
            return DataReturn(MyLang('operate_success'), 0);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }
}
?>