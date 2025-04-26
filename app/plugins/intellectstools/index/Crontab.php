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
namespace app\plugins\intellectstools\index;

use app\plugins\intellectstools\index\Common;
use app\plugins\intellectstools\service\CrontabService;

/**
 * 智能工具箱 - 脚本
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Crontab extends Common
{
    /**
     * 订单删除
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-07
     * @desc    description
     * @param    [array]         $params [输入参数]
     */
    public function OrderDelete($params = [])
    {
        $ret = CrontabService::OrderDelete($this->plugins_config);
        if($ret['code'] == 0)
        {
            return $ret['data']['msg'];
        }
        return $ret['msg'];
    }

    /**
     * 商品改价
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-07
     * @desc    description
     * @param    [array]         $params [输入参数]
     */
    public function GoodsPriceEditExecute($params = [])
    {
        $ret = CrontabService::GoodsPriceEditExecute($params);
        return $ret['msg'];
    }

    /**
     * 商品改价复原
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-07
     * @desc    description
     * @param    [array]         $params [输入参数]
     */
    public function GoodsPriceRestoreExecute($params = [])
    {
        $ret = CrontabService::GoodsPriceRestoreExecute($params);
        return $ret['msg'];
    }
}
?>