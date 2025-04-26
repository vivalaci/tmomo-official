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
namespace app\plugins\homemiddleadv\admin;

use app\plugins\homemiddleadv\admin\Common;
use app\plugins\homemiddleadv\service\SliderService;

/**
 * 首页中间广告插件 - 轮播图片
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Slider extends Common
{
    /**
     * 数据列表页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        $ret = SliderService::SliderList();
        MyViewAssign('common_platform_type', MyConst('common_platform_type'));
        MyViewAssign('data_list', empty($ret['data']) ? [] : array_values($ret['data']));
        return MyView('../../../plugins/homemiddleadv/view/admin/slider/index');
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-10-15
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        $data = [];
        if(!empty($this->data_request['id']))
        {
            $data_params = [
                'get_id'    => $params['id'],
            ];
            $ret = SliderService::SliderList($data_params);
            $data = empty($ret['data']) ? [] : $ret['data'];
        }
        MyViewAssign('data', $data);
        MyViewAssign('common_platform_type', MyConst('common_platform_type'));
        return MyView('../../../plugins/homemiddleadv/view/admin/slider/detail');
    }

    /**
     * 数据列表编辑
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        // 数据
        $data = [];
        if(!empty($params['id']))
        {
            $data_params = [
                'get_id'    => $params['id'],
            ];
            $ret = SliderService::SliderList($data_params);
            $data = empty($ret['data']) ? [] : $ret['data'];
        }
        MyViewAssign('data', $data);
        MyViewAssign('common_platform_type', MyConst('common_platform_type'));
        return MyView('../../../plugins/homemiddleadv/view/admin/slider/saveinfo');
    }

    /**
     * 数据列表保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-02-07T08:21:54+0800
     * @param    [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        return SliderService::SliderSave($params);
    }

    /**
     * 数据列表删除
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     * @param    [array]          $params [输入参数]
     */
    public function Delete($params = [])
    {
        return SliderService::SliderDelete($params);
    }

    /**
     * 数据列表状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-01-12T22:23:06+0800
     * @param    [array]          $params [输入参数]
     */
    public function StatusUpdate($params = [])
    {
        return SliderService::SliderStatusUpdate($params);
    }
}
?>