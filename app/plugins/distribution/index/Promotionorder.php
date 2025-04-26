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
namespace app\plugins\distribution\index;

use app\service\SeoService;
use app\plugins\distribution\index\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\BusinessService;

/**
 * 分销 - 推广订单
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class PromotionOrder extends Common
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

        // 推广订单菜单
        $promotion_order_nav = BaseService::WebPromotionOrderNav($this->plugins_config);
        MyViewAssign('promotion_order_nav', $promotion_order_nav);

        // 关闭头尾
        MyViewAssign('is_header', 0);
        MyViewAssign('is_footer', 0);
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
        // 基础参数
        $type = empty($params['type']) ? 0 : intval($params['type']);
        $start = empty($params['start']) ? '' : $params['start'];
        $end = empty($params['end']) ? '' : $params['end'];

        // 基础条件
        $params['user'] = $this->user;
        $map = BusinessService::BaseDataMap($params);

        // 根据类型处理数据
        switch($type)
        {
            // 新增客户总GMV
            case 0 :
                // 获取总数
                $where = array_merge($this->form_where, $map['order_where']);
                $total = BusinessService::BaseDataOrderNewUserPriceGMVTotal($where, $map['order_new_user_not_front_where']);

                // 分页
                $page_params = [
                    'number'    => $this->page_size,
                    'total'     => $total,
                    'where'     => $params,
                    'page'      => $this->page,
                    'url'       => PluginsHomeUrl('distribution', 'promotionorder', 'index'),
                ];
                $page = new \base\Page($page_params);

                // 获取列表
                $data_params = [
                    'm'                => $page->GetPageStarNumber(),
                    'n'                => $this->page_size,
                    'where'            => $where,
                    'not_front_where'  => $map['order_new_user_not_front_where'],
                    'order_by'         => $this->form_order_by['data'],
                ];
                $data = BusinessService::BaseDataOrderNewUserPriceGMVList($data_params);
                break;

            // 订单总数
            case 1 :
                // 获取总数
                $where = array_merge($this->form_where, $map['order_where']);
                $total = BusinessService::BaseDataOrderUserCountTotal($where);

                // 分页
                $page_params = [
                    'number'    => $this->page_size,
                    'total'     => $total,
                    'where'     => $params,
                    'page'      => $this->page,
                    'url'       => PluginsHomeUrl('distribution', 'promotionorder', 'index'),
                ];
                $page = new \base\Page($page_params);

                // 获取列表
                $data_params = [
                    'm'         => $page->GetPageStarNumber(),
                    'n'         => $this->page_size,
                    'where'     => $where,
                    'order_by'  => $this->form_order_by['data'],
                ];
                $data = BusinessService::BaseDataOrderUserCountList($data_params);
                break;

            // 订单总GMV
            case 2 :
                // 获取总数
                $where = array_merge($this->form_where, $map['order_where']);
                $total = BusinessService::BaseDataOrderUserPriceGMVTotal($where);

                // 分页
                $page_params = [
                    'number'    => $this->page_size,
                    'total'     => $total,
                    'where'     => $params,
                    'page'      => $this->page,
                    'url'       => PluginsHomeUrl('distribution', 'promotionorder', 'index'),
                ];
                $page = new \base\Page($page_params);

                // 获取列表
                $data_params = [
                    'm'            => $page->GetPageStarNumber(),
                    'n'            => $this->page_size,
                    'where'        => $where,
                    'order_by'     => $this->form_order_by['data'],
                ];
                $data = BusinessService::BaseDataOrderUserPriceGMVList($data_params);
                break;

            default :
                return MyView('public/tips_error', ['msg'=>'业务类型有误！', 'is_to_home'=>0]);
        }
        MyViewAssign('page_html', $page->GetPageHtml());
        MyViewAssign('data_list', $data['data']);
        MyViewAssign('type', $type);
        MyViewAssign('start', $start);
        MyViewAssign('end', $end);

        // 浏览器名称
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('推广订单 - 我的分销', 1));
        return MyView('../../../plugins/distribution/view/index/promotionorder/index');
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
                'm'         => 0,
                'n'         => 1,
                'where'     => [
                    ['o.id', '=', intval($params['id'])],
                    ['u.referrer', '=', $this->user['id']],
                ],
            ];
            $ret = BusinessService::UserOrderList($data_params);
            if(!empty($ret['data']) && !empty($ret['data'][0]))
            {
                $data = $ret['data'][0];
            }
        }
        MyViewAssign('data', $data);
        return MyView('../../../plugins/distribution/view/index/promotionorder/detail');
    }
}
?>