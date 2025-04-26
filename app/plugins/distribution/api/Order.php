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
namespace app\plugins\distribution\api;

use app\plugins\distribution\api\Common;
use app\plugins\distribution\service\BusinessService;

/**
 * 分销 - 分销订单
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Order extends Common
{
    /**
     * 构造方法
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // 是否登录
        IsUserLogin();
    }

    /**
     * 列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-19
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 参数
        $params['user'] = $this->user;
        $params['user_type'] = 'user';

        // 条件
        $where = BusinessService::UserOrderWhere($params);

        // 获取总数
        $total = BusinessService::UserOrderTotal($where);
        $page_total = ceil($total/$this->page_size);
        $start = intval(($this->page-1)*$this->page_size);

        // 获取列表
        $data_params = [
            'm'             => $start,
            'n'             => $this->page_size,
            'where'         => $where,
        ];
        $data = BusinessService::UserOrderList($data_params);

        // 返回数据
        $result = [
            'total'             => $total,
            'page_total'        => $page_total,
            'data'              => $data['data'],
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 获取详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-01-07
     * @param   [array]          $params [输入参数]
     */
    public function Detail($params = [])
    {
        // 参数
        $params['user'] = $this->user;
        $params['user_type'] = 'user';
        if(empty($params['id']))
        {
            return DataReturn(MyLang('params_error_tips'), -1);
        }

        // 条件
        $where = BusinessService::UserOrderWhere($params);

        // 获取列表
        $data_params = [
            'm'         => 0,
            'n'         => 1,
            'where'     => $where,
        ];
        $ret = BusinessService::UserOrderList($data_params);
        if(!empty($ret['data'][0]))
        {
            // 返回信息
            $result = [
                'data'      => $ret['data'][0],
            ];

            return DataReturn('success', 0, $result);
        }
        return DataReturn(MyLang('data_no_exist_or_delete_error_tips'), -100);
    }

    /**
     * 地图列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-19
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Map($params = [])
    {
        $params['plugins_config'] = $this->plugins_config;
        $params['user'] = $this->user;
        $params['user_type'] = 'user';
        $ret = BusinessService::UserOrderMapData($params);
        $result = [
            'user'  => $ret['data'],
            'scale' => 10,
        ];
        return DataReturn('success', 0, $result);
    }
}
?>