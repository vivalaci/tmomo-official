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

/**
 * 限时秒杀-语言包-中文
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
return [
    // 导航名称
    'admin_nav_menu_list_data'          => [
        'admin'    => '基础配置',
        'goods'    => '秒杀商品',
        'periods'  => '时段管理',
    ],

    // 后台管理
    'admin' => [
        'form_item_application_name'              => '应用导航名称',
        'form_item_application_name_tips'         => '空则不显示',
        'form_item_application_name_message'      => '应用导航名称格式最多60个字符',
        'form_item_goods_detail_icon'             => '商品详情秒杀图标',
        'form_item_goods_detail_icon_tips'        => '默认 秒杀价',
        'form_item_goods_detail_icon_message'     => '商品详情秒杀图标格式最多8个字符',
        'form_item_goods_detail_title'            => '商品详情秒杀导航条标题',
        'form_item_goods_detail_title_tips'       => '默认 限时秒杀',
        'form_item_goods_detail_title_message'    => '商品详情秒杀导航条标题格式最多30个字符',
        'form_item_is_actas_price_original'       => '使用销售价作为原价',
        'form_item_is_actas_price_original_tips'  => '仅活动期间有效',
        'form_item_is_home_show'                  => '首页显示秒杀',
        'form_item_is_home_show_tips'             => '配置的商品需设置推荐',
        'form_item_is_home_auto_play'             => '首页显示自动播放',
        'form_item_is_home_auto_play_tips'        => '左右自动滚动',
        'form_item_content_notice'                => '公告内容',
        'form_item_content_notice_tips'           => '空则不显示',
        'form_item_content_notice_message'        => '请填写公告内容',
        'form_item_is_shop_seckill'               => '开启支持多商户',
        'form_item_header_logo'                   => '头部logo',
        'form_item_header_logo_tips'              => '删除则恢复默认',
        'form_item_header_logo_message'           => '请上传头部logo',
        'form_item_header_bg'                     => '头部背景图',
        'form_item_header_bg_tips'                => '删除则恢复默认',
        'form_item_header_bg_message'             => '请上传头部背景图',
        'form_item_home_title_icon'               => '首页展示标题图标',
        'form_item_home_title_icon_tips'          => '删除则恢复默认',
        'form_item_home_title_icon_message'       => '请上传首页展示标题图标',
        'form_item_home_bg'                       => '首页背景图',
        'form_item_home_bg_tips'                  => '删除则恢复默认',
        'form_item_home_bg_message'               => '请上传首页背景图',
    ],

    // 时段
    'periods'   => [
        // 表单
        'base_nav_title'                   => '时段',
        'form_item_is_shop'                => '商户',
        'form_item_name'                   => '名称',
        'form_item_name_message'           => '名称长度1~60个字符',
        'form_item_start_hour'             => '开始时间(整点)',
        'form_item_start_hour_message'     => '请输入开始时间(整点)',
        'form_item_continue_hour'          => '持续时间(时)',
        'form_item_continue_hour_message'  => '请输入持续时间(时)',
        'form_item_periods_id_message'     => '请选择时段',
        'form_item_discount_rate'          => '折扣',
        'form_item_discount_rate_message'  => '折扣应输入 0.00~0.99 的数字,小数保留两位',
        'form_item_dec_price'              => '减金额',
        'form_item_dec_price_message'      => '请输入有效的减金额',
    ],
];
?>