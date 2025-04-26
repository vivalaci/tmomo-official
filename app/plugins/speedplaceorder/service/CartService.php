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
namespace app\plugins\speedplaceorder\service;

use think\facade\Db;
use app\service\ResourcesService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\service\BuyService;
use app\service\UserService;
use app\service\BrandService;
use app\service\RegionService;
use app\service\GoodsCartService;

/**
 * 极速下单 - 购物车服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-05T21:51:08+0800
 */
class CartService
{
    /**
     * 购物车列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-20
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public static function CartList($params = [])
    {
        // 请求参数
        $p = [
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

        // 条件
        $where = empty($params['where']) ? [] : $params['where'];

        // 加入用户id
        $where[] = ['sc.user_id', '=', $params['user']['id']];

        // 获取数据
        $field = 'sc.*, g.images,g.simple_desc,g.is_shelves,g.inventory_unit,g.is_shelves,g.is_delete_time,g.buy_min_number,g.buy_max_number,g.model,g.brand_id,g.place_origin,g.site_type';
        $data = Db::name('PluginsSpeedplaceorderCart')->alias('sc')->join('goods g', 'sc.goods_id=g.id')->where($where)->field($field)->order('id desc')->select()->toArray();

        // 数据处理
        if(!empty($data))
        {
            // 商品处理
            $res = GoodsService::GoodsDataHandle($data, ['data_key_field'=>'goods_id']);
            $data = $res['data'];
            foreach($data as &$v)
            {
                // 规格
                $v['spec'] = empty($v['spec']) ? null : json_decode($v['spec'], true);

                // 获取商品基础信息
                $goods_base = GoodsService::GoodsSpecDetail(['id'=>$v['goods_id'], 'spec'=>$v['spec']]);
                $v['is_invalid'] = 0;
                if($goods_base['code'] == 0)
                {
                    $v['inventory'] = $goods_base['data']['spec_base']['inventory'];
                    $v['spec_base_id'] = $goods_base['data']['spec_base']['id'];
                    $v['spec_buy_min_number'] = $goods_base['data']['spec_base']['buy_min_number'];
                    $v['spec_buy_max_number'] = $goods_base['data']['spec_base']['buy_max_number'];
                    $v['spec_weight'] = $goods_base['data']['spec_base']['weight'];
                    $v['spec_coding'] = $goods_base['data']['spec_base']['coding'];
                    $v['spec_barcode'] = $goods_base['data']['spec_base']['barcode'];
                    $v['extends'] = $goods_base['data']['spec_base']['extends'];
                } else {
                    $v['is_invalid'] = 1;
                    $v['inventory'] = 0;
                    $v['spec_base_id'] = 0;
                    $v['spec_buy_min_number'] = 0;
                    $v['spec_buy_max_number'] = 0;
                    $v['spec_weight'] = 0;
                    $v['spec_coding'] = '';
                    $v['spec_barcode'] = '';
                    $v['extends'] = '';
                }

                // 基础信息
                $v['goods_url'] = MyUrl('index/goods/index', ['id'=>$v['goods_id']]);
                $v['images_old'] = $v['images'];
                $v['images'] = ResourcesService::AttachmentPathViewHandle($v['images']);
                $v['total_price'] = $v['stock']* ((float) $v['price']);
                $v['buy_max_number'] = ($v['buy_max_number'] <= 0) ? $v['inventory']: $v['buy_max_number'];

                // 商品分类
                $category = GoodsCategoryService::GoodsCategoryNames($v['goods_id']);
                $v['category'] = $category['data'];
                $v['category_text'] = empty($category['data']) ? '' : implode(',', $category['data']);

                // 品牌
                $v['brand_name'] = BrandService::BrandName($v['brand_id']);

                // 产地
                $v['place_origin_name'] = RegionService::RegionName($v['place_origin']);

                // 错误处理
                if(!isset($v['is_error']) || $v['is_error'] == 0)
                {
                    $v['is_error'] = 0;
                    $v['error_msg'] = '';
                }
                if($v['is_error'] == 0 && $v['is_invalid'] == 1)
                {
                    $v['is_error'] = 1;
                    $v['error_msg'] = MyLang('goods_already_invalid_title');
                }
                if($v['is_error'] == 0 && $v['inventory'] <= 0)
                {
                    $v['is_error'] = 1;
                    $v['error_msg'] = MyLang('goods_no_inventory_title');
                }
            }
        }

        return DataReturn(MyLang('operate_success'), 0, $data);
    }

    /**
     * 购物车添加
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-20
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CartSave($params = [])
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
                'key_name'          => 'stock',
                'error_msg'         => '购买数量有误',
            ],
            [
                'checked_type'      => 'min',
                'key_name'          => 'stock',
                'checked_data'      => 1,
                'error_msg'         => '购买数量有误',
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

        // 查询用户状态是否正常
        $ret = UserService::UserStatusCheck($params['user']['id']);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 获取商品
        $goods_id = intval($params['goods_id']);
        $goods = Db::name('Goods')->where(['id'=>$goods_id, 'is_shelves'=>1, 'is_delete_time'=>0])->find();
        if(empty($goods))
        {
            return DataReturn(MyLang('goods_no_exist_or_delete_error_tips'), -2);
        }

        // 规格处理
        $spec = BuyService::GoodsSpecificationsHandle($params);

        // 获取商品基础信息
        $goods_base = GoodsService::GoodsSpecDetail(['id'=>$goods_id, 'spec'=>$spec]);
        if($goods_base['code'] != 0)
        {
            return $goods_base;
        }

        // 获取商品规格图片
        if(!empty($spec))
        {
            $images = BuyService::BuyGoodsSpecImages($goods_id, $spec);
            if(!empty($images))
            {
                $goods['images'] = $images;
                $goods['images_old'] = ResourcesService::AttachmentPathViewHandle($images);
            }
        }

        // 数量
        $stock = ($goods['buy_max_number'] > 0 && $params['stock'] > $goods['buy_max_number']) ? $goods['buy_max_number'] : $params['stock'];

        // 库存
        if($stock > $goods['inventory'])
        {
            return DataReturn('库存不足', -1);
        }

        // 添加购物车
        $data = [
            'user_id'       => $params['user']['id'],
            'goods_id'      => $goods_id,
            'title'         => $goods['title'],
            'images'        => $goods['images'],
            'original_price'=> $goods_base['data']['spec_base']['original_price'],
            'price'         => $goods_base['data']['spec_base']['price'],
            'stock'         => $stock,
            'spec'          => empty($spec) ? '' : json_encode($spec),
        ];

        // 存在则更新
        $where = ['user_id'=>$data['user_id'], 'goods_id'=>$data['goods_id'], 'spec'=>$data['spec']];
        $temp = Db::name('PluginsSpeedplaceorderCart')->where($where)->find();
        if(empty($temp))
        {
            $data['add_time'] = time();
            if(Db::name('PluginsSpeedplaceorderCart')->insertGetId($data) > 0)
            {
                return DataReturn('加入成功', 0);
            }
        } else {
            $data['upd_time'] = time();
            $data['stock'] += $temp['stock'];
            if($data['stock'] > $goods['inventory'])
            {
                $data['stock'] = $goods['inventory'];
            }
            if($goods['buy_max_number'] > 0 && $data['stock'] > $goods['buy_max_number'])
            {
                $data['stock'] = $goods['buy_max_number'];
            }
            if(Db::name('PluginsSpeedplaceorderCart')->where($where)->update($data))
            {
                return DataReturn('加入成功', 0);
            }
        }
        
        return DataReturn('加入失败', -100);
    }

    /**
     * 购物车数量保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CartStock($params = [])
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
                'key_name'          => 'goods_id',
                'error_msg'         => MyLang('goods_id_error_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'stock',
                'error_msg'         => '购买数量有误',
            ],
            [
                'checked_type'      => 'min',
                'key_name'          => 'stock',
                'checked_data'      => 1,
                'error_msg'         => '购买数量有误',
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

        // 查询用户状态是否正常
        $ret = UserService::UserStatusCheck($params['user']['id']);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 获取数据
        $params['where'] = [
            ['sc.id', '=', intval($params['id'])],
            ['sc.goods_id', '=', intval($params['goods_id'])],
        ];
        $cart = self::CartList($params);
        if($cart['code'] != 0)
        {
            return $cart;
        }
        if(empty($cart['data']) || empty($cart['data'][0]))
        {
            return DataReturn('请先加入购物车', -1);
        }
        $cart_data = $cart['data'][0];
        // 是否存在错误
        if($cart_data['is_error'] == 1)
        {
            return DataReturn($cart_data['error_msg'], -1);
        }

        // 商品校验
        $cart_data['stock'] = intval($params['stock']);
        $check = BuyService::BuyGoodsCheck(['goods'=>[$cart_data]]);
        if($check['code'] != 0)
        {
            return $check;
        }

        // 更新数据
        $data = [
            'stock'     => $cart_data['stock'],
            'upd_time'  => time(),
        ];
        if(Db::name('PluginsSpeedplaceorderCart')->where(['id'=>$cart_data['id']])->update($data) !== false)
        {
            return DataReturn(MyLang('update_success'), 0);
        }
        return DataReturn(MyLang('update_fail'), -100);
    }

    /**
     * 购物车删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CartDelete($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'ids',
                'error_msg'         => MyLang('data_id_error_tips'),
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
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 查询用户状态是否正常
        $ret = UserService::UserStatusCheck($params['user']['id']);
        if($ret['code'] != 0)
        {
            return $ret;
        }

        // 删除
        $where = [
            'id'        => $params['ids'],
            'user_id'   => $params['user']['id']
        ];
        if(Db::name('PluginsSpeedplaceorderCart')->where($where)->delete())
        {
            return DataReturn(MyLang('delete_success'), 0);
        }
        return DataReturn(MyLang('delete_fail'), -100);
    }

    /**
     * 购物车拷贝
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-03-22
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CartCopy($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'ids',
                'error_msg'         => '购物车id有误',
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

        // 开启事务
        Db::startTrans();

        // 循环处理
        $ids = explode(',', $params['ids']);
        foreach($ids as $cart_id)
        {
            // 获取购物车数据
            $cart = Db::name('PluginsSpeedplaceorderCart')->where(['id'=>$cart_id, 'user_id'=>$params['user']['id']])->find();
            if(empty($cart))
            {
                Db::rollback();
                return DataReturn('购物车数据不存在['.$cart_id.']', -1);
            }

            // 同步数据到系统购物车
            $cart['user'] = $params['user'];
            $ret = GoodsCartService::GoodsCartSave($cart);
            if($ret['code'] != 0)
            {
                Db::rollback();
                return $ret;
            }

            // 删除数据
            if(!Db::name('PluginsSpeedplaceorderCart')->where(['id'=>$cart_id])->delete())
            {
                Db::rollback();
                return DataReturn('原删除失败', -1);
            }
        }
        // 提交事务
        Db::commit();
        return DataReturn('加入购物车成功', 0);
    }
}
?>