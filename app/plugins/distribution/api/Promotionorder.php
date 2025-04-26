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
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\BusinessService;

/**
 * 分销 - 推广订单
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class PromotionOrder extends Common
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
        IsUserLogin();
    }

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
        // 类型
        $type = empty($params['type']) ? 0 : intval($params['type']);

        // 基础条件
        $params['user'] = $this->user;
        $map = BusinessService::BaseDataMap($params);

        // 根据类型处理数据
        switch($type)
        {
            // 新增客户总GMV
            case 0 :
                // 获取总数
                $total = BusinessService::BaseDataOrderNewUserPriceGMVTotal($map['order_where'], $map['order_new_user_not_front_where']);
                $page_total = ceil($total/$this->page_size);
                $start = intval(($this->page-1)*$this->page_size);

                // 获取列表
                $data_params = [
                    'm'                => $start,
                    'n'                => $this->page_size,
                    'where'            => $map['order_where'],
                    'not_front_where'  => $map['order_new_user_not_front_where'],
                ];
                $data = BusinessService::BaseDataOrderNewUserPriceGMVList($data_params);
                break;

            // 订单总数
            case 1 :
                // 获取总数
                $total = BusinessService::BaseDataOrderUserCountTotal($map['order_where']);
                $page_total = ceil($total/$this->page_size);
                $start = intval(($this->page-1)*$this->page_size);

                // 获取列表
                $data_params = [
                    'm'      => $start,
                    'n'      => $this->page_size,
                    'where'  => $map['order_where'],
                ];
                $data = BusinessService::BaseDataOrderUserCountList($data_params);
                break;

            // 订单总GMV
            case 2 :
                // 获取总数
                $total = BusinessService::BaseDataOrderUserPriceGMVTotal($map['order_where']);
                $page_total = ceil($total/$this->page_size);
                $start = intval(($this->page-1)*$this->page_size);

                // 获取列表
                $data_params = [
                    'm'      => $start,
                    'n'      => $this->page_size,
                    'where'  => $map['order_where'],
                ];
                $data = BusinessService::BaseDataOrderUserPriceGMVList($data_params);
                break;

            default :
                return DataReturn('业务类型有误！', -1);
        }
        // 返回数据
        $result = [
            'total'             => $total,
            'page_total'        => $page_total,
            'data'              => $data['data'],
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-04
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        if(!empty($params['id']))
        {
            $data_params = [
                'm'         => 0,
                'n'         => 1,
                'where'     => [
                    ['o.id', '=', intval($params['id'])],
                    ['u.referrer', '=', $this->user['id']],
                ],
            ];
            $ret = BusinessService::UserOrderList($data_params);
            if(!empty($ret['data']) && !empty($ret['data'][0]))
            {
                $result = [
                    'data'  => $ret['data'][0],
                ];
                return DataReturn('success', 0, $result);
            }
        }
        return DataReturn(MyLang('data_no_exist_or_delete_error_tips'), -100);
    }
}
?>