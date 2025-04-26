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
namespace app\plugins\invoice\api;

use app\module\FormTableHandleModule;
use app\plugins\invoice\api\Common;
use app\plugins\invoice\service\InvoiceService;
use app\plugins\invoice\service\BaseService;

/**
 * 发票 - 开票管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class User extends Common
{
    /**
     * 用户中心
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Center($params = [])
    {
        $nav = BaseService::$invoice_status_list;
        array_unshift($nav, ['id'=>-1, 'name'=>MyLang('all_title')]);
        $result = [
            'base'  => $this->plugins_config,
            'nav'   => $nav,
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 数据列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        return DataReturn('success', 0, FormModuleData($params));
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        $result = FormModuleData($params);
        if(empty($result) || empty($result['data']))
        {
            return DataReturn(MyLang('no_data'), -1);
        }
        return DataReturn('success', 0, $result);
    }

    /**
     * 编辑页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        $data = $this->data_detail;
        if(empty($params['id']))
        {
            if(isset($this->plugins_config['is_app_invoice_default_last_info']) && $this->plugins_config['is_app_invoice_default_last_info'] == 1)
            {
                $data_params = [
                    'where' => [
                        ['user_id', '=', intval($this->user['id'])],
                    ],
                    'n'     => 1,
                ];
                $ret = InvoiceService::InvoiceList($data_params);
                $data = (empty($ret['data']) || empty($ret['data'][0])) ? [] : $ret['data'][0];
                unset($data['id']);
            }
        }

        // 基础数据计算
        $save_base_data = InvoiceService::InvoiceSaveBaseDataHandle(empty($data['id']) ? $params : $data);

        // 发票内容
        $invoice_content_list = empty($this->plugins_config['invoice_content_type']) ? [] : $this->plugins_config['invoice_content_type'];

        // 可选发票类型
        $can_invoice_type_list = BaseService::CanInvoiceTypeList(empty($this->plugins_config['can_invoice_type']) ? [] : $this->plugins_config['can_invoice_type']);

        // 返回数据
        $result = [
            'base'                  => $this->plugins_config,
            'data'                  => $data,
            'save_base_data'        => $save_base_data,
            'apply_type_list'       => BaseService::$apply_type_list,
            'invoice_content_list'  => $invoice_content_list,
            'can_invoice_type_list' => $can_invoice_type_list,
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params['user'] = $this->user;
        return InvoiceService::InvoiceSave($params);
    }

    /**
     * 删除
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Delete($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params['user'] = $this->user;
        return InvoiceService::InvoiceDelete($params);
    }
}
?>