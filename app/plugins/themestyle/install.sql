# 默认主题样式 - 应用
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_themestyle_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` char(255) NOT NULL DEFAULT '' COMMENT 'icon图标',
  `config` longtext COMMENT '配置信息',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '顺序',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用（0否，1是）',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否默认（0否，1是）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='默认主题样式 - 应用';