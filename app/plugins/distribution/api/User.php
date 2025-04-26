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

use app\service\UserService;
use app\plugins\distribution\api\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\ExtractionService;
use app\plugins\distribution\service\StatisticalService;

/**
 * 分销 - 用户
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class User extends Common
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
     * 用户中心
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-17T21:10:41+0800
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        // 分销信息
        $user_level = BaseService::UserDistributionLevel($this->user['id'], $this->plugins_config);

        // 取货点信息
        if(isset($this->plugins_config['is_enable_self_extraction']) && $this->plugins_config['is_enable_self_extraction'] == 1)
        {
            $extraction = ExtractionService::ExtractionData($this->user['id']);
        }

        // 上级用户
        if(isset($this->plugins_config['is_show_superior']) && $this->plugins_config['is_show_superior'] == 1)
        {
            $superior = BaseService::UserSuperiorData($this->user, $this->plugins_config);
        }

        // 阶梯返佣提示
        if(isset($this->plugins_config['is_show_profit_ladder_tips']) && $this->plugins_config['is_show_profit_ladder_tips'] == 1)
        {
            $profit_ladder = BaseService::AppointProfitLadderOrderLevel($this->plugins_config, $this->user['id']);
        }

        // 默认时间
        $default_day = 'this-month';

        // 时间
        $time_data = StatisticalService::DateTimeList();

        // 基础数据总计
        $params['user'] = $this->user;
        if(!empty($time_data) && !empty($default_day) && isset($time_data[$default_day]))
        {
            $params['start'] = $time_data[$default_day]['start'];
            $params['end'] = $time_data[$default_day]['end'];
        }
        // 用户推广数据
        $stats_user_promotion_data_list = StatisticalService::AppStatssUserPromotionDataList($params);
        // 用户基础数据
        $stats_base_data_list = StatisticalService::AppStatssUserBaseDataList($params);
        // 收益数据
        $stats_profit_data_list = StatisticalService::AppStatssProfitDataList($params);

        // 返回数据
        $result = [
            'base'                            => $this->plugins_config,
            'user_level'                      => $user_level['data'],
            'extraction'                      => (isset($extraction) && !empty($extraction['data'] )) ? $extraction['data'] : null,
            'superior'                        => empty($superior) ? null : $superior,
            'profit_ladder'                   => empty($profit_ladder) ? null : $profit_ladder,
            'nav_list'                        => BaseService::AppUserCenterNav($this->plugins_config),
            'time_data'                       => $time_data,
            'stats_user_promotion_data_list'  => $stats_user_promotion_data_list,
            'stats_base_data_list'            => $stats_base_data_list,
            'stats_profit_data_list'          => $stats_profit_data_list,
            'default_day'                     => $default_day,
            'user_referrer'                   => UserService::UserReferrerEncryption($this->user['id']),
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 数据统计
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-11
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Stats($params = [])
    {
        $params['user'] = $this->user;
        // 用户推广数据
        $stats_user_promotion_data_list = StatisticalService::AppStatssUserPromotionDataList($params);
        // 用户基础数据
        $stats_base_data_list = StatisticalService::AppStatssUserBaseDataList($params);
        $result = [
            'stats_user_promotion_data_list'  => $stats_user_promotion_data_list,
            'stats_base_data_list'            => $stats_base_data_list,
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 用户查询
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-05-08
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function UserQuery($params = [])
    {
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return BaseService::UserQuery($params);
    }

    /**
     * 上级用户修改
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-05-08
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function SuperiorSave($params = [])
    {
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return BaseService::SuperiorSave($params);
    }
}
?>