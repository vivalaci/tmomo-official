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
use app\plugins\intellectstools\service\GoodsAllPriceService;

/**
 * 智能工具箱 - 脚本服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class CrontabService
{
    /**
     * 订单地址坐标解析
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-07
     * @desc    description
     * @param   [array]           $config [插件配置]
     */
    public static function OrderDelete($config = [])
    {
        // 配置信息
        if(!isset($config['is_order_data_keep_only']) || $config['is_order_data_keep_only'] != 1)
        {
            return DataReturn('未开启订单删除', -1);
        }
        if(empty($config['order_data_keep_only_number']))
        {
            return DataReturn('未配置订单删除保留数量', -1);
        }

        // 条件
        $where = [
            ['is_delete_time', '=', 0],
        ];
        // 是否限制状态
        if(!empty($config['order_data_keep_only_order_status']))
        {
            $where[] = ['status', 'in', $config['order_data_keep_only_order_status']];
        }

        // 处理数据
        $msg = '';
        $count = Db::name('Order')->where($where)->order('id desc')->count();
        if($count > $config['order_data_keep_only_number'])
        {
            $node = Db::name('Order')->where($where)->order('id desc')->limit($config['order_data_keep_only_number'], 1)->select()->toArray();
            if(!empty($node) && !empty($node[0]))
            {
                $where = array_merge($where, [
                    ['id', '<=', $node[0]['id']]
                ]);
                $res = Db::name('Order')->where($where)->update(['is_delete_time'=>time(), 'upd_time'=>time()]);
                if($res > 0)
                {
                    $msg = '处理成功 '.$res.' 条';
                } else {
                    $msg = '处理失败';
                }
            } else {
                $msg = '没有获取到节点订单';
            }
        } else {
            $msg = '数据库'.$count.' < 基准值'.$config['order_data_keep_only_number'];
        }
        return DataReturn(MyLang('operate_success'), 0, ['msg'=>$msg]);
    }

    /**
     * 商品改价
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GoodsPriceEditExecute($params = [])
    {
        return GoodsAllPriceService::GoodsAllPriceEditExecute($params);
    }

    /**
     * 商品改价复原
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GoodsPriceRestoreExecute($params = [])
    {
        return GoodsAllPriceService::GoodsAllPriceEditExecute($params, [], 'crontab_restore_rules', 'crontab_restore_value');
    }
}
?>