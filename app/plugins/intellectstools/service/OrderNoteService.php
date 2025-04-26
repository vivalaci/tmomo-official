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
use app\plugins\intellectstools\service\OrderBaseService;

/**
 * 智能工具箱 - 订单备注服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class OrderNoteService
{
    // 数据缓存标记
    public static $cache_key = 'plugins_intellectstools_order_note_';

    /**
     * 获取数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-15
     * @desc    description
     * @param   [int|array]          $order_ids [订单id]
     */
    public static function OrderNoteData($order_ids)
    {
        $result = [];
        $temp_order_ids = is_array($order_ids) ? $order_ids : explode(',', $order_ids);
        if(!empty($temp_order_ids))
        {
            $data_ids = [];
            foreach($temp_order_ids as $oid)
            {
                $key = self::$cache_key.$oid;
                $result[$oid] = MyCache($key);
                if($result[$oid] === null)
                {
                    $data_ids[] = $oid;
                }
            }
            if(!empty($data_ids))
            {
                $data = Db::name('PluginsIntellectstoolsOrderNote')->where(['order_id'=>$data_ids])->column('*', 'order_id');
                foreach($data_ids as $doid)
                {
                    $result[$doid] = (!empty($data) && array_key_exists($doid, $data)) ? $data[$doid] : [];
                    if(!empty($result[$doid]))
                    {
                        if(array_key_exists('add_time', $result[$doid]))
                        {
                            $result[$doid]['add_time'] = date('Y-m-d H:i:s', $result[$doid]['add_time']);
                        }
                        if(array_key_exists('upd_time', $result[$doid]))
                        {
                            $result[$doid]['upd_time'] = empty($result[$doid]['upd_time']) ? '' : date('Y-m-d H:i:s', $result[$doid]['upd_time']);
                        }
                    }
                    MyCache(self::$cache_key.$doid, $result[$doid], 1800);
                }
            }
        }
        return is_array($order_ids) ? $result : (isset($result[$order_ids]) ? $result[$order_ids] : '');
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function OrderNoteSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('order_id_error_tips'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 获取订单信息
        $ret = OrderBaseService::OrderDetail($params);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 数据
        $data = [
            'order_id'  => intval($params['id']),
            'content'   => empty($params['content']) ? '' : $params['content'],
        ];

        // 获取数据
        $info = Db::name('PluginsIntellectstoolsOrderNote')->where(['order_id'=>$data['order_id']])->find();
        if(empty($info))
        {
            $data['add_time'] = time();
            if(Db::name('PluginsIntellectstoolsOrderNote')->insertGetId($data) <= 0)
            {
                return DataReturn(MyLang('operate_fail'), -1);
            }
        } else {
            $data['upd_time'] = time();
            if(Db::name('PluginsIntellectstoolsOrderNote')->where(['order_id'=>$data['order_id']])->update($data) === false)
            {
                return DataReturn(MyLang('operate_fail'), -1);
            }
        }

        // 清除缓存
        MyCache(self::$cache_key.$data['order_id'], null);
        return DataReturn(MyLang('operate_success'), 0);
    }
}
?>