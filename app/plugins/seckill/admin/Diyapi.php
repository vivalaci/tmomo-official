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

use app\plugins\seckill\admin\Common;
use app\plugins\seckill\service\SeckillGoodsService;

/**
 * 限时秒杀 - diyapi接口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class DiyApi extends Common
{
    /**
     * 秒杀商品
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-09-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Goods($params = [])
    {
        return SeckillGoodsService::SeckillData($this->plugins_config);
    }
}
?>