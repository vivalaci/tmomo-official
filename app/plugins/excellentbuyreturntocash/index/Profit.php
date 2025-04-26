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
namespace app\plugins\excellentbuyreturntocash\index;

use app\service\SeoService;
use app\plugins\excellentbuyreturntocash\index\Common;
use app\plugins\excellentbuyreturntocash\service\BaseService;
use app\plugins\excellentbuyreturntocash\service\CrontabService;
use app\plugins\excellentbuyreturntocash\service\BusinessService;

/**
 * 优购返现 - 返现明细
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Profit extends Common
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

        // 是否已经登录
        IsUserLogin();
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
        // 总数
        $total = BusinessService::ProfitTotal($this->form_where);

        // 分页
        $page_params = [
            'number'    =>  $this->page_size,
            'total'     =>  $total,
            'where'     =>  $this->data_request,
            'page'      =>  $this->page,
            'url'       =>  PluginsHomeUrl('excellentbuyreturntocash', 'profit', 'index'),
        ];
        $page = new \base\Page($page_params);

        // 获取列表
        $data_params = [
            'm'             => $page->GetPageStarNumber(),
            'n'             => $this->page_size,
            'where'         => $this->form_where,
            'order_by'      => $this->form_order_by['data'],
            'user_type'     => 'user',
        ];
        $ret = BusinessService::ProfitList($data_params);

        // 分享提示信息
        MyViewAssign('user_share_msg', BaseService::$user_share_msg);

        // 浏览器名称
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('返现明细 - 优购返现', 1));

        // 基础参数赋值
        MyViewAssign('params', $this->data_request);
        MyViewAssign('page_html', $page->GetPageHtml());
        MyViewAssign('data_list', $ret['data']);
        return MyView('../../../plugins/excellentbuyreturntocash/view/index/profit/index');
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
        $data = [];
        if(!empty($params['id']))
        {
            // 条件
            $where = [
                ['c.id', '=', intval($params['id'])],
                ['c.user_id', '=', $this->user['id']],
            ];

            // 获取列表
            $data_params = [
                'm'         => 0,
                'n'         => 1,
                'where'     => $where,
                'user_type' => 'user',
            ];
            $ret = BusinessService::ProfitList($data_params);
            if(!empty($ret['data']) && !empty($ret['data'][0]))
            {
                $data = $ret['data'][0];
            }
        }
        MyViewAssign('data', $data);

        // 关闭头尾
        MyViewAssign('is_header', 0);
        MyViewAssign('is_footer', 0);
        return MyView('../../../plugins/excellentbuyreturntocash/view/index/profit/detail');
    }

    /**
     * 自动返现操作
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-06
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Auto($params = [])
    {
        $ret = CrontabService::AutoSettlement($params);
        if($ret['code'] == 0 && isset($ret['data']['sucs']) && $ret['data']['sucs'] > 0)
        {
            return DataReturn(MyLang('operate_success'));
        }
        return DataReturn(BaseService::$user_share_msg, -1);
    }
}
?>