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
namespace app\plugins\freightfee;

use think\facade\Db;
use app\service\PluginsService;
use app\plugins\freightfee\service\BaseService;

/**
 * 运费设置 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 配置信息
    private $plugins_config;

    // 模块、控制器、方法
    private $module_name;
    private $controller_name;
    private $action_name;
    private $mca;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-09T14:25:44+0800
     * @param    [array]          $params [输入参数]
     */
    public function handle($params = [])
    {
        if(!empty($params['hook_name']))
        {
            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();
            $this->mca = $this->module_name.$this->controller_name.$this->action_name;

            // 样式、js
            $is_style = in_array($this->mca, ['indexbuyindex', 'indexgoodsindex']);
            $is_js = in_array($this->mca, ['indexbuyindex']);

            $ret = '';
            switch($params['hook_name'])
            {
                // css
                case 'plugins_css' :
                    if($is_style)
                    {
                        $ret = 'static/plugins/freightfee/css/index/style.css';
                    }
                    break;

                // js
                case 'plugins_js' :
                    if($is_js)
                    {
                        $ret = 'static/plugins/freightfee/js/index/style.js';
                    }
                    break;

                // 运费计算
                case 'plugins_service_buy_group_goods_handle' :
                    BaseService::FreightFeeCalculateDataHandle($params['data'], $params);
                    break;

                // 商品免运费icon
                case 'plugins_view_goods_detail_title' :
                    $ret = $this->FreeShippingGoodsIcon($params);
                    break;

                // 购买确认页面运费选择
                case 'plugins_view_buy_group_goods_inside_extension_top' :
                    $ret = $this->BuyFreightfeeView($params);
                    break;

                // 购买提交订单页面隐藏域html
                case 'plugins_view_buy_form_inside' :
                    $ret = $this->BuyFormInsideInput($params);
                    break;
            }
            return $ret;
        }
    }

    /**
     * 购买提交订单页面隐藏域html
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function BuyFormInsideInput($params = [])
    {
        $ret = '';
        if(!empty($params['params']) && is_array($params['params']))
        {
            $ids = [];
            $key_field_first = 'freightfee_id_';
            foreach($params['params'] as $k=>$v)
            {
                if(substr($k, 0, strlen($key_field_first)) == $key_field_first)
                {
                    $key = str_replace($key_field_first, '', $k);
                    $ids[$key] = $v;
                }
            }
            if(!empty($ids))
            {
                foreach($ids as $k=>$v)
                {
                    $ret .= '<input type="hidden" name="'.$key_field_first.$k.'" value="'.$v.'" />';
                }
            }
        }
        return $ret;
    }

    /**
     * 购买确认页面运费选择
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-08-12
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function BuyFreightfeeView($params = [])
    {
        if(!empty($params['data']['plugins_freightfee_data']) && !empty($params['data']['plugins_freightfee_data']['fee_list']) && is_array($params['data']['plugins_freightfee_data']['fee_list']))
        {
            // return MyView('../../../plugins/freightfee/view/index/public/buy', [
            //     'fee_list'      => $params['data']['plugins_freightfee_data']['fee_list'],
            //     'warehouse_id'  => $params['data']['id'],
            // ]);
            
             $fee_list = $params['data']['plugins_freightfee_data']['fee_list'];
        
            // 获取配送方式
            $delivery_type = isset($params['data']['order_base']['site_model']) ? $params['data']['order_base']['site_model'] : 0;
            
            // 根据配送方式过滤运费列表
            if($delivery_type == 0) {
                // 发快递状态：排除第一条信息
                if(count($fee_list) > 1) {
                    array_shift($fee_list);
                }
            } else if($delivery_type == 2) {
                // 自提状态：只保留第一条信息
                $fee_list = [array_shift($fee_list)];
            }
            
            // 只有当列表不为空时才显示
            if(!empty($fee_list)) {
                return MyView('../../../plugins/freightfee/view/index/public/buy', [
                    'fee_list'      => $fee_list,
                    'warehouse_id'  => $params['data']['id'],
                ]);
            }
        }
        return '';
            
            
            
        
      
        
    }

    /**
     * 商品免运费icon
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-26
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    private function FreeShippingGoodsIcon($params = [])
    {
        $goods_ids = Db::name('PluginsFreightfeeTemplate')->where(['is_enable'=>1])->column('goods_ids');
        if(!empty($goods_ids) && !empty($params['goods_id']))
        {
            foreach($goods_ids as $ids)
            {
                if(!empty($ids))
                {
                    if(in_array($params['goods_id'], explode(',', $ids)))
                    {
                        return '<span class="am-badge am-badge-success-plain am-radius am-vertical-align-middle">免运费</span>';
                    }
                }
            }
        }
        return '';
    }    
}
?>