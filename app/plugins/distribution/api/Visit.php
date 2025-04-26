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

use app\service\ResourcesService;
use app\plugins\distribution\api\Common;
use app\plugins\distribution\service\BaseService;
use app\plugins\distribution\service\VisitService;

/**
 * 分销 - 拜访客户管理
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-29
 * @desc    description
 */
class Visit extends Common
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
        // 返回格式
        $result = [
            'page_total'    => 0,
            'total'         => 0,
            'data'          => [],
        ];

        // 条件
        $where = [
            ['team_user_id', '=', $this->user['id']],
        ];

        // 总数
        $result['total'] = VisitService::VisitTotal($where);

        // 分页计算
        $page = max(1, isset($params['page']) ? intval($params['page']) : 1);
        $m = intval(($page-1)*$this->page_size);

        // 获取列表
        $data_params = [
            'where'  => $where,
            'm'      => $m,
            'n'      => $this->page_size,
        ];
        $ret = VisitService::VisitList($data_params);

        // 返回数据
        $result['data'] = $ret['data'];
        $result['page_total'] = ceil($result['total']/$this->page_size);
        return DataReturn('success', 0, $result);
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
        if(!empty($params['id']))
        {
            $data_params = [
                'm'             => 0,
                'n'             => 1,
                'where'         => [
                    ['id', '=', intval($params['id'])],
                    ['team_user_id', '=', $this->user['id']],
                ],
            ];
            $ret = VisitService::VisitList($data_params);
            if(!empty($ret['data']) && empty($ret['data'][0]))
            {
                return DataReturn('success', 0, [
                    'data'  => $data,
                ]);
            }
        }
        return DataReturn(MyLang('no_data'), -1);
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
        // 数据
        $data = null;
        if(!empty($params['id']))
        {
            $data_params = [
                'm'     => 0,
                'n'     => 1,
                'where' => [
                    ['id', '=', intval($params['id'])],
                    ['team_user_id', '=', $this->user['id']],
                ],
            ];
            $ret = VisitService::VisitList($data_params);
            $data = empty($ret['data'][0]) ? null : $ret['data'][0];
        }

        // 返回数据
        return DataReturn('success', 0, [
            'data'              => $data,
            'editor_path_type'  => ResourcesService::EditorPathTypeValue('plugins_distribution-visit-'.$this->user['id']),
            
        ]);
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
        $params['user_type'] = 'user';
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return VisitService::VisitSave($params);
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
        $params['user_type'] = 'user';
        $params['user'] = $this->user;
        $params['plugins_config'] = $this->plugins_config;
        return VisitService::VisitDelete($params);
    }
}
?>