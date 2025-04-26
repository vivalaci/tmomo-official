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
namespace app\plugins\excellentbuyreturntocash\service;

use think\facade\Db;
use app\service\GoodsCategoryService;
use app\plugins\excellentbuyreturntocash\service\BaseService;

/**
 * 优购返现 - 订单服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class OrderService
{
    /**
     * 订单提交校验
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-20
     * @desc    description
     * @param   [array]        $params   [输入参数]
     */
    public static function OrderInsertBeginCheck($params = [])
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();
        if(empty($base['data']) || empty($base['data']['limit_buy_category_ids_all']) || (empty($base['data']['limit_goods_buy_bumber_max']) && empty($base['data']['limit_order_buy_bumber_max'])))
        {
            return DataReturn('插件未配置', 0);
        }

        // 限购数量
        $limit_goods_buy_bumber_max = intval($base['data']['limit_goods_buy_bumber_max']);
        $limit_order_buy_bumber_max = intval($base['data']['limit_order_buy_bumber_max']);

        // 总数量限购
        if($limit_order_buy_bumber_max > 0 && array_sum(array_column($params['goods'], 'buy_number')) > $limit_order_buy_bumber_max)
        {
            return DataReturn('订单限购'.$limit_order_buy_bumber_max.'件', -1);
        }

        // 单品限购
        if($limit_goods_buy_bumber_max > 0)
        {
            // 分类 id
            $category_ids = GoodsCategoryService::GoodsCategoryItemsIds($base['data']['limit_buy_category_ids_all']);

            // 循环处理
            foreach($params['goods'] as $v)
            {
                // 超购则校验是否存在限制分类中
                if($v['buy_number'] > $limit_goods_buy_bumber_max)
                {
                    // 获取商品所属分类
                    $ids = Db::name('GoodsCategoryJoin')->where(['goods_id'=>$v['goods_id']])->column('category_id');
                    if(!empty($ids))
                    {
                        // 循环匹配是否存在分类中
                        foreach($ids as $cid)
                        {
                            if(in_array($cid, $category_ids))
                            {
                                return DataReturn('商品限购'.$limit_goods_buy_bumber_max.$v['inventory_unit'].'['.$v['title'].']', -1);
                            }
                        }
                    }
                }
            }
        }

        return DataReturn(MyLang('check_success'), 0);
    }
}
?>