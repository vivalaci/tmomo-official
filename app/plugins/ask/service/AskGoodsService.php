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
namespace app\plugins\ask\service;

use think\facade\Db;
use app\service\GoodsService;
use app\service\GoodsCategoryService;

/**
 * 问答商品推荐服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class AskGoodsService
{
    /**
     * 商品搜索
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function GoodsSearchList($params = [])
    {
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

        // 获取数据
        return GoodsService::CategoryGoodsList(['where'=>$where, 'm'=>0, 'n'=>100, 'is_admin_access'=>1]);
    }

    /**
     * 关联商品保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-07
     * @desc    description
     * @param    [array]          $params [输入参数]
     */
    public static function GoodsSave($params = [])
    {
        // 清除商品id
        Db::name('PluginsAskGoods')->where('id', '>', 0)->delete();

        // 写入商品id
        if(!empty($params['goods_ids']))
        {
            $ids_all = explode(',', $params['goods_ids']);
            $data = [];
            foreach($ids_all as $goods_id)
            {
                $data[] = [
                    'goods_id'  => $goods_id,
                    'add_time'  => time(),
                ];
            }
            if(Db::name('PluginsAskGoods')->insertAll($data) < count($data))
            {
                return DataReturn(MyLang('operate_fail'), -100);
            }
        }
        return DataReturn(MyLang('operate_success'), 0);
    }

    /**
     * 商品列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function RecommendGoodsList($params = [])
    {
        // 获取推荐商品id
        $goods_ids = Db::name('PluginsAskGoods')->column('goods_id');
        if(empty($goods_ids))
        {
            return DataReturn('没有商品', 0, ['goods'=>[], 'goods_ids'=>[]]);
        }

        // 条件
        $where = [
            ['is_delete_time', '=', 0],
            ['is_shelves', '=', 1],
            ['id', 'in', $goods_ids],
        ];

        // 获取数据
        $ret = GoodsService::GoodsList(['where'=>$where, 'm'=>0, 'n'=>100, 'is_spec'=>1, 'is_cart'=>1]);
        return DataReturn(MyLang('operate_success'), 0, ['goods'=>$ret['data'], 'goods_ids'=>$goods_ids]);
    }
}
?>