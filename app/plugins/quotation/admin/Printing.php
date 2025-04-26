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
namespace app\plugins\quotation\admin;

use app\plugins\quotation\admin\Common;
use app\plugins\quotation\service\BaseService;

/**
 * 报价单 - 数据打印
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2019-09-24
 * @desc    description
 */
class Printing extends Common
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
        // 参数处理
        $params = BaseService::ParamsHandle($this->data_request);
        
        // 获取商品数据
        $ret = BaseService::ExportGoodsList($params);
        if($ret['code'] == 0)
        {
            // 操作类型type 0在线打印预览, 1导出excel
            if((isset($params['type']) ? intval($params['type']) : 0) == 0)
            {
                MyViewAssign('params', $params);
                MyViewAssign('data', $ret['data']);
                return MyView('../../../plugins/quotation/view/admin/printing/index');
            } else {
                BaseService::ExcelExport($ret['data']);
            }
        } else {
            MyViewAssign('msg', $ret['msg']);
            return MyView('../../../plugins/quotation/view/admin/printing/index');
        }
    }
}
?>