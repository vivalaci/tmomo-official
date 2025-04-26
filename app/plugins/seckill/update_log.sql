# v2.0.0
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

# 秒杀商品新增字段
ALTER TABLE `{PREFIX}plugins_seckill_goods` add `periods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '时段id' after `id`;
ALTER TABLE `{PREFIX}plugins_seckill_goods` add `time_start` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间' after `dec_price`;
ALTER TABLE `{PREFIX}plugins_seckill_goods` add `time_end` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间' after `time_start`;

# 秒杀商品增加索引
ALTER TABLE `{PREFIX}plugins_seckill_goods` ADD INDEX periods_id(`periods_id`);
ALTER TABLE `{PREFIX}plugins_seckill_goods` ADD INDEX time_start(`time_start`);
ALTER TABLE `{PREFIX}plugins_seckill_goods` ADD INDEX time_end(`time_end`);

# 删除轮播表
DROP TABLE IF EXISTS `{PREFIX}plugins_seckill_slider`;
























# v1.0.5
# 商品表
ALTER TABLE `{PREFIX}plugins_seckill_goods` add `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间' after `add_time`;
ALTER TABLE `{PREFIX}plugins_seckill_goods` add `shop_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '店铺id' after `goods_id`;
ALTER TABLE `{PREFIX}plugins_seckill_goods` add `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0待审核，1已审核，2已拒绝）' after `shop_id`;
ALTER TABLE `{PREFIX}plugins_seckill_goods` add `refuse_reason` char(230) NOT NULL DEFAULT '' COMMENT '拒绝原因' after `status`;

# 增加索引
ALTER TABLE `{PREFIX}plugins_seckill_goods` ADD INDEX shop_id(`shop_id`);
ALTER TABLE `{PREFIX}plugins_seckill_goods` ADD INDEX status(`status`);


# 轮播表
ALTER TABLE `{PREFIX}plugins_seckill_slider` add `platform` char(30) NOT NULL DEFAULT 'pc' COMMENT '所属平台（pc PC网站, h5 H5手机网站, ios 苹果APP, android 安卓APP, alipay 支付宝小程序, weixin 微信小程序, baidu 百度小程序, toutiao 头条小程序, qq QQ小程序）' after `id`;
ALTER TABLE `{PREFIX}plugins_seckill_slider` add `event_type` tinyint(2) NOT NULL DEFAULT '-1' COMMENT '事件类型（0 WEB页面, 1 内部页面(小程序或APP内部地址), 2 外部小程序(同一个主体下的小程序appid), 3 打开地图, 4 拨打电话）' after `platform`;
ALTER TABLE `{PREFIX}plugins_seckill_slider` change `url` `event_value` char(255) NOT NULL DEFAULT '' COMMENT '事件值';
ALTER TABLE `{PREFIX}plugins_seckill_slider` add `bg_color` char(30) NOT NULL DEFAULT '' COMMENT 'css背景色值' after `name`;

# 增加索引
ALTER TABLE `{PREFIX}plugins_seckill_slider` ADD INDEX platform(`platform`);