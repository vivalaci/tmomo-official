# 分销等级
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_level` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '名称',
  `images_url` char(255) NOT NULL DEFAULT '' COMMENT '图标',
  `auto_level_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '条件类型（0满足以下任意条件、1满足以下全部条件）',
  `auto_level_type_where` char(255) NOT NULL DEFAULT '' COMMENT '条件类型（json存储）',
  `auto_level_self_consume_price_rules_min` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '自消费最小总额',
  `auto_level_self_consume_price_rules_max` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '自消费最大总额',
  `auto_level_self_consume_number_rules_min` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '自消费单最小数',
  `auto_level_self_consume_number_rules_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '自消费单最大数',
  `auto_level_promotion_income_order_price_rules_min` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '推广收益最小总额',
  `auto_level_promotion_income_order_price_rules_max` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '推广收益最大总额',
  `auto_level_promotion_income_order_number_rules_min` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推广收益单最小数',
  `auto_level_promotion_income_order_number_rules_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推广收益单最大数',
  `auto_level_promotion_income_team_number_rules_min` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推广人数最小数',
  `auto_level_promotion_income_team_number_rules_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推广人数最大数',
  `auto_level_promotion_income_team_consume_rules_min` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推广消费人数最小总额',
  `auto_level_promotion_income_team_consume_rules_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推广消费人数最大总额',
  `auto_level_user_points_number_rules_min` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '有效积分最小数',
  `auto_level_user_points_number_rules_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '有效积分最大数',
  `level_rate_one` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '一级返佣比例',
  `level_rate_two` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '二级返佣比例',
  `level_rate_three` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '三级返佣比例',
  `down_return_rate` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '向下返佣比例',
  `self_buy_rate` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '内购返佣比例',
  `force_current_user_rate_one` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '强制返佣至取货点返佣比例（一级）',
  `force_current_user_rate_two` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '强制返佣至取货点返佣比例（二级）',
  `force_current_user_rate_three` decimal(6,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '强制返佣至取货点返佣比例（三级）',
  `is_level_auto` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否自动分配等级（0否, 1是）',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用（0否, 1是）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `is_enable` (`is_enable`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销等级 - 分销';

# 佣金明细
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_profit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单id',
  `order_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单用户id',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `profit_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '收益金额',
  `rate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '等级返佣比例 0~100 的数字（创建时写入，防止发生退款重新计算时用户等级变更）',
  `spec_extends` mediumtext COMMENT '订单中所购买的商品对应规格的扩展数据（用于重新计算时候使用）',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '当前级别（1~3）0则是向下返佣',
  `user_level_id` text COMMENT '用户等级id（扩展数据id）',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '结算状态（0待生效, 1待结算, 2已结算, 3已失效）',
  `msg` text COMMENT '描述（一般用于退款描述）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销佣金明细 - 分销';

# 分销商取货点
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_user_self_extraction` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '站点id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '站点所属用户id',
  `logo` char(255) NOT NULL DEFAULT '' COMMENT 'logo图片',
  `alias` char(60) NOT NULL DEFAULT '' COMMENT '别名',
  `name` char(60) NOT NULL DEFAULT '' COMMENT '姓名',
  `tel` char(15) NOT NULL DEFAULT '' COMMENT '电话',
  `province` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '所在省',
  `city` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '所在市',
  `county` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '所在县/区',
  `address` char(80) NOT NULL DEFAULT '' COMMENT '详细地址',
  `lng` decimal(13,10) unsigned NOT NULL DEFAULT '0.0000000000' COMMENT '经度',
  `lat` decimal(13,10) unsigned NOT NULL DEFAULT '0.0000000000' COMMENT '纬度',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0待审核, 1已通过, 2已拒绝, 3已解约）',
  `fail_reason` char(200) NOT NULL DEFAULT '' COMMENT '审核拒绝原因',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销商取货点 - 分销';


# 分销商取货点关联订单
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_user_self_extraction_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '站点id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '站点所属用户id',
  `order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
  `self_extraction_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '取货点地址id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0待处理, 1已处理）',
  `notes` char(200) NOT NULL DEFAULT '' COMMENT '备注',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `self_extraction_id` (`self_extraction_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销商取货点关联订单 - 分销';

# 积分发放明细
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_integral_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `integral` int(11) unsigned NOT NULL COMMENT '积分',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0待发放, 1已发放, 2已退回）',
  `msg` text COMMENT '描述（一般用于退回描述）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销积分明细 - 分销';

# 自定义取货地址
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_custom_extraction_address` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `address_key` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '地址key',
  `address_oldid` int(11) unsigned NOT NULL COMMENT '原始id',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `address_key` (`address_key`),
  KEY `address_oldid` (`address_oldid`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销自定义取货地址 - 分销';

# 分销阶梯返佣记录
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_appoint_ladder_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单id',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '级别记录',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销阶梯返佣记录 - 分销';

# 分销阶梯返佣记录商品
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_appoint_ladder_log_goods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `log_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '日志id',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品id',
  PRIMARY KEY (`id`),
  KEY `log_id` (`log_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销阶梯返佣记录商品 - 分销';

# 分销等级多商户
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_level_shop` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `shop_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '店铺id',
  `config` text COMMENT '返佣配置（json存储）',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用（0否, 1是）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `shop_id` (`shop_id`),
  KEY `is_enable` (`is_enable`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销等级多商户 - 分销';

# 分销商推荐宝
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_recommend` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `icon` char(255) NOT NULL DEFAULT '' COMMENT '图标',
  `title` char(60) NOT NULL DEFAULT '' COMMENT '标题',
  `describe` char(255) NOT NULL DEFAULT '' COMMENT '简介',
  `goods_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品数量',
  `access_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '访问次数',
  `seo_title` char(100) NOT NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` char(130) NOT NULL DEFAULT '' COMMENT 'SEO关键字',
  `seo_desc` char(230) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用（0否，1是）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `title` (`title`),
  KEY `is_enable` (`is_enable`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销商推荐宝 - 分销';

# 分销商推荐宝商品
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_recommend_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `recommend_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推荐id',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `spec` char(255) NOT NULL DEFAULT '' COMMENT '规格',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `recommend_id` (`recommend_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='分销商推荐宝商品 - 分销';

# 客户拜访
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_visit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `team_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '团队用户id',
  `custom_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '客户用户id',
  `content` longtext COMMENT '拜访内容',
  `images` text COMMENT '图片数据（一维数组json）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `team_user_id` (`team_user_id`),
  KEY `custom_user_id` (`custom_user_id`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='客户拜访 - 分销';

# 海报地址
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_poster` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `system_type` char(60) NOT NULL DEFAULT 'default' COMMENT '系统类型（默认 default, 其他按照SYSTEM_TYPE常量类型）',
  `platform` char(255) NOT NULL DEFAULT 'pc' COMMENT '所属平台（pc PC网站, h5 H5手机网站, ios 苹果APP, android 安卓APP, alipay 支付宝小程序, weixin 微信小程序, baidu 百度小程序, toutiao 头条小程序, qq QQ小程序, kuaishou 快手小程序）',
  `images_url` char(255) NOT NULL DEFAULT '' COMMENT '海报地址',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `system_type` (`system_type`),
  KEY `platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='海报地址 - 分销';

# 上级用户修改
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_distribution_superior_modify` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `modify_number` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '已修改次数',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='上级用户修改 - 分销';



# 用户分销等级（手动指定）
ALTER TABLE `{PREFIX}user` add `plugins_distribution_level` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户分销等级（手动指定）' after `referrer`;
# 用户分销等级（自动匹配）
ALTER TABLE `{PREFIX}user` add `plugins_distribution_auto_level` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户分销等级（自动匹配）' after `plugins_distribution_level`;