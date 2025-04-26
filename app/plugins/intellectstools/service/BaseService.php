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
use app\service\PluginsService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\service\WarehouseService;
use app\plugins\shop\service\ShopService;
use app\plugins\shop\service\BaseService as ShopBaseService;
use app\plugins\chat\service\BaseService as ChatBaseService;

/**
 * 智能工具箱 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [
        'order_aftersale_server_weixin',
        'order_aftersale_server_line',
    ];

    // 价格类型
    public static $price_type_list = [
        0 => ['value' => 0, 'field' => 'price', 'name' => '销售价'],
        1 => ['value' => 1, 'field' => 'original_price', 'name' => '原价'],
    ];

    // 调整规则
    public static $modify_price_rules_list = [
        0 => ['value' => 0, 'type' => '+', 'name' => '增加'],
        1 => ['value' => 1, 'type' => '-', 'name' => '减少'],
        2 => ['value' => 2, 'type' => '*', 'name' => '乘以'],
        3 => ['value' => 3, 'type' => '/', 'name' => '除以'],
        4 => ['value' => 4, 'type' => 'fixed', 'name' => '固定'],
    ];

    // 头条结算 分账状态（0待分账, 1已分账, 2已失败）
    public static $toutiaosettlement_status_list = [
        0 => ['value' => 0, 'name' => '未分账'],
        1 => ['value' => 1, 'name' => '已分账'],
        2 => ['value' => 2, 'name' => '已失败'],
    ];

    // 模板导出主键类型
    public static $goods_export_key_type = [
        'coding' => [
            'title'     => '规格编码',
            'field'     => 'coding',
            'type'      => 'string',
        ],
        'barcode' => [
            'title'     => '规格条形码',
            'field'     => 'barcode',
            'type'      => 'string',
        ],
    ];

    // 商品导出字段定义
    public static $goods_export_fields = [
        'title' => [
            'title'     => '标题名称',
            'field'     => 'title',
            'type'      => 'base',
            'data_type' => 'string',
        ],
        'simple_desc' => [
            'title'     => '商品简述',
            'field'     => 'simple_desc',
            'type'      => 'base',
            'data_type' => 'string',
        ],
        'model' => [
            'title'     => '商品型号',
            'field'     => 'model',
            'type'      => 'base',
            'data_type' => 'string',
        ],
        'is_category' => [
            'title'     => '商品分类',
            'field'     => 'is_category',
            'type'      => 'category',
            'data_type' => 'int',
        ],
        'brand_id' => [
            'title'     => '品牌',
            'field'     => 'brand_id',
            'type'      => 'base',
            'data_type' => 'int',
            'method'    => 'GoodsBrandHandle',
        ],
        'place_origin' => [
            'title'     => '生产地',
            'field'     => 'place_origin',
            'type'      => 'base',
            'data_type' => 'int',
            'method'    => 'GoodsPlaceOriginHandle',
        ],
        'inventory_unit' => [
            'title'     => '库存单位',
            'field'     => 'inventory_unit',
            'type'      => 'base',
            'data_type' => 'string',
        ],
        'buy_min_number' => [
            'title'     => '最低起购数量',
            'field'     => 'buy_min_number',
            'type'      => 'base',
            'data_type' => 'int',
        ],
        'buy_max_number' => [
            'title'     => '单次最大购买数量',
            'field'     => 'buy_max_number',
            'type'      => 'base',
            'data_type' => 'int',
        ],
        'site_type' => [
            'title'     => '商品类型',
            'field'     => 'site_type',
            'type'      => 'base',
            'data_type' => 'int',
            'method'    => 'GoodsSiteTypeHandle',
        ],
        'is_deduction_inventory' => [
            'title'     => '是否扣减库存',
            'field'     => 'is_deduction_inventory',
            'type'      => 'base',
            'data_type' => 'int',
            'method'    => 'IsTextHandle',
        ],
        'is_shelves' => [
            'title'     => '是否上下架',
            'field'     => 'is_shelves',
            'type'      => 'base',
            'data_type' => 'int',
            'method'    => 'IsTextHandle',
        ],
        'price' => [
            'title'     => '商品销售价',
            'field'     => 'price',
            'type'      => 'spec',
        ],
        'original_price' => [
            'title'     => '商品原价',
            'field'     => 'original_price',
            'type'      => 'spec',
            'data_type' => 'float',
        ],
        'weight' => [
            'title'     => '商品重量(kg)',
            'field'     => 'weight',
            'type'      => 'spec',
            'data_type' => 'float',
        ],
        'inventory' => [
            'title'     => '商品库存',
            'field'     => 'inventory',
            'type'      => 'inventory',
            'data_type' => 'int',
        ],
        'plugins_intellectstools_buy_btn_link_name' => [
            'title'     => '链接按钮名称',
            'field'     => 'plugins_intellectstools_buy_btn_link_name',
            'type'      => 'base',
            'data_type' => 'string',
        ],
        'plugins_intellectstools_buy_btn_link_url' => [
            'title'     => '链接按钮链接',
            'field'     => 'plugins_intellectstools_buy_btn_link_url',
            'type'      => 'base',
            'data_type' => 'string',
        ],
        'seo_title' => [
            'title'     => 'SEO标题',
            'field'     => 'seo_title',
            'type'      => 'base',
            'data_type' => 'string',
        ],
        'seo_keywords' => [
            'title'     => 'SEO关键字',
            'field'     => 'seo_keywords',
            'type'      => 'base',
            'data_type' => 'string',
        ],
        'seo_desc' => [
            'title'     => 'SEO描述',
            'field'     => 'seo_desc',
            'type'      => 'base',
            'data_type' => 'string',
        ],
        'parameters' => [
            'title'     => '商品参数',
            'field'     => 'parameters',
            'type'      => 'parameters',
        ],
    ];

    // 数据key=>val分割符
    public static $data_colon_join = '{cn}';

    // 数据段分割符
    public static $data_semicolon_join = '{sn}';

    /**
     * 基础配置信息保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseConfigSave($params = [])
    {
        return PluginsService::PluginsDataSave(['plugins'=>'intellectstools', 'data'=>$params]);
    }
    
    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [boolean]          $is_cache [是否缓存中读取]
     */
    public static function BaseConfig($is_cache = true)
    {
        $ret = PluginsService::PluginsData('intellectstools', self::$base_config_attachment_field, $is_cache);

        // 数据为空则赋值空数组
        if(empty($ret['data']))
        {
            $ret['data'] = [];
        }

        // 购物车页面底部说明
        $ret['data']['cart_view_bottom_desc'] = empty($ret['data']['cart_view_bottom_desc']) ? [] : explode("\n", $ret['data']['cart_view_bottom_desc']);

        // 订单确认页面商品底部说明
        $ret['data']['buy_view_goods_bottom_desc'] = empty($ret['data']['buy_view_goods_bottom_desc']) ? [] : explode("\n", $ret['data']['buy_view_goods_bottom_desc']);

        // 商品详情页-详情内容顶部提示信息
        $ret['data']['goods_detail_content_top_tips_msg'] = empty($ret['data']['goods_detail_content_top_tips_msg']) ? [] : explode("\n", $ret['data']['goods_detail_content_top_tips_msg']);

        // 订单保留的状态
        $ret['data']['order_data_keep_only_order_status'] = empty($ret['data']['order_data_keep_only_order_status']) ? [] : explode(',', $ret['data']['order_data_keep_only_order_status']);

        // 留言快速选择数据
        if(!empty($ret['data']['buy_user_note_fast_choice_data']))
        {
            $ret['data']['buy_user_note_fast_choice_data'] = explode(',', $ret['data']['buy_user_note_fast_choice_data']);
        }

        // 指定商品数据查询
        $ret['data']['home_banner_right_goods_data'] = empty($ret['data']['home_banner_right_goods_data']) ? [] : $ret['data']['home_banner_right_goods_data'];
        $ret['data']['appoint_home_banner_right_goods_list'] = [];
        // 查询商品进行组装
        if(!empty($ret['data']['home_banner_right_goods_data']))
        {
            static $plugins_intellectstools_config_goods_static_data = null;
            if($plugins_intellectstools_config_goods_static_data === null)
            {
                $goods_ids = array_column($ret['data']['home_banner_right_goods_data'], 'id');
                $plugins_intellectstools_config_goods_static_data = Db::name('Goods')->where(['id'=>$goods_ids])->field('id,title,images')->select()->toArray();
                if(!empty($plugins_intellectstools_config_goods_static_data))
                {
                    foreach($plugins_intellectstools_config_goods_static_data as $g)
                    {
                        $g['goods_url'] = MyUrl('index/goods/index', ['id'=>$g['id']]);
                        if(in_array($g['id'], $goods_ids))
                        {
                            $ret['data']['appoint_home_banner_right_goods_list'][$g['id']] = $g;
                        }
                    }
                }
            }
        }

        return $ret;
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
                'name'      => '商品数据美化',
                'control'   => 'goods',
                'action'    => 'beautifyinfo',
            ],
            [
                'name'      => '批量评价模板',
                'control'   => 'comments',
                'action'    => 'index',
            ],
            [
                'name'      => '商品批量调价',
                'control'   => 'goodsallprice',
                'action'    => 'index',
            ],
            [
                'name'      => '商品库存修改',
                'control'   => 'goodsinventory',
                'action'    => 'index',
            ],
            [
                'name'      => '商品批量修改',
                'control'   => 'goodsalledit',
                'action'    => 'index',
            ],
            [
                'name'      => '头条支付分账',
                'control'   => 'toutiaosettlement',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 商品条件
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-08
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GoodsWhere($params = [])
    {
        // 字段名称
        $field = empty($params['field']) ? 'id' : $params['field'];

        // 商品id
        $data = [];
        if(!empty($params['goods_ids']))
        {
            $data = is_array($params['goods_ids']) ? $params['goods_ids'] : explode(',', $params['goods_ids']);
        }

        // 商品分类
        if(!empty($params['category_ids']))
        {
            // 获取所有子级分类id
            $cids = GoodsCategoryService::GoodsCategoryItemsIds($params['category_ids']);
            if(!empty($cids))
            {
                $gids = Db::name('GoodsCategoryJoin')->where(['category_id'=>$cids])->column('goods_id');
                if(!empty($gids))
                {
                    $data = array_merge($data, $gids);
                }
            }
        }

        // 品牌
        if(!empty($params['brand_ids']))
        {
            $gids = Db::name('Goods')->where(['brand_id'=>is_array($params['brand_ids']) ? $params['brand_ids'] : explode(',', $params['brand_ids'])])->column('id');
            if(!empty($gids))
            {
                $data = array_merge($data, $gids);
            }
        }

        // 去重商品id并返回
        $goods_ids = array_unique($data);
        $where_value = empty($goods_ids) ? [$field, '>', 0] : [$field, 'in', $goods_ids];
        return [
            'goods_ids' => $goods_ids,
            'where'     => [
                $where_value
            ],
        ];
    }

    /**
     * 订单售后页面客服信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-01-13
     * @desc    description
     * @param   [array]          $order  [订单信息]
     * @param   [array]          $config [插件配置]
     */
    public static function UserOrderAftersaleDServiceData($order, $config)
    {
        // 在线客服
        $chat = [];
        if(isset($config['is_order_aftersale_service_chat']) && $config['is_order_aftersale_service_chat'] == 1)
        {
            $chat = ChatBaseService::ChatUrl();
        }
        // QQ客服
        $service_qq = empty($config['order_aftersale_service_qq']) ? '' : $config['order_aftersale_service_qq'];
        // 电话客服
        $service_tel = empty($config['order_aftersale_service_tel']) ? '' : $config['order_aftersale_service_tel'];
        // 微信客服
        $service_weixin = empty($config['order_aftersale_service_weixin']) ? '' : $config['order_aftersale_service_weixin'];
        // line客服
        $service_line = empty($config['order_aftersale_service_line']) ? '' : $config['order_aftersale_service_line'];

        // 多商户
        if(isset($config['is_order_aftersale_service_show_shop']) && $config['is_order_aftersale_service_show_shop'] == 1 && !empty($order['shop_id']))
        {
            // 店铺信息
            $shop = ShopService::UserShopInfo($order['shop_id'], 'id', 'id,user_id,service_weixin_qrcode,service_line_qrcode,service_qq,service_tel');
            if(!empty($shop))
            {
                // 客服地址
                $chat = ShopBaseService::ChatUrl(null, $shop['user_id']);
                // qq
                $service_qq = empty($shop['service_qq']) ? '' : $shop['service_qq'];
                // tel
                $service_tel = empty($shop['service_tel']) ? '' : $shop['service_tel'];
                // 微信
                $service_weixin = empty($shop['service_weixin_qrcode']) ? '' : $shop['service_weixin_qrcode'];
                // line
                $service_line = empty($shop['service_line_qrcode']) ? '' : $shop['service_line_qrcode'];
            }
        }

        return [
            'service_msg'       => empty($config['order_aftersale_service_msg']) ? '' : $config['order_aftersale_service_msg'],
            'service_qq'        => $service_qq,
            'service_tel'       => $service_tel,
            'service_weixin'    => $service_weixin,
            'service_line'      => $service_line,
            'chat'              => $chat,
        ];
    }

    /**
     * 订单再次购买数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-31
     * @desc    description
     * @param   [array]          $goods     [订单商品数据]
     * @param   [string]         $key_field [商品id字段名称]
     */
    public static function OrderBuyAgainData($goods, $key_field = 'goods_id')
    {
        $result = [];
        if(!empty($goods) && is_array($goods))
        {
            $temp_goods = Db::name('Goods')->where(['id'=>array_column($goods, $key_field)])->column('id,inventory,is_exist_many_spec', 'id');
            if(!empty($temp_goods))
            {
                foreach($goods as $v)
                {
                    if(array_key_exists($v[$key_field], $temp_goods) && $temp_goods[$v[$key_field]]['inventory'] > 0)
                    {
                        $status = false;
                        $info = $temp_goods[$v[$key_field]];
                        if(empty($v['spec']))
                        {
                            // 没有规格、则商品也必须还是单规格
                            if($info['is_exist_many_spec'] == 0)
                            {
                                $status = true;
                            }
                        } else {
                            // 存在规格、则商品也必须还是多规格的、再校验规格是否存在
                            if($info['is_exist_many_spec'] == 1)
                            {
                                $ret = GoodsService::GoodsSpecDetail(['id'=>$info['id'], 'spec'=>$v['spec']]);
                                if($ret['code'] == 0 && !empty($ret['data']['spec_base']) && isset($ret['data']['spec_base']['inventory']) && $ret['data']['spec_base']['inventory'] > 0)
                                {
                                    $status = true;
                                    // 最低起购数量限制
                                    if(isset($ret['data']['spec_base']['buy_min_number']) && $ret['data']['spec_base']['buy_min_number'] > 0 && $ret['data']['spec_base']['buy_min_number'] > $v['buy_number'])
                                    {
                                        $v['buy_number'] = $ret['data']['spec_base']['buy_min_number'];
                                    }
                                    // 最大购买数量限制
                                    if(isset($ret['data']['spec_base']['buy_max_number']) && $ret['data']['spec_base']['buy_max_number'] > 0 && $ret['data']['spec_base']['buy_max_number'] < $v['buy_number'])
                                    {
                                        $v['buy_number'] = $ret['data']['spec_base']['buy_max_number'];
                                    }
                                }
                            }
                        }
                        if($status)
                        {
                            $result[] = [
                                'goods_id'  => $v[$key_field],
                                'stock'     => $v['buy_number'],
                                'spec'      => $v['spec'],
                            ];
                        }
                    }
                }
            }
        }
        return $result;
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
     * 商品数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-07-09
     * @desc    description
     * @param   [array]          $goods_ids [商品id]
     */
    public static function HomeBannerRightGoodsData($plugins_config)
    {
        $goods_list = [];
        $params = [
            'where'     => [
                ['id', 'in', array_column($plugins_config['appoint_home_banner_right_goods_list'], 'id')],
                ['is_shelves', '=', 1],
            ],
            'is_spec'   => 1,
        ];
        $ret = GoodsService::GoodsList($params);
        if(!empty($ret['data']))
        {
            $temp = array_column($ret['data'], null, 'id');
            foreach($plugins_config['home_banner_right_goods_data'] as $v)
            {
                if(array_key_exists($v['id'], $temp))
                {
                    $temp_goods = $temp[$v['id']];
                    if(empty($v['name']))
                    {
                        $temp_goods['title'] = mb_substr($temp_goods['title'], 0, 2, 'utf-8');
                    } else {
                        $temp_goods['title'] = $v['name'];
                    }
                    $goods_list[] = $temp_goods;
                }
            }
        }
        return $goods_list;
    }

    /**
     * 仓库列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-08-31
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function WarehouseList($params = [])
    {
        $where = [
            'is_delete_time'  => 0,
            'is_enable'       => 1,
        ];
        $ret = WarehouseService::WarehouseList(['field'=>'id,name,alias', 'where'=>$where]);
        return empty($ret['data']) ? [] : $ret['data'];
    }
}
?>