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
namespace app\plugins\Themestyle\service;

use think\facade\Db;
use app\service\ResourcesService;

/**
 * 默认主题样式 - 配置服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class ThemeStyleConfigService
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
    public static function ConfigList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'sort desc, id desc' : trim($params['order_by']);
        $data = Db::name('PluginsThemestyleConfig')->field($field)->where($where)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::ConfigListHandle($data));
    }

    /**
     * 列表数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-06-13
     * @desc    description
     * @param   [array]          $data   [列表数据]
     * @param   [array]          $params [输入参数]
     */
    public static function ConfigListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                if(array_key_exists('config', $v))
                {
                    $v['config'] = empty($v['config']) ? '' : json_decode($v['config'], true);
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
    public static function ConfigSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '1,60',
                'error_msg'         => MyLang('service.config.form_item_name_message'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'sort',
                'checked_data'      => 3,
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
        $data_fields = ['icon'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 配置信息
        $config = empty($params['config']) ? '' : (is_array($params['config']) ? json_encode($params['config'], JSON_UNESCAPED_UNICODE) : $params['config']);
        
        // 操作数据
        $is_enable = isset($params['is_enable']) ? intval($params['is_enable']) : 0;
        $is_default = isset($params['is_default']) ? intval($params['is_default']) : 0;
        $data = [
            'name'              => $params['name'],
            'icon'              => $attachment['data']['icon'],
            'config'            => $config,
            'sort'              => intval($params['sort']),
            'is_enable'         => $is_enable,
            'is_default'        => $is_default,
        ];

        // 捕获异常
        Db::startTrans();
        try {
            // 默认地址处理
            if($is_default == 1)
            {
                Db::name('PluginsThemestyleConfig')->where(['is_default'=>1])->update(['is_default'=>0]);
            }

            // 添加/更新数据
            if(empty($params['id']))
            {
                $data['add_time'] = time();
                if(Db::name('PluginsThemestyleConfig')->insertGetId($data) <= 0)
                {
                    throw new \Exception(MyLang('insert_fail'));
                }
            } else {
                $data['upd_time'] = time();
                if(!Db::name('PluginsThemestyleConfig')->where(['id'=>intval($params['id'])])->update($data))
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
    public static function ConfigDelete($params = [])
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
        if(Db::name('PluginsThemestyleConfig')->where(['id'=>$params['ids']])->delete())
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
    public static function ConfigStatusUpdate($params = [])
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

        // 捕获异常
        Db::startTrans();
        try {
            // 先去掉默认数据
            if($params['field'] == 'is_default' && $params['state'] == 1)
            {
                Db::name('PluginsThemestyleConfig')->where(['is_default'=>1])->update(['is_default'=>0, 'upd_time'=>time()]);
            }

            // 数据更新
            if(!Db::name('PluginsThemestyleConfig')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
            {
                throw new \Exception(MyLang('update_fail'));
            }
            Db::commit();
            return DataReturn(MyLang('operate_success'), 0);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }
}
?>