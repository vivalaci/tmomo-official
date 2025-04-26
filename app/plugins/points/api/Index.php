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
namespace app\plugins\points\api;

use app\service\IntegralService;
use app\plugins\points\api\Common;

/**
 * 积分商城 - 首页
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class Index extends Common
{
    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        // 用户积分
        $integral = empty($this->user['id']) ? [] : IntegralService::UserIntegral($this->user['id']);

        // 返回数据
        $result = [
            'base'          => $this->plugins_config,
            'user_integral' => $integral,
        ];
        return DataReturn('success', 0, $result);
    }
}
?>