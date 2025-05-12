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
namespace app\plugins\orderexportprint\service;

use think\facade\Db;
use app\service\PluginsService;

/**
 * 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2019-09-24
 * @desc    description
 */
class BaseService
{
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
        // 可打印的订单状态
        if(!empty($params['print_order_status_ids']))
        {
            $params['print_order_status_ids_all'] = explode(',', $params['print_order_status_ids']);
            $params['print_order_status_ids_names'] = [];
            if(!empty($params['print_order_status_ids_all']))
            {
                $status_all = MyConst('common_order_status');
                foreach($params['print_order_status_ids_all'] as $v)
                {
                    if(isset($status_all[$v]))
                    {
                        $params['print_order_status_ids_names'][] = $status_all[$v]['name'];
                    }
                }
            }
        }

        return PluginsService::PluginsDataSave(['plugins'=>'orderexportprint', 'data'=>$params]);
    }

    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [boolean]          $is_cache [是否缓存中读取]
     */
    public static function BaseConfig($is_cache = true)
    {
        $ret = PluginsService::PluginsData('orderexportprint', null, $is_cache);
        return $ret;
    }

    /**
     * 获取店铺id
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-08
     * @desc    description
     */
    public static function ShopID()
    {
        return CallPluginsServiceMethod('shop', 'ShopService', 'CurrentUserShopID', true);
    }
}
?>