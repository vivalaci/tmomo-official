# v3.2.1
# 标题长度更新
ALTER TABLE `{PREFIX}plugins_speedplaceorder_cart` change `title` `title` char(200) NOT NULL DEFAULT '' COMMENT '标题';