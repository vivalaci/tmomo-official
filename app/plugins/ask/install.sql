# 用户问答
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `category_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '联系人',
  `tel` char(20) NOT NULL DEFAULT '' COMMENT '联系电话',
  `title` char(230) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COMMENT '详细内容',
  `reply` text COMMENT '回复内容',
  `is_reply` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否已回复（0否, 1是）',
  `reply_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '回复时间',
  `is_show` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否显示（0不显示, 1显示）',
  `images` text COMMENT '图片数据（一维数组json）',
  `images_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '图片数量',
  `access_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '访问次数',
  `comments_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论总数',
  `give_thumbs_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '点赞总数',
  `email_notice` char(60) NOT NULL DEFAULT '' COMMENT '回复邮箱通知',
  `mobile_notice` char(60) NOT NULL DEFAULT '' COMMENT '回复手机通知',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`name`) USING BTREE,
  KEY `category_id` (`category_id`) USING BTREE,
  KEY `is_show` (`is_show`) USING BTREE,
  KEY `images_count` (`images_count`) USING BTREE,
  KEY `access_count` (`access_count`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='用户问答 - 问答';

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

# 问答评论
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

# 问答点赞
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

# 问答推荐商品
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='问答推荐商品 - 问答';

# 问答系统轮播图 - 问答
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_ask_slider` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `platform` char(255) NOT NULL DEFAULT 'pc' COMMENT '所属平台（pc PC网站, h5 H5手机网站, ios 苹果APP, android 安卓APP, alipay 支付宝小程序, weixin 微信小程序, baidu 百度小程序, toutiao 头条小程序, qq QQ小程序, kuaishou 快手小程序）',
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
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='问答系统轮播图 - 问答';