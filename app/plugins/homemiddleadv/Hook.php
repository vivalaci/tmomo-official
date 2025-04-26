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
namespace app\plugins\homemiddleadv;

use app\plugins\homemiddleadv\service\BaseService;

/**
 * 首页中间广告插件 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Hook
{
    // 模块、控制器、方法
    private $module_name;
    private $controller_name;
    private $action_name;

    // 配置信息
    private $plugins_config;

    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-09T14:25:44+0800
     * @param    [array]                    $params [输入参数]
     */
    public function handle($params = [])
    {
        if(!empty($params['hook_name']))
        {
            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();
            $mca = $this->module_name.$this->controller_name.$this->action_name;

            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];

            $ret = '';
            switch($params['hook_name'])
            {
                // style css
                case 'plugins_common_header' :
                    if($mca == 'indexindexindex')
                    {
                        $ret = $this->StyleCss($params);
                    }
                    break;

                // 楼层数据上面
                case 'plugins_view_home_floor_top' :
                    $ret = $this->HomeFloorTopAdv($params);
                    break;

                // 首页接口数据
                case 'plugins_service_base_data_return_api_index_index' :
                    $ret = $this->IndexResultHandle($params);
                    break;
                    
            }
            return $ret;
        }
    }

    /**
     * 首页接口数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function IndexResultHandle($params = [])
    {
        $data = BaseService::HomeDataList($this->plugins_config);
        if(!empty($data))
        {
            $params['data']['plugins_homemiddleadv_data'] = $data;
        }
    }

    /**
     * 首页楼层顶部广告
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-06T16:16:34+0800
     * @param    [array]          $params [输入参数]
     */
    public function HomeFloorTopAdv($params = [])
    {
        // 获取图片列表
        $data = BaseService::HomeDataList($this->plugins_config);
        if(!empty($data))
        {
            return MyView('../../../plugins/homemiddleadv/view/index/public/content', [
                'data_list' => $data,
            ]);
        }
        return '';
    }

    /**
     * css
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-06T16:16:34+0800
     * @param    [array]          $params [输入参数]
     */
    public function StyleCss($params = [])
    {
        return '<style type="text/css">
                    .plugins-homemiddleadv-home-adv {
                        margin-top: 0rem;
                        margin-bottom: 0.6rem;
                    }
                    .plugins-homemiddleadv-home-adv .am-gallery-overlay>li {
                        padding: 0;
                    }
                    .plugins-homemiddleadv-home-adv .am-gallery-overlay .am-gallery-item {
                        margin: 1.2rem 0.6rem 0 0.6rem;
                    }
                    @media only screen and (min-width:1025px) {
                        .plugins-homemiddleadv-home-adv ul.am-gallery {
                            width: calc(100% + 1.2rem);
                            margin-left: -0.6rem;
                        }
                    }
                </style>';
    }
}
?>