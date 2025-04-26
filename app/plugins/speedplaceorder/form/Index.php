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
namespace app\plugins\speedplaceorder\form;

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
class Index
{
    // 基础条件
    public $condition_base = [];

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
                'search_url'    => PluginsHomeUrl('speedplaceorder', 'index', 'index'),
                'is_delete'     => 1,
                'delete_url'    => PluginsHomeUrl('speedplaceorder', 'index', 'delete'),
                'delete_key'    => 'ids',
            ],
            // 表单配置
            'form' => [
                [
                    'view_type'         => 'checkbox',
                    'is_checked'        => 0,
                    'checked_text'      => MyLang('reverse_select_title'),
                    'not_checked_text'  => MyLang('select_all_title'),
                    'view_key'          => 'ids',
                    'align'             => 'center',
                    'width'             => 80,
                ],
                [
                    'label'         => '商品信息',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/speedplaceorder/view/index/index/module/info',
                    'grid_size'     => 'lg',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'g.title|g.simple_desc|g.seo_title|g.seo_keywords|g.seo_keywords',
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
                        'form_name'         => 'sc.price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '原价',
                    'view_type'     => 'field',
                    'view_key'      => 'original_price',
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'sc.original_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '数量',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/speedplaceorder/view/index/index/module/stock',
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'stock',
                    ],
                ],
                 [
                    'label'         => '库存数量',
                    'view_type'     => 'field',
                    'view_key'      => ['inventory', 'inventory_unit'],
                    'view_key_join' => ' ',
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'g.inventory',
                    ],
                ],
                [
                    'label'         => '规格',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/speedplaceorder/view/index/index/module/spec',
                ],
                [
                    'label'         => '商品型号',
                    'view_type'     => 'field',
                    'view_key'      => 'model',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'g.model',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '品牌',
                    'view_type'     => 'field',
                    'view_key'      => 'brand_name',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'g.brand_id',
                        'where_type'        => 'in',
                        'data'              => BrandService::CategoryBrand(),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '商品分类',
                    'view_type'     => 'field',
                    'view_key'      => 'category_text',
                    'search_config' => [
                        'form_type'             => 'module',
                        'template'              => 'lib/module/multi_level_category',
                        'form_name'             => 'g.id',
                        'where_type'            => 'in',
                        'where_value_custom'    => 'WhereValueGoodsCategory',
                        'data'                  => GoodsCategoryService::GoodsCategoryAll(),
                    ],
                ],
                [
                    'label'         => '生产地',
                    'view_type'     => 'field',
                    'view_key'      => 'place_origin_name',
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'g.place_origin',
                        'data'              => RegionService::RegionItems(['pid'=>0]),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'where_type'        => 'in',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/speedplaceorder/view/index/index/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
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