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
namespace app\plugins\seckill\index;

use app\plugins\seckill\index\Common;
use app\plugins\seckill\service\BaseService;
use app\plugins\seckill\service\SeckillGoodsService;
use app\plugins\seckill\service\PeriodsService;
use app\plugins\shop\service\ShopGoodsCategoryService;

/**
 * 限时秒杀 - 店铺商品
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class ShopGoods extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // 是否已经登录
        $this->IsShopLogin();

        MyViewAssign([
            // 关闭顶部底部内容
            'is_header'        => 0,
            'is_footer'        => 0,
            // 页面加载层
            'is_page_loading'  => 1,
        ]);
    }

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
        return MyView('../../../plugins/seckill/view/index/shopgoods/index');
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
        MyViewAssign('data', $this->data_detail);
        return MyView('../../../plugins/seckill/view/index/shopgoods/detail');
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
        // 获取店铺信息
        $shop_id = BaseService::ShopID();
        if(!empty($shop_id))
        {
            // 商品数据
            $goods = SeckillGoodsService::SeckillGoodsList(['shop_id'=>$shop_id]);
            // 店铺商品分类
            $shop_goods_category = ShopGoodsCategoryService::GoodsCategoryAll(['user_id'=>$this->user['id']]);
            MyViewAssign([
                // 商品数据
                'goods'                => $goods['data'],
                // 商品分类
                'goods_category_list'  => $shop_goods_category['data'],
                // 时段
                'periods_list'         => PeriodsService::PeriodsDataList(),
                // 店铺id
                'shop_id'              => $shop_id,
            ]);
        }
        return MyView('../../../plugins/seckill/view/index/shopgoods/saveinfo');
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
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 获取店铺信息
        $shop_id = BaseService::ShopID();
        if(empty($shop_id))
        {
            return DataReturn('店铺信息有误', -1);
        }

        // 开始操作
        $params['shop_id'] = $shop_id;
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
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 获取店铺信息
        $shop_id = BaseService::ShopID();
        if(empty($shop_id))
        {
            return DataReturn('店铺信息有误', -1);
        }

        // 开始操作
        $params['shop_id'] = $shop_id;
        return SeckillGoodsService::ShopSeckillGoodsSave($this->plugins_config, $params);
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
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 获取店铺信息
        $shop_id = BaseService::ShopID();
        if(empty($shop_id))
        {
            return DataReturn('店铺信息有误', -1);
        }

        // 开始操作
        $params['shop_id'] = $shop_id;
        return SeckillGoodsService::SeckillGoodsDelete($params);
    }
}
?>