<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2099 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://opensource.org/licenses/mit-license.php )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\plugins\intellectstools\service;

use think\facade\Db;
use app\service\ResourcesService;

/**
 * 智能工具箱 - 商品评论服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2021-05-07
 * @desc    description
 */
class GoodsCommentsService
{
    /**
     * 商品评论保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-10-17
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function GoodsCommentsSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'goods_id',
                'error_msg'         => MyLang('goods_id_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'rating',
                'error_msg'         => MyLang('common_service.goodscomments.save_rating_empty_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'content',
                'error_msg'         => MyLang('common_service.goodscomments.save_content_empty_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user',
                'error_msg'         => MyLang('user_info_incorrect_tips'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 附件处理
        if(!empty($params['images']))
        {
            if(!is_array($params['images']))
            {
                $params['images'] = json_decode(htmlspecialchars_decode($params['images']), true);
            }
            foreach($params['images'] as &$iv)
            {
                $iv = ResourcesService::AttachmentPathHandle($iv);
            }
            if(count($params['images']) > 3)
            {
                return DataReturn(MyLang('common_service.goodscomments.form_item_images_message'), -1);
            }
        }

        // 评论数据
        $data = [
            'user_id'       => $params['user']['id'],
            'order_id'      => 0,
            'goods_id'      => intval($params['goods_id']),
            'business_type' => 'order',
            'content'       => str_replace(['"', "'", '&quot', '&lt;', '&gt;'], '', $params['content']),
            'images'        => empty($params['images']) ? '' : json_encode($params['images']),
            'rating'        => intval($params['rating']),
            'is_anonymous'  => isset($params['is_anonymous']) ? intval($params['is_anonymous']) : 0,
            'is_show'       => 0,
            'add_time'      => time(),
        ];
        if(Db::name('GoodsComments')->insertGetId($data))
        {
            return DataReturn('评论成功、等待管理员审核后展示！', 0);
        }
        return DataReturn(MyLang('comments_fail'), -1);
    }
}
?>