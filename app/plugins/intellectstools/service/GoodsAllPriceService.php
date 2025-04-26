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
use app\service\GoodsService;
use app\plugins\intellectstools\service\BaseService;

/**
 * 智能工具箱 - 商品批量调价服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class GoodsAllPriceService
{
    /**
     * 批量调价
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GoodsAllPriceEdit($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'isset',
                'key_name'          => 'modify_price_type',
                'error_msg'         => '请选择价格类型',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'modify_rules',
                'checked_data'      => array_column(BaseService::$modify_price_rules_list, 'value'),
                'error_msg'         => '请选择调整规则',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => 'modify_value',
                'error_msg'         => '请填写调整值、最大数10000000',
            ],
            [
                'checked_type'      => 'min',
                'key_name'          => 'modify_value',
                'checked_data'      => 0,
                'error_msg'         => '调整值不能小于0',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 条件至少选择一个
        if(empty($params['goods_ids']) && empty($params['category_ids']) && empty($params['brand_ids']))
        {
            return DataReturn('请至少选择一个条件', -1);
        }

        // 数据保存
        return self::GoodsAllPriceEditSave($params);
    }

    /**
     * 批量调价保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GoodsAllPriceEditSave($params = [])
    {
        $data = [
            'goods_ids'              => empty($params['goods_ids']) ? '' : json_encode(explode(',', $params['goods_ids'])),
            'category_ids'           => empty($params['category_ids']) ? '' : json_encode(explode(',', $params['category_ids'])),
            'brand_ids'              => empty($params['brand_ids']) ? '' : json_encode(explode(',', $params['brand_ids'])),
            'modify_price_type'      => (isset($params['modify_price_type']) && $params['modify_price_type'] != '') ? json_encode(explode(',', $params['modify_price_type'])) : '',
            'modify_rules'           => isset($params['modify_rules']) ? $params['modify_rules'] : '',
            'modify_value'           => isset($params['modify_value']) ? floatval($params['modify_value']) : 0,
            'crontab_restore_rules'  => isset($params['crontab_restore_rules']) ? $params['crontab_restore_rules'] : '',
            'crontab_restore_value'  => isset($params['crontab_restore_value']) ? floatval($params['crontab_restore_value']) : 0,
            'crontab_password'       => empty($params['crontab_password']) ? '' : $params['crontab_password'],
        ];
        $info = Db::name('PluginsIntellectstoolsGoodsModifyPrice')->where('id', '>', 0)->find();
        if(empty($info))
        {
            $data['add_time'] = time();
            $id = Db::name('PluginsIntellectstoolsGoodsModifyPrice')->insertGetId($data);
            if($id <= 0)
            {
                return DataReturn('配置保存失败', -1);
            }
            $data['id'] = $id;
        } else {
            $data['upd_time'] = time();
            if(Db::name('PluginsIntellectstoolsGoodsModifyPrice')->where(['id'=>$info['id']])->update($data) === false)
            {
                return DataReturn('配置更新失败', -1);
            }
            $data['id'] = $info;
        }
        return DataReturn('保存成功', 0);
    }

    /**
     * 批量调价数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-18
     * @desc    description
     */
    public static function GoodsAllPriceEditData()
    {
        return self::GoodsAllPriceEditDataHandle(Db::name('PluginsIntellectstoolsGoodsModifyPrice')->where('id', '>', 0)->find());
    }

    /**
     * 批量调价数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-17
     * @desc    description
     * @param   [array]          $data   [配置数据]
     * @param   [array]          $params [输入参数]
     */
    public static function GoodsAllPriceEditDataHandle($data, $params = [])
    {
        if(!empty($data) && is_array($data))
        {
            $json_arr = ['goods_ids', 'category_ids', 'brand_ids', 'modify_price_type'];
            foreach($data as $k=>$v)
            {
                // 数组字段
                if(in_array($k, $json_arr))
                {
                    $data[$k] = empty($v) ? [] : json_decode($v, true);
                }
            }
        }
        return $data;
    }

    /**
     * 处理修改
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-18
     * @desc    description
     * @param   [array]           $params             [输入参数]
     * @param   [array]           $config             [配置数据]
     * @param   [string]          $modify_rules_field [规格类型字段]
     * @param   [string]          $modify_value_field [规格值字段]
     */
    public static function GoodsAllPriceEditExecute($params = [], $config = [], $modify_rules_field = 'modify_rules', $modify_value_field = 'modify_value')
    {
        // 未指定数据则读取
        if(empty($config))
        {
            $config = self::GoodsAllPriceEditData();
        }

        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'modify_price_type',
                'error_msg'         => '价格类型未配置',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => $modify_rules_field,
                'checked_data'      => array_column(BaseService::$modify_price_rules_list, 'value'),
                'error_msg'         => '('.$modify_rules_field.')规则范围值有误',
            ],
            [
                'checked_type'      => 'isset',
                'key_name'          => $modify_value_field,
                'error_msg'         => '请填写调整值、最大数10000000',
            ],
            [
                'checked_type'      => 'min',
                'key_name'          => $modify_value_field,
                'checked_data'      => 0,
                'error_msg'         => '('.$modify_value_field.')调整值不能小于0',
            ],
        ];
        $ret = ParamsChecked($config, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 密码是否正确
        if(empty($params['password']) || $params['password'] != $config['crontab_password'])
        {
            return DataReturn('访问密码错误', -1);
        }

        // 条件
        $config['field'] = 'goods_id';
        $data = BaseService::GoodsWhere($config);
        if(empty($data['goods_ids']))
        {
            return DataReturn('没有需要调整的商品', -1);
        }

        // 捕获异常
        Db::startTrans();
        try {
            // 根据价格类型字段循环处理
            foreach($config['modify_price_type'] as $v)
            {
                // 价格字段
                $field = BaseService::$price_type_list[$v]['field'];

                // 操作符号
                $opt = BaseService::$modify_price_rules_list[$config[$modify_rules_field]]['type'];

                // 调整值
                $modify_value = floatval($config[$modify_value_field]);

                // 批量处理
                $where = $data['where'];
                // 排除0元数据
                if(in_array($opt, ['-', '/', '*']))
                {
                    $where[] = ['price', '>', 0];
                }
                // 调整值
                $value = ($opt == 'fixed') ? $modify_value : '`'.$field.'`'.$opt.$modify_value;
                $res = Db::name('GoodsSpecBase')->where($where)->exp($field, $value)->update();
                if(!$res)
                {
                    throw new \Exception('价格规格操作失败、影响('.$res.')项');
                }

                // 避免小于0的金额
                $where = $data['where'];
                $where[] = [$field, '<', 0];
                Db::name('GoodsSpecBase')->where($where)->update([$field=>0]);

                // 批量更新基础信息
                foreach($data['goods_ids'] as $gid)
                {
                    $ret = GoodsService::GoodsSaveBaseUpdate($gid);
                    if($ret['code'] != 0)
                    {
                        throw new \Exception($ret['msg']);
                    }
                }
            }

            // 完成
            Db::commit();
            return DataReturn(MyLang('operate_success'), 0);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }
}
?>