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
namespace app\plugins\ask\form\admin;

/**
 * 问答推荐商品动态表格-管理
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
                'is_middle'     => 0,
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => '商品信息',
                    'view_type'     => 'module',
                    'view_key'      => '../../../plugins/ask/view/admin/goods/module/info',
                    'grid_size'     => 'lg',
                ],
                [
                    'label'         => '销售价',
                    'view_type'     => 'field',
                    'view_key'      => 'price',
                ],
                [
                    'label'         => '原价',
                    'view_type'     => 'field',
                    'view_key'      => 'original_price',
                ],
                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                ],
            ],
        ];
    }
}
?>