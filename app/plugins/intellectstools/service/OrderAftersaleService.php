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
namespace app\plugins\intellectstools\service;

use think\facade\Db;
use app\service\UserService;
use app\service\OrderAftersaleService as SystemOrderAftersaleService;

/**
 * 智能工具箱 - 订单售后服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class OrderAftersaleService
{
    /**
     * 售后保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-10-20
     * @desc    description
     * @param   array           $params [输入参数]
     */
    public static function AftersaleSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order_id',
                'error_msg'         => MyLang('order_id_error_tips'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'refundment',
                'checked_data'      => array_column(MyConst('common_order_aftersale_refundment_list'), 'value'),
                'error_msg'         => '退款方式有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'goods_data',
                'error_msg'         => '请输入需要退款或退货商品',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'operate_id',
                'error_msg'         => '操作人id为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'creator_name',
                'error_msg'         => '操作人名称为空',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        if(!is_array($params['goods_data']))
        {
            $params['goods_data'] = json_decode($params['goods_data'], true);
        }

        // 订单信息
        $order = Db::name('Order')->where(['id'=>intval($params['order_id'])])->find();
        $order_detail = Db::name('OrderDetail')->where(['order_id'=>intval($params['order_id'])])->select()->toArray();
        if(empty($order) || empty($order_detail))
        {
            return DataReturn(MyLang('order_no_exist_or_delete_error_tips'), -1);
        }
        // 订单用户
        $user = UserService::UserBaseInfo('id', $order['user_id']);
        if(empty($user))
        {
            return DataReturn(MyLang('user_info_incorrect_tips'), -1);
        }

        // 售后单id
        $aftersale_ids = [];

        // 捕获异常
        Db::startTrans();
        try {
            foreach($order_detail as $v)
            {
                // 是否存在申请数据中
                if(!array_key_exists($v['id'], $params['goods_data']))
                {
                    continue;
                }
                // 是否申请了数量或者金额
                $apply_detail = $params['goods_data'][$v['id']];
                if(empty($apply_detail['price']) && empty($apply_detail['number']))
                {
                    continue;
                }

                // 可退数量和金额
                $returned = SystemOrderAftersaleService::OrderAftersaleCalculation($v['order_id'], $v['id']);
                if(!empty($apply_detail['price']) && $apply_detail['price'] > $returned['data']['refund_price'])
                {
                    throw new \Exception('超过可退款金额'.$apply_detail['price'].'>'.$returned['data']['refund_price'].'（'.$v['title'].'）');
                }
                if(!empty($apply_detail['number']) && $apply_detail['number'] > $returned['data']['returned_quantity'])
                {
                    throw new \Exception('超过可退货数量'.$apply_detail['number'].'>'.$returned['data']['returned_quantity'].'（'.$v['title'].'）');
                }

                // 退款类型
                $type = empty($apply_detail['number']) ? 0 : 1;

                // 创建售后订单
                $data = [
                    'user'              => $user,
                    'order_id'          => $v['order_id'],
                    'order_detail_id'   => $v['id'],
                    'type'              => $type,
                    'number'            => $apply_detail['number'],
                    'price'             => empty($apply_detail['price']) ? 0 : $apply_detail['price'],
                    'reason'            => $params['creator_name'].MyLang('operate_title'),
                ];
                $aftersale = SystemOrderAftersaleService::AftersaleCreate($data);
                if($aftersale['code'] != 0)
                {
                    throw new \Exception($aftersale['msg']);
                }
                $aftersale_ids[] = $aftersale['data'];

                // 退款退货方式
                if($type == 1)
                {
                    // 系统操作确认
                    $data = [
                        'id'    => $aftersale['data'],
                    ];
                    $ret = SystemOrderAftersaleService::AftersaleConfirm($data);
                    if($ret['code'] != 0)
                    {
                        throw new \Exception($ret['msg']);
                    }

                    // 系统操作退货
                    $data = [
                        'id'                => $aftersale['data'],
                        'user'              => $user,
                        'express_name'      => $params['creator_name'].MyLang('operate_title'),
                        'express_number'    => '1',
                    ];
                    $ret = SystemOrderAftersaleService::AftersaleDelivery($data);
                    if($ret['code'] != 0)
                    {
                        throw new \Exception($ret['msg']);
                    }
                }
            }
            // 提交事物
            Db::commit();
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }

        // 操作自动退款
        if(!empty($aftersale_ids))
        {
            foreach($aftersale_ids as $aid)
            {
                $data = [
                    'id'            => $aid,
                    'refundment'    => $params['refundment'],
                ];
                $ret = SystemOrderAftersaleService::AftersaleAudit($data);
                if($ret['code'] != 0)
                {
                    return $ret;
                }
            }
        }
        return DataReturn(MyLang('operate_success'), 0);
    }
}
?>