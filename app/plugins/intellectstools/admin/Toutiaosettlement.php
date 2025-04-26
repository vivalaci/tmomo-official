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
namespace app\plugins\intellectstools\admin;

use app\plugins\intellectstools\admin\Common;
use app\plugins\intellectstools\service\ToutiaoSettlementService;

/**
 * 智能工具箱 - 头条支付分账
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class ToutiaoSettlement extends Common
{
    /**
     * 列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/admin/toutiaosettlement/index');
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        return MyView('../../../plugins/intellectstools/view/admin/toutiaosettlement/detail');
    }

    /**
     * 推送
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-28
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Push($params = [])
    {
        // 是否ajax
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始操作
        return ToutiaoSettlementService::OrderPushHandle($params);
    }

    /**
     * 结算
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-28
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Settlement($params = [])
    {
        // 是否ajax
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始操作
        return ToutiaoSettlementService::Settlement($params);
    }
}
?>