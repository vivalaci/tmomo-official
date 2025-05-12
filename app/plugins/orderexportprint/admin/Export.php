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
namespace app\plugins\orderexportprint\admin;

use app\service\OrderService;
use app\plugins\orderexportprint\admin\Common;
use app\plugins\orderexportprint\service\BaseService;
use app\plugins\orderexportprint\service\ExportService;
use app\module\FormTableHandleModule;

/**
 * 订单导出打印 - 导出
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2019-09-24
 * @desc    description
 */
class Export extends Common
{
    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-24
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function index($params = [])
    {
        // 解析订单动态表格
        $module = (new FormTableHandleModule())->Run('\app\admin\form\Order', 'Index', $params);

        // 条件
        $where = empty($module['data']) || empty($module['data']['where']) ? [] : $module['data']['where'];

        // 获取列表
        $data_params = [
            'where'     => $where,
            'm'         => 0,
            'n'         => 0,
            'is_public' => 0,
            'user_type' => 'admin',
        ];
        $data = OrderService::OrderList($data_params);

        // 插件配置
        $config = BaseService::BaseConfig();

        // Excel驱动导出数据
        $excel = new \base\Excel(array('filename'=>'order', 'title'=>ExportService::OrderExcelTitle($config['data']), 'data'=>ExportService::OrderExportData($data['data'], $config['data']), 'msg'=>'没有相关数据'));
        return $excel->Export();
    }    
}
?>