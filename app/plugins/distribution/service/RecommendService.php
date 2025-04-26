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
namespace app\plugins\distribution\service;

use think\facade\Db;
use app\service\ResourcesService;
use app\service\UserService;
use app\service\GoodsService;
use app\plugins\distribution\service\BaseService;

/**
 * 分销 - 推荐宝服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class RecommendService
{
    /**
     * 推荐数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-08-04
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function RecommendData($params = [])
    {
        // 参数
        if(empty($params['id']))
        {
            return DataReturn('数据id为空', -1);
        }

        // 查询数据
        $data = Db::name('PluginsDistributionRecommend')->where(['id'=>intval($params['id']), 'is_enable'=>1])->find();
        if(empty($data))
        {
            return DataReturn('数据不存在或未启用', -1);
        }

        // 数据处理
        $res = self::RecommendListHandle([$data], $params);
        $res[0]['total_price'] = 0;
        $res[0]['buy_count'] = 0;
        if(!empty($res[0]['detail_list']))
        {
            foreach($res[0]['detail_list'] as $v)
            {
                if(!empty($v['goods']) && $v['goods']['is_error'] == 0)
                {
                    $res[0]['total_price'] += $v['goods']['price'];
                    $res[0]['buy_count'] += $v['goods']['buy_min_number'];
                }
            }
            $res[0]['total_price'] = PriceNumberFormat($res[0]['total_price']);
        }

        // 访问数量增加
        self::RecommendAccessCountInc($res[0]);

        // 返回数据
        return DataReturn('success', 0, $res[0]);
    }

    /**
     * 获取列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:50:14+0800
     * @param    [array]          $params [输入参数]
     */
    public static function RecommendList($params = [])
    {
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];
        $data = Db::name('PluginsDistributionRecommend')->field($field)->where($where)->limit($m, $n)->order($order_by)->select()->toArray();
        if(!empty($data))
        {
            $common_is_enable_tips = MyConst('common_is_enable_tips');
            $data = self::RecommendListHandle($data);
            foreach($data as &$v)
            {
                // 是否启用
                if(array_key_exists('is_enable', $v))
                {
                    $v['is_enable_text'] = $common_is_enable_tips[$v['is_enable']]['name'];
                }

                // 时间
                if(array_key_exists('add_time', $v))
                {
                    $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                if(array_key_exists('upd_time', $v))
                {
                    $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
                }
            }
        }
        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    /**
     * 列表条件
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T22:53:46+0800
     * @param    [array]          $params [输入参数]
     */
    public static function RecommendWhere($params = [])
    {
        $where = [];

        // 关键字
        if(!empty($params['keywords']))
        {
            $where[] = ['title|describe', 'like', '%'.$params['keywords'].'%'];
        }

        // id
        if(!empty($params['id']))
        {
            $where[] = ['id', '=', intval($params['id'])];
        }

        // 用户id
        if(!empty($params['user_id']))
        {
            $where[] = ['user_id', '=', $params['user_id']];
        }

        return $where;
    }

    /**
     * 总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-06-09T23:10:43+0800
     * @param    [array]          $where [条件]
     */
    public static function RecommendTotal($where)
    {
        return (int) Db::name('PluginsDistributionRecommend')->where($where)->count();
    }

    /**
     * 数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-09-29
     * @desc    description
     * @param   [array]          $data [数据]
     */
    public static function RecommendListHandle($data, $params = [])
    {
        if(!empty($data))
        {
            // 用户
            $user = UserService::GetUserViewInfo(array_column($data, 'user_id'));

            // 关联的商品
            $detail_group = [];
            $detail = Db::name('PluginsDistributionRecommendGoods')->where(['recommend_id'=>array_column($data, 'id')])->select()->toArray();
            if(!empty($detail))
            {
                // 读取所有商品
                $goods = BaseService::GoodsList(array_column($detail, 'goods_id'));
                $goods_data = empty($goods['data']) ? [] : array_column($goods['data'], null, 'id');

                // 详情分组
                foreach($detail as $dv)
                {
                    // 规格字符串
                    $dv['spec_data'] = empty($dv['spec']) ? '' : json_decode($dv['spec'], true);
                    $dv['spec_text_value'] = empty($dv['spec_data']) ? '' : implode('', array_column($dv['spec_data'], 'value'));
                    $dv['spec_text_view'] = empty($dv['spec_data']) ? '' : implode(' / ', array_column($dv['spec_data'], 'value'));

                    // 商品加入
                    $dv['goods'] = (!empty($goods_data) && array_key_exists($dv['goods_id'], $goods_data)) ? $goods_data[$dv['goods_id']] : null;
                    if(!empty($dv['goods']))
                    {
                        // 规格价格、没有规格则取最低价格
                        if(empty($dv['spec_data']))
                        {
                            $dv['goods']['price'] = $dv['goods']['min_price'];
                            $dv['goods']['inventory'] = $dv['goods']['inventory'];
                        } else {
                            // 规格信息
                            $spec_ret = GoodsService::GoodsSpecDetail(['id'=>$dv['goods_id'], 'spec'=>$dv['spec_data']]);
                            if($spec_ret['code'] == 0 && !empty($spec_ret['data']['spec_base']))
                            {
                                $dv['goods']['price'] = $spec_ret['data']['spec_base']['price'];
                                $dv['goods']['original_price'] = $spec_ret['data']['spec_base']['original_price'];
                                $dv['goods']['inventory'] = $spec_ret['data']['spec_base']['inventory'];
                                $dv['goods']['buy_min_number'] = empty($spec_ret['data']['spec_base']['buy_min_number']) ? 1 : $spec_ret['data']['spec_base']['buy_min_number'];
                                $dv['goods']['buy_max_number'] = $spec_ret['data']['spec_base']['buy_max_number'];
                            } else {
                                $dv['goods']['is_error'] = 1;
                                $dv['goods']['error_msg'] = $spec_ret['msg'];
                            }
                        }
                    }

                    // 加入分组
                    if(!array_key_exists($dv['recommend_id'], $detail_group))
                    {
                        $detail_group[$dv['recommend_id']] = [];
                    }
                    $detail_group[$dv['recommend_id']][] = $dv;
                }
            }

            foreach($data as &$v)
            {
                // 用户
                if(array_key_exists('user_id', $v))
                {
                    $v['user'] = empty($user[$v['user_id']]) ? null : $user[$v['user_id']];
                }

                // url
                if(array_key_exists('id', $v))
                {
                    $v['url'] = (APPLICATION == 'web') ? PluginsHomeUrl('distribution', 'recommend', 'detail', ['id'=>$v['id']]) : '/pages/plugins/distribution/recommend/recommend?id='.$v['id'];
                }

                // 关联商品
                if(array_key_exists('goods_count', $v))
                {
                    $v['detail_list'] = (!empty($detail_group) && array_key_exists($v['id'], $detail_group)) ? $detail_group[$v['id']] : [];
                }

                // 封面图片
                if(array_key_exists('icon', $v))
                {
                    $v['icon'] = ResourcesService::AttachmentPathViewHandle($v['icon']);
                }
            }
        }
        return $data;
    }

    /**
     * 保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function RecommendSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user_id',
                'error_msg'         => '用户信息有误',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'title',
                'checked_data'      => '1,60',
                'error_msg'         => '标题格式1~60个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'describe',
                'checked_data'      => '230',
                'is_checked'        => 1,
                'error_msg'         => '描述格式最多230个字符',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'goods_data',
                'error_msg'         => '请选择商品',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'seo_title',
                'checked_data'      => '100',
                'is_checked'        => 1,
                'error_msg'         => 'SEO标题格式最多100个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'seo_keywords',
                'checked_data'      => '130',
                'is_checked'        => 1,
                'error_msg'         => 'SEO关键字格式最多130个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'seo_desc',
                'checked_data'      => '230',
                'is_checked'        => 1,
                'error_msg'         => 'SEO描述格式最多230个字符',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 推荐商品
        $goods_data = json_decode(base64_decode(urldecode($params['goods_data'])), true);

        // 附件
        $data_fields = ['icon'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        $data = [
            'user_id'       => intval($params['user_id']),
            'icon'          => $attachment['data']['icon'],
            'title'         => $params['title'],
            'describe'      => empty($params['describe']) ? '' : $params['describe'],
            'goods_count'   => count($goods_data),
            'is_enable'     => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
            'seo_title'     => empty($params['seo_title']) ? '' : $params['seo_title'],
            'seo_keywords'  => empty($params['seo_keywords']) ? '' : $params['seo_keywords'],
            'seo_desc'      => empty($params['seo_desc']) ? '' : $params['seo_desc'],
        ];

        // 当前数据
        $info = empty($params['id']) ? [] : Db::name('PluginsDistributionRecommend')->where(['id'=>intval($params['id']), 'user_id'=>$data['user_id']])->find();

        // 捕获异常
        Db::startTrans();
        try {
            if(empty($info))
            {
                $data['add_time'] = time();
                $recommend_id = Db::name('PluginsDistributionRecommend')->insertGetId($data);
                if($recommend_id <= 0)
                {
                    throw new \Exception(MyLang('insert_fail'));
                }
            } else {
                $data['upd_time'] = time();
                if(!Db::name('PluginsDistributionRecommend')->where(['id'=>$info['id']])->update($data))
                {
                    throw new \Exception(MyLang('edit_fail'));
                }
                $recommend_id = $info['id'];
            }

            // 关联推荐商品
            // 先删除关联数据再添加
            Db::name('PluginsDistributionRecommendGoods')->where(['recommend_id'=>$recommend_id])->delete();

            // 添加关联数据
            if(!empty($goods_data))
            {
                $goods_insert_data = [];
                foreach($goods_data as $v)
                {
                    $goods_insert_data[] = [
                        'recommend_id'  => $recommend_id,
                        'goods_id'      => $v['goods_id'],
                        'spec'          => empty($v['spec']) ? '' : $v['spec'],
                        'add_time'      => time(),
                    ];
                }
                if(Db::name('PluginsDistributionRecommendGoods')->insertAll($goods_insert_data) < count($goods_insert_data))
                {
                    throw new \Exception('关联商品添加失败');
                }
            }

            Db::commit();
            return DataReturn(MyLang('operate_success'), 0); 
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 访问统计加1
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-10-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function RecommendAccessCountInc($params = [])
    {
        if(!empty($params['id']))
        {
            return Db::name('PluginsDistributionRecommend')->where(['id'=>intval($params['id'])])->inc('access_count')->update();
        }
        return false;
    }

    /**
     * 删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function RecommendDelete($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'ids',
                'error_msg'         => '数据id为空',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 是否用户操作
        if(empty($params['user_type']) || $params['user_type'] == 'user')
        {
            if(empty($params['user_id']))
            {
                return DataReturn('用户信息有误', -1);
            }
        }

        // 捕获异常
        Db::startTrans();
        try {
            // 主数据
            $where = [
                ['id', 'in', $params['ids']],
            ];
            if(!empty($params['user_id']))
            {
                $where[] = ['user_id', '=', intval($params['user_id'])];
            }
            if(!Db::name('PluginsDistributionRecommend')->where($where)->delete())
            {
                return DataReturn(MyLang('delete_fail'), -1);
            }

            // 关联数据
            $where = [
                ['recommend_id', 'in', $params['ids']],
            ];
            if(Db::name('PluginsDistributionRecommendGoods')->where($where)->delete() === false)
            {
                return DataReturn('关联商品删除失败', -1);
            }

            Db::commit();
            return DataReturn(MyLang('delete_success'), 0);
        } catch(\Exception $e) {
            Db::rollback();
            return DataReturn($e->getMessage(), -1);
        }
    }

    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function RecommendStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => MyLang('data_id_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => MyLang('operate_field_error_tips'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => MyLang('form_status_range_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 是否用户操作
        if(empty($params['user_type']) || $params['user_type'] == 'user')
        {
            if(empty($params['user_id']))
            {
                return DataReturn('用户信息有误', -1);
            }
        }

        // 操作条件
        $where = [
            ['id', '=', intval($params['id'])],
        ];
        if(!empty($params['user_id']))
        {
            $where[] = ['user_id', '=', intval($params['user_id'])];
        }
        if(Db::name('PluginsDistributionRecommend')->where($where)->update([$params['field']=>intval($params['state']), 'upd_time'=>time()]))
        {
            return DataReturn(MyLang('operate_success'));
        }
        return DataReturn(MyLang('operate_fail'), -100);
    }
}
?>