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
namespace app\plugins\excellentbuyreturntocash\index;

use app\plugins\excellentbuyreturntocash\service\CrontabService;

/**
 * 优购返现 - 脚本
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Crontab
{
    /**
     * 返现结算
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-12-07T14:56:20+0800
     * @desc     description
     * @param    [array]         $params [输入参数]
     */
    public function profit($params = [])
    {
        $ret = CrontabService::ProfitSettlement($params);
        $sucs = isset($ret['data']['sucs']) ? $ret['data']['sucs'] : 0;
        $fail = isset($ret['data']['fail']) ? $ret['data']['fail'] : 0;
        return 'sucs:'.$sucs.', fail:'.$fail.', msg:'.$ret['msg'];
    }

    /**
     * 自动返现结算
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-12-07T14:56:20+0800
     * @desc     description
     * @param    [array]         $params [输入参数]
     */
    public function auto($params = [])
    {
        $ret = CrontabService::AutoSettlement($params);
        $sucs = isset($ret['data']['sucs']) ? $ret['data']['sucs'] : 0;
        $fail = isset($ret['data']['fail']) ? $ret['data']['fail'] : 0;
        return 'sucs:'.$sucs.', fail:'.$fail.', msg:'.$ret['msg'];
    }
}
?>