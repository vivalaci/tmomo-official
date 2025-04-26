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
namespace app\plugins\signin\index;

use app\service\SeoService;
use app\plugins\signin\index\Common;
use app\plugins\signin\service\QrcodeService;
use app\plugins\signin\service\SigninService;

/**
 * 签到 - 签到码管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class UserComing extends Common
{
    /**
     * 用户签到列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function index($params = [])
    {
        // 关闭头尾
        MyViewAssign('is_to_home', 0);
        MyViewAssign('is_header', 0);
        MyViewAssign('is_footer', 0);

        // 是否开启了组队查看签到人员
        if(empty($this->plugins_config['is_team_show_coming_user']) || $this->plugins_config['is_team_show_coming_user'] != 1)
        {
            MyViewAssign('msg', '无权限查看签到人员、请联系管理员');
            return MyView('public/tips_error');
        }

        // 基础条件
        $qrcode_id = isset($params['id']) ? intval($params['id']) : 0;
        $where = empty($this->form_where) ? [] : $this->form_where;
        $where[] = ['q.id', '=', $qrcode_id];
        $where[] = ['q.user_id', '=', $this->user['id']];

        // 总数
        $total = QrcodeService::UserComingTotal($where);

        // 分页
        $page_params = [
            'number'    =>  $this->page_size,
            'total'     =>  $total,
            'where'     =>  $this->data_request,
            'page'      =>  $this->page,
            'url'       =>  PluginsHomeUrl('signin', 'usercoming', 'index', ['id'=>$qrcode_id]),
        ];
        $page = new \base\Page($page_params);

        // 获取列表
        $data_params = [
            'm'         => $page->GetPageStarNumber(),
            'n'         => $this->page_size,
            'where'     => $where,
            'order_by'  => $this->form_order_by['data'],
            'is_public' => 0,
        ];
        $ret = QrcodeService::UserComingList($data_params);

        // 基础参数赋值
        MyViewAssign('params', $this->data_request);
        MyViewAssign('page_html', $page->GetPageHtml());
        MyViewAssign('data_list', $ret['data']);
        return MyView('../../../plugins/signin/view/index/usercoming/index');
    }
}
?>