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
 * 问答-语言包-中文
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
return [
    // 后台管理
    'admin' => [
        'form_item_application_name'                => '应用导航名称',
        'form_item_application_name_tips'           => '空则不显示',
        'form_item_is_user_menu'                    => '开启用户中心菜单入口',
        'form_item_application_name_message'        => '应用导航名称格式最多60个字符',
        'form_item_user_center_left_name'           => '用户中心菜单名称',
        'form_item_user_center_left_name_tips'      => '空则不显示',
        'form_item_user_center_left_name_message'   => '用户中心菜单名称格式最多60个字符',
        'form_item_right_top_rec_name'              => '右侧推荐问答名称',
        'form_item_right_top_rec_name_tips'         => '默认 推荐问答',
        'form_item_right_top_rec_name_message'      => '右侧推荐问答名称格式最多30个字符',
        'form_item_middle_new_name'                 => '首页最新问答名称',
        'form_item_middle_new_name_tips'            => '默认 最新问答',
        'form_item_middle_new_name_message'         => '首页最新问答名称格式最多30个字符',
        'form_item_middle_new_page_number'          => '最新问答展示数量',
        'form_item_middle_new_page_number_tips'     => '默认 20 条',
        'form_item_middle_new_page_number_message'  => '请填写最新问答展示数量',
        'form_item_hot_name'                        => '热门问答名称',
        'form_item_hot_name_tips'                   => '默认 热门问答',
        'form_item_hot_name_message'                => '热门问答名称格式最多30个字符',
        'form_item_hot_page_number'                 => '热门问答展示数量',
        'form_item_hot_page_number_tips'            => '默认 10 条',
        'form_item_hot_page_number_message'         => '请填写热门问答展示数量',
        'form_item_right_top_goods_name'            => '右侧推荐商品名称',
        'form_item_right_top_goods_name_tips'       => '默认 推荐商品',
        'form_item_right_top_goods_name_message'    => '右侧推荐商品名称格式最多30个字符',
        'form_item_search_page_number'              => '搜索问答展示数量',
        'form_item_search_page_number_tips'         => '默认 30 条',
        'form_item_search_page_number_message'      => '请填写搜索问答展示数量数量',
        'form_item_header_logo'                     => '头部logo',
        'form_item_header_logo_tips'                => '默认站点logo、建议220*60px',
        'form_item_header_logo_message'             => '请上传头部logo',
        'form_item_header_bg'                       => '头部背景图',
        'form_item_header_bg_tips'                  => '删除则恢复默认、建议1920*300px<br />背景模糊建议填写（1rem）',
        'form_item_header_bg_message'               => '请上传头部背景图',
        'form_item_header_bg_vague'                 => '模糊程度',
        'form_item_header_bg_vague_message'         => '请上传模糊程度',
        'form_item_recommend'                       => '推荐问答',
        'form_item_recommend_placeholder'           => '推荐问答',
    ],

    // 问答
    'ask'   => [
        // 基础
        'base_nav_title'                   => '问答',
        'user_info_title'                  => '用户信息',
        'no_username_name'                 => '网友',
        // 表单
        'form_item_name'                   => '联系人',
        'form_item_name_message'           => '联系人格式最多30个字符',
        'form_item_tel'                    => '电话',
        'form_item_tel_message'            => '请填写有效的电话',
        'form_item_title'                  => '标题',
        'form_item_title_message'          => '标题长度2~120个字符',
        'form_item_access_count'           => '访问次数',
        'form_item_access_count_message'   => '访问次数格式0~9的数值',
        'form_item_category_id'            => '提问分类',
        'form_item_category_id_message'    => '请选择提问分类',
        'form_item_content'                => '提问内容',
        'form_item_content_message'        => '提问内容格式至少输入5个字符',
        'form_item_email_notice'           => '回复邮件通知',
        'form_item_email_notice_message'   => '回复邮件通知账户格式有误',
        'form_item_mobile_notice'          => '回复手机通知',
        'form_item_mobile_notice_message'  => '回复手机通知账户格式有误',
        'form_item_reply'                  => '回复内容',
        'form_item_reply_message'          => '回复内容格式至少输入5个字符',
        'form_item_save_reply_message'     => '回复内容格式至少输入5个字符',
        'form_is_reply'                    => '是否已回复',
    ],

    // 问答分类
    'askcategory'  => [
        // 基础
        'base_nav_title'                     => '分类',
        // 表单
        'form_item_name'                     => '名称',
        'form_item_name_message'             => '名称格式1~16个字符',
        'category_pid_current_equal_tips'    => '父级不能与当前相同',
    ],

    // 动态表格
    'form_table'        => [
        // 问答
        'ask'   => [
            'user'               => '用户信息',
            'user_placeholder'   => '请输入用户名/联系人/手机/邮箱',
            'goods'              => '商品信息',
            'goods_placeholder'  => '请输入商品名称/简述/SEO信息',
            'title'              => '提问标题',
            'category_name'      => '分类',
            'name'               => '联系人',
            'tel'                => '联系电话',
            'is_show_name'       => '是否显示',
            'is_reply_name'      => '是否回复',
            'reply_time_time'    => '回复时间',
            'images_count'       => '图片数量',
            'access_count'       => '访问次数',
            'comments_count'     => '评论总数',
            'give_thumbs_count'  => '点赞总数',
            'email_notice'       => '回复邮箱通知',
            'mobile_notice'      => '回复手机通知',
            'add_time_time'      => '创建时间',
            'upd_time_time'      => '更新时间',
        ],
    ],
];
?>