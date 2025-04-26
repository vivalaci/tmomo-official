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
use app\plugins\intellectstools\service\BaseService;
use app\plugins\intellectstools\service\GoodsInventoryService;

/**
 * 智能工具箱 - 商品库存修改
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class GoodsInventory extends Common
{
    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-04
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        MyViewAssign([
            'goods_export_key_type'  => BaseService::$goods_export_key_type,
            'warehouse_list'         => BaseService::WarehouseList(),
            'data'                   => $this->plugins_config,
        ]);
        return MyView('../../../plugins/intellectstools/view/admin/goodsinventory/index');
    }

    /**
     * 文件上传
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Upload($params = [])
    {
        return GoodsInventoryService::GoodsInventoryEditHandle($params);
    }
}
?>