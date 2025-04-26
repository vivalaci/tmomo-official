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
 * 签到 - 签到码管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-10
 * @desc    description
 */
class UserQrcode extends Common
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
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Index($params = [])
    {
        // 是否开启了组队
        $ret = $this->IsEnableTeam($params);
        if($ret['code'] != 0)
        {
            return $ret['data'];
        }

        // 总数
        $total = QrcodeService::QrcodeTotal($this->form_where);

        // 分页
        $page_params = [
            'number'    =>  $this->page_size,
            'total'     =>  $total,
            'where'     =>  $this->data_request,
            'page'      =>  $this->page,
            'url'       =>  PluginsHomeUrl('signin', 'userqrcode', 'index'),
        ];
        $page = new \base\Page($page_params);

        // 获取列表
        $data_params = [
            'm'         => $page->GetPageStarNumber(),
            'n'         => $this->page_size,
            'where'     => $this->form_where,
            'order_by'  => $this->form_order_by['data'],
            'is_share'  => 1,
        ];
        $ret = QrcodeService::QrcodeList($data_params);

        // 基础参数赋值
        MyViewAssign('params', $this->data_request);
        MyViewAssign('page_html', $page->GetPageHtml());
        MyViewAssign('data_list', $ret['data']);
        MyViewAssign('home_seo_site_title', SeoService::BrowserSeoTitle('签到码管理', 1));
        return MyView('../../../plugins/signin/view/index/userqrcode/index');
    }

    /**
     * 详情
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Detail($params = [])
    {
        // 关闭头尾
        MyViewAssign('is_to_home', 0);
        MyViewAssign('is_header', 0);
        MyViewAssign('is_footer', 0);

        // 是否开启了组队
        $ret = $this->IsEnableTeam($params, 0);
        if($ret['code'] != 0)
        {
            return $ret['data'];
        }

        // 获取详情数据
        $data = [];
        if(!empty($params['id']))
        {
            // 条件
            $where = [
                ['id', '=', intval($params['id'])],
                ['user_id', '=', $this->user['id']],
            ];

            // 获取列表
            $data_params = [
                'where'     => $where,
                'is_share'  => 1,
            ];
            $ret = QrcodeService::QrcodeList($data_params);
            $data = (empty($ret['data']) || empty($ret['data'][0])) ? [] : $ret['data'][0];
        }
        MyViewAssign('data', $data);
        return MyView('../../../plugins/signin/view/index/userqrcode/detail');
    }

    /**
     * 添加/编辑页面
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-04
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function SaveInfo($params = [])
    {
        $data = [];
        if(!empty($params['id']))
        {
            // 条件
            $where = [
                ['id', '=', intval($params['id'])],
                ['user_id', '=', $this->user['id']],
            ];

            // 获取列表
            $data_params = [
                'where'     => $where,
            ];
            $ret = QrcodeService::QrcodeList($data_params);
            $data = (empty($ret['data']) || empty($ret['data'][0])) ? [] : $ret['data'][0];
        }

        MyViewAssign('data', $data);
        MyViewAssign('params', $this->data_request);
        return MyView('../../../plugins/signin/view/index/userqrcode/saveinfo');
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-04
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Save($params = [])
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return ViewError();
        }

        // 开始处理
        $params['user_id'] = $this->user['id'];
        return QrcodeService::UserQrcodeSave($params);
    }

    /**
     * 是否启用了组队功能
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-16
     * @desc    description
     * @param   [array]           $params [输入参数]
     * @param   [int]             $is_to  [是否开启进入签到页面提示]
     */
    private function IsEnableTeam($params = [], $is_to = 1)
    {
        // 是否开启了组队
        if(empty($this->plugins_config['is_team']) || $this->plugins_config['is_team'] != 1)
        {
            if(!empty($params['id']) && $is_to == 1)
            {
                MyViewAssign('to_title', '回到签到页面');
                MyViewAssign('to_url', PluginsHomeUrl('signin', 'index', 'detail', ['id'=>$params['id']]));
            }
            MyViewAssign('msg', '未开启组队功能、请联系管理员');
            return DataReturn('error', -1, MyView('public/tips_error'));
        }
        return DataReturn('success', 0);
    }

    /**
     * 组队
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Team($params = [])
    {
        // 是否开启了组队
        $ret = $this->IsEnableTeam($params);
        if($ret['code'] != 0)
        {
            return $ret['data'];
        }

        // 用户组队数据
        $ret = QrcodeService::UserTeamData($this->user['id'], $this->plugins_config, $params);
        if($ret['code'] != 0)
        {
            MyViewAssign('msg', $ret['msg']);
            return MyView('public/tips_error');
        }

        // 是否需要填写信息
        if($ret['data']['status'] == 1)
        {
            // 进入创建页面填写数据
            $url = PluginsHomeUrl('signin', 'userqrcode', 'saveinfo', ['id'=>$ret['data']['qrcode_id']]);
        }
        // 进入签到详情页面
        $url = PluginsHomeUrl('signin', 'index', 'detail', ['id'=>$ret['data']['qrcode_id']]);
        return MyRedirect($url);
    }
}
?>