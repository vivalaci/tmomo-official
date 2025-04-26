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
namespace app\plugins\exchangerate\admin;

use app\service\PaymentService;
use app\plugins\exchangerate\admin\Common;
use app\plugins\exchangerate\service\BaseService;
use app\plugins\exchangerate\service\CurrencyService;

/**
 * 汇率 - 管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Admin extends Common
{
    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        $this->ViewAssignData();
        return MyView('../../../plugins/exchangerate/view/admin/admin/index');
    }

    /**
     * 编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        $this->ViewAssignData();
        return MyView('../../../plugins/exchangerate/view/admin/admin/saveinfo');
    }

    /**
     * 页面assign数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-05-25
     * @desc    description
     */
    public function ViewAssignData()
    {
        // 语言列表
        MyViewAssign('multilingual_list', MyConst('common_multilingual_list'));

        // 货币列表
        $data_params = [
            'where' => [
                ['is_enable', '=', 1],
            ],
            'field' => 'id,name'
        ];
        $currency = CurrencyService::CurrencyList($data_params);
        MyViewAssign('currency_list', empty($currency['data']) ? [] : array_column($currency['data'], 'name', 'id'));

        // 支付方式
        MyViewAssign('payment_list', PaymentService::PaymentList(['is_enable'=>1, 'is_open_user'=>1]));

        // 插件数据
        MyViewAssign('data', $this->plugins_config);
    }

    /**
     * 数据保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        return BaseService::BaseConfigSave($params);
    }
}
?>