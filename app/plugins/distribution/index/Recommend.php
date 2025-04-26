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
namespace app\plugins\distribution\index;

use app\service\SeoService;
use app\plugins\distribution\index\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\RecommendService;

/**
 * 分销 - 推荐宝
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Recommend extends Common
{
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
        parent::__construct($params);

        // 详情页面不需要登录
        if($this->plugins_action_name != 'detail')
        {
            // 是否已经登录
            IsUserLogin();
        }
    }

    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 浏览器名称
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('推荐宝 - 我的分销', 1));
        return MyView('../../../plugins/distribution/view/index/recommend/index');
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-04
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        // 获取数据
        $ret = RecommendService::RecommendData($params);
        MyViewAssign('data', $ret['data']);
        if(!empty($ret['data']))
        {
            // seo
            $seo_title = empty($ret['data']['seo_title']) ? $ret['data']['title'] : $ret['data']['seo_title'];
            MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($seo_title, 2));
            if(!empty($ret['data']['seo_keywords']))
            {
                MyViewAssign('home_seo_site_keywords', $ret['data']['seo_keywords']);
            }
            $seo_desc = empty($ret['data']['seo_desc']) ? (empty($ret['data']['describe']) ? '' : $ret['data']['describe']) : $ret['data']['seo_desc'];
            if(!empty($seo_desc))
            {
                MyViewAssign('home_seo_site_description', $seo_desc);
            }
        }
        return MyView('../../../plugins/distribution/view/index/recommend/detail');
    }

    /**
     * 添加编辑页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        // 基础数据
        $goods_data_value = empty($this->data_detail['detail_list']) ? '' : urlencode(base64_encode(json_encode(array_map(function($item){
            unset($item['goods']);
            return $item;
        }, $this->data_detail['detail_list']), JSON_UNESCAPED_UNICODE)));
        MyViewAssign('goods_data_value', $goods_data_value);

        // 编辑器文件存放地址定义
        MyViewAssign('editor_path_type', 'plugins_distribution-user_recommend-'.$this->user['id']);
        return MyView('../../../plugins/distribution/view/index/recommend/saveinfo');
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-02
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Save($params = [])
    {
        $params['user_type'] = 'user';
        $params['user_id'] = $this->user['id'];
        $params['plugins_config'] = $this->plugins_config;
        return RecommendService::RecommendSave($params);
    }

    /**
     * 状态更新
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Statusupdate($params = [])
    {
        $params['user_type'] = 'user';
        $params['user_id'] = $this->user['id'];
        return RecommendService::RecommendStatusupdate($params);
    }

    /**
     * 删除
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Delete($params = [])
    {
        $params['user_type'] = 'user';
        $params['user_id'] = $this->user['id'];
        return RecommendService::RecommendDelete($params);
    }

    /**
     * 商品搜索
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsSearch($params = [])
    {
        return BaseService::BaseGoodsSearchList($params);
    }
}
?>