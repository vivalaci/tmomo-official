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
namespace app\plugins\signin\service;

use app\service\PluginsService;
use app\service\UserService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;

/**
 * 签到 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [
        'default_bg_images',
        'default_logo',
        'default_success_icon',
    ];

    // 客户端二维码定义
    public static $client_type_qrcode = [
        ['type'=>'pc', 'name'=>'PC端'],
        ['type'=>'h5', 'name'=>'H5端'],
        ['type'=>'weixin', 'name'=>'微信'],
        ['type'=>'alipay', 'name'=>'支付宝'],
        ['type'=>'toutiao', 'name'=>'头条'],
        ['type'=>'baidu', 'name'=>'百度'],
    ];

    /**
     * 基础配置信息保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseConfigSave($params = [])
    {
        return PluginsService::PluginsDataSave(['plugins'=>'signin', 'data'=>$params]);
    }
    
    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [boolean]          $is_cache [是否缓存中读取]
     */
    public static function BaseConfig($is_cache = true)
    {
        $ret = PluginsService::PluginsData('signin', self::$base_config_attachment_field, $is_cache);
        if(!empty($ret['data']))
        {
            // 描述
            $ret['data']['signin_desc'] = empty($ret['data']['signin_desc']) ? [] : explode("\n", $ret['data']['signin_desc']);
        }
        return $ret;
    }

    /**
     * 后台导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-06
     * @desc    description
     */
    public static function AdminNavMenuList()
    {
        return [
            [
                'name'      => '基础配置',
                'control'   => 'admin',
                'action'    => 'index',
            ],
            [
                'name'      => '签到码',
                'control'   => 'qrcode',
                'action'    => 'index',
            ],
            [
                'name'      => '用户签到',
                'control'   => 'signin',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 默认图片数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-07-27
     * @desc    description
     * @param   [array]          $config [配置信息]
     */
    public static function DefaultImagesData($config)
    {
        return [
            'default_bg_images'     => empty($config['default_bg_images']) ? StaticAttachmentUrl('signin-bg.png') : $config['default_bg_images'],
            'default_logo'          => empty($config['default_logo']) ? StaticAttachmentUrl('logo.png') : $config['default_logo'],
            'default_success_icon'  => empty($config['default_success_icon']) ? StaticAttachmentUrl('coming-success-icon.png') : $config['default_success_icon'],
        ];
    }

    /**
     * 用户搜索
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-15
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function UserSearchList($params = [])
    {
        // 返回数据
        $result = [
            'page_total'    => 0,
            'page_size'     => 20,
            'page'          => max(1, isset($params['page']) ? intval($params['page']) : 1),
            'total'         => 0,
            'data'          => [],
        ];

        // 条件
        $where = [
            ['is_delete_time', '=', 0],
        ];

        // 搜素关键字
        if(!empty($params['keywords']))
        {
            $where[] = ['number_code|username|nickname|mobile|email', 'like', '%'.$params['keywords'].'%'];
        }

        // 获取用户总数
        $result['total'] = UserService::UserTotal($where);

        // 获取用户列表
        if($result['total'] > 0)
        {
            // 基础参数
            $field = 'id,username,nickname,mobile,email,avatar';
            $order_by = 'id desc';

            // 分页计算获取数据
            $m = intval(($result['page']-1)*$result['page_size']);
            $res = UserService::UserList(['where'=>$where, 'm'=>$m, 'n'=>$result['page_size'], 'field'=>$field]);
            $result['data'] = $res['data'];
            $result['page_total'] = ceil($result['total']/$result['page_size']);
            // 数据处理
            if(!empty($result['data']))
            {
                foreach($result['data'] as &$v)
                {
                    $v = UserService::UserHandle($v);
                }
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $result);
    }

    /**
     * 商品搜索
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function GoodsSearchList($params = [])
    {
        // 条件
        $where = [
            ['g.is_delete_time', '=', 0],
            ['g.is_shelves', '=', 1]
        ];

        // 关键字
        if(!empty($params['keywords']))
        {
            $where[] = ['g.title', 'like', '%'.$params['keywords'].'%'];
        }

        // 分类id
        if(!empty($params['category_id']))
        {
            $category_ids = GoodsCategoryService::GoodsCategoryItemsIds([$params['category_id']], 1);
            $category_ids[] = $params['category_id'];
            $where[] = ['gci.category_id', 'in', $category_ids];
        }

        // 指定字段
        $field = 'g.id,g.title';

        // 获取数据
        return GoodsService::CategoryGoodsList(['where'=>$where, 'm'=>0, 'n'=>100, 'field'=>$field, 'is_admin_access'=>1]);
    }

    /**
     * 商品列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]         $params [商品id]
     * @param   [int]           $m      [分页起始值]
     * @param   [int]           $n      [分页数量]
     */
    public static function GoodsList($goods_ids = [], $m = 0, $n = 0)
    {
        // 获取推荐商品id
        if(empty($goods_ids))
        {
            return DataReturn('没有商品id', 0, ['goods'=>[], 'goods_ids'=>[]]);
        }
        if(!is_array($goods_ids))
        {
            $goods_ids = json_decode($goods_ids, true);
        }

        // 条件
        $where = [
            ['is_delete_time', '=', 0],
            ['is_shelves', '=', 1],
            ['id', 'in', $goods_ids],
        ];

        // 获取数据
        $ret = GoodsService::GoodsList(['where'=>$where, 'm'=>$m, 'n'=>$n, 'is_spec'=>1, 'is_cart'=>1]);
        $goods = [];
        if(!empty($ret['data']))
        {
            // 按照条件id进行排序
            foreach($goods_ids as $goods_id)
            {
                foreach($ret['data'] as $v)
                {
                    if($goods_id == $v['id'])
                    {
                        $goods[] = $v;
                        break;
                    }
                }
            }
        }
        return DataReturn(MyLang('operate_success'), 0, ['goods'=>$goods, 'goods_ids'=>$goods_ids]);
    }

    /**
     * 用户中心菜单
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-18
     * @desc    description
     * @param   [array]          $base [配置信息]
     */
    public static function UserCenterNav($base)
    {
        $data = [
            [
                'icon'  => StaticAttachmentUrl('app/user-signin-icon.png'),
                'title' => '签到记录',
                'url'   => '/pages/plugins/signin/user-signin/user-signin',
            ]
        ];

        // 是否开启组队
        if(isset($base['is_team']) && $base['is_team'] == 1)
        {
            $data[] = [
                'icon'  => StaticAttachmentUrl('app/user-qrcode-icon.png'),
                'title' => '签到码管理',
                'url'   => '/pages/plugins/signin/user-qrcode/user-qrcode',
            ];
        }
        return $data;
    }
}
?>