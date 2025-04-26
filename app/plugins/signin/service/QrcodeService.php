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

use think\facade\Db;
use app\service\UserService;
use app\service\ResourcesService;
use app\plugins\signin\service\BaseService;
use app\plugins\signin\service\PosterService;

/**
 * 签到 - 签到码管理服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class QrcodeService
{
    /**
     * 数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function QrcodeList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        // 获取数据
        $data = Db::name('PluginsSigninQrcode')->where($where)->field($field)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::DataHandle($data, $params));
    }

    /**
     * 数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-18
     * @desc    description
     * @param   [array]          $data      [数据]
     * @param   [array]          $params    [输入参数]
     */
    public static function DataHandle($data, $params = [])
    {
        if(!empty($data))
        {
            // 字段列表
            $keys = ArrayKeys($data);

            // 是否处理附件
            $is_annex = isset($params['is_annex']) && $params['is_annex'] == 1;
            if($is_annex && isset($params['plugins_config']))
            {
                $default_images_data = BaseService::DefaultImagesData($params['plugins_config']);
            }
            
            // 用户列表
            if(in_array('user_id', $keys) && isset($params['is_public']) && $params['is_public'] == 0)
            {
                $user_list = UserService::GetUserViewInfo(array_column($data, 'user_id'));
            }

            foreach($data as &$v)
            {
                // 用户信息
                if(array_key_exists('user_id', $v))
                {
                    if(isset($params['is_public']) && $params['is_public'] == 0)
                    {
                        $v['user'] = (!empty($user_list) && is_array($user_list) && array_key_exists($v['user_id'], $user_list)) ? $user_list[$v['user_id']] : [];
                    }
                }

                // 关联商品
                if(array_key_exists('goods_count', $v) && isset($params['is_goods']) && $params['is_goods'] == 1)
                {
                    $v['goods_list'] = [];
                    if($v['goods_count'] > 0)
                    {
                        $goods_ids = Db::name('PluginsSigninQrcodeGoods')->where(['qrcode_id'=>$v['id']])->column('goods_id');
                        $goods = BaseService::GoodsList($goods_ids);
                        $v['goods_list'] = empty($goods['data']['goods']) ? [] : $goods['data']['goods'];
                    }
                }

                // 背景图片、logo
                if(array_key_exists('bg_images', $v))
                {
                    $v['bg_images'] = empty($v['bg_images']) ? '' : ResourcesService::AttachmentPathViewHandle($v['bg_images']);
                }
                if(array_key_exists('logo', $v))
                {
                    $v['logo'] = empty($v['logo']) ? '' : ResourcesService::AttachmentPathViewHandle($v['logo']);
                }
                // 是否处理附件
                if($is_annex && !empty($default_images_data))
                {
                    if(empty($v['bg_images']))
                    {
                        $v['bg_images'] = $default_images_data['default_bg_images'];
                    }
                    if(empty($v['logo']))
                    {
                        $v['logo'] = $default_images_data['default_logo'];
                    }
                    $v['success_icon'] = $default_images_data['default_success_icon'];
                }

                // 连续翻倍奖励
                if(array_key_exists('continuous_rules', $v))
                {
                    $v['continuous_rules'] = empty($v['continuous_rules']) ? '' : json_decode($v['continuous_rules'], true);
                }

                // 指定时间段额外奖励
                if(array_key_exists('specified_time_reward', $v))
                {
                    $v['specified_time_reward'] = empty($v['specified_time_reward']) ? '' : json_decode($v['specified_time_reward'], true);
                }

                // 底部代码
                if(array_key_exists('footer_code', $v))
                {
                    $v['footer_code'] = empty($v['footer_code']) ? '' : htmlspecialchars_decode($v['footer_code']);
                }

                // 分享数据
                if(isset($params['is_share']) && $params['is_share'] == 1 && array_key_exists('id', $v) && array_key_exists('user_id', $v))
                {
                    $v['share_data'] = self::ShareData($v['id'], $v['user_id'], $params);
                }

                // 时间
                if(array_key_exists('add_time', $v))
                {
                    $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                if(array_key_exists('upd_time', $v))
                {
                    $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
                }
            }
        }
        return $data;
    }

    /**
     * 分享数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-15
     * @desc    description
     * @param   [int]          $qrcode_id [签到码id]
     * @param   [int]          $user_id   [用户id]
     * @param   [array]        $params    [输入参数]
     */
    public static function ShareData($qrcode_id, $user_id, $params = [])
    {
        // url分享
        $share_url = PosterService::UserShareUrl($qrcode_id, $user_id);

        // 默认二维码
        $res = PosterService::UserShareQrcodeCreate($qrcode_id, $user_id, false, APPLICATION_CLIENT_TYPE);

        // 默认返回数据格式
        $result = [
            'url'       => $share_url,
            'qrcode'    => empty($res['data']) ? '' : $res['data'],
        ];

        // 是否读取全部二维码
        if(isset($params['is_qrcode']) && $params['is_qrcode'] == 1)
        {
            $result['qrcode_all'] = [];
            foreach(BaseService::$client_type_qrcode as $v)
            {
                $result['qrcode_all'][] = [
                    'type'  => $v['type'],
                    'name'  => $v['name'],
                    'res'   => PosterService::UserShareQrcodeCreate($qrcode_id, $user_id, false, $v['type']),
                ];
            }
        }
        return $result;
    }

    /**
     * 总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function QrcodeTotal($where = [])
    {
        return (int) Db::name('PluginsSigninQrcode')->where($where)->count();
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-07
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function QrcodeSave($params = [])
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();

        // 参数校验
        $ret = self::QrcodeSaveParamsCheck($params, $base['data']);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 关联商品
        $goods_ids = empty($params['goods_ids']) ? [] : explode(',', $params['goods_ids']);

        // 附件
        $data_fields = ['bg_images', 'logo'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'user_id'               => intval($params['user_id']),
            'reward_master'         => empty($params['reward_master']) ? 0 : intval($params['reward_master']),
            'reward_invitee'        => empty($params['reward_invitee']) ? 0 : intval($params['reward_invitee']),
            'continuous_rules'      => self::ContinuousRulesParamsHandle($params),
            'specified_time_reward' => self::SpecifiedTimeRewardParamsHandle($params),
            'max_number_limit'      => empty($params['max_number_limit']) ? 0 : intval($params['max_number_limit']),
            'day_number_limit'      => empty($params['day_number_limit']) ? 0 : intval($params['day_number_limit']),
            'note'                  => empty($params['note']) ? '' : $params['note'],
            'is_enable'             => empty($params['is_enable']) ? 0 : intval($params['is_enable']),
            'bg_images'             => $attachment['data']['bg_images'],
            'logo'                  => $attachment['data']['logo'],
            'name'                  => empty($params['name']) ? '' : $params['name'],
            'tel'                   => empty($params['tel']) ? '' : $params['tel'],
            'address'               => empty($params['address']) ? '' : $params['address'],
            'goods_count'           => count($goods_ids),
            'footer_code'           => empty($params['footer_code']) ? '' : $params['footer_code'],
            'seo_title'             => empty($params['seo_title']) ? '' : $params['seo_title'],
            'seo_keywords'          => empty($params['seo_keywords']) ? '' : $params['seo_keywords'],
            'seo_desc'              => empty($params['seo_desc']) ? '' : $params['seo_desc'],
        ];

        // 默认赠送积分
        if(empty($data['reward_master']))
        {
            $daat['reward_master'] = empty($base['data']['reward_master']) ? 1 : intval($base['data']['reward_master']);
        }
        if(empty($data['reward_invitee']))
        {
            $daat['reward_invitee'] = empty($base['data']['reward_invitee']) ? 1 : intval($base['data']['reward_invitee']);
        }

        // 开启事务
        Db::startTrans();

        // 捕获异常
        try {
            if(empty($params['id']))
            {
                $data['add_time'] = time();
                $qrcode_id = Db::name('PluginsSigninQrcode')->insertGetId($data);
                if($qrcode_id <= 0)
                {
                    throw new \Exception(MyLang('insert_fail'));
                }
            } else {
                $data['upd_time'] = time();
                $qrcode_id = intval($params['id']);
                if(!Db::name('PluginsSigninQrcode')->where(['id'=>$qrcode_id])->update($data))
                {
                    throw new \Exception('编辑失败');
                }
            }

            // 关联推荐商品
            // 先删除关联数据再添加
            Db::name('PluginsSigninQrcodeGoods')->where(['qrcode_id'=>$qrcode_id])->delete();

            // 添加关联数据
            if(!empty($goods_ids))
            {
                $goods_data = [];
                foreach($goods_ids as $goods_id)
                {
                    $goods_data[] = [
                        'qrcode_id' => $qrcode_id,
                        'goods_id'  => $goods_id,
                        'add_time'  => time(),
                    ];
                }
                if(Db::name('PluginsSigninQrcodeGoods')->insertAll($goods_data) < count($goods_data))
                {
                    throw new \Exception('关联推荐商品添加失败');
                }
            }

            // 完成
            Db::commit();
            return DataReturn(MyLang('operate_success'), 0, $qrcode_id);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 连续翻倍参数解析
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-15
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function ContinuousRulesParamsHandle($params = [])
    {
        $result = [];

        // 周期和费用必须
        if(!empty($params['continuous_number']) && !empty($params['continuous_value']) && is_array($params['continuous_number']) && is_array($params['continuous_value']) && count($params['continuous_number']) == count($params['continuous_value']))
        {
            // 循环处理规则数据
            foreach($params['continuous_number'] as $k=>$v)
            {
                if(array_key_exists($k, $params['continuous_value']))
                {
                    $result[] = [
                        'number'    => $v,
                        'value'     => $params['continuous_value'][$k],
                    ];
                }
            }
        }
        return empty($result) ? '' : json_encode($result);
    }

    /**
     * 指定时段额外奖励参数解析
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-12-15
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SpecifiedTimeRewardParamsHandle($params = [])
    {
        $result = '';
        if(!empty($params['specified_time_start']) && !empty($params['specified_time_start']) && !empty($params['specified_value']))
        {
            $result = json_encode([
                'time_start'    => $params['specified_time_start'],
                'time_end'      => $params['specified_time_end'],
                'value'         => intval($params['specified_value']),
            ]);
        }
        return $result;
    }

    /**
     * 保存参数校验
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-07
     * @desc    description
     * @param   [array]          $params    [输入参数]
     * @param   [array]          $plugins_config [配置数据]
     */
    public static function QrcodeSaveParamsCheck($params = [], $plugins_config = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user_id',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'seo_title',
                'checked_data'      => '100',
                'is_checked'        => 1,
                'error_msg'         => MyLang('form_seo_title_message'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'seo_keywords',
                'checked_data'      => '130',
                'is_checked'        => 1,
                'error_msg'         => MyLang('form_seo_keywords_message'),
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'seo_desc',
                'checked_data'      => '230',
                'is_checked'        => 1,
                'error_msg'         => MyLang('form_seo_desc_message'),
            ],
        ];

        // 签到码必填用户信息
        if(isset($plugins_config['is_qrcode_must_userinfo']) && $plugins_config['is_qrcode_must_userinfo'] == 1)
        {
            $p[] = [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '2,30',
                'error_msg'         => '收件人姓名格式 2~30 个字符之间',
            ];
            $p[] = [
                'checked_type'      => 'length',
                'key_name'          => 'tel',
                'checked_data'      => '6,30',
                'error_msg'         => '请填写收件人电话 6~15 个字符',
            ];
            $p[] = [
                'checked_type'      => 'length',
                'key_name'          => 'address',
                'checked_data'      => '1,230',
                'error_msg'         => '请填写收件人地址、最多230个字符',
            ];
        }

        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        return DataReturn('success', 0);
    }

    /**
     * 用户保存签到码数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-07
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UserQrcodeSave($params = [])
    {
        // 插件配置信息
        $base = BaseService::BaseConfig();

        // 是否开启组队
        if(empty($base['data']) || empty($base['data']['is_team']))
        {
            return DataReturn('未开启组队功能、请联系管理员', -1);
        }

       // 参数校验
        $ret = self::QrcodeSaveParamsCheck($params, $base['data']);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 数据
        $data = [
            'user_id'   => intval($params['user_id']),
            'note'      => empty($params['note']) ? '' : $params['note'],
            'name'      => empty($params['name']) ? '' : $params['name'],
            'tel'       => empty($params['tel']) ? '' : $params['tel'],
            'address'   => empty($params['address']) ? '' : $params['address'],
        ];
        // 来源id
        if(!empty($params['rid']))
        {
            $data['request_id'] = intval($params['rid']);
        }

        // 捕获异常
        try {
            if(empty($params['id']))
            {
                // 该用户的数据、是否已存在、前端不允许用户重复创建数据
                $info = self::UserQrCodeInfo($data['user_id']);
                if(!empty($info))
                {
                    $data['upd_time'] = time();
                    $qrcode_id = $info['id'];
                    if(!Db::name('PluginsSigninQrcode')->where(['id'=>$info['id'], 'user_id'=>$data['user_id']])->update($data))
                    {
                        throw new \Exception('编辑失败');
                    }
                } else {
                    // 默认赠送积分
                    $data['reward_master'] = empty($base['data']['reward_master']) ? 1 : intval($base['data']['reward_master']);
                    $data['reward_invitee'] = empty($base['data']['reward_invitee']) ? 1 : intval($base['data']['reward_invitee']);

                    $data['is_enable'] = 1;
                    $data['add_time'] = time();
                    $qrcode_id = Db::name('PluginsSigninQrcode')->insertGetId($data);
                    if($qrcode_id <= 0)
                    {
                        throw new \Exception(MyLang('insert_fail'));
                    }
                }
            } else {
                $data['upd_time'] = time();
                $qrcode_id = intval($params['id']);
                if(!Db::name('PluginsSigninQrcode')->where(['id'=>$qrcode_id, 'user_id'=>$data['user_id']])->update($data))
                {
                    throw new \Exception('编辑失败');
                }
            }

            return DataReturn('组队成功、立即分享给好友赚取更多积分！', 0, $qrcode_id);
        } catch(\Exception $e) {
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 获取用户签到码数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-23
     * @desc    description
     * @param   [int]    $user_id [用户id]
     */
    public static function UserQrCodeInfo($user_id)
    {
        return Db::name('PluginsSigninQrcode')->where(['user_id'=>$user_id])->field('id,name,tel,address,is_enable')->order('id desc')->find();
    }

    /**
     * 状态更新
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-07
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function QrcodeStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => MyLang('operate_field_error_tips'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => MyLang('form_status_range_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据更新
        if(Db::name('PluginsSigninQrcode')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
            return DataReturn(MyLang('edit_success'), 0);
        }
        return DataReturn(MyLang('edit_fail'), -100);
    }

    /**
     * 删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function QrcodeDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn(MyLang('data_id_error_tips'), -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 开启事务
        Db::startTrans();

        // 捕获异常
        try {
            // 签到码删除
            if(!Db::name('PluginsSigninQrcode')->where(['id'=>$params['ids']])->delete())
            {
                throw new \Exception('删除失败');
            }

            // 签到商品删除
            if(Db::name('PluginsSigninQrcodeGoods')->where(['qrcode_id'=>$params['ids']])->delete() === false)
            {
                throw new \Exception('关联商品删除失败');
            }

            // 签到数据删除
            if(Db::name('PluginsSigninQrcodeData')->where(['qrcode_id'=>$params['ids']])->delete() === false)
            {
                throw new \Exception('关联签到删除失败');
            }

            // 完成
            Db::commit();
            return DataReturn(MyLang('delete_success'), 0);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 访问统计加1
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-10-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function QrcodeAccessCountInc($params = [])
    {
        if(!empty($params['id']))
        {
            return Db::name('PluginsSigninQrcode')->where(['id'=>intval($params['id'])])->inc('access_count')->update();
        }
        return false;
    }

    /**
     * 用户签到总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function UserComingTotal($where = [])
    {
        return (int) Db::name('PluginsSigninQrcode')->alias('q')->join('plugins_signin_qrcode_data qd', 'q.id=qd.qrcode_id')->where($where)->count('qd.id');
    }

    /**
     * 用户签到数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function UserComingList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'qd.*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'qd.id desc' : $params['order_by'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        // 获取数据
        $data = Db::name('PluginsSigninQrcode')->alias('q')->join('plugins_signin_qrcode_data qd', 'q.id=qd.qrcode_id')->where($where)->field($field)->limit($m, $n)->order($order_by)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, self::DataHandle($data, $params));
    }

    /**
     * 用户组队数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-23
     * @desc    description
     * @param   [int]       $user_id [用户id]
     * @param   [array]     $base    [插件配置数据]
     * @param   [array]     $params  [输入参数]
     */
    public static function UserTeamData($user_id, $base, $params = [])
    {
        // 用户签到码
        $info = self::UserQrCodeInfo($user_id);

        // 是否开启了必须填写联系人信息、未开启直接创建数据、则进入页面填写数据再创建
        // 0 组队成功
        // 1 需要填写联系人信息
        $status = 0;
        $qrcode_id = 0;
        if(isset($base['is_qrcode_must_userinfo']) && $base['is_qrcode_must_userinfo'] == 1)
        {
            if(!empty($info))
            {
                // 签到码id
                $qrcode_id = $info['id'];

                // 是否已填写联系信息
                if(empty($info['name']) || empty($info['tel']) || empty($info['address']))
                {
                    $status = 1;
                }
            } else {
                $status = 1;
            }
        } else {
            // 不存在签到码则直接添加数据组队
            if(empty($info))
            {
                $params['user_id'] = $user_id;
                $ret = self::UserQrcodeSave($params);
                if($ret['code'] == 0)
                {
                    $qrcode_id = $ret['data'];
                } else {
                    return $ret;
                }
            } else {
                $qrcode_id = $info['id'];
            }
        }
        return DataReturn('组队成功、立即分享给好友赚取更多积分！', 0, ['status'=>$status, 'qrcode_id'=>$qrcode_id]);
    }
}
?>