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
namespace app\plugins\invoice\index;

use app\service\SeoService;
use app\plugins\invoice\index\Common;
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
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('发票管理', 1));
        return MyView('../../../plugins/invoice/view/index/user/index');
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
        MyViewAssign([
            'is_header' => 0,
            'is_footer' => 0,
        ]);
        return MyView('../../../plugins/invoice/view/index/user/detail');
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
        // 数据
        $data = $this->data_detail;
        if(empty($params['id']))
        {
            if(isset($this->plugins_config['is_web_invoice_default_last_info']) && $this->plugins_config['is_web_invoice_default_last_info'] == 1)
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
        MyViewAssign('invoice_content_list', empty($this->plugins_config['invoice_content_type']) ? [] : $this->plugins_config['invoice_content_type']);

        // 可选发票类型
        $can_invoice_type_list = BaseService::CanInvoiceTypeList(empty($this->plugins_config['can_invoice_type']) ? [] : $this->plugins_config['can_invoice_type']);
        MyViewAssign('can_invoice_type_list', $can_invoice_type_list);

        // 静态数据
        MyViewAssign('apply_type_list', BaseService::$apply_type_list);

        // 数据
        MyViewAssign('data', $data);
        MyViewAssign('save_base_data', $save_base_data);
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('发票添加/编辑', 1));
        return MyView('../../../plugins/invoice/view/index/user/saveinfo');
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