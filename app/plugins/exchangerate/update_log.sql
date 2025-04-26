# v3.1.0
# 添加图标
ALTER TABLE `{PREFIX}plugins_exchangerate_currency` add `icon` char(255) NOT NULL DEFAULT '' COMMENT 'icon图标' after `id`;





















# v2.1.3
ALTER TABLE `{PREFIX}plugins_exchangerate_currency` change `rate` `rate` decimal(12,6) unsigned NOT NULL DEFAULT '0.000000' COMMENT '汇率';