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
use app\service\PluginsService;
use app\service\GoodsCategoryService;

/**
 * 基础服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [];

    // 消息类型
    public static $message_business_type = '优购返现';

    // 是否
    public static $common_is_whether_list =  [
        0 => ['value' => 0, 'name' => '否'],
        1 => ['value' => 1, 'name' => '是', 'checked' => true],
    ];

    // 返现结算状态（0待生效, 1生效中, 2待结算, 3已结算, 4已失效）
    public static $profit_status_list = [
        0 => ['value' => 0, 'name' => '待生效', 'checked' => true],
        1 => ['value' => 1, 'name' => '生效中'],
        2 => ['value' => 2, 'name' => '待结算'],
        3 => ['value' => 3, 'name' => '已结算'],
        4 => ['value' => 4, 'name' => '已失效'],
    ];

    // 返券类型（0购买数量倍数， 1订单金额）
    public static $return_coupon_type_list =  [
        0 => ['value' => 0, 'name' => '购买数量倍数', 'checked' => true],
        1 => ['value' => 1, 'name' => '订单金额'],
    ];

    // 用户分享提示信息
    public static $user_share_msg = '人数不够、分享让更多人参与';

    /**
     * 基础配置信息保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseConfigSave($params = [])
    {
        // 限制分类购买数量
        if(!empty($params['limit_buy_category_ids']))
        {
            $params['limit_buy_category_ids_all'] = explode(',', $params['limit_buy_category_ids']);
            $params['limit_buy_category_ids_names'] = Db::name('GoodsCategory')->where(['id'=>$params['limit_buy_category_ids_all']])->column('name');
        }

        // 返券
        $params['return_category_coupon_ids'] = htmlspecialchars_decode($params['return_category_coupon_ids']);

        // 指定分类下商品购买返现
        if(!empty($params['return_to_cash_category_ids']))
        {
            $params['return_to_cash_category_ids_all'] = explode(',', $params['return_to_cash_category_ids']);
            $params['return_to_cash_category_ids_names'] = Db::name('GoodsCategory')->where(['id'=>$params['return_to_cash_category_ids_all']])->column('name');
        }

        // 指定分类下商品购买自动返现
        if(!empty($params['return_auto_cash_category_ids']))
        {
            $params['return_auto_cash_category_ids_all'] = explode(',', $params['return_auto_cash_category_ids']);
            $params['return_auto_cash_category_ids_names'] = Db::name('GoodsCategory')->where(['id'=>$params['return_auto_cash_category_ids_all']])->column('name');
        }

        // 保存数据
        return PluginsService::PluginsDataSave(['plugins'=>'excellentbuyreturntocash', 'data'=>$params], self::$base_config_attachment_field);
    }

    /**
     * 基础配置信息获取
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [boolean]          $is_cache [是否缓存中读取]
     */
    public static function BaseConfig($is_cache = true)
    {
        $ret = PluginsService::PluginsData('excellentbuyreturntocash', self::$base_config_attachment_field, $is_cache);
        if(!empty($ret['data']))
        {
            // 用户返现订单页面公告
            if(!empty($ret['data']['user_cach_order_notice']))
            {
                $ret['data']['user_cach_order_notice'] = explode("\n", $ret['data']['user_cach_order_notice']);
            }

            // 返券
            if(!empty($ret['data']['return_category_coupon_ids']))
            {
                $ret['data']['return_category_coupon_ids_all'] = json_decode($ret['data']['return_category_coupon_ids'], true);
            }
        }

        return $ret;
    }

    /**
     * 商品标题icon - 返现
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     * @param   [array]           $base   [配置信息]
     */
    public static function CashGoodsDetailTitleIcon($params = [], $base = [])
    {
        // 参数判断
        if(empty($params['goods_id']) || empty($base['data']) || empty($base['data']['return_to_cash_rate']) || empty($base['data']['return_to_cash_category_ids_all']) || empty($base['data']['goods_detail_title_cash_icon']))
        {
            return DataReturn('返现未配置', -1);
        }

        // 获取配置指定分类所有子分类
        $base_ids = GoodsCategoryService::GoodsCategoryItemsIds($base['data']['return_to_cash_category_ids_all']);

        // 获取商品所属分类
        $ids = Db::name('GoodsCategoryJoin')->where(['goods_id'=>$params['goods_id']])->column('category_id');
        if(!empty($ids))
        {
            // 循环匹配是否存在分类中
            foreach($ids as $cid)
            {
                if(in_array($cid, $base_ids))
                {
                    return DataReturn('success', 0);
                }
            }
        }
        return DataReturn('未匹配到', -100);
    }

    /**
     * 商品标题icon - 返券
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     * @param   [array]           $base   [配置信息]
     */
    public static function CouponGoodsDetailTitleIcon($params = [], $base = [])
    {
        // 参数判断
        if(empty($params['goods_id']) || empty($base['data']) || empty($base['data']['return_category_coupon_ids_all']) || empty($base['data']['goods_detail_title_coupon_icon']))
        {
            return DataReturn('返券未配置', -1);
        }

        // 获取配置指定分类所有子分类
        $base_ids = GoodsCategoryService::GoodsCategoryItemsIds(array_unique(array_column($base['data']['return_category_coupon_ids_all'], 'category_id')));

        // 获取商品所属分类
        $ids = Db::name('GoodsCategoryJoin')->where(['goods_id'=>$params['goods_id']])->column('category_id');
        if(!empty($ids))
        {
            // 循环匹配是否存在分类中
            foreach($ids as $cid)
            {
                if(in_array($cid, $base_ids))
                {
                    return DataReturn('success', 0);
                }
            }
        }
        return DataReturn('未匹配到', -100);
    }
}
?>