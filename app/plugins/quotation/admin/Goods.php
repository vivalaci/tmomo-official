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
namespace app\plugins\quotation\admin;

use app\service\GoodsService;
use app\plugins\quotation\admin\Common;
use app\plugins\quotation\service\BaseService;

/**
 * 报价单 - 数据列表
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2019-09-24
 * @desc    description
 */
class Goods extends Common
{
    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-24
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function index($params = [])
    {
        // 总数
        $total = GoodsService::GoodsTotal($this->form_where);

        // 分页
        $page_params = array(
                'number'    =>  $this->page_size,
                'total'     =>  $total,
                'where'     =>  $this->data_request,
                'page'      =>  $this->page,
                'url'       =>  PluginsAdminUrl('quotation', 'goods', 'index'),
            );
        $page = new \base\Page($page_params);

        // 获取数据列表
        $data_params = [
            'where'         => $this->form_where,
            'm'             => $page->GetPageStarNumber(),
            'n'             => $this->page_size,
            'is_category'   => 1,
        ];
        $ret = GoodsService::GoodsList($data_params);

        // 导出字段
        MyViewAssign('goods_export_title', BaseService::$goods_export_title);

        // 规格可选字段
        MyViewAssign('goods_export_spec_title', BaseService::$goods_export_spec_title);

        // 会员等级数据
        MyViewAssign('vip_level_data', BaseService::MembershiplevelVipData());

        MyViewAssign('params', $this->data_request);
        MyViewAssign('page_html', $page->GetPageHtml());
        MyViewAssign('data_list', $ret['data']);
        return MyView('../../../plugins/quotation/view/admin/goods/index');
    }

    /**
     * 商品数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-07-08
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function GoodsData($params = [])
    {
        $msg = '';
        $ids = '';
        $count = 0;
        $data = [];
        if(empty($params['goods_ids']))
        {
            $msg = '请先选择商品';
        } else {
            $where = [
                ['id', 'in', is_array($params['goods_ids']) ? $params['goods_ids'] : explode(',', $params['goods_ids'])],
            ];
            $ret = BaseService::GoodsListData($where);
            $data = $ret['data'];
            if(empty($data))
            {
                $msg = '没有商品数据';
            } else {
                $count  = empty($data) ? 0 : count($data);
                $ids    = empty($data) ? '' : implode(',', array_column($data, 'id'));
            }
        }
        return DataReturn(MyLang('operate_success'), 0, [
                'html'  => MyView('../../../plugins/quotation/view/admin/public/goods', [
                    'data'   => $data,
                    'count'  => $count,
                    'ids'    => $ids,
                    'msg'    => $msg,
                ]),
                'count' => $count,
            ]);
    }
}
?>