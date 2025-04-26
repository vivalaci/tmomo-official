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
 * 分销 - 推广用户
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class PromotionUser extends Common
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
        if(in_array($type, [0,1,2]))
        {
            $map = BusinessService::UserPromotionMap($params);
        } else {
            $map = BusinessService::BaseDataMap($params);
        }

        // 根据类型处理数据
        switch($type)
        {
            // 已推广用户
            case 0 :
                // 获取总数
                $total = BusinessService::UserPromotionAllTotal($map['user_where']);
                $page_total = ceil($total/$this->page_size);
                $start = intval(($this->page-1)*$this->page_size);

                // 获取列表
                $data_params = [
                    'm'      => $start,
                    'n'      => $this->page_size,
                    'where'  => $map['user_where'],
                ];
                $data = BusinessService::UserPromotionAllList($data_params);
                break;

            // 已推广已消费用户
            case 1 :
                // 获取总数
                $total = BusinessService::UserPromotionValidTotal($map['valid_where']);
                $page_total = ceil($total/$this->page_size);
                $start = intval(($this->page-1)*$this->page_size);

                // 获取列表
                $data_params = [
                    'm'      => $start,
                    'n'      => $this->page_size,
                    'where'  => $map['valid_where'],
                ];
                $data = BusinessService::UserPromotionValidList($data_params);
                break;

            // 已推广未消费用户
            case 2 :
                // 获取总数
                $total = BusinessService::UserPromotionNotConsumedTotal($map['valid_where'], $map['not_where']);
                $page_total = ceil($total/$this->page_size);
                $start = intval(($this->page-1)*$this->page_size);

                // 获取列表
                $data_params = [
                    'm'            => $start,
                    'n'            => $this->page_size,
                    'where'        => $map['not_where'],
                    'valid_where'  => $map['valid_where'],
                ];
                $data = BusinessService::UserPromotionNotConsumedList($data_params);
                break;

            // 新增客户
            case 3 :
                // 获取总数
                $total = BusinessService::BaseDataOrderNewUserCountTotal($map['order_where'], $map['order_new_user_not_front_where']);
                $page_total = ceil($total/$this->page_size);
                $start = intval(($this->page-1)*$this->page_size);

                // 获取列表
                $data_params = [
                    'm'                => $start,
                    'n'                => $this->page_size,
                    'where'            => $map['order_where'],
                    'not_front_where'  => $map['order_new_user_not_front_where'],
                ];
                $data = BusinessService::BaseDataOrderNewUserCountList($data_params);
                break;

            // 新增客户-有效
            case 4 :
                // 获取总数
                $total = BusinessService::BaseDataOrderNewValidUserCountTotal($map['order_where'], $map['order_new_user_not_front_where']);
                $page_total = ceil($total/$this->page_size);
                $start = intval(($this->page-1)*$this->page_size);

                // 获取列表
                $data_params = [
                    'm'                => $start,
                    'n'                => $this->page_size,
                    'where'            => $map['order_where'],
                    'not_front_where'  => $map['order_new_user_not_front_where'],
                ];
                $data = BusinessService::BaseDataOrderNewValidUserCountList($data_params);
                break;

            // 新增客户-需复购
            case 5 :
                // 获取总数
                $total = BusinessService::BaseDataOrderNewValidUserNeedRepurchaseCountTotal($map['order_where'], $map['order_new_user_not_front_where']);
                $page_total = ceil($total/$this->page_size);
                $start = intval(($this->page-1)*$this->page_size);

                // 获取列表
                $data_params = [
                    'm'                => $start,
                    'n'                => $this->page_size,
                    'where'            => $map['order_where'],
                    'not_front_where'  => $map['order_new_user_not_front_where'],
                ];
                $data = BusinessService::BaseDataOrderNewValidUserNeedRepurchaseCountList($data_params);
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
                    ['id', '=', intval($params['id'])],
                    ['referrer', '=', $this->user['id']],
                ],
            ];
            $ret = BusinessService::UserPromotionAllList($data_params);
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