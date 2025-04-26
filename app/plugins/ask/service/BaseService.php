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
namespace app\plugins\ask\service;

use app\service\PluginsService;
use app\plugins\ask\service\AskService;

/**
 * 问答基础服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class BaseService
{
    // 基础数据附件字段
    public static $base_config_attachment_field = [
        'header_logo',
        'header_bg',
    ];

    /**
     * 基础配置信息保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function BaseConfigSave($params = [])
    {
        return PluginsService::PluginsDataSave(['plugins'=>'ask', 'data'=>$params], self::$base_config_attachment_field);
    }
    
    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * 
     * @param   [boolean]          $is_cache [是否缓存中读取]
     */
    public static function BaseConfig($is_cache = true)
    {
        return PluginsService::PluginsData('ask', self::$base_config_attachment_field, $is_cache);
    }

    /**
     * 后台导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-06
     * @desc    description
     */
    public static function AdminNavMenuList()
    {
        return [
            [
                'name'      => '基础配置',
                'control'   => 'admin',
                'action'    => 'index',
            ],
            [
                'name'      => '问答管理',
                'control'   => 'ask',
                'action'    => 'index',
            ],
            [
                'name'      => '问答分类',
                'control'   => 'askcategory',
                'action'    => 'index',
            ],
            [
                'name'      => '问答评论',
                'control'   => 'askcomments',
                'action'    => 'index',
            ],
            [
                'name'      => '推荐商品',
                'control'   => 'goods',
                'action'    => 'index',
            ],
            [
                'name'      => '轮播管理',
                'control'   => 'slider',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 头部数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-07-27
     * @desc    description
     * @param   [array]          $config [配置信息]
     */
    public static function HeaderData($config)
    {
        return [
            'header_logo'      => empty($config['header_logo']) ? AttachmentPathViewHandle(MyC('home_site_logo')) : $config['header_logo'],
            'header_bg'        => empty($config['header_bg']) ? StaticAttachmentUrl('header-bg.png') : $config['header_bg'],
            'header_bg_vague'  => (isset($config['header_bg_vague']) && $config['header_bg_vague'] !== '') ? $config['header_bg_vague'] : 1,
        ];
    }

    /**
     * 静态数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-10-13
     * @desc    description
     * @param   [string]          $key [数据key]
     */
    public static function ConstData($key)
    {
        $data = [
            // 显示状态
            'ask_is_show_list' => [
                0 => ['value' => 0, 'name' => '未显示', 'checked' => true],
                1 => ['value' => 1, 'name' => '已显示'],
            ],

            // 回复状态
            'ask_is_reply_list' => [
                0 => ['value' => 0, 'name' => '未回复', 'checked' => true],
                1 => ['value' => 1, 'name' => '已回复'],
            ],

            // 问答评论状态
            'ask_comments_status_list' => [
                0 => ['value' => 0, 'name' => '待审核', 'checked' => true],
                1 => ['value' => 1, 'name' => '已审核'],
                2 => ['value' => 2, 'name' => '已隐藏'],
            ],

            // 问答排序类型
            'ask_order_by_type_list' => [
                0 => ['index' => 0, 'value' => 'id', 'name' => '最新', 'checked' => true],
                1 => ['index' => 1, 'value' => 'access_count', 'name' => '热度'],
                2 => ['index' => 2, 'value' => 'comments_count', 'name' => '评论'],
                3 => ['index' => 3, 'value' => 'give_thumbs_count', 'name' => '点赞'],
                4 => ['index' => 4, 'value' => 'upd_time', 'name' => '更新'],
            ],
        ];
        return array_key_exists($key, $data) ? $data[$key] : [];
    }

    /**
     * 表情列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-07-03
     * @desc    description
     */
    public static function EmojiList()
    {
        return [
            ['emoji' => '😀', 'tips' => '哈哈'],
            ['emoji' => '😁', 'tips' => '笑、呲牙、哈哈、哈哈哈'],
            ['emoji' => '🤣', 'tips' => '笑死、笑死了'],
            ['emoji' => '😂', 'tips' => '大哭、苦笑、笑哭了、哭笑不得、哭了'],
            ['emoji' => '😄', 'tips' => '哈哈、大笑、憨笑、高兴'],
            ['emoji' => '😅', 'tips' => '流汗、含汗、很汗'],
            ['emoji' => '😆', 'tips' => '笑死了、哈哈'],
            ['emoji' => '😇', 'tips' => '天使、光环'],

            ['emoji' => '😉', 'tips' => '眨眼、挤眼、嘿嘿、你懂得、你懂得'],
            ['emoji' => '😊', 'tips' => '开销、微信、可爱、好开心、嗯嗯、萌'],
            ['emoji' => '🙂', 'tips' => '微信'],
            ['emoji' => '🙃', 'tips' => '呵呵、我倒'],
            ['emoji' => '☺️', 'tips' => '笑脸、含羞、嘻嘻'],
            ['emoji' => '😋', 'tips' => '啦啦、好吃、好哦、嘿嘿'],
            ['emoji' => '😌', 'tips' => '唉、哎、就这样吧、满足'],
            ['emoji' => '😍', 'tips' => '爱你、色、花心、喜欢'],

            ['emoji' => '😘', 'tips' => '爱你、飞吻、吻你、么么哒、么么、摸摸哒'],
            ['emoji' => '😙', 'tips' => '亲亲、亲吻、爱你、亲一个'],
            ['emoji' => '😜', 'tips' => '鬼脸、嘿嘿'],
            ['emoji' => '😝', 'tips' => '嘿嘿、吐舌头、哈哈、嘿嘿嘿、嘻嘻、啦啦啦'],
            ['emoji' => '🤑', 'tips' => '财迷、真有钱'],
            ['emoji' => '🤓', 'tips' => '呆子、学霸'],
            ['emoji' => '😎', 'tips' => '酷、很酷'],
            ['emoji' => '🤗', 'tips' => '抱、抱抱、拥抱'],

            ['emoji' => '🤡', 'tips' => '小丑'],
            ['emoji' => '🤠', 'tips' => '牛仔'],
            ['emoji' => '😏', 'tips' => '坏笑、得意、哼哼、奸笑'],
            ['emoji' => '😶', 'tips' => '无语、囧'],
            ['emoji' => '😑', 'tips' => '呵呵、无语、败给你了'],
            ['emoji' => '😒', 'tips' => '哼、无语、讨厌、撒嘴、不开心'],
            ['emoji' => '🙄', 'tips' => '翻白眼、白眼、呵呵'],
            ['emoji' => '🤔', 'tips' => '思考、我想想'],

            ['emoji' => '😳', 'tips' => '懵、呆、惊呆了、囧、脸红'],
            ['emoji' => '😞', 'tips' => '失望、不开心、我错了、难过、忏悔'],
            ['emoji' => '😟', 'tips' => '担心、担忧'],
            ['emoji' => '😠', 'tips' => '生气、讨厌、心烦、去死'],
            ['emoji' => '😡', 'tips' => '愤怒、生气、你死定了、滚、太过分了'],
            ['emoji' => '😔', 'tips' => '哎、唉、失望'],
            ['emoji' => '😕', 'tips' => '哼、傲慢、不理你、不理你了'],
            ['emoji' => '☹️', 'tips' => '哼、难过、不开心'],

            ['emoji' => '😣', 'tips' => '难过、悔恨'],
            ['emoji' => '😖', 'tips' => '疯了、纠结、受不了'],
            ['emoji' => '😫', 'tips' => '疲惫、好累、不嘛'],
            ['emoji' => '😤', 'tips' => '生气、不理你了、气死、气死了'],
            ['emoji' => '😮', 'tips' => '惊呆'],
            ['emoji' => '😱', 'tips' => '恐怖、惊恐、好怕'],
            ['emoji' => '😨', 'tips' => '害怕'],
            ['emoji' => '😰', 'tips' => '糟糕、完了、怎么办'],

            ['emoji' => '😯', 'tips' => '哦、酱紫、原来如此'],
            ['emoji' => '😦', 'tips' => '天哪'],
            ['emoji' => '😢', 'tips' => '哭、想哭、委屈、眼泪'],
            ['emoji' => '😥', 'tips' => '失望、焦虑、怎么办'],
            ['emoji' => '😪', 'tips' => '睡觉、困、睡'],
            ['emoji' => '😓', 'tips' => '汗、好尴尬、晕'],
            ['emoji' => '🤤', 'tips' => '流口水、想吃、馋'],
            ['emoji' => '😭', 'tips' => '大哭、好惨、伤心、笑死、哭'],

            ['emoji' => '😲', 'tips' => '吃惊、不会吧、天哪、天呐、晕'],
            ['emoji' => '🤥', 'tips' => '说谎、匹诺曹、撒谎'],
            ['emoji' => '🤢', 'tips' => '恶心、呕吐'],
            ['emoji' => '🤧', 'tips' => '流鼻涕、感冒'],
            ['emoji' => '🤐', 'tips' => '不说、保密、嘴严'],
            ['emoji' => '😷', 'tips' => '生病、病了'],
            ['emoji' => '🤒', 'tips' => '发烧'],
            ['emoji' => '🤕', 'tips' => '受伤、可怜'],

            ['emoji' => '😴', 'tips' => '困、睡、睡觉'],
            ['emoji' => '💤', 'tips' => '睡觉'],
            ['emoji' => '💩', 'tips' => '便便、臭臭'],
            ['emoji' => '😈', 'tips' => '邪恶、恶魔、魔鬼'],
            ['emoji' => '👹', 'tips' => '怪物'],
            ['emoji' => '👺', 'tips' => '天狗、妖怪'],
            ['emoji' => '💀', 'tips' => '头骨、骷髅头'],
            ['emoji' => '👻', 'tips' => '幽灵、鬼、吓死你'],

            ['emoji' => '👽', 'tips' => '外星人'],
            ['emoji' => '🤖', 'tips' => '机器人'],
            ['emoji' => '👏', 'tips' => '鼓掌、拍手、说得好、说的好'],
            ['emoji' => '👋', 'tips' => '挥手、再见、拜拜'],
            ['emoji' => '👍', 'tips' => '赞、棒、真棒、超赞、强'],
            ['emoji' => '👎', 'tips' => '鄙视、弱、low'],
            ['emoji' => '👊', 'tips' => '拳头'],
            ['emoji' => '🤞', 'tips' => '祝愿'],

            ['emoji' => '🤝', 'tips' => '握手、合作愉快'],
            ['emoji' => '✌️', 'tips' => '胜利、耶'],
            ['emoji' => '👌', 'tips' => '好、ok、好的'],
            ['emoji' => '✋', 'tips' => '手'],
            ['emoji' => '💪', 'tips' => '强壮、加油、你可以的、肌肉'],
            ['emoji' => '🙏', 'tips' => '拜托、祈祷、祈福、保佑'],
            ['emoji' => '☝️', 'tips' => '一个、第一'],
            ['emoji' => '👆', 'tips' => '上面'],

            ['emoji' => '👇', 'tips' => '下面'],
            ['emoji' => '👈', 'tips' => '左边'],
            ['emoji' => '👉', 'tips' => '右边'],
            ['emoji' => '🖐', 'tips' => '手掌'],
            ['emoji' => '🤘', 'tips' => '摇滚'],
            ['emoji' => '✍️', 'tips' => '写字、书写'],
            ['emoji' => '💅', 'tips' => '美甲、指甲油'],
            ['emoji' => '👄', 'tips' => '嘴巴、嘴'],

            ['emoji' => '👅', 'tips' => '舌头、吐舌头'],
            ['emoji' => '👂', 'tips' => '耳朵、耳、听'],
            ['emoji' => '👃', 'tips' => '鼻子、鼻、闻'],
            ['emoji' => '👁', 'tips' => '眼睛、眼'],
            ['emoji' => '👀', 'tips' => '眼睛、看'],
            ['emoji' => '🗣', 'tips' => '演讲'],
            ['emoji' => '👶', 'tips' => '宝宝、婴儿'],
            ['emoji' => '👦', 'tips' => '男孩'],

            ['emoji' => '👧', 'tips' => '女孩'],
            ['emoji' => '👩', 'tips' => '女人'],
            ['emoji' => '👱', 'tips' => '男人'],
            ['emoji' => '👴', 'tips' => '老爷爷、老人'],
            ['emoji' => '👵', 'tips' => '老奶奶'],
            ['emoji' => '👲', 'tips' => '瓜皮帽'],
            ['emoji' => '👳', 'tips' => '头巾'],
            ['emoji' => '👮', 'tips' => '警察'],

            ['emoji' => '👝', 'tips' => '手袋、荷包、手拿包'],
            ['emoji' => '👛', 'tips' => '钱包、零钱包'],
            ['emoji' => '👜', 'tips' => '手提包'],
            ['emoji' => '💼', 'tips' => '公文包'],
            ['emoji' => '👓', 'tips' => '眼镜'],
            ['emoji' => '🕶', 'tips' => '墨镜'],
            ['emoji' => '💍', 'tips' => '戒指、钻石'],
            ['emoji' => '🌂', 'tips' => '雨伞、伞'],
        ];
    }

    /**
     * 搜索页面tab导航
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-15
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function SearchTabList($params = [])
    {
        // 基础导航
        $data = [
            [
                'name'  => '最新的',
                'type'  => '',
            ],
            [
                'name'  => '热门的',
                'type'  => 3,
            ],
            [
                'name'  => '已回答',
                'type'  => 1,
            ],
            [
                'name'  => '未回答',
                'type'  => 0,
            ],
        ];

        // web端数据处理
        if(APPLICATION == 'web')
        {
            $where = empty($params['bwd']) ? [] : ['bwd'=>$params['bwd']];
            foreach($data as &$v)
            {
                // 是否选中
                $v['active'] = ((!isset($params['type']) && $v['type'] === '') || (isset($params['type']) && $params['type'] == $v['type'])) ? 1 : 0;
                // url地址
                $temp_where = ($v['type'] === '') ? $where : array_merge($where, ['type'=>$v['type']]);
                $v['url'] = PluginsHomeUrl('ask', 'index', 'search', $temp_where);
            }
        }
        return $data;
    }

    /**
     * 条件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-03-11
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AskListWhere($params = [])
    {
        // 条件
        $where = [];

        // 是否显示（传null则不限制）
        $is_show = isset($params['is_show']) ? $params['is_show'] : 1;
        if($is_show !== null)
        {
            $where[] = ['is_show', '=', intval($is_show)];
        }

        // id
        if(!empty($params['id']))
        {
            $where[] = ['id', '=', $params['id']];
        }

        // 搜索关键字
        if(!empty($params['bwd']))
        {
            $keywords_value = (APPLICATION == 'web') ? AsciiToStr($params['bwd']) : $params['bwd'];
            $where[] = ['title|content', 'like', '%'.$keywords_value.'%'];
        }

        // 指定类型（0 未回复、1已回复）
        if(isset($params['type']) && in_array($params['type'], [0,1]))
        {
            $where[] = ['is_reply', '=', intval($params['type'])];
        }

        // 推荐问答id
        if(!empty($params['recommend_ids']))
        {
            $where[] = ['id', 'in', explode(',', $params['recommend_ids'])];
        }

        // 商品id
        if(!empty($params['goods_id']))
        {
            $where[] = ['goods_id', '=', intval($params['goods_id'])];
        }

        return $where;
    }

    /**
     * 问答列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AskList($params = [])
    {
        // 条件
        $where = empty($params['where']) ? self::AskListWhere($params) : $params['where'];
        // 排序
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        // 是否搜索指定热门
        if(!empty($params['type']) && $params['type'] == 3)
        {
            $order_by = 'access_count desc';
        }

        // 获取列表
        $params['m']         = isset($params['m']) ? intval($params['m']) : 0;
        $params['n']         = isset($params['n']) ? intval($params['n']) : 10;
        $params['where']     = $where;
        $params['order_by']  = $order_by;
        return AskService::AskList($params);
    }

    /**
     * 获取一条问答
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AskRow($params = [])
    {
        // 参数
        if(empty($params['id']))
        {
            return DataReturn('问答id有误', -1);
        }

        // 获取列表
        $params['m']             = 0;
        $params['n']             = 1;
        $params['where']         = [
            ['is_show', '=', 1],
            ['id', '=', intval($params['id'])],
        ];
        $params['is_comments']   = 1;
        $ret = self::AskList($params);
        if(empty($ret['data']) || empty($ret['data'][0]))
        {
            return DataReturn('没有相关问答', -1);
        }
        return DataReturn('success', 0, $ret['data'][0]);
    }

    /**
     * 推荐问答
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-14
     * @desc    description
     * @param   [array]           $config [插件配置]
     */
    public static function AskRecommend($config = [])
    {
        $data = [];
        if(!empty($config['recommend_ids']))
        {
            // 获取列表
            $ret = self::AskList([
                'n'              => 0,
                'recommend_ids'  => $config['recommend_ids']]
            );
            $data = $ret['data'];
        }
        return $data;
    }

    /**
     * 热门问答
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-14
     * @desc    description
     * @param   [array]           $config [插件配置]
     */
    public static function AskHot($config = [])
    {
        $number = isset($config['hot_page_number']) ? intval($config['hot_page_number']) : 10;
        $ret = self::AskList(['n'=>$number, 'order_by'=>'access_count desc']);
        return $ret['data'];
    }

    /**
     * 最新问答
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-14
     * @desc    description
     * @param   [array]           $config [插件配置]
     */
    public static function AskNew($config = [])
    {
        $number = isset($config['middle_new_page_number']) ? intval($config['middle_new_page_number']) : 20;
        $ret = self::AskList(['n'=>$number, 'order_by'=>'id desc']);
        return $ret['data'];
    }
}
?>