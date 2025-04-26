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
namespace app\plugins\searchrightad;

use app\plugins\searchrightad\service\BaseService;

/**
 * 搜索右侧广告 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-11T21:51:08+0800
 */
class Hook
{
    /**
     * 应用响应入口
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-11T14:25:44+0800
     * @param    [array]          $params [输入参数]
     */
    public function handle($params = [])
    {
        // 钩子名称
        $ret = '';
        if(!empty($params['hook_name']))
        {
            switch($params['hook_name'])
            {
                // css
                case 'plugins_common_header' :
                    $ret = $this->Css($params);
                    break;

                // 搜索右侧
                case 'plugins_view_common_search_right' :
                    $ret = $this->SearchRight($params);
                    break;
            }
        }
        return $ret;
    }

    /**
     * 内容
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    private function SearchRight($params = [])
    {
        // 配置
        $base = BaseService::BaseConfig();

        // 图片
        if(empty($base['data']['images']))
        {
            return '';
        }

        // 开始时间
        if(!empty($base['data']['time_start']))
        {
            if(strtotime($base['data']['time_start']) > time())
            {
                return '';
            }
        }

        // 结束时间
        if(!empty($base['data']['time_end']))
        {
            if(strtotime($base['data']['time_end']) < time())
            {
                return '';
            }
        }

        // 当前模块/控制器/方法
        $module_name = RequestModule();
        $controller_name = RequestController();
        $action_name = RequestAction();

        // 是否仅首页
        if(isset($base['data']['is_home']) && $base['data']['is_home'] == 1 && $module_name.$controller_name.$action_name != 'indexindexindex')
        {
            return '';
        }

        // 链接地址
        $url_new_window = (isset($base['data']['is_url_new_window']) && $base['data']['is_url_new_window'] == 1) ? ' target="_blank"' : '';
        $url = empty($base['data']['url']) ? 'href="javascript:;"' : 'href="'.$base['data']['url'].'"'.$url_new_window;
        return '<a '.$url.' class="plugins-searchrightad-images"><img src="'.$base['data']['images'].'" /></a>';
    }

    /**
     * css
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Css($params = [])
    {
        return '<style type="text/css">
                    .plugins-searchrightad-images {
                        position: absolute;
                        top: -12px;
                        margin-left: 8%;
                        display: inline-block;
                    }
                    .plugins-searchrightad-images img {
                        height: 115px;
                        border: 0;
                        display: block;
                    }
                    @media only screen and (max-width:1000px) {
                        .plugins-searchrightad-images {
                            margin-left: 5%;
                        }
                        .plugins-searchrightad-images img {
                            height: 76px;
                        }
                    }
                    @media only screen and (max-width:640px) {
                        .plugins-searchrightad-images {
                            display: none;
                        }
                    }
                </style>';
    }
}
?>