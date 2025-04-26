# v2.1.0
# 语音发送日志
CREATE TABLE IF NOT EXISTS `{PREFIX}plugins_messagenotice_voice_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `device_id` char(60) NOT NULL DEFAULT '' COMMENT '设备ID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类型（0订单通知，1用户审核，2扫码收款）',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0未发送，1已发送，2已失败）',
  `reason` char(255) NOT NULL DEFAULT '' COMMENT '失败原因',
  `content` mediumtext COMMENT '语音模板内容',
  `tsc` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '耗时时间（单位秒）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `tsc` (`tsc`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET={CHARSET} ROW_FORMAT=DYNAMIC COMMENT='语音发送日志 - 消息通知';


























# v2.0.0
ALTER TABLE `{PREFIX}plugins_messagenotice_sms_log` change `reason` `reason` char(255) NOT NULL DEFAULT '' COMMENT '失败原因';
ALTER TABLE `{PREFIX}plugins_messagenotice_email_log` change `reason` `reason` char(255) NOT NULL DEFAULT '' COMMENT '失败原因';