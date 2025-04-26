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
namespace app\plugins\distribution\api;

use app\service\GoodsCategoryService;
use app\plugins\distribution\api\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\RecommendService;

/**
 * 分销 - 推荐宝
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Recommend extends Common
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

        // 详情页面不需要登录
        if($this->plugins_action_name != 'detail')
        {
            // 是否已经登录
            IsUserLogin();
        }
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
        // 参数
        $params['user_id'] = $this->user['id'];

        // 条件
        $where = RecommendService::RecommendWhere($params);

        // 获取总数
        $total = RecommendService::RecommendTotal($where);
        $page_total = ceil($total/$this->page_size);
        $start = intval(($this->page-1)*$this->page_size);

        // 获取列表
        $data_params = [
            'm'             => $start,
            'n'             => $this->page_size,
            'where'         => $where,
        ];
        $data = RecommendService::RecommendList($data_params);

        // 返回数据
        $result = [
            'total'             => $total,
            'page_total'        => $page_total,
            'data'              => $data['data'],
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 编辑页面数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-04-04
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function SaveData($params = [])
    {
        // 获取数据
        $data = null;
        if(!empty($params['id']))
        {
            $params['user_id'] = $this->user['id'];
            $data_params = [
                'm'         => 0,
                'n'         => 1,
                'where'     => RecommendService::RecommendWhere($params),
            ];
            $ret = RecommendService::RecommendList($data_params);
            $data = (empty($ret['data']) || empty($ret['data'][0])) ? null : $ret['data'][0];
        }

        // 商品分类
        $goods_category_list = GoodsCategoryService::GoodsCategoryList([
            'where' => [
                ['pid', '=', 0],
                ['is_enable', '=', 1],
            ],
            'field' => 'id,name',
        ]);

        // 返回信息
        $result = [
            'data'                  => $data,
            'goods_category_list'   => $goods_category_list,
            'editor_path_type'      => 'plugins_distribution-user_recommend-'.$this->user['id'],
        ];
        return DataReturn('success', 0, $result);
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
        return RecommendService::RecommendData($params);
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-02
     * @desc    description
     */
    public function Save($params = [])
    {
        $params['user_id'] = $this->user['id'];
        $params['plugins_config'] = $this->plugins_config;
        return RecommendService::RecommendSave($params);
    }

    /**
     * 删除
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-03
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Delete($params = [])
    {
        $params['user_id'] = $this->user['id'];
        return RecommendService::RecommendDelete($params);
    }

    /**
     * 商品搜索
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function GoodsSearch($params = [])
    {
        return BaseService::BaseGoodsSearchList($params);
    }
}
?>