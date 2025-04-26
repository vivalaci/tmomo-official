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
namespace app\plugins\membershiplevelvip\admin;

use app\plugins\membershiplevelvip\admin\Common;
use app\plugins\membershiplevelvip\service\BaseService;

/**
 * 会员等级增强版插件 - 分销员管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Team extends Common
{
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
        // 获取总数
        $total = BaseService::UserTeamTotal($this->form_where);

        // 分页
        $page_params = [
            'number'    =>  $this->page_size,
            'total'     =>  $total,
            'where'     =>  $params,
            'page'      =>  $this->page,
            'url'       =>  PluginsAdminUrl('membershiplevelvip', 'team', 'index'),
        ];
        $page = new \base\Page($page_params);
        MyViewAssign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_list = [];
        if($total > 0)
        {
            $data_params = [
                'm'         => $page->GetPageStarNumber(),
                'n'         => $this->page_size,
                'where'     => $this->form_where,
            ];
            $ret = BaseService::UserTeamList($data_params);
            $data_list = $ret['data'];
        }
        MyViewAssign('data_list', $data_list);
        return MyView('../../../plugins/membershiplevelvip/view/admin/team/index');
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
        $data = [];
        if(!empty($params['id']))
        {
            $data_params = [
                'where'     => [
                    ['id', '=', intval($params['id'])],
                ],
            ];
            $ret = BaseService::UserTeamList($data_params);
            if(!empty($ret['data']) && !empty($ret['data'][0]))
            {
                $data = $ret['data'][0];
            }
        }
        MyViewAssign('data', $data);
        return MyView('../../../plugins/membershiplevelvip/view/admin/team/detail');
    }
}
?>