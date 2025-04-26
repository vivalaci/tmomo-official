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
namespace app\plugins\seckill\admin;

use app\service\GoodsCategoryService;
use app\plugins\seckill\admin\Common;
use app\plugins\seckill\service\BaseService;
use app\plugins\seckill\service\SeckillGoodsService;
use app\plugins\seckill\service\PeriodsService;

/**
 * 限时秒杀 - 商品
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Goods extends Common
{
    /**
     * 列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        return MyView('../../../plugins/seckill/view/admin/goods/index');
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-10-15
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        return MyView('../../../plugins/seckill/view/admin/goods/detail');
    }

    /**
     * 编辑
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        // 商品数据
        $goods = SeckillGoodsService::SeckillGoodsList();
        MyViewAssign([
            // 商品数据
            'goods'                => $goods['data'],
            // 商品分类
            'goods_category_list'  => GoodsCategoryService::GoodsCategoryAll(),
            // 时段
            'periods_list'         => PeriodsService::PeriodsDataList(),
        ]);
        return MyView('../../../plugins/seckill/view/admin/goods/saveinfo');
    }

    /**
     * 审核页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function AuditInfo($params = [])
    {
        MyViewAssign('data', $this->data_detail);
        return MyView('../../../plugins/seckill/view/admin/goods/auditinfo');
    }

    /**
     * 商品搜索
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Search($params = [])
    {
        return BaseService::GoodsSearchList($this->plugins_config, $params);
    }

    /**
     * 保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        return SeckillGoodsService::AdminSeckillGoodsSave($this->plugins_config, $params);
    }

    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-01-12T22:23:06+0800
     * @param    [array]          $params [输入参数]
     */
    public function StatusUpdate($params = [])
    {
        return SeckillGoodsService::SeckillGoodsStatusUpdate($params);
    }

    /**
     * 删除
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Delete($params = [])
    {
        return SeckillGoodsService::SeckillGoodsDelete($params);
    }

    /**
     * 审核
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-01-12T22:23:06+0800
     * @param    [array]          $params [输入参数]
     */
    public function Audit($params = [])
    {
        return SeckillGoodsService::SeckillGoodsAudit($params);
    }
}
?>