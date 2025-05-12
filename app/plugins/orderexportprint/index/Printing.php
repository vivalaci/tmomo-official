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
namespace app\plugins\orderexportprint\index;

use app\plugins\orderexportprint\index\Common;
use app\service\OrderService;
use app\plugins\orderexportprint\service\BaseService;

/**
 * 订单导出打印 - 打印
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2019-09-24
 * @desc    description
 */
class Printing extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // 是否已经登录
        $this->IsShopLogin();

        // 关闭顶部底部内容
        MyViewAssign('is_header', 0);
        MyViewAssign('is_footer', 0);
    }

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
        // 参数
        $params['user_type'] = 'admin';

        // 条件
        $where = OrderService::OrderListWhere($params);

        // 店铺id
        $shop_id = BaseService::ShopID();
        if(empty($shop_id))
        {
            $where[] = ['shop_id', '<', $shop_id];
        } else {
            $where[] = ['shop_id', '=', $shop_id];
        }

        // 获取列表
        $data_params = [
            'where' => $where,
            'm'     => 0,
            'n'     => 1,
        ];
        $ret = OrderService::OrderList($data_params);
        if($ret['code'] == 0)
        {
            MyViewAssign('data', empty($ret['data'][0]) ? [] : $ret['data'][0]);
        } else {
            MyViewAssign('msg', $ret['msg']);
        }
        return MyView('../../../plugins/orderexportprint/view/index/printing/index');
    }
}
?>