# 签到码 - 应用
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_signin_qrcode` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `request_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '来源签到码id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `reward_master` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '邀请人奖励积分',
  `reward_invitee` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '受邀人奖励积分',
  `continuous_rules` text COMMENT '连续签到翻倍奖励配置数据',
  `specified_time_reward` char(255) NOT NULL DEFAULT '' COMMENT '指定时段额外奖励',
  `max_number_limit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最大奖励数量限制（0则不限制）',
  `day_number_limit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '一天奖励数量限制（0则不限制）',
  `bg_images` char(255) NOT NULL DEFAULT '' COMMENT '背景图片',
  `logo` char(255) NOT NULL DEFAULT '' COMMENT 'logo',
  `name` char(60) NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `tel` char(15) NOT NULL DEFAULT '' COMMENT '联系人电话',
  `address` char(230) NOT NULL DEFAULT '' COMMENT '联系地址',
  `goods_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关联商品数量',
  `access_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '访问次数',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用（0否，1是）',
  `note` char(255) NOT NULL DEFAULT '' COMMENT '备注',
  `footer_code` text COMMENT '底部代码',
  `seo_title` char(100) NOT NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` char(130) NOT NULL DEFAULT '' COMMENT 'SEO关键字',
  `seo_desc` char(230) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `user_id` (`user_id`),
  KEY `reward_master` (`reward_master`),
  KEY `reward_invitee` (`reward_invitee`),
  KEY `goods_count` (`goods_count`),
  KEY `access_count` (`access_count`),
  KEY `name` (`name`),
  KEY `tel` (`tel`),
  KEY `is_enable` (`is_enable`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='签到码 - 应用';

# 签到码关联商品 - 应用
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_signin_qrcode_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `qrcode_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动id',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `qrcode_id` (`qrcode_id`),
  KEY `goods_id` (`goods_id`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='签到码关联商品 - 应用';

# 签到码数据 - 应用
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_signin_qrcode_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `qrcode_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '签到码id',
  `integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '奖励积分',
  `ymd` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '年月日',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `qrcode_id` (`qrcode_id`),
  KEY `integral` (`integral`),
  KEY `ymd` (`ymd`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='签到码数据 - 应用';