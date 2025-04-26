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
namespace app\plugins\membershiplevelvip\api;

use app\plugins\membershiplevelvip\api\Common;
use app\plugins\membershiplevelvip\service\BaseService;
use app\plugins\membershiplevelvip\service\BusinessService;
use app\plugins\membershiplevelvip\service\StatisticalService;

/**
 * 会员等级增强版插件 - 会员中心
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Vip extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function __construct($params = [])
    {
        // 调用父类前置方法
        parent::__construct($params);

        // 是否登录
        IsUserLogin();
    }

    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-19
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 会员信息
        $user_vip = BusinessService::UserVip($this->user['id']);

        // 统计数据
        $statistics_data = StatisticalService::UserStatisticsData($this->plugins_config, $this->user);

        // 返回数据
        $result = [
            'base'             => $this->plugins_config,
            'user_vip'         => $user_vip,
            'statistics_data'  => $statistics_data,
            'nav_list'         => BaseService::AppUserCenterNav($this->plugins_config),
        ];
        return DataReturn('success', 0, $result);
    }
}
?>