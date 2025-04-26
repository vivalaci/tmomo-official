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
namespace app\plugins\activity\admin;

use app\service\GoodsCategoryService;
use app\plugins\activity\admin\Common;
use app\plugins\activity\service\BaseService;
use app\plugins\activity\service\ActivityService;
use app\plugins\activity\service\CategoryService;

/**
 * 活动配置 - 活动管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-29
 * @desc    description
 */
class Activity extends Common
{
	/**
     * 列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
	public function Index($params = [])
	{
        return MyView('../../../plugins/activity/view/admin/activity/index');
	}

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        return MyView('../../../plugins/activity/view/admin/activity/detail');
    }

	/**
     * 添加/编辑页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
	public function SaveInfo($params = [])
	{
		// 参数
        $params = $this->data_request;

        // 数据
        $data = $this->data_detail;

		// 分类
        $activity_category = CategoryService::CategoryList(['field'=>'id,name']);
        MyViewAssign('activity_category_list', $activity_category['data']);

        // 关联的商品数据
        $goods = empty($data['goods_list']) ? [] : $data['goods_list'];
        MyViewAssign('goods', $goods);

        // 商品分类
        MyViewAssign('goods_category_list', GoodsCategoryService::GoodsCategoryAll());

        // 静态数据
        MyViewAssign('home_floor_location_list', BaseService::ConstData('home_floor_location_list'));
        MyViewAssign('recommend_style_type_list', BaseService::ConstData('recommend_style_type_list'));

        // 数据
        MyViewAssign('data', $data);
        MyViewAssign('params', $params);
        return MyView('../../../plugins/activity/view/admin/activity/saveinfo');
	}

	/**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
	public function Save($params = [])
	{
		// 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params = $this->data_request;
        return ActivityService::ActivitySave($params);
	}

	/**
     * 删除
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
	public function Delete($params = [])
	{
		// 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params = $this->data_request;
        $params['admin'] = $this->admin;
        return ActivityService::ActivityDelete($params);
	}

	/**
     * 状态更新
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
	public function StatusUpdate($params = [])
	{
		// 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params = $this->data_request;
        $params['admin'] = $this->admin;
        return ActivityService::ActivityStatusUpdate($params);
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
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 搜索数据
        return BaseService::GoodsSearchList($params);
    }
}
?>