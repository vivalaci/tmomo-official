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
namespace app\plugins\exchangerate\service;

use think\facade\Db;
use app\service\ResourcesService;

/**
 * 汇率 - 货币配置服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class CurrencyService
{
    /**
     * 数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function CurrencyList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'sort desc, id desc' : trim($params['order_by']);
        $data = Db::name('PluginsExchangerateCurrency')->field($field)->where($where)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::DataHandle($data));
    }

    /**
     * 数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-18
     * @desc    description
     * @param   [array]          $data [仓库数据]
     */
    public static function DataHandle($data)
    {
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                // 图标
                if(array_key_exists('icon', $v))
                {
                    $v['icon'] = ResourcesService::AttachmentPathViewHandle($v['icon']);
                }

                // 时间
                if(array_key_exists('add_time', $v))
                {
                    $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                if(array_key_exists('upd_time', $v))
                {
                    $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
                }
            }
        }
        return $data;
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-07
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CurrencySave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '1,60',
                'error_msg'         => '请填写名称、最多60个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'code',
                'checked_data'      => '1,30',
                'error_msg'         => '请填写代码、最多30个字符',
            ],
            [
                'checked_type'      => 'unique',
                'key_name'          => 'code',
                'checked_data'      => 'PluginsExchangerateCurrency',
                'checked_key'       => 'id',
                'error_msg'         => '代码已存在[{$var}]',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'symbol',
                'checked_data'      => '1,30',
                'error_msg'         => '请填写符号、最多30个字符',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'rate',
                'error_msg'         => '汇率不能为空',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'sort',
                'checked_data'      => '3',
                'error_msg'         => MyLang('form_sort_message'),
            ],
            [
                'checked_type'      => 'max',
                'key_name'          => 'sort',
                'checked_data'      => 255,
                'error_msg'         => MyLang('form_sort_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 附件
        $attachment = ResourcesService::AttachmentParams($params, ['icon']);
        
        // 操作数据
        $is_enable = isset($params['is_enable']) ? intval($params['is_enable']) : 0;
        $is_default = isset($params['is_default']) ? intval($params['is_default']) : 0;
        $data = [
            'icon'        => $attachment['data']['icon'],
            'name'        => $params['name'],
            'code'        => $params['code'],
            'symbol'      => $params['symbol'],
            'rate'        => PriceNumberFormat($params['rate'], 6),
            'sort'        => intval($params['sort']),
            'is_enable'   => $is_enable,
            'is_default'  => $is_default,
        ];

        // 捕获异常
        Db::startTrans();
        try {
            // 默认地址处理
            if($is_default == 1)
            {
                Db::name('PluginsExchangerateCurrency')->where(['is_default'=>1])->update(['is_default'=>0]);
            }

            // 添加/更新数据
            if(empty($params['id']))
            {
                $data['add_time'] = time();
                if(Db::name('PluginsExchangerateCurrency')->insertGetId($data) <= 0)
                {
                    throw new \Exception(MyLang('insert_success'));
                }
            } else {
                $data['upd_time'] = time();
                if(Db::name('PluginsExchangerateCurrency')->where(['id'=>intval($params['id'])])->update($data) === false)
                {
                    throw new \Exception(MyLang('update_fail'));
                }
            }

            Db::commit();
            return DataReturn(MyLang('operate_success'), 0); 
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CurrencyDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn(MyLang('data_id_error_tips'), -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 删除操作
        if(Db::name('PluginsExchangerateCurrency')->where(['id'=>$params['ids']])->delete())
        {
            return DataReturn(MyLang('delete_success'), 0);
        }
        return DataReturn(MyLang('delete_fail'), -100);
    }

    /**
     * 状态更新
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function CurrencyStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => MyLang('operate_field_error_tips'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => MyLang('form_status_range_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据更新
        if(Db::name('PluginsExchangerateCurrency')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
            return DataReturn(MyLang('edit_success'), 0);
        }
        return DataReturn(MyLang('edit_fail'), -100);
    }
}
?>