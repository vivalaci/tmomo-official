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
namespace app\plugins\multilingual;

use app\service\MultilingualService;
use app\plugins\multilingual\service\BaseService;
use app\plugins\multilingual\service\TranslateService;

/**
 * 多语言 - 钩子入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-11T21:51:08+0800
 */
class Hook
{
    // 基础属性
    public $module_name;
    public $controller_name;
    public $action_name;
    public $mca;

    private $pluginsname;
    private $pluginscontrol;
    private $pluginsaction;
    private $pca;

    // 插件配置
    public $plugins_config;

    // 当前选中的id
    private $multilingual_value;

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
        if(!empty($params['hook_name']))
        {
            // 当前模块/控制器/方法
            $this->module_name = RequestModule();
            $this->controller_name = RequestController();
            $this->action_name = RequestAction();
            $this->mca = $this->module_name.$this->controller_name.$this->action_name;

            // 插件
            $this->pluginsname = strtolower(MyInput('pluginsname'));
            $this->pluginscontrol = strtolower(MyInput('pluginscontrol', 'index'));
            $this->pluginsaction = strtolower(MyInput('pluginsaction', 'index'));
            $this->pca = $this->pluginsname.$this->pluginscontrol.$this->pluginsaction;

            // 配置信息
            $config = BaseService::BaseConfig();
            $this->plugins_config = empty($config['data']) ? [] : $config['data'];
   
            // 目标语言
            $this->multilingual_value = MultilingualService::GetUserMultilingualValue();

            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 公共翻译js
                case 'plugins_common_page_bottom' :
                case 'plugins_admin_common_page_bottom' :
                    if(isset($this->plugins_config['is_enable_frontend']) && $this->plugins_config['is_enable_frontend'] == 1)
                    {
                        $ret = $this->MultilingualViewPageBottomContent();
                    }
                    break;

                // 商品搜索条件处理
                case 'plugins_service_search_goods_list_where' :
                    if(!empty($this->plugins_config['is_search_auto_switch']) && $this->plugins_config['is_search_auto_switch'] == 1 && $this->module_name.$this->controller_name.$this->action_name == 'indexsearchgoodslist')
                    {
                        $this->GoodsSearchWhereHandle($params);
                    }
                    break;

                // 模板引擎数据渲染分配
                case 'plugins_view_assign_data' :
                case 'plugins_view_fetch_begin' :
                    if(isset($this->plugins_config['is_translate_web_assign']) && $this->plugins_config['is_translate_web_assign'] == 1)
                    {
                        TranslateService::DataTranslate($this->plugins_config, $this->multilingual_value, $params);
                    }
                    break;

                // api统一返回
                case 'plugins_service_api_data_return' :
                    // 输入参数
                    $temp_params = MyInput();

                    // 指定可以翻译的key
                    $lang_can_key = [];
                    if(!empty($temp_params) && !empty($temp_params['lang_can_key']))
                    {
                        $lang_can_key = is_array($temp_params['lang_can_key']) ? $temp_params['lang_can_key'] : explode(',', $temp_params['lang_can_key']);
                    }

                    // 公共接口数据
                    $is_base_api = isset($this->plugins_config['is_translate_web_ajax_base']) && $this->plugins_config['is_translate_web_ajax_base'] == 1;

                    // admin/index 异步接口
                    $is_web_api = isset($this->plugins_config['is_translate_web_ajax']) && $this->plugins_config['is_translate_web_ajax'] == 1 && in_array($this->module_name, ['admin', 'index']);

                    // 手机端
                    $is_translate_app = isset($this->plugins_config['is_translate_app']) && $this->plugins_config['is_translate_app'] == 1 && $this->module_name == 'api';

                    // api是否指定不翻译
                    if(!empty($temp_params) && isset($temp_params['is_lang']) && $temp_params['is_lang'] == 0)
                    {
                        $is_base_api = 0;
                        $is_web_api = 0;
                        $is_translate_app = 0;
                    }

                    if($is_base_api || $is_web_api || $is_translate_app)
                    {
                        // 是否仅翻译msg数据
                        $is_msg = isset($this->plugins_config['is_translate_web_ajax_msg']) && $this->plugins_config['is_translate_web_ajax_msg'] == 1;

                        // 是否翻译公共数据
                        $base_api = [
                            // 地区节点数据
                            'adminindexstats',
                            'adminregionnode',
                            'indexregionindex',
                        ];

                        // 插件
                        $plugins_api = [
                            'shopcenterstats',
                            'realstorecenterstats',
                        ];

                        // 非仅提示信息或是公共数据接口
                        if((!$is_msg && $is_web_api) || ($is_base_api && (in_array($this->mca, $base_api) || in_array($this->pca, $plugins_api))) || $is_translate_app)
                        {
                            // 是否指定可翻译的key
                            if(!empty($lang_can_key) && is_array($lang_can_key))
                            {
                                // 根据指定数据翻译
                                if(!empty($params['data']) && !empty($params['data']['data']) && is_array($params['data']['data']))
                                {
                                    foreach($params['data']['data'] as $k=>&$v)
                                    {
                                        if(in_array($k, $lang_can_key))
                                        {
                                            TranslateService::DataTranslate($this->plugins_config, $this->multilingual_value, $v);
                                        }
                                    }
                                }
                                // 描述
                                TranslateService::DataTranslate($this->plugins_config, $this->multilingual_value, $params['data']['msg']);
                            } else {
                                TranslateService::DataTranslate($this->plugins_config, $this->multilingual_value, $params);
                            }
                        } else {
                            // 仅翻译msg提示信息
                            if(!empty($params['data']) && !empty($params['data']['msg']))
                            {
                                TranslateService::DataTranslate($this->plugins_config, $this->multilingual_value, $params['data']['msg']);
                            }
                        }
                    }
                    break;

                // 模板引擎数据渲染结束
                case 'plugins_view_fetch_end' :
                    if(!empty($params['result']) && isset($this->plugins_config['is_translate_web_fetch_view']) && $this->plugins_config['is_translate_web_fetch_view'] == 1)
                    {
                        TranslateService::ViewTranslate($this->plugins_config, $this->multilingual_value, $params['result']);
                    }
                    break;
            }
            return $ret;
        }
    }

    /**
     * 商品搜索条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-01-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsSearchWhereHandle($params = [])
    {
        if(!empty($params['params']) && !empty($params['params']['wd']))
        {
            // 目标语言
            $to_value = BaseService::MultilingualToValue($this->plugins_config, $this->multilingual_value);
            if($to_value != 'zh')
            {
                $key = md5($params['params']['wd']);
                $result = $this->TranslateHandle($params['params']['wd'], $to_value);
                if(!empty($result) && !empty($result[$key]) && !empty($result[$key]['dst']))
                {
                    $params['params']['wd'] = $result[$key]['dst'];
                }
            }
        }
    }

    /**
     * 翻译js
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-11-01
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function MultilingualViewPageBottomContent($params = [])
    {
        // 获取语言列表
        $data = MultilingualService::MultilingualData();

        // 默认语言
        $multilingual_default_code = (empty($data['default']) || empty($data['default']['code'])) ? 'zh' : $data['default']['code'];

        // 实时监听时间
        $real_time_monitoring = empty($this->plugins_config['real_time_monitoring']) ? 0 : intval($this->plugins_config['real_time_monitoring'])*1000;

        // 停留页面翻译接口最多请求次数
        $stop_page_request_number = empty($this->plugins_config['stop_page_request_number']) ? 0 : intval($this->plugins_config['stop_page_request_number']);

        // 接口地址
        $request_url = $this->module_name == 'admin' ? PluginsAdminUrl("multilingual", "index", "fanyi") : PluginsHomeUrl("multilingual", "index", "fanyi");

        MyViewAssign('request_url', $request_url);
        MyViewAssign('stop_page_request_number', $stop_page_request_number);
        MyViewAssign('real_time_monitoring', $real_time_monitoring);
        MyViewAssign('multilingual_default', $data['default']);
        MyViewAssign('multilingual_default_code', $multilingual_default_code);
        return MyView('../../../plugins/multilingual/view/public/content');
    }
}
?>