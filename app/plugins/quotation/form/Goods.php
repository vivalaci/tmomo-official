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
namespace app\plugins\quotation\form;

use think\facade\Db;
use app\service\GoodsCategoryService;
use app\service\RegionService;
use app\service\BrandService;

/**
 * 商品动态表单
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-05-16
 * @desc    description
 */
class Goods
{
    // 基础条件
    public $condition_base = [
        ['is_delete_time', '=', 0],
    ];

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-05-16
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Run($params = [])
    {
        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'is_search'     => 1,
                'search_url'    => PluginsAdminUrl('quotation', 'goods', 'index'),
            ],
            // 表单配置
            'form' => [
                [
                    'view_type'         => 'checkbox',
                    'is_checked'        => 0,
                    'checked_text'      => MyLang('reverse_select_title'),
                    'not_checked_text'  => MyLang('select_all_title'),
                    'view_key'          => 'goods_ids',
                    'align'             => 'center',
                    'width'             => 80,
                ],
                [
                    'label'         => '商品ID',
                    'view_type'     => 'field',
                    'view_key'      => 'id',
                    'width'         => 120,
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'id',
                        'where_type'        => '=',
                    ],
                ],
                [
                    'label'         => '商品信息',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/quotation/view/admin/goods/module/info',
                    'grid_size'     => 'lg',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'title|simple_desc|seo_title|seo_keywords|seo_keywords',
                        'where_type'        => 'like',
                        'placeholder'       => '请输入商品名称/简述/SEO信息'
                    ],
                ],
                [
                    'label'         => '销售价格',
                    'view_type'     => 'field',
                    'view_key'      => 'price',
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'min_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '原价',
                    'view_type'     => 'field',
                    'view_key'      => 'original_price',
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'min_original_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '库存数量',
                    'view_type'     => 'field',
                    'view_key'      => ['inventory', 'inventory_unit'],
                    'view_key_join' => ' ',
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'inventory',
                    ],
                ],
                [
                    'label'         => '上下架',
                    'view_type'     => 'field',
                    'view_key'      => 'is_shelves',
                    'view_data'     => [0=>'下架', 1=>'上架'],
                    'align'         => 'center',
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'is_shelves',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_is_shelves_list'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '商品型号',
                    'view_type'     => 'field',
                    'view_key'      => 'model',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'model',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '商品分类',
                    'view_type'     => 'field',
                    'view_key'      => 'category_text',
                    'search_config' => [
                        'form_type'             => 'module',
                        'template'              => 'lib/module/multi_level_category',
                        'form_name'             => 'id',
                        'where_type'            => 'in',
                        'where_value_custom'    => 'WhereValueGoodsCategory',
                        'data'                  => GoodsCategoryService::GoodsCategoryAll(),
                    ],
                ],
                [
                    'label'         => '品牌',
                    'view_type'     => 'field',
                    'view_key'      => 'brand_name',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'brand_id',
                        'where_type'        => 'in',
                        'data'              => BrandService::CategoryBrand(),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '生产地',
                    'view_type'     => 'field',
                    'view_key'      => 'place_origin_name',
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'place_origin',
                        'data'              => RegionService::RegionItems(['pid'=>0]),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'where_type'        => 'in',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'add_time',
                    ],
                ],
            ],
        ];
    }

    /**
     * 商品分类条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-03
     * @desc    description
     * @param   [string]          $name     [字段名称]
     * @param   [array]           $params   [输入参数]
     */
    public function WhereValueGoodsCategory($value, $params = [])
    {
        if(!empty($value))
        {
            // 是否为数组
            if(!is_array($value))
            {
                $value = [$value];
            }

            // 获取分类下的所有分类 id
            $category_ids = GoodsCategoryService::GoodsCategoryItemsIds($value, 1);

            // 获取商品 id
            $goods_ids = Db::name('GoodsCategoryJoin')->where(['category_id'=>$category_ids])->column('goods_id');

            // 避免空条件造成无效的错觉
            return empty($goods_ids) ? [0] : $goods_ids;
        }
        return $value;
    }
}