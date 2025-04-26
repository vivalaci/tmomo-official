# v2.1.0
ALTER TABLE `{PREFIX}plugins_membershiplevelvip_level` add `free_shipping_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '满额免运费' after `discount_rate`;
ALTER TABLE `{PREFIX}plugins_membershiplevelvip_level` add `is_span_free_shipping_price` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否跨订单计算（0否, 1是）' after `free_shipping_price`;























# v1.5.8
# 用户会员等级（自动匹配）
ALTER TABLE `{PREFIX}user` add `plugins_user_auto_level` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员等级增强版（自动匹配）' after `plugins_user_level`;