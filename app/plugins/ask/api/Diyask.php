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
namespace app\plugins\ask\api;

use app\plugins\ask\api\Common;
use app\plugins\ask\service\AskService;

/**
 * 问答 - DIY问答
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class DiyAsk extends Common
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
        return DataReturn('success', 0, FormModuleData($params));
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-03-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        $result = FormModuleData($params);
        if(empty($result) || empty($result['data']))
        {
            return DataReturn(MyLang('no_data'), -1);
        }
        return DataReturn('success', 0, $result);
    }

    /**
     * 指定读取问答列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-03-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function AppointAskList($params = [])
    {
        return DataReturn('success', 0, AskService::AppointAskList($params));
    }

    /**
     * 自动读取问答列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-03-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function AutoAskList($params = [])
    {
        return DataReturn('success', 0, AskService::AutoAskList($params));
    }
}
?>