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
namespace app\plugins\distribution\admin;

use app\plugins\distribution\admin\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\BusinessService;
use app\plugins\distribution\service\LevelService;

/**
 * 分销 - 分销商管理
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
        // 额外的条件
        $ext_where = [];
        if(!empty($params['referrer_id']))
        {
            $ext_where['referrer_id'] = $params['referrer_id'];
        }

        // 获取总数
        $total = BusinessService::UserTeamTotal($this->form_where);

        // 分页
        $page_params = [
            'number'    => $this->page_size,
            'total'     => $total,
            'where'     => $params,
            'page'      => $this->page,
            'url'       => PluginsAdminUrl('distribution', 'team', 'index', $ext_where),
        ];
        $page = new \base\Page($page_params);
        MyViewAssign('page_html', $page->GetPageHtml());

        // 获取列表
        $params['m']               = $page->GetPageStarNumber();
        $params['n']               = $this->page_size;
        $params['where']           = $this->form_where;
        $params['order_by']        = $this->form_order_by['data'];
        $params['user_type']       = 'admin';
        $params['plugins_config']  = $this->plugins_config;
        $data = BusinessService::UserTeamList($params);
        MyViewAssign('data_list', $data['data']);

        // 参数
        MyViewAssign('params', $params);
        return MyView('../../../plugins/distribution/view/admin/team/index');
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
            $params['m']         = 0;
            $params['n']         = 1;
            $params['where']     = [
                ['id', '=', intval($params['id'])],
            ];
            $params['user_type'] = 'admin';
            $ret = BusinessService::UserTeamList($params);
            if(!empty($ret['data']) && !empty($ret['data'][0]))
            {
                $data = $ret['data'][0];
            }
        }
        MyViewAssign('data', $data);

        // 关闭头尾
        MyViewAssign('is_header', 0);
        MyViewAssign('is_footer', 0);
        return MyView('../../../plugins/distribution/view/admin/team/detail');
    }

    /**
     * 等级编辑
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        $data = [];
        if(!empty($params['id']))
        {
            $params['m']         = 0;
            $params['n']         = 1;
            $params['where']     = [
                ['id', '=', intval($params['id'])],
            ];
            $params['user_type'] = 'admin';
            $ret = BusinessService::UserTeamList($params);
            if(!empty($ret['data']) && !empty($ret['data'][0]))
            {
                $data = $ret['data'][0];
            }
        }
        MyViewAssign('data', $data);

        // 分销等级
        $ret = LevelService::DataList();
        MyViewAssign('level_list', $ret['data']);

        // 关闭头尾
        MyViewAssign('is_header', 0);
        MyViewAssign('is_footer', 0);
        return MyView('../../../plugins/distribution/view/admin/team/saveinfo');
    }

    /**
     * 保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        return BusinessService::TeamSave($params);
    }

    /**
     * 用户搜索
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function SearchUser($params = [])
    {
        return BaseService::DistributionSearchUser($params);
    }
}
?>