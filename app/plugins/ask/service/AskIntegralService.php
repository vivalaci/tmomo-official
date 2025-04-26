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
namespace app\plugins\ask\service;

use think\facade\Db;
use app\service\IntegralService;

/**
 * 问答 - 问答积分服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class AskIntegralService
{
    /**
     * 问答发布奖励积分
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-26
     * @desc    description
     * @param   [int]          $ask_id      [问答id]
     * @param   [int]          $user_id         [用户id]
     * @param   [array]        $plugins_config  [插件配置]
     */
    public static function AskAddGiveIntegral($ask_id, $user_id, $plugins_config)
    {
        // 是否开启奖励积分
        if(isset($plugins_config['is_ask_add_give_integral']) && $plugins_config['is_ask_add_give_integral'] == 1 && !empty($plugins_config['ask_add_give_integral_value']))
        {
            // 是否已奖励过
            $where = [
                ['ask_id', '=', $ask_id],
                ['user_id', '=', $user_id],
            ];
            $temp = Db::name('PluginsAskGiveIntegralLog')->where($where)->find();
            if(!empty($temp))
            {
                return DataReturn('已奖励过', 0);
            }

            // 是否已限制单日总数
            if(!empty($plugins_config['ask_add_day_limit_give_integral_value']))
            {
                // 今日已奖励总数
                $where = [
                    ['user_id', '=', $user_id],
                    ['add_time', '>=', strtotime(date('Y-m-d 00:00:00', time()))],
                ];
                $max = Db::name('PluginsAskGiveIntegralLog')->where($where)->sum('integral');
                if($max >= intval($plugins_config['ask_add_day_limit_give_integral_value']))
                {
                    return DataReturn('已超过单日最高限制', 0);
                }
            }

            // 奖励积分数据添加
            $data = [
                'ask_id'    => $ask_id,
                'user_id'   => $user_id,
                'integral'  => intval($plugins_config['ask_add_give_integral_value']),
                'add_time'  => time(),
            ];
            if(Db::name('PluginsAskGiveIntegralLog')->insertGetId($data) <= 0)
            {
                return DataReturn('积分奖励数据添加失败', -1);
            }

            // 实际奖励积分
            $user_integral = Db::name('User')->where(['id'=>$user_id])->value('integral');
            $name = empty($plugins_config['ask_main_name']) ? '问答' : $plugins_config['ask_main_name'];
            if(!IntegralService::UserIntegralLogAdd($user_id, $user_integral, $data['integral'], $name.'发布奖励', 1))
            {
                return DataReturn('积分奖励日志添加失败', -1);
            }

            // 积分增加
            if(!Db::name('User')->where(['id'=>$user_id])->inc('integral', $data['integral'])->update())
            {
                return DataReturn('积分奖励失败', -1);
            }
        }
        return DataReturn(MyLang('handle_success'), 0);
    }

    /**
     * 问答评论奖励积分
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-26
     * @desc    description
     * @param   [int]          $ask_id      [问答id]
     * @param   [int]          $comments_id     [问答评论id]
     * @param   [int]          $user_id         [用户id]
     * @param   [array]        $plugins_config  [插件配置]
     */
    public static function AskCommentsGiveIntegral($ask_id, $comments_id, $user_id, $plugins_config)
    {
        // 是否开启奖励积分
        if(isset($plugins_config['is_ask_comments_give_integral']) && $plugins_config['is_ask_comments_give_integral'] == 1 && !empty($plugins_config['ask_comments_give_integral_value']))
        {
            // 是否已奖励过
            $where = [
                ['ask_id', '=', $ask_id],
                ['comments_id', '=', $comments_id],
                ['user_id', '=', $user_id],
            ];
            $temp = Db::name('PluginsAskCommentsGiveIntegralLog')->where($where)->find();
            if(!empty($temp))
            {
                return DataReturn('已奖励过', 0);
            }

            // 是否已限制单日总数
            if(!empty($plugins_config['ask_comments_day_limit_give_integral_value']))
            {
                // 今日已奖励总数
                $where = [
                    ['user_id', '=', $user_id],
                    ['add_time', '>=', strtotime(date('Y-m-d 00:00:00', time()))],
                ];
                $max = Db::name('PluginsAskCommentsGiveIntegralLog')->where($where)->sum('integral');
                if($max >= intval($plugins_config['ask_comments_day_limit_give_integral_value']))
                {
                    return DataReturn('已超过单日最高限制', 0);
                }
            }

            // 奖励积分数据添加
            $data = [
                'ask_id'   => $ask_id,
                'comments_id'  => $comments_id,
                'user_id'      => $user_id,
                'integral'     => intval($plugins_config['ask_comments_give_integral_value']),
                'add_time'     => time(),
            ];
            if(Db::name('PluginsAskCommentsGiveIntegralLog')->insertGetId($data) <= 0)
            {
                return DataReturn('积分奖励数据添加失败', -1);
            }

            // 实际奖励积分
            $user_integral = Db::name('User')->where(['id'=>$user_id])->value('integral');
            $name = empty($plugins_config['ask_main_name']) ? '问答' : $plugins_config['ask_main_name'];
            if(!IntegralService::UserIntegralLogAdd($user_id, $user_integral, $data['integral'], $name.'评论奖励', 1))
            {
                return DataReturn('积分奖励日志添加失败', -1);
            }

            // 积分增加
            if(!Db::name('User')->where(['id'=>$user_id])->inc('integral', $data['integral'])->update())
            {
                return DataReturn('积分奖励失败', -1);
            }
        }
        return DataReturn(MyLang('handle_success'), 0);
    }

    /**
     * 问答点赞奖励积分
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-26
     * @desc    description
     * @param   [int]          $ask_id      [问答id]
     * @param   [int]          $comments_id     [问答评论id]
     * @param   [int]          $user_id         [用户id]
     * @param   [array]        $plugins_config  [插件配置]
     */
    public static function AskThumbsGiveIntegral($ask_id, $comments_id, $user_id, $plugins_config)
    {
        // 是否开启奖励积分
        if(isset($plugins_config['is_give_thumbs_give_integral']) && $plugins_config['is_give_thumbs_give_integral'] == 1 && !empty($plugins_config['give_thumbs_give_integral_value']))
        {
            // 是否已奖励过
            $where = [
                ['ask_id', '=', $ask_id],
                ['comments_id', '=', $comments_id],
                ['user_id', '=', $user_id],
            ];
            $temp = Db::name('PluginsAskThumbsGiveIntegralLog')->where($where)->find();
            if(!empty($temp))
            {
                return DataReturn('已奖励过', 0);
            }

            // 是否已限制单日总数
            if(!empty($plugins_config['give_thumbs_day_limit_give_integral_value']))
            {
                // 今日已奖励总数
                $where = [
                    ['user_id', '=', $user_id],
                    ['add_time', '>=', strtotime(date('Y-m-d 00:00:00', time()))],
                ];
                $max = Db::name('PluginsAskThumbsGiveIntegralLog')->where($where)->sum('integral');
                if($max >= intval($plugins_config['give_thumbs_day_limit_give_integral_value']))
                {
                    return DataReturn('已超过单日最高限制', 0);
                }
            }

            // 奖励积分数据添加
            $data = [
                'ask_id'   => $ask_id,
                'comments_id'  => $comments_id,
                'user_id'      => $user_id,
                'integral'     => intval($plugins_config['give_thumbs_give_integral_value']),
                'add_time'     => time(),
            ];
            if(Db::name('PluginsAskThumbsGiveIntegralLog')->insertGetId($data) <= 0)
            {
                return DataReturn('积分奖励数据添加失败', -1);
            }

            // 实际奖励积分
            $user_integral = Db::name('User')->where(['id'=>$user_id])->value('integral');
            $name = empty($plugins_config['ask_main_name']) ? '问答' : $plugins_config['ask_main_name'];
            if(!IntegralService::UserIntegralLogAdd($user_id, $user_integral, $data['integral'], $name.'点赞奖励', 1))
            {
                return DataReturn('积分奖励日志添加失败', -1);
            }

            // 积分增加
            if(!Db::name('User')->where(['id'=>$user_id])->inc('integral', $data['integral'])->update())
            {
                return DataReturn('积分奖励失败', -1);
            }
        }
        return DataReturn(MyLang('handle_success'), 0);
    }
}
?>