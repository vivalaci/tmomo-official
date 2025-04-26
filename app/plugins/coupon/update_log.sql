# v3.2.3
# 优惠券
ALTER TABLE `{PREFIX}plugins_coupon` add `is_repeat_receive` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许重复领取（0否, 1是）' after `is_user_receive`;





















# v1.2.4
# 优惠券
ALTER TABLE `{PREFIX}plugins_coupon` add `shop_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '店铺id' after `id`;