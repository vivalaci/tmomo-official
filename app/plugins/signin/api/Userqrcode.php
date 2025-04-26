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
namespace app\plugins\signin\api;

use app\module\FormTableHandleModule;
use app\plugins\signin\api\Common;
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
            return $ret;
        }

        // 条件
        $where = [
            ['user_id', '=', $this->user['id']],
        ];

        // 总数
        $total = QrcodeService::QrcodeTotal($where);
        $page_total = ceil($total/$this->page_size);
        $start = intval(($this->page-1)*$this->page_size);

        // 获取列表
        $data_params = [
            'm'         => $start,
            'n'         => $this->page_size,
            'where'     => $where,
            'is_share'  => 1,
        ];
        $ret = QrcodeService::QrcodeList($data_params);

        // 表格数据列表处理
        $ret['data'] = (new FormTableHandleModule())->FormTableDataListHandle($ret['data'], $params);

        // 返回数据
        $result = [
            'total'       => $total,
            'page_total'  => $page_total,
            'data'        => $ret['data'],
            'base'        => $this->plugins_config,
        ];
        return DataReturn('success', 0, $result);
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
        // 是否开启了组队
        $ret = $this->IsEnableTeam($params, 0);
        if($ret['code'] != 0)
        {
            return $ret;
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

            // 表格数据列表处理
            $ret['data'] = (new FormTableHandleModule())->FormTableDataListHandle($ret['data'], $params);

            $data = (empty($ret['data']) || empty($ret['data'][0])) ? [] : $ret['data'][0];
        }
        
        // 返回数据
        $result = [
            'data'  => $data,
        ];
        return DataReturn('success', 0, $result);
    }

    /**
     * 用户签到列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-10
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function UserComingList($params = [])
    {
        // 是否开启了组队查看签到人员
        if(empty($this->plugins_config['is_team_show_coming_user']) || $this->plugins_config['is_team_show_coming_user'] != 1)
        {
            return DataReturn('无权限查看签到人员、请联系管理员', -1);
        }

        // 基础条件
        $qrcode_id = isset($params['id']) ? intval($params['id']) : 0;
        $where = [
            ['q.id', '=', $qrcode_id],
            ['q.user_id', '=', $this->user['id']]
        ];

        // 总数
        $total = QrcodeService::UserComingTotal($where);
        $page_total = ceil($total/$this->page_size);
        $start = intval(($this->page-1)*$this->page_size);

        // 获取列表
        $data_params = [
            'm'         => $start,
            'n'         => $this->page_size,
            'where'     => $where,
            'is_public' => 0,
        ];
        $ret = QrcodeService::UserComingList($data_params);

        // 返回数据
        $result = [
            'total'             => $total,
            'page_total'        => $page_total,
            'data'              => $ret['data'],
        ];
        return DataReturn('success', 0, $result);
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

        // 返回数据
        $result = [
            'data'  => $data,
            'base'  => $this->plugins_config,
        ];
        return DataReturn('success', 0, $result);
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
            return DataReturn('未开启组队功能、请联系管理员', -1);
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
            return $ret;
        }

        // 用户组队数据
        return QrcodeService::UserTeamData($this->user['id'], $this->plugins_config, $params);
    }
}
?>