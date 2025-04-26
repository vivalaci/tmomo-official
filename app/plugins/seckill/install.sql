# 限时秒杀 - 商品
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_seckill_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `periods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '时段id',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `shop_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '店铺id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0待审核，1已审核，2已拒绝）',
  `refuse_reason` char(230) NOT NULL DEFAULT '' COMMENT '拒绝原因',
  `discount_rate` decimal(3,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '折扣系数 0.00~0.99',
  `dec_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '减金额',
  `time_start` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `time_end` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `is_recommend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐（0否，1是）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `periods_id` (`periods_id`),
  KEY `goods_id` (`goods_id`),
  KEY `shop_id` (`shop_id`),
  KEY `status` (`status`),
  KEY `time_start` (`time_start`),
  KEY `time_end` (`time_end`),
  KEY `is_recommend` (`is_recommend`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='限时秒杀 - 商品';

# 限时秒杀 - 时段
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_seckill_periods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` char(60) NOT NULL DEFAULT '' COMMENT '名称',
  `start_hour` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间(整点)',
  `continue_hour` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '持续时间(时)',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用（0否，1是）',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `is_enable` (`is_enable`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='限时秒杀 - 时段';