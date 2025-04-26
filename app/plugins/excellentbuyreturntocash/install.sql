# 返现订单
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_excellentbuyreturntocash_return_cash_order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单id',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `valid_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额',
  `profit_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返现金额',
  `rate` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '返现比例 0~100 的数字（创建时写入，防止发生退款重新计算）',
  `category_ids` longtext COMMENT '分类id（json 存储）',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '结算状态（0待生效, 1生效中, 2待结算, 3已结算, 4已失效）',
  `log` longtext COMMENT '日志',
  `success_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '完成结算时间',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='优购返现返现订单 - 应用';

# 优惠券发放记录
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_excellentbuyreturntocash_coupon_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_coupon_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户优惠券id',
  `coupon_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单id',
  `order_detail_id` int(11) unsigned NOT NULL COMMENT '订单详情id',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0已发放, 1已退回）',
  `return_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '退回时间',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `order_detail_id` (`order_detail_id`),
  KEY `goods_id` (`goods_id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `user_coupon_id` (`user_coupon_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='优购返现优惠券发放记录 - 应用';