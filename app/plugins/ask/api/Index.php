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
namespace app\plugins\ask\api;

use app\plugins\ask\api\Common;
use app\plugins\ask\service\BaseService;
use app\plugins\ask\service\AskService;
use app\plugins\ask\service\AskGoodsService;
use app\plugins\ask\service\AskCommentsService;
use app\plugins\ask\service\SliderService;

/**
 * 问答 - api独立页面入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
{
    /**
     * 首页
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-07
     * @desc    description
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        $result = [
            // 配置
            'base'             => $this->plugins_config,
            // 幻灯片
            'slider_list'      => SliderService::ClientSliderList(),
            // 搜索tab
            'search_tab_list'  => BaseService::SearchTabList($params),
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 搜索数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-11
     * @desc    description
     * @param    [array]          $params [输入参数]
     */
    public function DataList($params = [])
    {
        // 获取搜索数据
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        $where = BaseService::AskListWhere($params);
        $page_size = $this->page_size;

        // 是否自定义数量
        if(empty($params['page_size']) && !empty($this->plugins_config['search_page_number']))
        {
            $page_size = intval($this->plugins_config['search_page_number']);
        }

        // 获取总数
        $total = AskService::AskTotal($where);
        $page_total = ceil($total/$page_size);
        $start = intval(($this->page-1)*$page_size);

        // 获取列表
        $data = [];
        if($total > 0)
        {
            $params['m'] = $start;
            $params['n'] = $page_size;
            $params['where'] = $where;
            $ret = BaseService::AskList($params);
            $data = $ret['data'];
        }

        // 返回数据
        $result = [
            'total'          => $total,
            'page_total'     => $page_total,
            'data'           => $data,
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 详情入口
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-07
     * @desc    description
     * @param    [array]          $params [输入参数]
     */
    public function Detail($params = [])
    {
        $params['is_goods'] = 1;
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        $ret = BaseService::AskRow($params);
        if($ret['code'] == 0)
        {
            // 增加浏览次数
            AskService::AskAccessCountInc(['id'=>$ret['data']['id']]);

            // 推荐商品
            $goods = AskGoodsService::RecommendGoodsList();

            // 返回数据
            $result = [
                'base'        => $this->plugins_config,
                'data'        => $ret['data'],
                'goods'       => $goods['data']['goods'],
                'emoji_list'  => BaseService::EmojiList(),
            ];
            return DataReturn('success', 0, $result);
        }
        return $ret;
    }

    /**
     * 评论详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-28
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommentsInfo($params = [])
    {
        $params['is_comments'] = 1;
        $params['is_comments_list_info'] = 1;
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        $ret = BaseService::AskRow($params);
        if($ret['code'] == 0)
        {
            $result = [
                'base'          => $this->plugins_config,
                'data'          => $ret['data'],
                'emoji_list'    => BaseService::EmojiList(),
            ];
            return DataReturn('success', 0, $result);
        }
        return $ret;
    }

    /**
     * 问答点赞
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-19
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GiveThumbs($params = [])
    {
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return AskCommentsService::AskGiveThumbs($params);
    }

    /**
     * 问答评论
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-19
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Comments($params = [])
    {
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return AskCommentsService::AskComments($params);
    }

    /**
     * 问答回复数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-19
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommentsReplyList($params = [])
    {
        $params['is_comments_reply'] = empty($params['ask_comments_id']) ? 1 : 0;
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        $ret = AskCommentsService::AskCommentsReplyList($params);
        $ret['data']['base'] = $this->plugins_config;
        return $ret;
    }
}
?>