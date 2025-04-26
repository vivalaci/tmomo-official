# v3.2.6
ALTER TABLE `{PREFIX}plugins_label` add `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示（0否，1是）' after `use_count`;
# 增加索引
ALTER TABLE `{PREFIX}plugins_label` ADD INDEX is_show(`is_show`);



















# v3.2.1
ALTER TABLE `{PREFIX}plugins_label` change `text_color` `text_color` char(60) NOT NULL DEFAULT '' COMMENT '文字颜色值';
ALTER TABLE `{PREFIX}plugins_label` change `bg_color` `bg_color` char(60) NOT NULL DEFAULT '' COMMENT '背景颜色值';