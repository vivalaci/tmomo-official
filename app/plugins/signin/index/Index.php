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
namespace app\plugins\signin\index;

use app\service\SeoService;
use app\plugins\signin\index\Common;
use app\plugins\signin\service\QrcodeService;
use app\plugins\signin\service\SigninService;

/**
 * 签到 - 签到页面
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class Index extends Common
{
    /**
     * 签到详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        $data = [];
        if(!empty($params['id']))
        {
            // 获取列表
            $data_params = [
                'plugins_config'  => $this->plugins_config,
                'where'           => [
                    ['id', '=', intval($params['id'])],
                    ['is_enable', '=', 1],
                ],
                'is_goods'        => 1,
                'is_share'        => 1,
                'is_annex'        => 1,
            ];
            $ret = QrcodeService::QrcodeList($data_params);
            $data = (empty($ret['data']) || empty($ret['data'][0])) ? [] : $ret['data'][0];
            if(empty($data))
            {
                MyViewAssign('msg', '签到不存在或已关闭');
                return MyView('public/tips_error');
            }

            // 获取团队签到数据数量
            if(!empty($data['user_id']) && !empty($this->user['id']) && $data['user_id'] == $this->user['id'])
            {
                $team_signin_data = SigninService::TeamSigninData($data['id'], $this->user['id']);
                MyViewAssign('team_signin_data', $team_signin_data);
            }

            // 获取签到数据
            if(!empty($this->user['id']))
            {
                $user_signin_data = SigninService::UserSigninData($this->user['id'], $data['id']);
                MyViewAssign('user_signin_data', $user_signin_data);
            }

            // 访问数量增加
            QrcodeService::QrcodeAccessCountInc($data);

            // seo
            $seo_title = empty($data['seo_title']) ? '签到' : $data['seo_title'];
            MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle($seo_title, 2));
            if(!empty($data['seo_keywords']))
            {
                MyViewAssign('home_seo_site_keywords', $data['seo_keywords']);
            }
            $seo_desc = empty($data['seo_desc']) ? (empty($data['describe']) ? '' : $data['describe']) : $data['seo_desc'];
            if(!empty($seo_desc))
            {
                MyViewAssign('home_seo_site_description', $seo_desc);
            }
        }
        MyViewAssign('data', $data);
        return MyView('../../../plugins/signin/view/index/index/detail');
    }

    /**
     * 签到
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Coming($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 是否已经登录
        IsUserLogin();

        // 开始处理
        $params['user_id'] = $this->user['id'];
        return SigninService::SigninComing($params);
    }
}
?>