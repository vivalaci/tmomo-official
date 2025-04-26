# v2.0.0
# 新增背景和logo
ALTER TABLE `{PREFIX}plugins_signin_qrcode` add `bg_images` char(255) NOT NULL DEFAULT '' COMMENT '背景图片' after `day_number_limit`;
ALTER TABLE `{PREFIX}plugins_signin_qrcode` add `logo` char(255) NOT NULL DEFAULT '' COMMENT 'logo' after `bg_images`;

# 移除废弃字段
ALTER TABLE `{PREFIX}plugins_signin_qrcode` DROP `right_images`;
ALTER TABLE `{PREFIX}plugins_signin_qrcode` DROP `right_images_url_rules`;