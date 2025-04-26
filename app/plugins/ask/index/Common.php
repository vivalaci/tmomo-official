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
namespace app\plugins\ask\index;

use app\plugins\ask\service\BaseService;
use app\plugins\ask\service\AskGoodsService;

/**
 * 问答 - 公共
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2019-02-07T08:21:54+0800
 */
class Common
{
    // 公共属性参数数据
    protected $props_params;

    // 插件配置
    protected $plugins_config;
    protected $plugins_application_name;
    protected $plugins_user_center_left_name;
    protected $plugins_ask_main_name;

    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct($params = [])
    {
        // 公共属性参数数据
        $this->props_params = $params;

        // 视图初始化
        $this->ViewInit();
    }

    /**
     * 属性读取处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2024-04-23
     * @desc    description
     * @param   [string]          $name [属性名称]
     * @return  [mixed]                 [属性的数据]
     */
    public function __get($name)
    {
        return (!empty($this->props_params) && is_array($this->props_params) && isset($this->props_params[$name])) ? $this->props_params[$name] : null;
    }

    /**
     * 视图初始化
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:30:06+0800
     */
    public function ViewInit()
    {
        // 插件配置
        $base = BaseService::BaseConfig();
        $this->plugins_config = $base['data'];

        // 主导航名称
        $this->plugins_application_name = (empty($this->plugins_config['application_name']) ? '问答' : $this->plugins_config['application_name']).' - '.MyC('home_seo_site_title');

        // 用户中心入口名称
        $this->plugins_user_center_left_name = empty($this->plugins_config['user_center_left_name']) ? '提问/问答' : $this->plugins_config['user_center_left_name'];

        // 主名称
        $this->plugins_ask_main_name = empty($this->plugins_config['ask_main_name']) ? '问答' : $this->plugins_config['ask_main_name'];

        // 商品数据
        $goods = AskGoodsService::RecommendGoodsList();

        MyViewAssign([
            // 基础信息
            'plugins_config'                 => $this->plugins_config,
            'plugins_application_name'       => $this->plugins_application_name,
            'plugins_user_center_left_name'  => $this->plugins_user_center_left_name,
            'plugins_ask_main_name'          => $this->plugins_ask_main_name,
            // 表情
            'emoji_list'                     => BaseService::EmojiList(),
            // 头部数据
            'header_data'                    => BaseService::HeaderData($this->plugins_config),
            // 商品数据
            'recommend_goods_list'           => $goods['data']['goods'],
            // 热门问答
            'hot_ask_list'                   => BaseService::AskHot($this->plugins_config),
            // 推荐问答
            'recommend_ask_list'             => BaseService::AskRecommend($this->plugins_config),
        ]);
    }
}
?>