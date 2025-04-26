# 订单备注
DROP TABLE IF EXISTS `{PREFIX}plugins_intellectstools_order_note`;
# 商品备注
DROP TABLE IF EXISTS `{PREFIX}plugins_intellectstools_goods_note`;
# 批量评价
DROP TABLE IF EXISTS `{PREFIX}plugins_intellectstools_comments`;
# 批量评价商品增加记录
DROP TABLE IF EXISTS `{PREFIX}plugins_intellectstools_comments_goods`;
# 批量评价商品独立配置
DROP TABLE IF EXISTS `{PREFIX}plugins_intellectstools_comments_goods_config`;
# 头条支付分账
DROP TABLE IF EXISTS `{PREFIX}plugins_intellectstools_toutiaosettlement`;
# 用户备注
DROP TABLE IF EXISTS `{PREFIX}plugins_intellectstools_user_note`;
# 商品调价配置
DROP TABLE IF EXISTS `{PREFIX}plugins_intellectstools_goods_modify_price`;

# 商品按钮名称和链接
ALTER TABLE `{PREFIX}goods` DROP `plugins_intellectstools_buy_btn_link_name`;
ALTER TABLE `{PREFIX}goods` DROP `plugins_intellectstools_buy_btn_link_url`;