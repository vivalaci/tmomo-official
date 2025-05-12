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
namespace app\plugins\footercustomerservice\service;

use app\service\PluginsService;
use app\service\ResourcesService;

/**
 * 底部客户服务介绍服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Service
{
    /**
     * 获取数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function DataList($params = [])
    {
        $ret = PluginsService::PluginsData('footercustomerservice');
        $data = (empty($ret['data']) || empty($ret['data']['data_list'])) ? [] : $ret['data']['data_list'];

        if(!empty($data))
        {
            $common_is_enable_tips = MyConst('common_is_enable_tips');
            $common_is_text_list = MyConst('common_is_text_list');
            foreach($data as &$v)
            {
                // 是否启用
                $v['is_enable_text'] = isset($common_is_enable_tips[$v['is_enable']]) ? $common_is_enable_tips[$v['is_enable']]['name'] : '';

                // 图片地址
                $v['images_url_old'] = $v['images_url'];
                $v['images_url'] = ResourcesService::AttachmentPathViewHandle($v['images_url']);

                // 时间
                $v['operation_time_time'] = empty($v['operation_time']) ? '' : date('Y-m-d H:i:s', $v['operation_time']);
                $v['operation_time_date'] = empty($v['operation_time']) ? '' : date('Y-m-d', $v['operation_time']);
            }
        }

        // 是否读取单条
        if(!empty($params['get_id']) && isset($data[$params['get_id']]))
        {
            $data = $data[$params['get_id']];
        }

        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 数据列表保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function DataSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '1,8',
                'error_msg'         => '名称长度 1~8 个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'desc',
                'checked_data'      => '30',
                'error_msg'         => '描述格式最多 30 个字符',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'images_url',
                'checked_data'      => '255',
                'error_msg'         => MyLang('form_upload_images_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 附件
        $data_fields = ['images_url'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'name'                  => $params['name'],
            'desc'                  => $params['desc'],
            'images_url'            => $attachment['data']['images_url'],
            'is_enable'             => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
            'operation_time'        => time(),
        ];

        // 原有数据
        $ret = PluginsService::PluginsData('footercustomerservice', null, false);

        // 数据id
        $data['id'] = (empty($params['id']) || empty($ret['data']) || empty($ret['data']['data_list'][$params['id']])) ? date('YmdHis').GetNumberCode(6) : $params['id'];
        $ret['data']['data_list'][$data['id']] = $data;

        // 添加最多添加4条
        if(empty($params['id']))
        {
            if(count($ret['data']['data_list']) > 4)
            {
                return DataReturn('最多添加4条数据', -1);
            }
        }

        // 保存
        return PluginsService::PluginsDataSave(['plugins'=>'footercustomerservice', 'data'=>$ret['data']]);
    }

    /**
     * 数据列表删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function DataDelete($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 原有数据
        $ret = PluginsService::PluginsData('footercustomerservice', null, false);
        $ret['data']['data_list'] = (empty($ret['data']) || empty($ret['data']['data_list'])) ? [] : $ret['data']['data_list'];

        // 删除操作
        if(isset($ret['data']['data_list'][$params['id']]))
        {
            unset($ret['data']['data_list'][$params['id']]);
        }
        
        // 保存
        return PluginsService::PluginsDataSave(['plugins'=>'footercustomerservice', 'data'=>$ret['data']]);
    }

    /**
     * 数据列表删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function DataStatusUpdate($params = [])
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

        // 原有数据
        $ret = PluginsService::PluginsData('footercustomerservice', null, false);
        $ret['data']['data_list'] = (empty($ret['data']) || empty($ret['data']['data_list'])) ? [] : $ret['data']['data_list'];

        // 删除操作
        if(isset($ret['data']['data_list'][$params['id']]) && isset($ret['data']['data_list'][$params['id']][$params['field']]))
        {
            $ret['data']['data_list'][$params['id']][$params['field']] = intval($params['state']);
            $ret['data']['data_list'][$params['id']]['operation_time'] = time();
        }
        
        // 保存
        return PluginsService::PluginsDataSave(['plugins'=>'footercustomerservice', 'data'=>$ret['data']]);
    }
}
?>