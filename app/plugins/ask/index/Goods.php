<?php
// +----------------------------------------------------------------------
// | Licensed ( https://opensource.org/licenses/mit-license.php )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\plugins\ask\index;

use app\plugins\ask\index\Common;
use app\plugins\ask\service\BaseService;
use app\plugins\ask\service\AskService;
use app\plugins\ask\service\AskCommentsService;

/**
 * 问答 - 商品
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2019-02-07T08:21:54+0800
 */
class Goods extends Common
{
    /**
     * 商品问答
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-05-13T21:47:41+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 参数
        if(empty($params['goods_id']))
        {
            return DataReturn(MyLang('params_error_tips'), -1);
        }

        // 条件
        $where = BaseService::AskListWhere($params);

        // 获取总数
        $total = AskService::AskTotal($where);
        $page_total = ceil($total/$this->page_size);
        $start = intval(($this->page-1)*$this->page_size);

        // 获取列表
        $data = [];
        if($total > 0)
        {
            $data_params = [
                'm'               => $start,
                'n'               => $this->page_size,
                'where'           => $where,
                'is_comments'     => 1,
                'plugins_config'  => $this->plugins_config,
            ];
            $ret = BaseService::AskList($data_params);
            $data = $ret['data'];
        }

        // 返回数据
        $result = [
            'number'      => $this->page_size,
            'total'       => $total,
            'page_total'  => $page_total,
            'data'        => MyView('../../../plugins/ask/view/index/goods/index', ['data'=>$data]),
        ];
        return DataReturn('success', 0, $result);
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
        $ret['data']['data'] = MyView('../../../plugins/ask/view/index/goods/commentsreplylist', [
                'data'              => $ret['data']['data'],
                'params'            => $params,
                'user'              => $this->user,
                'plugins_config'    => $this->plugins_config
            ]);
        return $ret;
    }
}
?>