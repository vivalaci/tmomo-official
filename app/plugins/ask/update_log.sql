# v4.2.4
# 问答
ALTER TABLE `{PREFIX}plugins_ask` add `images` text COMMENT '图片数据（一维数组json）' after `is_show`;
ALTER TABLE `{PREFIX}plugins_ask` add `images_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '图片数量' after `images`;
ALTER TABLE `{PREFIX}plugins_ask` change `title` `title` char(230) NOT NULL DEFAULT '' COMMENT '标题';
























# v4.2.1
# 问答分类
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `icon` char(255) NOT NULL DEFAULT '' COMMENT 'icon图标',
  `name` char(60) NOT NULL DEFAULT '' COMMENT '名称',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用（0否，1是）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `name` (`name`),
  KEY `is_enable` (`is_enable`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='问答分类 - 问答';

# 问答
ALTER TABLE `{PREFIX}plugins_ask` add `category_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分类id' after `goods_id`;
ALTER TABLE `{PREFIX}plugins_ask` add `email_notice` char(60) NOT NULL DEFAULT '' COMMENT '回复邮箱通知' after `give_thumbs_count`;
ALTER TABLE `{PREFIX}plugins_ask` add `mobile_notice` char(60) NOT NULL DEFAULT '' COMMENT '回复手机通知' after `email_notice`;
ALTER TABLE `{PREFIX}plugins_ask` ADD INDEX category_id(`category_id`);




















# v4.1.2
# 平台
ALTER TABLE `{PREFIX}plugins_ask_slider` change `platform` `platform` char(255) NOT NULL DEFAULT 'pc' COMMENT '所属平台（pc PC网站, h5 H5手机网站, ios 苹果APP, android 安卓APP, alipay 支付宝小程序, weixin 微信小程序, baidu 百度小程序, toutiao 头条小程序, qq QQ小程序, kuaishou 快手小程序）';

# 删除多余的索引
ALTER TABLE `{PREFIX}plugins_ask_slider` DROP INDEX `platform`;


























# v3.0.0
# 问答评论 - 问答
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `ask_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问答id',
  `ask_comments_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问答评论id',
  `reply_comments_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '一级回复评论id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0待审核、1已审核、2已隐藏）',
  `md5_key` char(32) NOT NULL DEFAULT '' COMMENT '数据唯一md5key',
  `content` text COMMENT '评论内容',
  `comments_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论总数',
  `give_thumbs_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '点赞总数',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `ask_id` (`ask_id`),
  KEY `ask_comments_id` (`ask_comments_id`),
  KEY `reply_comments_id` (`reply_comments_id`),
  KEY `status` (`status`),
  KEY `md5_key` (`md5_key`),
  KEY `comments_count` (`comments_count`),
  KEY `give_thumbs_count` (`give_thumbs_count`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='问答评论 - 问答';

# 问答点赞 - 问答
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask_give_thumbs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `ask_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问答id',
  `ask_comments_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问答评论id',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `ask_id` (`ask_id`),
  KEY `ask_comments_id` (`ask_comments_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='问答点赞 - 问答';

# 问答发布奖励积分日志
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask_give_integral_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `ask_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问答id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '奖励积分',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `ask_id` (`ask_id`),
  KEY `user_id` (`user_id`),
  KEY `integral` (`integral`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='问答发布奖励积分日志 - 问答';

# 问答评论奖励积分日志
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask_comments_give_integral_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `ask_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问答id',
  `comments_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '奖励积分',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `ask_id` (`ask_id`),
  KEY `comments_id` (`comments_id`),
  KEY `user_id` (`user_id`),
  KEY `integral` (`integral`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='问答评论奖励积分日志 - 问答';

# 问答点赞奖励积分日志
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask_thumbs_give_integral_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `ask_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问答id',
  `comments_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问答id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '奖励积分',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `ask_id` (`ask_id`),
  KEY `comments_id` (`comments_id`),
  KEY `user_id` (`user_id`),
  KEY `integral` (`integral`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='问答点赞奖励积分日志 - 问答';


# 表重命名
RENAME TABLE `{PREFIX}answer` TO `{PREFIX}plugins_ask`;
RENAME TABLE `{PREFIX}plugins_answers_goods` TO `{PREFIX}plugins_ask_goods`;
RENAME TABLE `{PREFIX}plugins_answers_slider` TO `{PREFIX}plugins_ask_slider`;

# 插件名称
UPDATE `{PREFIX}plugins` SET `plugins`='ask' WHERE `plugins`='answers';
UPDATE `{PREFIX}attachment` SET `path_type`='plugins_ask' WHERE `path_type`='plugins_answers';

# 问答新增评论和点赞总数
ALTER TABLE `{PREFIX}plugins_ask` add `comments_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论总数' after `access_count`;
ALTER TABLE `{PREFIX}plugins_ask` add `give_thumbs_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞总数' after `comments_count`;

# 问答新增商品id
ALTER TABLE `{PREFIX}plugins_ask` add `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id' after `user_id`;























# v2.0.0
# 删除老的轮播表
DROP TABLE IF EXISTS `{PREFIX}plugins_ask_slide`;

# 问答系统轮播图 - 应用
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask_slider` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `platform` char(30) NOT NULL DEFAULT 'pc' COMMENT '所属平台（pc PC网站, h5 H5手机网站, ios 苹果APP, android 安卓APP, alipay 支付宝小程序, weixin 微信小程序, baidu 百度小程序, toutiao 头条小程序, qq QQ小程序）',
  `event_type` tinyint(2) NOT NULL DEFAULT '-1' COMMENT '事件类型（0 WEB页面, 1 内部页面(小程序或APP内部地址), 2 外部小程序(同一个主体下的小程序appid), 3 打开地图, 4 拨打电话）',
  `event_value` char(255) NOT NULL DEFAULT '' COMMENT '事件值',
  `images_url` char(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `name` char(60) NOT NULL DEFAULT '' COMMENT '名称',
  `bg_color` char(30) NOT NULL DEFAULT '' COMMENT 'css背景色值',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用（0否，1是）',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `is_enable` (`is_enable`),
  KEY `sort` (`sort`),
  KEY `platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='问答系统轮播图 - 应用';