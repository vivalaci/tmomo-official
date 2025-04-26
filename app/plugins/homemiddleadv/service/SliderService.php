<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\plugins\homemiddleadv\service;

use app\service\ResourcesService;
use app\plugins\homemiddleadv\service\BaseService;

/**
 * 数据管理服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class SliderService
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
    public static function SliderList($params = [])
    {
        $ret = BaseService::BaseConfig();
        $data = (empty($ret['data']) || empty($ret['data']['data_list'])) ? [] : $ret['data']['data_list'];
        if(!empty($data))
        {
            $common_platform_type = MyConst('common_platform_type');
            $module = RequestModule();
            foreach($data as &$v)
            {
                // 所属平台
                $platform_name = [];
                $v['platform'] = empty($v['platform']) ? [] : (is_array($v['platform']) ? $v['platform'] : json_decode($v['platform'], true));
                if(!empty($v['platform']))
                {
                    if(!empty($common_platform_type) && is_array($common_platform_type))
                    {
                        foreach($common_platform_type as $pv)
                        {
                            if(in_array($pv['value'], $v['platform']))
                            {
                                $platform_name[] = $pv['name'];
                            }
                        }
                    } else {
                        $platform_name = $v['platform'];
                    }
                }
                $v['platform_name'] = empty($platform_name) ? '' : implode('，', $platform_name);

                // 图片地址、这里兼容老版本图片地址images_url
                $v['images'] = ResourcesService::AttachmentPathViewHandle(empty($v['images']) ? (empty($v['images_url']) ? '' : $v['images_url']) : $v['images']);

                // url地址处理
                $v['url'] = empty($v['url']) ? [] : (is_array($v['url']) ? $v['url'] : json_decode($v['url'], true));
                if($module != 'admin')
                {
                    if(!empty($v['url']) && is_array($v['url']) && array_key_exists(APPLICATION_CLIENT_TYPE, $v['url']))
                    {
                        $v['url'] = $v['url'][APPLICATION_CLIENT_TYPE];
                    } else {
                        $v['url'] = '';
                    }
                }
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
    public static function SliderSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '2,60',
                'error_msg'         => '名称长度 2~60 个字符',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'images',
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
        $data_fields = ['images'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'name'                => $params['name'],
            'platform'            => empty($params['platform']) ? '' : json_encode(explode(',', $params['platform'])),
            'url'                 => empty($params['url']) ? '' : json_encode($params['url']),
            'images'              => $attachment['data']['images'],
            'is_enable'           => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
            'is_new_window_open'  => isset($params['is_new_window_open']) ? intval($params['is_new_window_open']) : 0,
            'operation_time'      =>  date('Y-m-d H:i:s', time()),
        ];

        // 原有数据
        $ret = BaseService::BaseConfig();

        // 数据id
        $data['id'] = (empty($params['id']) || empty($ret['data']) || empty($ret['data']['data_list'][$params['id']])) ? date('YmdHis').GetNumberCode(6) : $params['id'];
        $ret['data']['data_list'][$data['id']] = $data;

        // 保存
        return BaseService::BaseConfigSave($ret['data']);
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
    public static function SliderDelete($params = [])
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

        // 原有数据
        $ret = BaseService::BaseConfig();
        $ret['data']['data_list'] = (empty($ret['data']) || empty($ret['data']['data_list'])) ? [] : $ret['data']['data_list'];

        // 删除操作
        if(!empty($ret['data']['data_list']))
        {
            foreach($ret['data']['data_list'] as $k=>$v)
            {
                if(in_array($k, $params['ids']))
                {
                    unset($ret['data']['data_list'][$k]);
                }
            }
        }
        
        // 保存
        return BaseService::BaseConfigSave($ret['data']);
    }

    /**
     * 数据列表状态更新
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function SliderStatusUpdate($params = [])
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
        $ret = BaseService::BaseConfig();
        $ret['data']['data_list'] = (empty($ret['data']) || empty($ret['data']['data_list'])) ? [] : $ret['data']['data_list'];

        // 删除操作
        if(isset($ret['data']['data_list'][$params['id']]) && isset($ret['data']['data_list'][$params['id']][$params['field']]))
        {
            $ret['data']['data_list'][$params['id']][$params['field']] = intval($params['state']);
            $ret['data']['data_list'][$params['id']]['operation_time'] = time();
        }
        
        // 保存
        return BaseService::BaseConfigSave($ret['data']);
    }
}
?>