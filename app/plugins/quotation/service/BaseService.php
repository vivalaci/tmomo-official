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
namespace app\plugins\quotation\service;

use think\facade\Db;
use app\service\GoodsService;
use app\service\ResourcesService;

/**
 * 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2019-09-24
 * @desc    description
 */
class BaseService
{
    // 商品导出字段定义
    public static $goods_export_title = [
        'id' => [
            'title'     => '编号',
            'field'     => 'id',
            'type'      => 'int',
        ],
        'images' => [
            'title'     => '图片',
            'field'     => 'images',
            'type'      => 'images',
        ],
        'title' => [
            'title'     => '名称',
            'field'     => 'title',
            'type'      => 'string',
        ],
        'simple_desc' => [
            'title'     => '商品简述',
            'field'     => 'simple_desc',
            'type'      => 'string',
        ],
        'is_category' => [
            'title'     => '所属分类',
            'field'     => 'is_category',
            'type'      => 'string',
        ],
        'original_price' => [
            'title'     => '原价',
            'field'     => 'original_price',
            'type'      => 'string',
        ],
        'price' => [
            'title'     => '售价',
            'field'     => 'price',
            'type'      => 'string',
        ],
        'inventory' => [
            'title'     => '库存',
            'field'     => 'inventory',
            'type'      => 'string',
        ],
        'inventory_unit' => [
            'title'     => '单位',
            'field'     => 'inventory_unit',
            'type'      => 'string',
        ],
        'model' => [
            'title'     => '型号',
            'field'     => 'model',
            'type'      => 'string',
        ],
        'brand_id' => [
            'title'     => '品牌',
            'field'     => 'brand_id',
            'type'      => 'string',
        ],
        'place_origin' => [
            'title'     => '生产地',
            'field'     => 'place_origin',
            'type'      => 'string',
        ],
        'add_time' => [
            'title'     => '创建时间',
            'field'     => 'add_time',
            'type'      => 'string',
        ],
        'spec' => [
            'title'     => '规格',
            'field'     => 'spec',
            'type'      => 'string',
        ],
        'vip' => [
            'title'     => '规格+会员等级',
            'field'     => 'vip',
            'type'      => 'string',
        ],
    ];

    // 规格可选字段
    public static $goods_export_spec_title = [
        'images' => [
            'title'     => '规格图片',
            'field'     => 'images',
            'type'      => 'images',
        ],
        'original_price' => [
            'title'     => '原价',
            'field'     => 'original_price',
            'type'      => 'string',
        ],
        'inventory' => [
            'title'     => '库存',
            'field'     => 'inventory',
            'type'      => 'string',
        ],
        'weight' => [
            'title'     => '重量',
            'field'     => 'weight',
            'type'      => 'string',
        ],
        'coding' => [
            'title'     => '编码',
            'field'     => 'coding',
            'type'      => 'string',
        ],
        'barcode' => [
            'title'     => '条形码',
            'field'     => 'barcode',
            'type'      => 'string',
        ],
    ];

    // 导出字段映射
    public static $goods_export_title_as = [
        'brand_id'          => 'brand_name',
        'place_origin'      => 'place_origin_name',
        'is_category'       => 'category_text',
    ];

    /**
     * 商品导出
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-30
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ExcelExport($params = [])
    {
        $title = [];
        foreach($params['title'] as $k=>$v)
        {
            $title[$k] = [
                'name'      => $v,
                'type'      => in_array($k, ['images', 'vip_images']) ? 'images' : 'string',
                'height'    => 60,
                'width'     => 80,
            ];
        }

        // 导出类型
        $export_type = MyC('common_excel_export_type', 0, true);

        // 数据处理
        $value = [];
        foreach($params['value'] as $k=>$v)
        {
            $temp = [];
            $data = [];
            foreach($v as $ks=>$vs)
            {
                if(in_array($ks, ['spec', 'vip']))
                {
                    if(!empty($vs['value']))
                    {
                        foreach($vs['value'] as $spec)
                        {
                            $temp_spec = $temp;
                            foreach($spec as $sp_k=>$sp_v)
                            {
                                if($sp_k == 'spec')
                                {
                                    $temp_spec[$ks] = $sp_v;
                                } else {
                                    $key = $ks.'_'.str_replace('price_vip_', '', $sp_k);
                                    if($key == 'vip_images' && $export_type == 1)
                                    {
                                        $temp_spec[$key] = self::ImagesDownload($sp_v);

                                    } else {
                                        $temp_spec[$key] = $sp_v;
                                    }
                                }
                            }
                            $data[] = $temp_spec;
                        }
                    }
                    $value = array_merge($value, $data);
                } else {
                    if($ks == 'images' && $export_type == 1)
                    {
                        $temp[$ks] = self::ImagesDownload($vs['value']);

                    } else {
                        $temp[$ks] = $vs['value'];
                    }
                }
            }
            if(!array_key_exists('spec', $v) && !array_key_exists('vip', $v))
            {
                $value[] = $temp;
            }
        }

        // Excel驱动导出数据
        $excel = new \base\Excel(array('filename'=>'goods-quotation', 'title'=>$title, 'data'=>$value, 'msg'=>'没有相关数据'));
        return $excel->Export();
    }

    /**
     * 图片下载
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-01-31
     * @desc    description
     * @param   [string]          $value [图片地址]
     */
    public static function ImagesDownload($value)
    {
        if(!empty($value))
        {
            $path = ResourcesService::AttachmentPathHandle($value);
            if(!file_exists(ROOT.'public'.$path) && substr($value, 0, 4) == 'http')
            {
                $images_obj = \base\Images::Instance(['is_new_name'=>false]);
                $temp_path = ROOT.'runtime'.DS.'data'.DS.'plugins_quotation'.DS;
                $filename = $images_obj->DownloadImageSave($value, $temp_path);
                if(empty($filename))
                {
                    return DataReturn('商品主图远程下载失败', -11);
                }
                $value = $temp_path.$filename;
            } else {
                $value = ROOT.'public'.$path;
            }
        }
        return $value;
    }

    /**
     * 导出商品获取
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-25
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ExportGoodsList($params = [])
    {
        // 获取商品列表
        $ret = self::GoodsList($params);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 商品规格和vip等级信息
        $goods = self::GoodsSpecVipLevel($params, $ret['data']);

        // 会员等级默认数据
        $level_data = self::MembershiplevelVipAllData();

        // 数据组装
        $result_title = [];
        $result_value = [];
        foreach($goods as $k=>$v)
        {
            $data = [];
            $spec_value = [];
            $spec_key = null;
            foreach($params['export_data'] as $ks=>$vs)
            {
                if(in_array($ks, ['spec', 'vip']))
                {
                    if(!empty($v['spec']))
                    {
                        $temp_spec = [];
                        foreach($v['spec'] as $spec_v)
                        {
                            $temp_spec['spec'] = implode(',', $spec_v['spec']);
                            if(array_key_exists('original_price', $params['spec_data']))
                            {
                                $temp_spec['original_price'] = $spec_v['value']['original_price'];
                            }
                            $temp_spec['price'] = $spec_v['value']['price'];
                            if(!empty($params['vip_data']) && array_key_exists('vip', $params['export_data']))
                            {
                                foreach($params['vip_data'] as $vip_k=>$vip_v)
                                {
                                    $temp_spec['price_vip_'.$vip_k] = (isset($spec_v['value']['vip_data'][$vip_k]) && $spec_v['value']['vip_data'][$vip_k] !== '') ? $spec_v['value']['vip_data'][$vip_k] : ((!empty($level_data) && !empty($level_data[$vip_k]) && isset($level_data[$vip_k]['discount_rate']) && $level_data[$vip_k]['discount_rate'] > 0) ? PriceNumberFormat($temp_spec['price']*$level_data[$vip_k]['discount_rate']) : '');
                                }
                            }
                            foreach($params['spec_data'] as $sd_k=>$sd_v)
                            {
                                if(isset($spec_v['value'][$sd_k]))
                                {
                                    $temp_spec[$sd_k] = $spec_v['value'][$sd_k];
                                }
                            }
                            $spec_value[] = $temp_spec;
                        }
                    }
                    $spec_key = $ks;
                } else {
                    // 数据值
                    $key = array_key_exists($ks, self::$goods_export_title_as) ? self::$goods_export_title_as[$ks] : $ks;
                    $data[$key] = ['value'=> $v[$key], 'type'=>self::$goods_export_title[$ks]['type'], 'field'=>$key];

                    // 标题
                    if($k == 0)
                    {
                        $result_title[$key] = $vs;
                    }
                }
            }

            // 规格数据放在最后
            if($spec_key !== null)
            {
                $data[$spec_key] = ['value'=>$spec_value, 'type'=>'array', 'field'=>$spec_key];
            }
            $result_value[] = $data;
        }

        // 标题处理
        if(array_key_exists('spec', $params['export_data']) || array_key_exists('vip', $params['export_data']))
        {
            $key = array_key_exists('spec', $params['export_data']) ? 'spec' : 'vip';
            // 规格名称放在前面
            $result_title[$key] = $params['export_data'][$key];
            if(!empty($params['spec_data']) && array_key_exists('original_price', $params['spec_data']))
            {
                $result_title[$key.'_original_price'] = $params['spec_data']['original_price'];
            }
            $result_title[$key.'_price'] = '售价';
            if(!empty($params['vip_data']) && array_key_exists('vip', $params['export_data']))
            {
                foreach($params['vip_data'] as $k=>$v)
                {
                    $result_title[$key.'_'.$k] = $v;
                }
            }
            if(!empty($params['spec_data']))
            {
                foreach($params['spec_data'] as $k=>$v)
                {
                    if($k != 'original_price')
                    {
                        $result_title[$key.'_'.$k] = $v;
                    }
                }
            }
        }

        return DataReturn(MyLang('get_success'), 0, ['title'=>$result_title, 'value'=>$result_value]);
    }

    /**
     * 获取商品规格以及会员等级信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     * @param   [array]           $goods  [商品数据]
     */
    private static function GoodsSpecVipLevel($params = [], $goods = [])
    {
        // 是否需要vip会员等级数据
        $is_vip = array_key_exists('vip', $params['export_data']) ? 1 : 0;

        // 是否需要读取商品规格
        $is_spec = ($is_vip == 1 || array_key_exists('spec', $params['export_data'])) ? 1 : 0;

        // 读取会员等级数据
        if($is_vip == 1)
        {
            $vip_level_data = self::MembershiplevelVipData($params);
        }

        // 获取规格数据
        if($is_spec == 1)
        {
            // 处理商品数据
            foreach($goods as &$v)
            {
                $spec = GoodsService::GoodsEditSpecifications($v['id']);
                if(!empty($spec['value']))
                {
                    $spec_images = $v['images'];
                    $spec_data = [];
                    $v['spec'] = $spec['value'];
                    foreach($spec['value'] as $vs)
                    {
                        $temp_spec = [];
                        $temp_value = [];
                        foreach($vs as $kss=>$vss)
                        {
                            if($vss['data_type'] == 'spec')
                            {
                                $temp_spec[] = $vss['data']['value'];
                                $temp_spec_images = empty($spec['type'][$kss]) ? [] : array_column($spec['type'][$kss]['value'], 'images', 'name');
                                if(!empty($temp_spec_images) && !empty($temp_spec_images[$vss['data']['value']]))
                                {
                                    $spec_images = $temp_spec_images[$vss['data']['value']];
                                }
                            } else {
                                $vip_all = [];
                                if($is_vip == 1 && !empty($vss['data']['extends']))
                                {
                                    $extends = json_decode($vss['data']['extends'], true);
                                    if(!empty($extends))
                                    {
                                        foreach($extends as $ex_k=>$ex_v)
                                        {
                                            if(substr($ex_k, 0, 33) == 'plugins_membershiplevelvip_price_')
                                            {
                                                $key = substr($ex_k, 33, strlen($ex_k)-33);
                                                if(array_key_exists($key, $vip_level_data) && array_key_exists($key, $params['vip_data']))
                                                {
                                                    $vip_all[$key] = (empty($ex_v) || $ex_v <= 0) ? '' : PriceNumberFormat($ex_v);
                                                }
                                            }
                                        }
                                    }
                                }
                                unset($vss['data']['extends']);
                                $vss['data']['vip_data'] = $vip_all;
                                $temp_value = $vss['data'];
                            }
                        }
                        $temp_value['images'] = $spec_images;
                        $spec_data[] = [
                            'spec'  => $temp_spec,
                            'value' => $temp_value,
                        ];
                    }
                    $v['spec'] = $spec_data;
                }
            }
        }
        return $goods;
    }

    /**
     * 获取商品列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-25
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private static function GoodsList($params = [])
    {
        // 参数校验
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'goods_ids',
                'error_msg'         => '请选择商品',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'export_data',
                'error_msg'         => '请选择需要导出的字段',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 条件
        $where = [
            ['id', 'in', explode(',', $params['goods_ids'])],
        ];

        // 分类
        $is_category = array_key_exists('is_category', $params['export_data']) ? 1 : 0;

        // 排除不存在表中的字段
        $field_all = [];
        $search_field = ['spec', 'vip', 'is_category'];
        foreach($params['export_data'] as $k=>$v)
        {
            if(!in_array($k, $search_field))
            {
                $field_all[] = $k;
            }
        }

        // 商品id必要参数
        if(!in_array('id', $field_all))
        {
            $field_all[] = 'id';
        }
        // 图片必取
        if(!in_array('images', $field_all))
        {
            $field_all[] = 'images';
        }

        // 获取数据列表
        $ret = self::GoodsListData($where, implode(',', $field_all), $is_category);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 根据商品分类排序
        $order_by = 'sort asc';
        $category = Db::name('GoodsCategory')->where(['pid'=>0])->order($order_by)->column('id');
        $category_ids = [];
        foreach($category as $c)
        {
            $category_ids[] = $c;
            $categorys = Db::name('GoodsCategory')->where(['pid'=>$c])->order($order_by)->column('id');
            foreach($categorys as $cs)
            {
                $category_ids[] = $cs;
                $categorys = Db::name('GoodsCategory')->where(['pid'=>$cs])->order($order_by)->column('id');
                if(!empty($categorys))
                {
                    $category_ids = array_merge($category_ids, $categorys);
                }
            }
        }

        // 排序商品
        $result = [];
        $temp_ids = [];
        $temp_goods_category = [];
        foreach($category_ids as $cid)
        {
            foreach($ret['data'] as $v)
            {
                // 不存在商品分类则数据库读取
                if(!isset($temp_goods_category[$v['id']]))
                {
                    if(empty($v['category_ids']))
                    {
                        $temp_goods_category[$v['id']] = Db::name('GoodsCategoryJoin')->where(['goods_id'=>$v['id']])->order('id asc')->value('category_id');
                    }  else {
                        $temp_goods_category[$v['id']] = $v['category_ids'][0];
                    }
                }
                
                // 比较分类归类
                if($cid == $temp_goods_category[$v['id']])
                {
                    if(!in_array($v['id'], $temp_ids))
                    {
                        $temp_ids[] = $v['id'];
                        $result[] = $v;
                    }
                }
            }
        }
        
        return DataReturn(MyLang('get_success'), 0, $result);
    }

    /**
     * 获取会员等级数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-25
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function MembershiplevelVipData($params = [])
    {
        $ret = CallPluginsServiceMethod('membershiplevelvip', 'LevelService', 'DataList');
        $result = [];
        if($ret['code'] == 0 && !empty($ret['data']))
        {
            foreach($ret['data'] as $v)
            {
                $result[$v['id']] = $v['name'];
            }
        }
        return $result;
    }

    /**
     * 获取会员等级全部数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-25
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function MembershiplevelVipAllData($params = [])
    {
        $ret = CallPluginsServiceMethod('membershiplevelvip', 'LevelService', 'DataList');
        return empty($ret['data']) ? [] : array_column($ret['data'], null, 'id');
    }

    /**
     * 参数处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-25
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ParamsHandle($params = [])
    {
        $result = ['export_data'=>[], 'vip_data'=>[], 'spec_data'=>[]];
        foreach($params as $k=>$v)
        {
            // 获取导出字段
            $number = 13;
            $first = substr($k, 0, $number);
            if(in_array($first, ['export_field_', 'export_title_']))
            {
                if($first == 'export_field_')
                {
                    $result['export_data'][$v] = $params['export_title_'.substr($k, $number, strlen($k)-$number)];
                }
            } else {
                // 获取导出会员等级
                if(substr($k, 0, 10) == 'vip_level_')
                {
                    $number = 15;
                    if(substr($k, 0, $number) == 'vip_level_name_')
                    {
                        $key = substr($k, $number, strlen($k)-$number);
                        if(isset($params['vip_level_key_'.$key]))
                        {
                            $result['vip_data'][$params['vip_level_key_'.$key]] = $v;
                        }
                    }
                } else {
                    // 规格字段
                    $number = 11;
                    $first = substr($k, 0, $number);
                    if(in_array($first, ['spec_field_', 'spec_title_']))
                    {
                        if($first == 'spec_field_')
                        {
                            $result['spec_data'][$v] = $params['spec_title_'.substr($k, $number, strlen($k)-$number)];
                        }

                    // 默认字段
                    } else {
                        $result[$k] = $v;
                    }
                }
            }
        }
        if(array_key_exists('vip', $result['export_data']))
        {
            unset($result['export_data']['spec']);
        }
        return $result;
    }

    /**
     * 商品数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-04-17
     * @desc    description
     * @param   [array]           $where       [条件]
     * @param   [string]          $field       [读取字段]
     * @param   [boolean]         $is_category [是否读取分类]
     */
    public static function GoodsListData($where, $field = '*', $is_category = false)
    {
        // 获取数据列表
        $data_params = [
            'where'             => $where,
            'field'             => $field,
            'm'                 => 0,
            'n'                 => 0,
            'is_category'       => $is_category,
            'is_admin_access'   => 1,
        ];
        $ret = GoodsService::GoodsList($data_params);
        if($ret['code'] == 0 && empty($ret['data']))
        {
            return DataReturn('没有找到相关商品', -1);
        }
        return $ret;
    }
}
?>