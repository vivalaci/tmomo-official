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
namespace app\plugins\seckill\form\admin;

use think\facade\Db;
use app\plugins\seckill\service\BaseService;
use app\plugins\seckill\service\PeriodsService;

/**
 * 限时秒杀商品动态表格-用户
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-05-16
 * @desc    description
 */
class Goods
{
    // 基础条件
    public $condition_base = [];

    // 时段列表
    public $periods_list;

    /**
     * 构造方法
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function __construct($params = [])
    {
        // 时段
        $periods = PeriodsService::PeriodsDataList(['field'=>'id,name']);
        $this->periods_list = empty($periods) ? [] : array_column($periods, null, 'id');
    }

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
                'status_field'  => 'is_recommend',
                'is_search'     => 1,
                'is_delete'     => 1,
                'delete_url'    => PluginsAdminUrl('seckill', 'goods', 'delete'),
                'delete_key'    => 'ids',
                'is_middle'     => 0,
            ],
            // 表单配置
            'form' => [
                [
                    'view_type'         => 'checkbox',
                    'is_checked'        => 0,
                    'checked_text'      => MyLang('reverse_select_title'),
                    'not_checked_text'  => MyLang('select_all_title'),
                    'align'             => 'center',
                    'width'             => 80,
                ],
                [
                    'label'         => '商品信息',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/seckill/view/admin/goods/module/info',
                    'grid_size'     => 'lg',
                    'is_sort'       => 1,
                    'sort_field'    => 'title',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'g.title|g.simple_desc|g.seo_title|g.seo_keywords|g.seo_keywords',
                        'where_type'        => 'like',
                        'placeholder'       => '请输入商品名称/简述/SEO信息'
                    ],
                ],
                [
                    'label'         => '时段',
                    'view_type'     => 'field',
                    'view_key'      => 'periods_id',
                    'view_data_key' => 'name',
                    'view_data'     => $this->periods_list,
                    'width'         => 130,
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'where_type'        => 'in',
                        'data'              => $this->periods_list,
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '秒杀价',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/seckill/view/admin/goods/module/seckill_price',
                    'grid_size'     => 'sm',
                ],
                [
                    'label'         => '销售价格',
                    'view_type'     => 'field',
                    'view_key'      => 'price',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'g.min_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'         => '原价',
                    'view_type'     => 'field',
                    'view_key'      => 'original_price',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'section',
                        'form_name'         => 'g.min_original_price',
                        'is_point'          => 1,
                    ],
                ],
                [
                    'label'              => '审核状态',
                    'view_type'          => 'field',
                    'view_key'           => 'status_name',
                    'is_round_point'     => 1,
                    'round_point_key'    => 'status',
                    'round_point_style'  => [1=>'success', 2=>'danger'],
                    'align'              => 'center',
                    'is_sort'            => 1,
                    'search_config'      => [
                        'form_type'         => 'select',
                        'form_name'         => 'status',
                        'where_type'        => 'in',
                        'data'              => BaseService::$plugins_goods_audit_status_list,
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '是否推荐',
                    'view_type'     => 'status',
                    'view_key'      => 'is_recommend',
                    'is_form_su'    => 1,
                    'post_url'      => PluginsAdminUrl('seckill', 'goods', 'statusupdate'),
                    'align'         => 'center',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'select',
                        'form_name'         => 'pg.is_recommend',
                        'where_type'        => 'in',
                        'data'              => MyConst('common_is_text_list'),
                        'data_key'          => 'id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],
                [
                    'label'         => '开始时间',
                    'view_type'     => 'field',
                    'view_key'      => 'time_start',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'date',
                        'form_name'         => 'pg.time_start',
                    ],
                ],
                [
                    'label'         => '结束时间',
                    'view_type'     => 'field',
                    'view_key'      => 'time_end',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'date',
                        'form_name'         => 'pg.time_end',
                    ],
                ],
                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'pg.add_time',
                    ],
                ],
                [
                    'label'         => '更新时间',
                    'view_type'     => 'field',
                    'view_key'      => 'upd_time',
                    'is_sort'       => 1,
                    'search_config' => [
                        'form_type'         => 'datetime',
                        'form_name'         => 'pg.upd_time',
                    ],
                ],
                [
                    'label'         => MyLang('operate_title'),
                    'view_type'     => 'operate',
                    'view_key'      => '../../../plugins/seckill/view/admin/goods/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
            // 数据配置
            'data'  => [
                'table_obj'     => Db::name('PluginsSeckillGoods')->alias('pg')->join('goods g', 'g.id=pg.goods_id'),
                'select_field'  => 'pg.*,g.title,g.images,g.price,g.original_price,g.simple_desc',
                'order_by'      => 'pg.id asc',
                'detail_dkey'   => 'pg.id',
                'detail_action' => ['detail', 'saveinfo', 'auditinfo'],
                'data_handle'   => 'SeckillGoodsService::SeckillGoodsListHandle',
                'data_params'   => [
                    'data_key_field'    => 'goods_id',
                ],
                'is_page'       => 0,
            ],
        ];
    }
}
?>