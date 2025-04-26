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
namespace app\plugins\intellectstools\index;

use app\plugins\intellectstools\index\Common;
use app\plugins\intellectstools\service\OrderNoteService;
use app\plugins\intellectstools\service\OrderBaseService;

/**
 * 智能工具箱 - 用户订单
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class OrderUser extends Common
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

        // 关闭顶部底部内容
        MyViewAssign([
            'is_header'   => 0,
            'is_footer'   => 0,
            'page_pure'   => 1,
            'is_to_home'  => 0,
        ]);
    }

    /**
     * 订单备注查看页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function NoteViewInfo($params = [])
    {
        // 订单信息
        $params['where'] = [['user_id', '=', $this->user['id']]];
        $ret = OrderBaseService::OrderDetail($params);
        if($ret['code'] != 0)
        {
            MyViewAssign('msg', $ret['msg']);
            return MyView('public/tips_error');
        }
        $order_data = $ret['data'];
        MyViewAssign('order_data', $order_data);

        // 备注信息
        $note_data = OrderNoteService::OrderNoteData($order_data['id']);
        MyViewAssign('note_data', $note_data);
        MyViewAssign('main_note_name', empty($this->plugins_config['order_note_user_name']) ? MyLang('note_title') : $this->plugins_config['order_note_user_name']);
        return MyView('../../../plugins/intellectstools/view/index/orderuser/noteviewinfo');
    }
}
?>