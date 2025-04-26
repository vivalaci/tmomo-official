# 快速下单购物车 - 应用
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_speedplaceorder_cart` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户id',
  `goods_id` int(11) unsigned DEFAULT '0' COMMENT '商品id',
  `title` char(200) NOT NULL DEFAULT '' COMMENT '标题',
  `images` char(255) NOT NULL DEFAULT '' COMMENT '封面图片',
  `original_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '原价',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '销售价格',
  `stock` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '购买数量',
  `spec` text COMMENT '规格',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `goods_id` (`goods_id`),
  KEY `title` (`title`),
  KEY `stock` (`stock`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='快速下单购物车 - 应用'