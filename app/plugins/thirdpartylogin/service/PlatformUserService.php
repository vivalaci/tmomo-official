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
namespace app\plugins\thirdpartylogin\service;

use think\facade\Db;
use app\service\UserService;
use app\service\ApiService;
use app\service\ResourcesService;
use app\service\SystemService;
use app\plugins\thirdpartylogin\service\BaseService;

/**
 * 第三方登录 - 平台用户服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class PlatformUserService
{
    /**
     * 用户登录信息处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [string]         $platform       [平台类型]
     * @param   [array]          $data           [用户授权信息]
     * @param   [array]          $plugins_config [插件配置信息]
     */
    public static function PlatformUserLoginHandle($platform, $data, $plugins_config)
    {
        // 必须存在openid
        if(empty($data['openid']))
        {
            return DataReturn('openid为空', -1);
        }

        // 当前登录用户、存在已登录用户则校验状态（无效状态则赋值空）
        $login_user = UserService::LoginUserInfo();
        if(!empty($login_user))
        {
            $check = UserService::UserStatusCheck($login_user['id']);
            if($check['code'] != 0)
            {
                $login_user = [];
            }
        }

        // 获取用户信息
        $user_id = 0;
        $platform_user_id = 0;
        $platform_user = self::OpenPlatformUserInfo($platform, $data);
        if(!empty($platform_user))
        {
            // 平台用户id
            $platform_user_id = $platform_user['id'];

            // 当前用户已登录
            if(empty($login_user))
            {
                // 赋值原始平台用户状态
                $data['status'] = $platform_user['status'];

                // 是否存在绑定用户、并确认用户是否存在、确保已绑定用户存在、避免已被删除
                if(!empty($platform_user['user_id']))
                {
                    // 用户是否存在
                    $user = UserService::UserBaseInfo('id', $platform_user['user_id'], 'id');
                    if(empty($user))
                    {
                        $data['user_id'] = 0;
                        $data['status'] = ($platform_user['status'] == 1) ? 2 : 0;
                    } else {
                        $user_id = $user['id'];
                    }
                }
            } else {
                // 更新信息
                $data['status'] = 1;
                $data['user_id'] = $login_user['id'];

                // 用户id
                $user_id = $login_user['id'];
            }

            // openid 不一致的时候不更新用户数据
            // 存在unionid用户就不会再写入用户信息，插件用户登录表一个平台对应一条unionid数据
            $platform_user_data = $data;
            if(!empty($platform_user['openid']) && $platform_user['openid'] != $data['openid'])
            {
                unset($platform_user_data['openid']);
            }

            // 用户存在则更新用户数据
            $platform_user_data['upd_time'] = time();
            if(!Db::name('PluginsThirdpartyloginUser')->where(['id'=>$platform_user['id']])->update($platform_user_data))
            {
                return DataReturn('平台用户更新失败', -1);
            }
        } else {
            // 当前用户已登录
            $status = 0;
            if(!empty($login_user))
            {
                $status = 1;
                $user_id = $login_user['id'];
            }

            // 平台用户添加
            $platform_insert = self::PlatformUserInsert($data, $platform, $user_id, $status);
            if($platform_insert['code'] != 0)
            {
                return $platform_insert;
            }
            $data['status'] = $status;
            $platform_user_id = $platform_insert['data'];

            // 重新获取添加的用户信息
            $platform_user = Db::name('PluginsThirdpartyloginUser')->where(['id'=>$platform_user_id])->find();
        }

        // 用户状态正常、是否存在绑定用户
        if(isset($data['status']) && $data['status'] == 1 && !empty($user_id))
        {
            // 用户状态校验
            $check = UserService::UserStatusCheck($user_id);
            if($check['code'] != 0)
            {
                return $check;
            }

            // 用户登录成功处理
            return self::UserLoginSuccessHandle($platform, $user_id, $data);
        }

        // 用户状态正常、openid或unionid是否存在用户表中
        $ret = self::SystemUserInfo($platform, $data);
        if($ret['code'] == 0)
        {
            // 用户状态校验
            $user_id = $ret['data'];
            $check = UserService::UserStatusCheck($user_id);
            if($check['code'] != 0)
            {
                return $check;
            }

            // 绑定用户
            Db::name('PluginsThirdpartyloginUser')->where(['id'=>$platform_user_id])->update([
                'user_id'   => $user_id,
                'status'    => 1,
                'upd_time'  => time(),
            ]);

            // 用户登录成功处理
            return self::UserLoginSuccessHandle($platform, $user_id, $data);
        }

        // 是否开启强制绑定帐号
        if(isset($plugins_config['is_force_bind_user']) && $plugins_config['is_force_bind_user'] == 1)
        {
            // 平台名称
            $platform_user['platform_name'] = BaseService::PlatformTypeName($plugins_config, $platform_user['platform']);

            // 跳转地址
            $back_url = MyUrl('index/user/logininfo');

            // 来源标识
            $request_data = BaseService::GetRequestValue();
            if(!empty($request_data) && isset($request_data['request_type']) && $request_data['request_type'] == 'scan')
            {
                $request_data['status'] = 3;
                $request_data['bind_platform_user_id'] = $platform_user_id;
                ScanLoginService::SetScanValue($request_data);

                // 设置跳转地址
                $back_url = BaseService::BackRedirectUrl();
            }

            // 根据终端返回数据格式
            if(BaseService::GetApplicationClientType() == 'pc')
            {
                // 缓存用户绑定信息
                MyCookie(BaseService::$bind_platform_user_key, $platform_user_id);
                return DataReturn('需要绑定账号', 0, $back_url);
            } else {
                $platform_user['is_force_bind_user'] = 1;
                return DataReturn('需要绑定账号', 0, $platform_user);
            }
        }

        // 直接写入数据库并登录返回
        $user_insert = self::UserInsert($platform, $data);
        if($user_insert['code'] != 0)
        {
            return $user_insert;
        }

        // 更新平台用户关联id
        $platform_bind = self::PlatformUserBind($platform_user_id, $user_insert['data']);
        if($platform_bind['code'] != 0)
        {
            return $platform_bind;
        }

        // 用户状态校验
        $check = UserService::UserStatusCheck($user_insert['data']);
        if($check['code'] != 0)
        {
            return $check;
        }

        // 用户登录成功处理
        return self::UserLoginSuccessHandle($platform, $user_insert['data'], $data);
    }

    /**
     * 用户登陆成功处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-11-13
     * @desc    description
     * @param   [string]       $platform    [平台类型]
     * @param   [int]          $user_id     [用户id]
     * @param   [array]        $data        [用户授权信息]
     */
    public static function UserLoginSuccessHandle($platform, $user_id, $data)
    {
        // 来源系统标识
        $system_type = BaseService::GetSystemType();
        // 来源终端
        $application_client_type = BaseService::GetApplicationClientType();

        // 是否存在用户平台基础信息、不存在则添加
        $user_base_platform = Db::name('UserPlatform')->where(['user_id'=>$user_id, 'platform'=>$application_client_type])->find();
        if(empty($user_base_platform))
        {
            $user_base_platform_insert = [
                'user_id'       => $user_id,
                'system_type'   => $system_type,
                'platform'      => $application_client_type,
                'add_time'      => time(),
            ];
            // 微信webopenid
            if($platform == 'weixin' && IsWeixinEnv() && !empty($data['openid']))
            {
                $user_base_platform_insert['weixin_web_openid'] = $data['openid'];
            }
            // unionid
            if(in_array($platform, ['weixin', 'qq', 'toutiao']) && !empty($data['unionid']))
            {
                $user_base_platform_insert[$platform.'_unionid'] = $data['unionid'];
            }
            Db::name('UserPlatform')->insertGetId($user_base_platform_insert);
        }

        // openid、unionid同步到用户表处理
        if(in_array($platform, ['weixin', 'qq', 'toutiao']))
        {
            // 获取用户信息
            $user_list = Db::name('UserPlatform')->where(['user_id'=>$user_id])->field('id,user_id,platform,weixin_openid,weixin_unionid,weixin_web_openid,qq_openid,qq_unionid')->select()->toArray();
            if(!empty($user_list))
            {
                foreach($user_list as $u)
                {
                    // openid
                    if(!empty($data['openid']))
                    {
                        // 微信环境、微信端仅手机环境存在用户表web字段
                        if(in_array($u['platform'], ['pc', 'h5']) && $platform == 'weixin' && IsWeixinEnv() && empty($u['weixin_web_openid']))
                        {
                            Db::name('UserPlatform')->where(['id'=>$u['id']])->update(['weixin_web_openid'=>$data['openid']]);
                        }
                    }

                    // unionid
                    if(!empty($data['unionid']) && empty($u[$platform.'_unionid']))
                    {
                        Db::name('UserPlatform')->where(['id'=>$u['id']])->update([$platform.'_unionid'=>$data['unionid']]);
                    }
                }
            }
        }

        // 根据平台处理不同登陆逻辑
        if($application_client_type == 'pc')
        {
            // 用户登录session纪录
            if(UserService::UserLoginRecord($user_id))
            {
                // 用户cookie设置
                self::SetUserCookie($user_id);

                // 返回跳转地址
                return DataReturn(MyLang('login_success'), 0,  BaseService::BackRedirectUrl());
            }
        } else {
            // 生成token和用户信息返回
            $user = self::SystemAppUserLoginUserInfo($user_id, $application_client_type, $system_type);
            return DataReturn(MyLang('login_success'), 0, $user);
        }
        return DataReturn('登录失败', -100);
    }

    /**
     * APP用户登录成功用户信息处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-01-25
     * @desc    description
     * @param   [int]          $user_id      [用户id]
     * @param   [string]       $client_type  [平台类型]
     * @param   [string]       $system_type  [系统标识]
     */
    public static function SystemAppUserLoginUserInfo($user_id, $client_type, $system_type)
    {
        // 用户平台表结构
        $structure = ResourcesService::TableStructureData('UserPlatform');
        unset($structure['id'], $structure['user_id'], $structure['add_time'], $structure['upd_time']);
        $structure = array_keys($structure);

        // 查询字段处理
        $field = 'u.*, up.'.implode(', up.', $structure);

        // 查询用户信息
        $where = [
            ['up.system_type', '=', $system_type],
            ['up.platform', '=', $client_type],
            ['u.id', '=', $user_id],
            ['u.is_delete_time', '=', 0],
            ['u.is_logout_time', '=', 0],
        ];
        $user = Db::name('User')->alias('u')->join('user_platform up', 'u.id=up.user_id')->where($where)->field($field)->find();
        if(!empty($user))
        {
            // 移出密码字段数据
            unset($user['salt'], $user['pwd']);

            // 是否强制绑定手机号码
            $user['is_mandatory_bind_mobile'] = intval(MyC('common_user_is_mandatory_bind_mobile'));

            // 会员码生成处理
            if(empty($user['number_code']))
            {
                $user['number_code'] = UserService::UserNumberCodeCreatedHandle($user_id);
            }

            // 生成token
            $where = [
                ['user_id', '=', $user_id],
                ['system_type', '=', $system_type],
                ['platform', '=', $client_type],
            ];
            $user['token'] = ApiService::CreatedUserToken($user_id);
            Db::name('UserPlatform')->where($where)->update(['token'=>$user['token'], 'upd_time'=>time()]);

            // 用户信息处理
            $user = UserService::UserHandle($user);

            // 存储token缓存
            MyCache(SystemService::CacheKey('shopxo.cache_user_info').$user['token'], $user);
            // 兼容api端缓存
            MyCache(MyConfig('shopxo.cache_user_info').'_'.$system_type.'_api'.$user['token'], $user);
        }
        return $user;
    }

    /**
     * 系统用户是否存在对应信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-10-27
     * @desc    description
     * @param   [string]         $platform [平台类型]
     * @param   [array]          $data     [用户授权信息]
     */
    public static function SystemUserInfo($platform, $data)
    {
        // 目前仅微信和QQ存在在用户表存在相关字段
        if(in_array($platform, ['weixin', 'qq', 'toutiao']))
        {
            // 来源系统标识
            $system_type = BaseService::GetSystemType();

            // 系统唯一类型
            $common_user_unique_system_type_model = MyC('common_user_unique_system_type_model');

            // openid
            if(!empty($data['openid']))
            {
                $openid_field = '';
                if($platform == 'weixin')
                {
                    // 微信环境、微信端仅手机环境存在用户表web字段
                    if(IsWeixinEnv())
                    {
                        $openid_field = 'weixin_web_openid';
                    }
                } else {
                    $openid_field = $platform.'_openid';
                }
                if(!empty($openid_field))
                {
                    // 来源终端
                    $application_client_type = BaseService::GetApplicationClientType();

                    // 是否存在用户平台基础信息
                    $where = [
                        [$openid_field, '=', $data['openid']],
                    ];
                    if($common_user_unique_system_type_model == 1)
                    {
                        $where[] = ['system_type', '=', $system_type];
                    }
                    $user_base_platform = Db::name('UserPlatform')->where(array_merge($where, [['platform', '=', $application_client_type]]))->find();
                    if(empty($user_base_platform))
                    {
                        // 当前平台不存在，存在其他平台则添加当前平台数据
                        $temp = Db::name('UserPlatform')->where($where)->find();
                        if(!empty($temp))
                        {
                            $ret = self::SystemUserPlatformInsert($temp, $system_type, $application_client_type, $platform, $data);
                            if($ret['code'] != 0)
                            {
                                return $ret;
                            }
                            $user_base_platform = $ret['data'];
                        }
                    }
                    if(!empty($user_base_platform))
                    {
                        return DataReturn('success', 0, $user_base_platform['user_id']);
                    }
                }
            }

            // unionid
            if(in_array($platform, ['weixin', 'qq', 'toutiao']) && !empty($data['unionid']))
            {
                // 是否存在用户平台基础信息
                $where = [
                    [$platform.'_unionid', '=', $data['unionid']],
                ];
                if($common_user_unique_system_type_model == 1)
                {
                    $where[] = ['system_type', '=', $system_type];
                }
                $user_base_platform = Db::name('UserPlatform')->where($where)->find();
                if(!empty($user_base_platform))
                {
                    return DataReturn('success', 0, $user_base_platform['user_id']);
                }
            }
        }
        return DataReturn('无相关用户', -1);
    }

    /**
     * 用户平台信息添加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-09-07
     * @desc    description
     * @param   [array]           $platform_info           [平台信息]
     * @param   [string]          $system_type             [系统类型]
     * @param   [string]          $application_client_type [客户端理想]
     * @param   [string]          $platform                [登录平台]
     * @param   [array]           $data                    [登录数据]
     */
    public static function SystemUserPlatformInsert($platform_info, $system_type, $application_client_type, $platform, $data)
    {
        $insert_data = [
            'user_id'      => $platform_info['user_id'],
            'system_type'  => $system_type,
            'platform'     => $application_client_type,
            'add_time'     => time(),
        ];
        if(!empty($data['openid']))
        {
            $insert_data[$platform.'_openid'] = $data['openid'];
        }
        // unionid
        if(in_array($platform, ['weixin', 'qq', 'toutiao']) && !empty($data['unionid']))
        {
            $insert_data[$platform.'_unionid'] = $data['unionid'];
        }
        $insert_data['id'] = Db::name('UserPlatform')->insertGetId($insert_data);
        if($insert_data['id'] <= 0)
        {
            return DataReturn('平台用户信息添加失败', -1);
        }
        return DataReturn('success', 0, $insert_data);
    }

    /**
     * 用户添加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [string]         $platform       [平台类型]
     * @param   [array]          $data           [用户授权信息]
     */
    public static function UserInsert($platform, $data)
    {
        // 根据手机、邮箱先查询用户
        if(!empty($data['mobile']))
        {
            $user = UserService::UserInfo('mobile', $data['mobile']);
        }
        if(!empty($data['email']) && empty($user))
        {
            $user = UserService::UserInfo('email', $data['email']);
        }

        // 用户信息、不存在则添加
        $user_id = 0;
        if(!empty($user))
        {
            $user_id = $user['id'];

            // 头像和昵称绑定
            $upd_data = [];
            foreach(['avatar', 'nickname'] as $fv)
            {
                if(empty($user[$fv]) && !empty($data[$fv]))
                {
                    $upd_data[$fv] = $data[$fv];
                }
            }
            if(!empty($upd_data))
            {
                UserService::UserUpdateHandle($upd_data, $user_id);
            }
        } else {
            // 需要添加的用户信息
            $insert_data = [
                'nickname'  => isset($data['nickname']) ? $data['nickname'] : '',
                'avatar'    => isset($data['avatar']) ? $data['avatar'] : '',
                'mobile'    => isset($data['mobile']) ? $data['mobile'] : '',
                'email'     => isset($data['email']) ? $data['email'] : '',
                'gender'    => isset($data['gender']) ? intval($data['gender']) : 0,
                'province'  => isset($data['province']) ? $data['province'] : '',
                'city'      => isset($data['city']) ? $data['city'] : '',
                'add_time'  => time(),
            ];
            // 微信登陆处理openid和unionid
            if(in_array($platform, ['weixin', 'qq', 'toutiao']))
            {
                // openid处理
                if(!empty($data['openid']))
                {
                    if($platform == 'weixin')
                    {
                        // openid仅微信环境存储到用户表 weixin_web_openid
                        if(IsWeixinEnv())
                        {
                            $insert_data['weixin_web_openid'] = $data['openid'];
                        }
                    } else {
                        $insert_data[$platform.'_openid'] = $data['openid'];
                    }
                }

                // 是否存在unionid
                if(!empty($data['unionid']))
                {
                    $insert_data[$platform.'_unionid'] = $data['unionid'];
                }
            }

            // 添加用户
            $ret = UserService::UserInsert($insert_data);
            if($ret['code'] != 0)
            {
                return $ret;
            }
            $user_id = $ret['data']['user_id'];
        }
        return DataReturn('添加成功', 0, $user_id);
    }

    /**
     * 平台用户绑定
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [int]          $platform_user_id [平台用户id]
     * @param   [int]          $user_id          [系统用户id]
     */
    public static function PlatformUserBind($platform_user_id, $user_id)
    {
        $data = [
            'user_id'   => $user_id,
            'status'    => 1,
            'upd_time'  => time(),
        ];
        if(!Db::name('PluginsThirdpartyloginUser')->where(['id'=>$platform_user_id])->update($data))
        {
            return DataReturn('平台用户关联失败', -1);
        }

        // 获取用户判断是否需要更新信息
        $system_user = UserService::UserBaseInfo('id', $user_id);
        $platform_user = Db::name('PluginsThirdpartyloginUser')->where(['id'=>$platform_user_id])->find();
        if(!empty($system_user) && !empty($platform_user))
        {
            // 用户基础信息更新
            $update_data = [];
            $field_arr = ['nickname', 'mobile', 'email', 'avatar', 'gender', 'province', 'city'];
            foreach($field_arr as $v)
            {
                if(array_key_exists($v, $system_user) && empty($system_user[$v]) && !empty($platform_user[$v]))
                {
                    $update_data[$v] = $platform_user[$v];
                }
            }
            if(!empty($update_data))
            {
                UserService::UserUpdateHandle($update_data, $user_id);
            }

            // 平台信息处理
            // unionid
            if(!empty($platform_user['unionid']) && in_array($platform_user['platform'], ['weixin', 'qq', 'toutiao']))
            {
                // 来源系统标识
                $system_type = BaseService::GetSystemType();

                // 来源终端
                $application_client_type = BaseService::GetApplicationClientType();

                // 当前平台信息是否存在
                $temp = Db::name('UserPlatform')->where(['system_type'=>$system_type, 'platform'=>$application_client_type, 'user_id'=>$user_id])->find();
                if(empty($temp))
                {
                    self::SystemUserPlatformInsert($platform_user, $system_type, $application_client_type, $platform_user['platform'], $platform_user);
                } else {
                    // 更新数据
                    $upd_data = [
                        $platform_user['platform'].'_unionid'  => $platform_user['unionid'],
                        'upd_time'                             => time(),
                    ];
                    // openid
                    $openid_field = '';
                    if($platform_user['platform'] == 'weixin')
                    {
                        // 微信环境、微信端仅手机环境存在用户表web字段
                        if(!empty($platform_user['weixin_web_openid']))
                        {
                            $openid_field = 'weixin_web_openid';
                        }
                    } else {
                        $openid_field = $platform_user['platform'].'_openid';
                    }
                    if(!empty($openid_field) && empty($temp[$openid_field]) && !empty($platform_user[$openid_field]))
                    {
                        $upd_data[$openid_field] = $platform_user[$openid_field];
                    }
                    // 更新当前平台数据
                    Db::name('UserPlatform')->where(['id'=>$temp['id']])->update($upd_data);
                }
            }
        }

        return DataReturn('平台用户关联成功', 0);
    }

    /**
     * 平台用户添加
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [array]         $data     [用户授权数据]
     * @param   [string]        $platform [平台]
     * @param   [int]           $user_id  [用户id]
     * @param   [int]           $status   [绑定状态]
     */
    public static function PlatformUserInsert($data, $platform, $user_id = 0, $status = 0)
    {
        // 添加平台用户
        $data['user_id'] = $user_id;
        $data['status'] = $status;
        $data['add_time'] = time();
        $data['platform'] = $platform;
        $platform_id = Db::name('PluginsThirdpartyloginUser')->insertGetId($data);
        if($platform_id > 0)
        {
            return DataReturn('添加成功', 0, $platform_id);
        }
        return DataReturn('平台用户添加失败', -1);
    }

    /**
     * 用户登录cookie设置
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-02-26
     * @desc    description
     * @param   [int]          $user_id [用户id]
     */
    public static function SetUserCookie($user_id)
    {
        // 获取用户信息
        $user = UserService::UserInfo('id', intval($user_id), 'id,token,username,nickname,mobile,email,avatar');
        if(!empty($user))
        {
            // 用户信息处理
            $user = UserService::UserHandle($user);

            // 没有token则生成
            if(empty($user['token']))
            {
                // 生成用户token
                $user['token'] = ApiService::CreatedUserToken($user_id);

                // 更新用户token
                if(!UserService::UserPlatformUpdate('user_id', $user_id, ['token' => $user['token']]))
                {
                    return false;
                }
            }

            // 设置cookie数据
            MyCookie('user_info', json_encode($user, JSON_UNESCAPED_UNICODE), false);
            return true;
        }
        return false;
    }

    /**
     *  开发平台用户信息获取
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [string]          $platform [平台类型]
     * @param   [array]           $data     [用户授权信息（openid,unionid）]
     */
    public static function OpenPlatformUserInfo($platform, $data)
    {
        // 初始化用户
        $user = [];

        // openid
        if(!empty($data['openid']))
        {
            $user = Db::name('PluginsThirdpartyloginUser')->where(['platform'=>$platform, 'openid'=>$data['openid']])->find();
        }

        // unionid
        if(empty($user) && in_array($platform, ['weixin', 'qq', 'toutiao']) && !empty($data['unionid']))
        {
            $user = Db::name('PluginsThirdpartyloginUser')->where(['platform'=>$platform, 'unionid'=>$data['unionid']])->find();
        }

        return $user;
    }
    
    /**
     * 平台用户信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-22
     * @desc    description
     * @param   [int]          $platform_user_id [平台用户id]
     */
    public static function PlatformUserInfo($platform_user_id)
    {
        return empty($platform_user_id) ? [] : Db::name('PluginsThirdpartyloginUser')->where(['id'=>intval($platform_user_id)])->find();
    }

    /**
     * 平台用户列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-22
     * @desc    description
     * @param   [int]          $user_id [用户id]
     */
    public static function PlatformUserList($user_id)
    {
        return empty($user_id) ? [] : Db::name('PluginsThirdpartyloginUser')->where(['user_id'=>intval($user_id)])->select()->toArray();
    }

    /**
     * 平台用户解绑
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-05-21
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UnbindHandle($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作数据有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 获取数据
        $info = Db::name('PluginsThirdpartyloginUser')->where(['id'=>intval($params['id']), 'user_id'=>$params['user']['id']])->find();
        if(empty($info))
        {
            return DataReturn('绑定数据不存在', -1);
        }

        // 开始处理
        $data = [
            'user_id'   => 0,
            'status'    => 2,
            'upd_time'  => time(),
        ];
        if(Db::name('PluginsThirdpartyloginUser')->where(['id'=>$info['id']])->update($data))
        {
            switch($info['platform'])
            {
                // 微信
                case 'weixin' :
                    // 去除用户平台表webopenid
                    Db::name('UserPlatform')->where(['weixin_web_openid'=>$info['openid']])->update(['weixin_web_openid'=>'']);
                    break;
            }
            return DataReturn('解绑成功', 0);
        }
        return DataReturn('解绑失败', -1);
    }
}
?>