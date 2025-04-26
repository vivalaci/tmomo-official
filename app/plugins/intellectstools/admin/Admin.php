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
namespace app\plugins\intellectstools\admin;

use app\service\BrandService;
use app\service\GoodsCategoryService;
use app\plugins\intellectstools\admin\Common;
use app\plugins\intellectstools\service\BaseService;

/**
 * 智能工具箱 - 管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class Admin extends Common
{
    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date     2021-05-07
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        MyViewAssign('data', $this->plugins_config);
        return MyView('../../../plugins/intellectstools/view/admin/admin/index');
    }

    /**
     * 编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date     2021-05-07
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        // 品牌
        MyViewAssign('brand_list', BrandService::CategoryBrand());

        // 商品分类
        MyViewAssign('goods_category_list', GoodsCategoryService::GoodsCategoryAll());

        // 配置数据
        MyViewAssign('data', $this->plugins_config);
        return MyView('../../../plugins/intellectstools/view/admin/admin/saveinfo');
    }

    /**
     * 数据保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @date     2021-05-07
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        return BaseService::BaseConfigSave($params);
    }

    /**
     * 商品搜索
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-10-16
     * @desc    description
     * @param   [array]           $params [商品搜索]
     */
    public function GoodsSearch($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 搜索数据
        $ret = BaseService::LimitGoodsSearchList($params);
        if($ret['code'] == 0)
        {
            $ret['data']['data'] = MyView('../../../plugins/intellectstools/view/admin/public/goodssearch', ['data'=>$ret['data']['data']]);
        }
        return $ret;
    }
}
?>