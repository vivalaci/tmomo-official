# v3.2.8
# 商品按钮名称和链接
ALTER TABLE `{PREFIX}goods` add `plugins_intellectstools_buy_btn_link_name` char(30) NOT NULL DEFAULT '' COMMENT '链接按钮名称' after `sort_level`;
ALTER TABLE `{PREFIX}goods` add `plugins_intellectstools_buy_btn_link_url` char(255) NOT NULL DEFAULT '' COMMENT '链接按钮链接' after `plugins_intellectstools_buy_btn_link_name`;
























# v2.0.0
# 智能工具箱 - 用户备注
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_intellectstools_user_note` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `content` text COMMENT '备注信息',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='智能工具箱 - 用户备注';

# 智能工具箱 - 商品调价配置
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_intellectstools_goods_modify_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `goods_ids` char(255) NOT NULL DEFAULT '' COMMENT '商品id（json存储）',
  `category_ids` char(255) NOT NULL DEFAULT '' COMMENT '商品分类id（json存储）',
  `brand_ids` char(255) NOT NULL DEFAULT '' COMMENT '品牌id（json存储）',
  `modify_price_type` char(120) NOT NULL DEFAULT '' COMMENT '价格类型（json存储）',
  `modify_rules` char(30) NOT NULL DEFAULT '' COMMENT '调价规则',
  `modify_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '调价值',
  `crontab_restore_rules` char(30) NOT NULL DEFAULT '' COMMENT '复原规则',
  `crontab_restore_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '复原值',
  `crontab_password` char(30) NOT NULL DEFAULT '' COMMENT '脚本密码',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='智能工具箱 - 商品调价配置';















# v1.4.7
ALTER TABLE `{PREFIX}plugins_intellectstools_toutiaosettlement` add `is_push` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推送（0否, 1是）' after `status`;
ALTER TABLE `{PREFIX}plugins_intellectstools_toutiaosettlement` add `push_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '推送时间' after `response_data`;














# v1.4.6
# 智能工具箱 - 头条支付分账
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_intellectstools_toutiaosettlement` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '支付日志id',
  `pay_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '支付日志id',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '分账状态（0待分账, 1已分账, 2已失败）',
  `reason` char(230) NOT NULL DEFAULT '' COMMENT '失败原因',
  `response_data` mediumtext COMMENT '响应参数（数组则json字符串存储）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pay_id` (`pay_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `upd_time` (`upd_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='智能工具箱 - 头条支付分账';







# v1.3.4
# 智能工具箱 - 批量评价
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_intellectstools_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `goods_category_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品分类id',
  `content` char(230) NOT NULL DEFAULT '' COMMENT '评价信息',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `goods_category_id` (`goods_category_id`),
  KEY `content` (`content`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='智能工具箱 - 批量评价 - 应用';

# 智能工具箱 - 批量评价商品增加记录
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_intellectstools_comments_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `inc_count` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '增加次数',
  `data_count` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '数据条数',
  `last_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后时间',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`),
  KEY `inc_count` (`inc_count`),
  KEY `data_count` (`data_count`),
  KEY `last_time` (`last_time`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='智能工具箱 - 批量评价商品增加记录 - 应用';

# 智能工具箱 - 批量评价商品独立配置
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_intellectstools_comments_goods_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `first_number_min` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '首次随机范围最小值',
  `first_number_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '首次随机范围最大值',
  `last_number_min` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '续增随机范围最小值',
  `last_number_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '续增随机范围最大值',
  `last_interval_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '续增间隔时间',
  `time_interval_min` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '每条时间间隔最小值',
  `time_interval_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '每条时间间隔最大值',
  `rating_rand_min` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数据评分随机最小值',
  `auto_control_number` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '自动控制',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='智能工具箱 - 批量评价商品独立配置 - 应用';