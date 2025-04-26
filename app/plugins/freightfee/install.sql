# 仓库运费模板
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_freightfee_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `warehouse_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '仓库id',
  `show_name` char(60) NOT NULL DEFAULT '' COMMENT '展示名称',
  `payment` text COMMENT '免运费支付方式（英文逗号分割存储）',
  `goods_ids` text COMMENT '免运费商品（英文逗号分割存储）',
  `goods_category_append` text COMMENT '商品分类额外追加的运费（json存储）',
  `valuation` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '计价方式（0按件数, 1按重量）',
  `is_insufficient_first_price` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '首(件/重)不满足按首费计算(默认0, 0否, 1是)',
  `is_continue_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '续费计算方式(默认0, 0四舍五入取整, 1向上取整（有小数就加1）, 2向下取整（有小数就忽略、直接取整）)',
  `data` text COMMENT '运费模板规则(json存储)',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用（0否，1是）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `is_enable` (`is_enable`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='运费设置 - 仓库运费模板';