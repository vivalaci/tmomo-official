# 钱包
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_wallet` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0正常, 1异常, 2已注销）',
  `normal_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额（包含赠送金额）',
  `frozen_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
  `give_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额（所有赠送金额总计）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`) USING BTREE,
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='钱包 - 钱包';

# 钱包日志
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_wallet_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `wallet_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '钱包id',
  `business_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '业务类型（0系统, 1充值, 2提现, 3消费, 4转账）',
  `money_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '金额类型（0正常, 1冻结, 2赠送）',
  `operation_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '操作类型（ 0减少, 1增加）',
  `operation_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '操作金额',
  `original_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '原始金额',
  `latest_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最新金额',
  `msg` char(200) NOT NULL DEFAULT '' COMMENT '变更说明',
  `operate_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作人id',
  `operate_name` char(30) NOT NULL DEFAULT '' COMMENT '操作人名称',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `wallet_id` (`wallet_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='钱包日志 - 钱包';

# 充值
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_wallet_recharge` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `wallet_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '钱包id',
  `recharge_no` char(60) NOT NULL DEFAULT '' COMMENT '充值单号',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0未支付, 1已支付）',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '金额',
  `pay_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `payment_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付方式id',
  `payment` char(60) NOT NULL DEFAULT '' COMMENT '支付方式标记',
  `payment_name` char(60) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `operate_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作人id',
  `operate_name` char(30) NOT NULL DEFAULT '' COMMENT '操作人名称',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `recharge_no` (`recharge_no`),
  KEY `status` (`status`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='钱包充值 - 钱包';

# 钱包提现
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_wallet_cash` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `wallet_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '钱包id',
  `cash_no` char(60) NOT NULL DEFAULT '' COMMENT '提现单号',
  `cash_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '提现方式（0其他方式, 1微信, 2支付宝）',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0未打款, 1已打款, 2打款失败）',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `commission` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `pay_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '打款金额',
  `bank_name` char(60) NOT NULL DEFAULT '' COMMENT '收款平台',
  `bank_accounts` char(60) NOT NULL DEFAULT '' COMMENT '收款账号',
  `bank_username` char(60) NOT NULL DEFAULT '' COMMENT '开户人姓名',
  `msg` char(200) NOT NULL DEFAULT '' COMMENT '描述（用户可见）',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '打款时间',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cash_no` (`cash_no`),
  KEY `cash_type` (`cash_type`),
  KEY `status` (`status`),
  KEY `user_id` (`user_id`),
  KEY `wallet_id` (`wallet_id`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='钱包提现 - 钱包';

# 钱包提现支付
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_wallet_cash_payment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `wallet_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '钱包id',
  `cash_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '提现id',
  `cash_no` char(60) NOT NULL DEFAULT '' COMMENT '提现单号',
  `pay_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '付款方式（0微信, 1支付宝）',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0待处理, 1已支付, 2已失败, 3已关闭）',
  `reason` char(230) NOT NULL DEFAULT '' COMMENT '请求失败原因',
  `out_order_no` char(160) NOT NULL DEFAULT '' COMMENT '外部订单号',
  `request_params` mediumtext COMMENT '请求参数（数组则json字符串存储）',
  `response_data` mediumtext COMMENT '响应参数（数组则json字符串存储）',
  `tsc` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '耗时时间（单位秒）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `wallet_id` (`wallet_id`),
  KEY `cash_id` (`cash_id`),
  KEY `cash_no` (`cash_no`),
  KEY `pay_type` (`pay_type`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='钱包提现支付 - 钱包';

# 转账记录
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_wallet_transfer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `transfer_no` char(60) NOT NULL DEFAULT '' COMMENT '转账单号',
  `send_user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '发起用户id',
  `send_wallet_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '发起钱包id',
  `receive_user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '接收用户id',
  `receive_wallet_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '接收钱包id',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '金额',
  `note` char(230) NOT NULL DEFAULT '' COMMENT '备注',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `transfer_no` (`transfer_no`),
  KEY `send_user_id` (`send_user_id`),
  KEY `send_wallet_id` (`send_wallet_id`),
  KEY `receive_user_id` (`receive_user_id`),
  KEY `receive_wallet_id` (`receive_wallet_id`),
  KEY `money` (`money`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='转账记录 - 钱包';

# 付款码
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_wallet_payment_code` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `wallet_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '钱包id',
  `code` char(30) NOT NULL DEFAULT '' COMMENT '付款码',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `wallet_id` (`wallet_id`),
  KEY `user_id` (`user_id`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='付款码 - 钱包';