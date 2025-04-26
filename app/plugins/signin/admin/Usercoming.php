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
namespace app\plugins\signin\admin;

use app\plugins\signin\admin\Common;
use app\plugins\signin\service\QrcodeService;

/**
 * 签到 - 用户签到列表
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
    public function Index($params = [])
    {
        // 基础条件
        $qrcode_id = isset($params['id']) ? intval($params['id']) : 0;
        $where = empty($this->form_where) ? [] : $this->form_where;
        $where[] = ['q.id', '=', $qrcode_id];

        // 总数
        $total = QrcodeService::UserComingTotal($where);

        // 分页
        $page_params = [
            'number'    =>  $this->page_size,
            'total'     =>  $total,
            'where'     =>  $this->data_request,
            'page'      =>  $this->page,
            'url'       =>  PluginsAdminUrl('signin', 'usercoming', 'index', ['id'=>$qrcode_id]),
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
        return MyView('../../../plugins/signin/view/admin/usercoming/index');
    }
}
?>