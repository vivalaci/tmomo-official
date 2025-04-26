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
use app\service\UserService;
use app\plugins\distribution\index\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\StatisticalService;
use app\plugins\distribution\service\ExtractionService;

/**
 * 分销 - 首页
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
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
        // 默认时间
        $default_day = 'this-month';
        MyViewAssign('default_day', $default_day);

        // 时间
        $time_data = StatisticalService::DateTimeList();
        MyViewAssign('time_data', $time_data);

        // 基础数据总计
        $params['user'] = $this->user;
        if(!empty($time_data) && !empty($default_day) && isset($time_data[$default_day]))
        {
            // 日期转时间戳
            $params['start'] = strtotime($time_data[$default_day]['start']);
            $params['end'] = strtotime($time_data[$default_day]['end']);
        }

        // 基础统计数据
        $base_data = StatisticalService::BaseData($params);
        MyViewAssign('base_data', $base_data);

        // 推广用户
        $user_promotion_data = StatisticalService::UserPromotionTotalData($params);
        MyViewAssign('user_promotion_data', $user_promotion_data);

        // 收益统计数据
        $profit_data = StatisticalService::ProfitData($params);
        MyViewAssign('profit_data', $profit_data);

        // 获取取货点信息
        if(isset($this->plugins_config['is_enable_self_extraction']) && $this->plugins_config['is_enable_self_extraction'] == 1)
        {
            $extraction = ExtractionService::ExtractionData($this->user['id']);
            MyViewAssign('extraction_data', $extraction['data']);
        }

        // 上级用户
        $superior = null;
        if(isset($this->plugins_config['is_show_superior']) && $this->plugins_config['is_show_superior'] == 1)
        {
            $superior = BaseService::UserSuperiorData($this->user, $this->plugins_config);
            MyViewAssign('superior', $superior);
        }

        // 阶梯返佣提示
        if(isset($this->plugins_config['is_show_profit_ladder_tips']) && $this->plugins_config['is_show_profit_ladder_tips'] == 1)
        {
            $profit_ladder = BaseService::AppointProfitLadderOrderLevel($this->plugins_config, $this->user['id']);
            MyViewAssign('profit_ladder', $profit_ladder);
        }

        // 加载图表组件
        MyViewAssign('is_load_echarts', 1);

        // 修改上级用户表单名称
        MyViewAssign('search_form_name', 'superior_id');
        MyViewAssign('search_user_data', $superior);

        // 推荐码
        MyViewAssign('user_referrer', UserService::UserReferrerEncryption($this->user['id']));

        // 浏览器名称
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('我的分销', 1));
        MyViewAssign('params', $params);
        return MyView('../../../plugins/distribution/view/index/index/index');
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
        $params['plugins_config'] = $this->plugins_config;
        return StatisticalService::StatsData($params);
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