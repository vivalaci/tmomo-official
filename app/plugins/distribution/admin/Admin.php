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

use app\service\PluginsService;
use app\service\GoodsCategoryService;
use app\plugins\distribution\admin\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\StatisticalService;
use app\plugins\distribution\service\CrontabService;
use app\plugins\distribution\service\LevelService;

/**
 * 分销 - 管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Admin extends Common
{
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
        // 佣金结算脚本自动运行一次
        CrontabService::ProfitSettlement();

        // 静态资源设置
        $this->SetResources();

        // 配置数据
        MyViewAssign('data', $this->plugins_config);
        return MyView('../../../plugins/distribution/view/admin/admin/index');
    }

    /**
     * 编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        // 静态资源设置
        $this->SetResources();

        // 商品分类
        MyViewAssign('goods_category_list', GoodsCategoryService::GoodsCategoryAll());

        // 配置数据
        MyViewAssign('data', $this->plugins_config);
        return MyView('../../../plugins/distribution/view/admin/admin/saveinfo');
    }

    /**
     * 静态资源设置
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-06-04
     * @desc    description
     * @return  [type]          [description]
     */
    private function SetResources()
    {
        // 分销等级
        $level = LevelService::DataList();
        MyViewAssign('level_data_list', $level['data']);

        // 分销层级
        MyViewAssign('distribution_level_list', BaseService::$distribution_level_list);

        // 取货点返佣上级
        MyViewAssign('distribution_extraction_profit_level_list', BaseService::$distribution_extraction_profit_level_list);

        // 返佣类型
        MyViewAssign('distribution_profit_type_list', BaseService::$distribution_profit_type_list);
        
        // 是否开启
        MyViewAssign('distribution_is_enable_list', BaseService::$distribution_is_enable_list);
    }

    /**
     * 数据保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        // 海报
        $poster = BaseService::PosterData();
        $params['poster_data'] = $poster['data'];
        return PluginsService::PluginsDataSave(['plugins'=>'distribution', 'data'=>$params], BaseService::$base_config_attachment_field);
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
            $ret['data']['data'] = MyView('../../../plugins/distribution/view/admin/public/goodssearch', ['data'=>$ret['data']['data']]);
        }
        return $ret;
    }
}
?>