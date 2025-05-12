# v3.3.5
# 佣金明细
ALTER TABLE `{PREFIX}plugins_distribution_profit_log` add `profit_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '收益类型（0金额, 1积分）' after `total_price`;