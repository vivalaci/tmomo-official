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

use app\service\UserService;
use app\plugins\distribution\admin\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\BusinessService;

/**
 * 分销 - 推广用户
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class PromotionUser extends Common
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

        // 推广用户菜单
        $promotion_user_nav = BaseService::WebPromotionUserNav($this->plugins_config);
        MyViewAssign('promotion_user_nav', $promotion_user_nav);
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

        // 用户信息
        $uid = empty($params['uid']) ? 0 : intval($params['uid']);
        if(!empty($uid))
        {
            $params['user'] = UserService::UserHandle(UserService::UserBaseInfo('id', $uid));
        }

        // 基础条件
        if(in_array($type, [0,1,2]))
        {
            $map = BusinessService::UserPromotionMap($params);
        } else {
            $map = BusinessService::BaseDataMap($params);
        }

        // 根据类型处理数据
        switch($type)
        {
            // 已推广用户
            case 0 :
                // 获取总数
                $where = array_merge($this->form_where, $map['user_where']);
                $total = BusinessService::UserPromotionAllTotal($where);

                // 分页
                $page_params = [
                    'number'    => $this->page_size,
                    'total'     => $total,
                    'where'     => $params,
                    'page'      => $this->page,
                    'url'       => PluginsAdminUrl('distribution', 'promotionuser', 'index'),
                ];
                $page = new \base\Page($page_params);

                // 获取列表
                $data_params = [
                    'm'         => $page->GetPageStarNumber(),
                    'n'         => $this->page_size,
                    'where'     => $where,
                    'order_by'  => $this->form_order_by['data'],
                ];
                $data = BusinessService::UserPromotionAllList($data_params);
                break;

            // 已推广已消费用户
            case 1 :
                // 获取总数
                $where = array_merge($this->form_where, $map['valid_where']);
                $total = BusinessService::UserPromotionValidTotal($where);

                // 分页
                $page_params = [
                    'number'    => $this->page_size,
                    'total'     => $total,
                    'where'     => $params,
                    'page'      => $this->page,
                    'url'       => PluginsAdminUrl('distribution', 'promotionuser', 'index'),
                ];
                $page = new \base\Page($page_params);

                // 获取列表
                $data_params = [
                    'm'         => $page->GetPageStarNumber(),
                    'n'         => $this->page_size,
                    'where'     => $where,
                    'order_by'  => $this->form_order_by['data'],
                ];
                $data = BusinessService::UserPromotionValidList($data_params);
                break;

            // 已推广未消费用户
            case 2 :
                // 获取总数
                $where = array_merge($this->form_where, $map['not_where']);
                $total = BusinessService::UserPromotionNotConsumedTotal($map['valid_where'], $where);

                // 分页
                $page_params = [
                    'number'    => $this->page_size,
                    'total'     => $total,
                    'where'     => $params,
                    'page'      => $this->page,
                    'url'       => PluginsAdminUrl('distribution', 'promotionuser', 'index'),
                ];
                $page = new \base\Page($page_params);

                // 获取列表
                $data_params = [
                    'm'            => $page->GetPageStarNumber(),
                    'n'            => $this->page_size,
                    'where'        => $where,
                    'valid_where'  => $map['valid_where'],
                    'order_by'     => $this->form_order_by['data'],
                ];
                $data = BusinessService::UserPromotionNotConsumedList($data_params);
                break;

            // 新增客户
            case 3 :
                // 获取总数
                $where = array_merge($this->form_where, $map['order_where']);
                $total = BusinessService::BaseDataOrderNewUserCountTotal($where, $map['order_new_user_not_front_where']);

                // 分页
                $page_params = [
                    'number'    => $this->page_size,
                    'total'     => $total,
                    'where'     => $params,
                    'page'      => $this->page,
                    'url'       => PluginsAdminUrl('distribution', 'promotionuser', 'index'),
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
                $data = BusinessService::BaseDataOrderNewUserCountList($data_params);
                break;

            // 新增客户-有效
            case 4 :
                // 获取总数
                $where = array_merge($this->form_where, $map['order_where']);
                $total = BusinessService::BaseDataOrderNewValidUserCountTotal($where, $map['order_new_user_not_front_where']);

                // 分页
                $page_params = [
                    'number'    => $this->page_size,
                    'total'     => $total,
                    'where'     => $params,
                    'page'      => $this->page,
                    'url'       => PluginsAdminUrl('distribution', 'promotionuser', 'index'),
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
                $data = BusinessService::BaseDataOrderNewValidUserCountList($data_params);
                break;

            // 新增客户-需复购
            case 5 :
                // 获取总数
                $where = array_merge($this->form_where, $map['order_where']);
                $total = BusinessService::BaseDataOrderNewValidUserNeedRepurchaseCountTotal($where, $map['order_new_user_not_front_where']);

                // 分页
                $page_params = [
                    'number'    => $this->page_size,
                    'total'     => $total,
                    'where'     => $params,
                    'page'      => $this->page,
                    'url'       => PluginsAdminUrl('distribution', 'promotionuser', 'index'),
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
                $data = BusinessService::BaseDataOrderNewValidUserNeedRepurchaseCountList($data_params);
                break;

            default :
                return MyView('public/tips_error', ['msg'=>'业务类型有误！', 'is_to_home'=>0]);
        }
        MyViewAssign('page_html', $page->GetPageHtml());
        MyViewAssign('data_list', $data['data']);
        MyViewAssign('uid', $uid);
        MyViewAssign('type', $type);
        MyViewAssign('start', $start);
        MyViewAssign('end', $end);
        return MyView('../../../plugins/distribution/view/admin/promotionuser/index');
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
                    ['id', '=', intval($params['id'])],
                ],
            ];
            $ret = BusinessService::UserPromotionAllList($data_params);
            if(!empty($ret['data']) && !empty($ret['data'][0]))
            {
                $data = $ret['data'][0];
            }
        }
        MyViewAssign('data', $data);
        return MyView('../../../plugins/distribution/view/admin/promotionuser/detail');
    }
}
?>