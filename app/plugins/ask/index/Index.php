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

use app\service\SeoService;
use app\plugins\ask\index\Common;
use app\plugins\ask\service\BaseService;
use app\plugins\ask\service\SliderService;
use app\plugins\ask\service\AskService;
use app\plugins\ask\service\AskCommentsService;

/**
 * 问答 - 前端独立页面入口
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
{
    /**
     * 首页入口
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-07
     * @desc    description
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 幻灯片
        $slider_list = SliderService::ClientSliderList();
        MyViewAssign('slider_list', $slider_list);

        // 最新问答内容
        $new_ask_list = BaseService::AskNew($this->plugins_config);
        MyViewAssign('new_ask_list', $new_ask_list);

        // seo
        $this->SeoInfo();
        return MyView('../../../plugins/ask/view/index/index/index');
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
            // 基础操作按钮
            MyViewAssign('is_show_view_all_comments_submit', 1);
            MyViewAssign('is_show_view_more_comments_submit', 0);

            // 增加浏览次数
            AskService::AskAccessCountInc(['id'=>$ret['data']['id']]);

            // seo
            $this->SeoInfo(empty($ret['data']['title']) ? $ret['data']['content'] : $ret['data']['title']);
        }
        MyViewAssign('data', $ret['data']);
        return MyView('../../../plugins/ask/view/index/index/detail');
    }

    /**
     * 搜索
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-11
     * @desc    description
     * @param    [array]          $params [输入参数]
     */
    public function Search($params = [])
    {
        if(input('post.bwd'))
        {
            $bwd = str_replace(['?', ' ', '+', '-'], '', trim(input('post.bwd')));
            return MyRedirect(PluginsHomeUrl('ask', 'index', 'search', ['bwd'=>StrToAscii($bwd)]));
        } else {
            // 获取搜索数据
            $where = BaseService::AskListWhere($params);

            // 获取总数
            $total = AskService::AskTotal($where);
            $page_size = $this->page_size;

            // 是否自定义数量
            if(empty($params['page_size']) && !empty($this->plugins_config['search_page_number']))
            {
                $page_size = intval($this->plugins_config['search_page_number']);
            }

            // 分页
            $page_params = [
                'number'    =>  $page_size,
                'total'     =>  $total,
                'where'     =>  $params,
                'page'      =>  $this->page,
                'url'       =>  PluginsHomeUrl('ask', 'index', 'search'),
            ];
            $page = new \base\Page($page_params);
            MyViewAssign('page_html', $page->GetPageHtml());

            // 获取列表
            $data_list = [];
            if($total > 0)
            {
                $data_params = [
                    'm'         => $page->GetPageStarNumber(),
                    'n'         => $page_size,
                    'where'     => $where,
                ];
                $ret = BaseService::AskList($data_params);
                $data_list = $ret['data'];
            }
            MyViewAssign('data_list', $data_list);

            // tab切换导航
            MyViewAssign('search_tab_list', BaseService::SearchTabList($params));

            // 搜索关键字
            $ask_keywords = empty($params['bwd']) ? '' : AsciiToStr($params['bwd']);
            MyViewAssign('ask_keywords', $ask_keywords);

            // seo
            $this->SeoInfo(empty($ask_keywords) ? '问答搜索' : $ask_keywords);
            return MyView('../../../plugins/ask/view/index/index/search');
        }
    }

    /**
     * seo信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-15
     * @desc    description
     * @param   [string]          $title [标题]
     */
    public function SeoInfo($title = '')
    {
        // seo
        $seo_title = empty($title) ? (empty($this->plugins_config['seo_title']) ? (empty($this->plugins_config['application_name']) ? '问答' : $this->plugins_config['application_name']) : $this->plugins_config['seo_title']) : $title;
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($seo_title, 2));
        if(!empty($this->plugins_config['seo_keywords']))
        {
            MyViewAssign('home_seo_site_keywords', $this->plugins_config['seo_keywords']);
        }
        $seo_desc = empty($this->plugins_config['seo_desc']) ? (empty($this->plugins_config['describe']) ? '' : $this->plugins_config['describe']) : $this->plugins_config['seo_desc'];
        if(!empty($seo_desc))
        {
            MyViewAssign('home_seo_site_description', $seo_desc);
        }
    }

    /**
     * 评论信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-28
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function CommentsInfo($params = [])
    {
        $params['is_comments_list_info'] = 1;
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        $ret = BaseService::AskRow($params);
        MyViewAssign('data', $ret['data']);
        if($ret['code'] == 0)
        {
            // seo
            $seo_title = empty($ret['data']['title']) ? $ret['data']['content'] : $ret['data']['title'];
            MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('评论信息 - '.$seo_title, 2));
            MyViewAssign('home_seo_site_keywords', $seo_title);
            $seo_desc = empty($ret['data']['content']) ? (empty($ret['data']['title']) ? '' : $ret['data']['title']) : $ret['data']['content'];
            if(!empty($seo_desc))
            {
                MyViewAssign('home_seo_site_description', $seo_desc);
            }
        }
        // 基础操作按钮
        MyViewAssign('is_show_view_all_comments_submit', 0);
        MyViewAssign('is_show_view_more_comments_submit', 1);
        MyViewAssign('comments_page_value', 1);
        // 关闭头尾
        MyViewAssign('is_header', 0);
        MyViewAssign('is_footer', 0);
        MyViewAssign('page_pure', 1);
        MyViewAssign('params', $params);
        return MyView('../../../plugins/ask/view/index/index/commentsinfo');
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
        $params['plugins_config'] = $this->plugins_config;
        $ret = AskCommentsService::AskCommentsReplyList($params);
        $ret['data']['data'] = MyView('../../../plugins/ask/view/index/index/commentsreplylist', [
                'data'              => $ret['data']['data'],
                'params'            => $params,
                'user'              => $this->user,
                'plugins_config'    => $this->plugins_config
            ]);
        return $ret;
    }
}
?>