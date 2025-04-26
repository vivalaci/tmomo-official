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
namespace app\plugins\label\service;

use think\facade\Db;
use app\service\ResourcesService;
use app\plugins\label\service\BaseService;

/**
 * 标签 - 标签管理服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class LabelService
{
    // 关联标签数据定义
    public static $label_join = [
        'user'  => [
            'table' => 'PluginsLabelUser',
            'field' => 'user_id',
        ],
        'goods'  => [
            'table' => 'PluginsLabelGoods',
            'field' => 'goods_id',
        ],
    ];

    /**
     * 数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function LabelList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        // 获取数据
        $data = Db::name('PluginsLabel')->where($where)->field($field)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::DataHandle($data, $params));
    }

    /**
     * 数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-18
     * @desc    description
     * @param   [array]          $data      [数据]
     * @param   [array]          $params    [输入参数]
     */
    public static function DataHandle($data, $params = [])
    {
        if(!empty($data))
        {
            // 开始处理数据
            foreach($data as &$v)
            {
                // 是否显示
                if(array_key_exists('name', $v) && array_key_exists('is_show', $v) && $v['is_show'] != 1)
                {
                    $v['name'] .= '(不显示)';
                }

                // url
                if(array_key_exists('id', $v))
                {
                    $v['url'] = BaseService::LabelUrl($v['id']);
                }

                // 图标
                if(array_key_exists('icon', $v))
                {
                    $v['icon'] = ResourcesService::AttachmentPathViewHandle($v['icon']);
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
        return $data;
    }

    /**
     * 总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function LabelTotal($where = [])
    {
        return (int) Db::name('PluginsLabel')->where($where)->count();
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-02-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function LabelSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '1,30',
                'error_msg'         => '名称最多30个字符',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'is_show',
                'checked_data'      => [0,1],
                'is_checked'        => 2,
                'error_msg'         => MyLang('form_is_show_message'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'is_enable',
                'checked_data'      => [0,1],
                'is_checked'        => 2,
                'error_msg'         => MyLang('form_is_enable_message'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'seo_title',
                'checked_data'      => '100',
                'is_checked'        => 1,
                'error_msg'         => MyLang('form_seo_title_message'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'seo_keywords',
                'checked_data'      => '130',
                'is_checked'        => 1,
                'error_msg'         => MyLang('form_seo_keywords_message'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'seo_desc',
                'checked_data'      => '230',
                'is_checked'        => 1,
                'error_msg'         => MyLang('form_seo_desc_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 附件
        $data_fields = ['icon'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 基础数据
        $data = [
            'name'          => $params['name'],
            'text_color'    => isset($params['text_color']) ? $params['text_color'] : '',
            'bg_color'      => isset($params['bg_color']) ? $params['bg_color'] : '',
            'icon'          => $attachment['data']['icon'],
            'is_show'       => isset($params['is_show']) ? intval($params['is_show']) : 0,
            'is_enable'     => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
            'seo_title'     => empty($params['seo_title']) ? '' : $params['seo_title'],
            'seo_keywords'  => empty($params['seo_keywords']) ? '' : $params['seo_keywords'],
            'seo_desc'      => empty($params['seo_desc']) ? '' : $params['seo_desc'],
        ];

        // 捕获异常
        try {
            // 获取数据
            $info =  Db::name('PluginsLabel')->where(['name'=>$data['name']])->find();
            if(!empty($info))
            {
                // 名称相同
                if($info['name'] == $data['name'] && (empty($params['id']) || $params['id'] != $info['id']))
                {
                    return DataReturn('标签已存在['.$data['name'].']', -1);
                }
            }

            // 不存在添加、则更新
            if(empty($params['id']))
            {
                $data['add_time'] = time();
                $label_id = Db::name('PluginsLabel')->insertGetId($data);
                if($label_id <= 0)
                {
                    throw new \Exception(MyLang('insert_fail'));
                }
            } else {
                $data['upd_time'] = time();
                if(!Db::name('PluginsLabel')->where(['id'=>intval($params['id'])])->update($data))
                {
                    throw new \Exception(MyLang('update_fail'));
                }
            }

            return DataReturn(MyLang('operate_success'), 0);
        } catch(\Exception $e) {
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function LabelStatusUpdate($params = [])
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
        if(Db::name('PluginsLabel')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
           return DataReturn(MyLang('operate_success'));
        }
        return DataReturn(MyLang('operate_fail'), -100);
    }

    /**
     * 删除
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-12-07T00:24:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function LabelDelete($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'ids',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 开始事务
        Db::startTrans();

        // 捕获异常
        try {
            // 删除标签
            if(!Db::name('PluginsLabel')->where(['id'=>$params['ids']])->delete())
            {
                throw new \Exception('删除失败');
            }

            // 删除关联的用户
            $where = ['label_id'=>$params['ids']];
            if(Db::name('PluginsLabelUser')->where($where)->delete() === false)
            {
                throw new \Exception('关联用户删除失败');
            }

            // 删除关联的商品
            if(Db::name('PluginsLabelGoods')->where($where)->delete() === false)
            {
                throw new \Exception('关联商品删除失败');
            }

            Db::commit();
            return DataReturn(MyLang('delete_success'), 0);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 关联数据选中标签数据id
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-06
     * @desc    description
     * @param   [int|array]    $vid             [数据id]
     * @param   [string]       $type            [user 用户、 goods 商品]
     * @param   [string]       $where_field     [查询条件字段]
     * @param   [string]       $query_field     [需要查询的字段]
     */
    public static function JoinSelectLabelDataIds($vid, $type, $where_field = '', $query_field = 'label_id')
    {
        $ids = [];
        if(array_key_exists($type, self::$label_join))
        {
            if(empty($where_field))
            {
                $where_field = self::$label_join[$type]['field'];
            }
            if(empty($query_field))
            {
                $query_field = 'label_id';
            }
            $where = empty($vid) ? [] : [$where_field=>$vid];
            $ids = Db::name(self::$label_join[$type]['table'])->where($where)->column($query_field);
        }
        return empty($ids) ? [] : $ids;
    }

    /**
     * 关联标签数据保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function LabelJoinSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'vid',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'type',
                'checked_data'      => ['user', 'goods'],
                'error_msg'         => '数据类型值有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 类型是否定义
        if(!array_key_exists($params['type'], self::$label_join))
        {
            return DataReturn('标签关联数据未定义', -1);
        }
        $join = self::$label_join[$params['type']];

        // 原始标签数据
        $vid = intval($params['vid']);
        $label_ids_old = Db::name($join['table'])->where([$join['field']=>$vid])->column('label_id');

        // 新的标签数据
        $data_add = [];
        $label_ids_new = empty($params['label_ids']) ? [] : explode(',', $params['label_ids']);
        if(!empty($label_ids_new))
        {
            $time = time();
            foreach($label_ids_new as $v)
            {
                if(empty($label_ids_old) || !in_array($v, $label_ids_old))
                {
                    $data_add[] = [
                        'label_id'      => $v,
                        $join['field']  => $vid,
                        'add_time'      => $time,
                    ];
                }
            }
        }

        // 需要取消的数据
        $data_cancel = [];
        if(!empty($label_ids_old))
        {
            foreach($label_ids_old as $v)
            {
                if(empty($label_ids_new) || !in_array($v, $label_ids_new))
                {
                    $data_cancel[] = $v;
                }
            }
        }

        // 是否存在需要更新的数据
        if(empty($data_add) && empty($data_cancel))
        {
            return DataReturn(MyLang('operate_success'), 0);
        }

        // 启动事务
        Db::startTrans();

        // 捕获异常
        try {
            // 存在新增数据
            if(!empty($data_add))
            {
                // 添加关联数据
                if(Db::name($join['table'])->insertAll($data_add) != count($data_add))
                {
                    throw new \Exception('关联添加失败');
                }

                // 增加数量
                if(Db::name('PluginsLabel')->where(['id'=>array_column($data_add, 'label_id')])->inc('use_count', 1)->update() === false)
                {
                    throw new \Exception('标签数量增加失败');
                }
            }

            // 存在取消的数据
            if(!empty($data_cancel))
            {
                // 删除取消数据
                $where = [
                    ['label_id', 'in', $data_cancel],
                    [$join['field'], '=', $vid],
                ];
                if(!Db::name($join['table'])->where($where)->delete())
                {
                    throw new \Exception('关联取消失败');
                }

                // 扣减数量
                $where = [
                    ['id', 'in', $data_cancel],
                    ['use_count', '>', 0],
                ];
                if(Db::name('PluginsLabel')->where($where)->dec('use_count', 1)->update() === false)
                {
                    throw new \Exception('标签数量扣减失败');
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

    /**
     * 商品关联的标签信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-08-05
     * @desc    description
     * @param   [array]           $goods_ids [商品id]
     */
    public static function LabelGoodsData($goods_ids = [])
    {
        $result = [];
        if(!empty($goods_ids))
        {
            // 非数组则使用逗号转数组
            if(!is_array($goods_ids))
            {
                $goods_ids = explode(',', $goods_ids);
            }

            // 商品标签数据
            static $plugins_label_goods_data = [];
            $temp_goods_ids = [];
            foreach($goods_ids as $gid)
            {
                if(!array_key_exists($gid, $plugins_label_goods_data))
                {
                    $temp_goods_ids[] = $gid;
                }
            }
            if(!empty($temp_goods_ids))
            {
                // 获取标签关联信息
                $where = [
                    ['plv.goods_id', 'in', $temp_goods_ids],
                    ['pl.is_enable', '=', 1],
                    ['pl.is_show', '=', 1],
                ];
                $data = Db::name('PluginsLabel')->alias('pl')->join('plugins_label_goods plv', 'pl.id=plv.label_id')->where($where)->field('pl.id,pl.name,pl.text_color,pl.bg_color,pl.icon,plv.goods_id')->order('pl.id desc')->select()->toArray();
                if(!empty($data))
                {
                    foreach($data as $v)
                    {
                        $v['url'] = BaseService::LabelUrl($v['id']);
                        $v['icon'] = ResourcesService::AttachmentPathViewHandle($v['icon']);
                        $plugins_label_goods_data[$v['goods_id']][] = $v;
                    }
                }
                // 不存在则记录空，避免重复查询
                foreach($temp_goods_ids as $gid)
                {
                    if(!array_key_exists($gid, $plugins_label_goods_data))
                    {
                        $plugins_label_goods_data[$gid] = [];
                    }
                }
            }

            // 返回组合数据
            foreach($goods_ids as $gid)
            {
                if(array_key_exists($gid, $plugins_label_goods_data))
                {
                    $result[$gid] = $plugins_label_goods_data[$gid];
                }
            }
        }
        return $result;
    }

    /**
     * 获取标签所有关联的商品数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function LabelListGoodsData($params = [])
    {
        $data_params = [
            'where' => [
                ['is_enable', '=', 1],
                ['is_show', '=', 1],
                ['use_count', '>', 0],
            ],
            'field' => 'id,name,text_color,bg_color,icon,use_count',
            'm' => 0,
            'n' => 0,
        ];
        $ret = self::LabelList($data_params);
        if(!empty($ret['data']))
        {
            // 获取所有活动关联的商品
            $goods = Db::name('PluginsLabelGoods')->where(['label_id'=>array_column($ret['data'], 'id')])->field('label_id,goods_id')->select()->toArray();
            $goods_group = [];
            if(!empty($goods))
            {
                foreach($goods as $g)
                {
                    $goods_group[$g['label_id']][] = $g['goods_id'];
                }
            }
            foreach($ret['data'] as &$v)
            {
                $v['goods_ids'] = array_key_exists($v['id'], $goods_group) ? $goods_group[$v['id']] : [];
            }
        }
        return $ret['data'];
    }

    /**
     * 获取商品关联的标签数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-02
     * @desc    description
     * @param   [int]           $goods_id [商品id]
     */
    public static function GoodsLabelData($goods_id)
    {
        $where = [
            ['pl.is_enable', '=', 1],
            ['pl.is_show', '=', 1],
            ['pl.use_count', '>', 0],
            ['plg.goods_id', '=', $goods_id],
        ];
        return self::DataHandle(Db::name('PluginsLabelGoods')->alias('plg')->join('plugins_label pl', 'pl.id=plg.label_id')->where($where)->field('pl.id,pl.name,pl.text_color,pl.bg_color,pl.icon,plg.goods_id')->select()->toArray());
    }
}
?>