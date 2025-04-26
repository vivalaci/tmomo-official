# 分销等级
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_level`;
# 佣金明细
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_profit_log`;
# 分销商取货点
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_user_self_extraction`;
# 分销商取货点关联订单
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_user_self_extraction_order`;
# 积分发放明细
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_integral_log`;
# 自定义取货地址
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_custom_extraction_address`;
# 分销阶梯返佣记录
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_appoint_ladder_log`;
# 分销阶梯返佣记录商品
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_appoint_ladder_log_goods`;
# 分销等级多商户
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_level_shop`;
# 分销商推荐宝
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_recommend`;
# 分销商推荐宝商品
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_recommend_goods`;
# 客户拜访
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_visit`;
# 海报地址
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_poster`;
# 上级用户修改
DROP TABLE IF EXISTS `{PREFIX}plugins_distribution_superior_modify`;


# 用户分销等级（手动指定）
ALTER TABLE `{PREFIX}user` DROP `plugins_distribution_level`;
# 用户分销等级（自动匹配）
ALTER TABLE `{PREFIX}user` DROP `plugins_distribution_auto_level`;