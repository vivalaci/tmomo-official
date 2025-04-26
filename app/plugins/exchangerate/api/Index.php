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
namespace app\plugins\exchangerate\api;

use app\plugins\exchangerate\api\Common;
use app\plugins\exchangerate\service\BaseService;

/**
 * 汇率
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function __construct($params = [])
    {
        // 调用父类前置方法
        parent::__construct($params);
    }

    /**
     * 货币列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-10-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 指定的货币值
        $currency_value = BaseService::GetUserCurrencyCacheValue($this->plugins_config);

        // 获取货币列表
        $data = BaseService::UserCurrencyData($currency_value);

        // 返回数据
        $result = [
            'base'  => $this->plugins_config,
            'data'  => $data,
        ];
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 设置货币
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-10-17
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function SetCurrency($params = [])
    {
        // 指定的货币值
        $currency_value = BaseService::GetUserCurrencyCacheValue($this->plugins_config);

        // 获取货币列表
        $data = BaseService::UserCurrencyData($currency_value);
        if(!empty($data['default']))
        {
            // 存储选择的货币值
            BaseService::SetUserCurrencyCacheValue($data['default']['id']);
        }
        return DataReturn(MyLang('switch_success'), 0);
    }
}
?>