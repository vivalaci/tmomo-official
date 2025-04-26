# v2.1.4
ALTER TABLE `{PREFIX}plugins_invoice_value` add `business_no` char(60) NOT NULL DEFAULT '' COMMENT '业务单号' after `business_id`;
ALTER TABLE `{PREFIX}plugins_invoice_value` ADD INDEX business_no(`business_no`);