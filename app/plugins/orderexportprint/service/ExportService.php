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
namespace app\plugins\orderexportprint\service;

use think\facade\Db;
use app\service\ResourcesService;

/**
 * 导出服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2019-09-24
 * @desc    description
 */
class ExportService
{
    /**
     * 订单数据导出数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]          $data      [订单数据]
     * @param   [array]          $config    [插件配置]
     */
    public static function OrderExportData($data, $config)
    {
        $result = [];
         if(!empty($data))
         {
            // 是否订单导出仅第一条有订单主数据
            $is_export_is_one_main = (isset($config['is_export_is_one_main']) && $config['is_export_is_one_main'] == 1) ? 1 : 0;

            // 是否导出图片
            $is_export_goods_images  = (isset($config['is_export_goods_images']) && $config['is_export_goods_images'] == 1) ? 1 : 0;

            // 导出订单商品表单插件数据
            $is_export_ordergoodsform = (isset($config['is_export_ordergoodsform']) && $config['is_export_ordergoodsform'] == 1) ? 1 : 0;
            $form_data_group = [];
            if($is_export_ordergoodsform == 1)
            {
                $temp = Db::name('Plugins')->where(['plugins'=>'ordergoodsform', 'is_enable'=>1])->find();
                if(!empty($temp))
                {
                    $form_data = Db::name('PluginsOrdergoodsformOrderData')->where(['order_id'=>array_column($data, 'id')])->select()->toArray();                
                    if(!empty($form_data))
                    {
                        foreach($form_data as $f)
                        {
                            if(!array_key_exists($f['order_id'], $form_data_group))
                            {
                                $form_data_group[$f['order_id']] = [];
                            }
                            $form_data_group[$f['order_id']][$f['goods_id']][] = $f['title'].':'.$f['content'];
                        }
                    }
                }
            }

            // 临时空字段
            $temp_item = [];

            // 循环处理
            foreach($data as $v)
            {
                // 订单地址
                $address_data = isset($v['address_data']) ? $v['address_data'] : null;

                // 数据组装
                $order = [
                    // 基础信息
                    'order_no'              => $v['order_no'],
                    'warehouse_name'        => $v['warehouse_name'],
                    'order_model_name'      => $v['order_model_name'],
                    'client_type_name'      => $v['client_type_name'],
                    'status_name'           => $v['status_name'],

                    // 用户信息
                    'user_id'               => $v['user_id'],
                    'user_username'         => empty($v['user']) ? '' : $v['user']['user_name_view'],
                    'user_mobile'           => empty($v['user']) ? '' : $v['user']['mobile'],
                    'user_email'            => empty($v['user']) ? '' : $v['user']['email'],

                    // 支付信息
                    'pay_status_name'       => $v['pay_status_name'],
                    'payment_name'          => $v['payment_name'],

                    // 价格
                    'preferential_price'    => $v['preferential_price'],
                    'price'                 => $v['price'],
                    'total_price'           => $v['total_price'],
                    'pay_price'             => $v['pay_price'],
                    'refund_price'          => $v['refund_price'],
                    'returned_quantity'     => $v['returned_quantity'],
                    
                    // 其它数据
                    'user_note'             => $v['user_note'],
                    'items_count'           => $v['items_count'],
                    'buy_number_count'      => $v['buy_number_count'],
                    'describe'              => $v['describe'],

                    // 快递信息
                    'express_name'          => empty($v['express_data']) ? '' : implode(',', array_column($v['express_data'], 'express_name')),
                    'express_number'        => empty($v['express_data']) ? '' : implode(',', array_column($v['express_data'], 'express_number')),

                    // 扩展数据
                    'extension_data_text'   => self::OrderExtensionDataHandle($v['extension_data']),

                    // 用户地址
                    'receive_name'          => isset($address_data['name']) ? $address_data['name'] : '',
                    'receive_tel'           => isset($address_data['tel']) ? $address_data['tel'] : '',
                    'receive_province_name' => isset($address_data['province_name']) ? $address_data['province_name'] : '',
                    'receive_city_name'     => isset($address_data['city_name']) ? $address_data['city_name'] : '',
                    'receive_county_name'   => isset($address_data['county_name']) ? $address_data['county_name'] : '',
                    'receive_address'       => isset($address_data['address']) ? $address_data['address'] : '',
                    'receive_lng'           => isset($address_data['lng']) ? $address_data['lng'] : '',
                    'receive_lat'           => isset($address_data['lat']) ? $address_data['lat'] : '',
                    'receive_idcard_name'   => isset($address_data['idcard_name']) ? $address_data['idcard_name'] : '',
                    'receive_idcard_number' => isset($address_data['idcard_number']) ? $address_data['idcard_number'] : '',
                    'receive_idcard_front'  => isset($address_data['idcard_front']) ? $address_data['idcard_front'] : '',
                    'receive_idcard_back'   => isset($address_data['idcard_back']) ? $address_data['idcard_back'] : '',
                    

                    // 汇率信息
                    'currency_name'         => $v['currency_data']['currency_name'],
                    'currency_code'         => $v['currency_data']['currency_code'],
                    'currency_symbol'       => $v['currency_data']['currency_symbol'],
                    'currency_rate'         => $v['currency_data']['currency_rate'],

                    // 时间
                    'confirm_time'          => $v['confirm_time'],
                    'pay_time'              => $v['pay_time'],
                    'delivery_time'         => $v['delivery_time'],
                    'collect_time'          => $v['collect_time'],
                    'cancel_time'           => $v['cancel_time'],
                    'close_time'            => $v['close_time'],
                    'add_time'              => $v['add_time'],
                    'upd_time'              => $v['upd_time'],
                    'user_is_comments_time' => $v['user_is_comments_time'],
                ];

                // 商品详情
                if(!empty($v['items']))
                {
                    // 空字段处理
                    if($is_export_is_one_main == 1)
                    {
                        if(empty($temp_item))
                        {
                            $keys = array_keys($order);
                            for($i=0; $i<count($keys); $i++)
                            {
                               $temp_item[$keys[$i]] = ''; 
                            }
                        }
                    }

                    $item = $order;
                    foreach($v['items'] as $ks=>$vs)
                    {
                        // 仅第一条有订单数据
                        if($is_export_is_one_main == 1)
                        {
                            if($ks > 0)
                            {
                                $item = $temp_item;
                            }
                        }

                        // 商品数据
                        $item['goods_id']               = $vs['goods_id'];
                        $item['goods_title']            = $vs['title'];
                        $item['goods_url']              = $vs['goods_url'];
                        $item['goods_original_price']   = $vs['original_price'];
                        $item['goods_price']            = $vs['price'];
                        $item['goods_total_price']      = $vs['total_price'];
                        $item['goods_spec_text']        = $vs['spec_text'];
                        $item['goods_model']            = $vs['model'];
                        $item['goods_spec_weight']      = $vs['spec_weight'];
                        $item['goods_spec_volume']      = $vs['spec_volume'];
                        $item['goods_spec_coding']      = $vs['spec_coding'];
                        $item['goods_spec_barcode']     = $vs['spec_barcode'];
                        $item['goods_buy_number']       = $vs['buy_number'];
                        $item['goods_returned_quantity']= $vs['returned_quantity'];
                        $item['goods_refund_price']     = $vs['refund_price'];

                        // 是否开启图片导出
                        if($is_export_goods_images == 1)
                        {
                            $item['goods_images'] = self::ImagesDownload($vs['images']);
                        }

                        // 订单商品表单数据
                        if(!empty($form_data_group) && is_array($form_data_group) && array_key_exists($v['id'], $form_data_group) && array_key_exists($item['goods_id'], $form_data_group[$v['id']]))
                        {
                            $item['goods_ordergoodsform'] = empty($form_data_group[$v['id']][$item['goods_id']]) ? '' : implode(" ; ", $form_data_group[$v['id']][$item['goods_id']]);
                        }

                        // 数据组合
                        $result[] = $item;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 扩展数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [string]          $data [json 数据]
     */
    public static function OrderExtensionDataHandle($data)
    {
        $string = '';
        if(!empty($data))
        {
            if(!is_array($data))
            {
                $data = json_decode($data, true);
            }
            foreach($data as $k=>$v)
            {
                if(isset($v['name']) && isset($v['tips']))
                {
                    $string .= $v['name'].'：'.$v['tips'];
                    if($k > 0)
                    {
                        $string .= "\n";
                    }
                }
            }
        }
        return $string;
    }

    /**
     * 图片下载
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-01-31
     * @desc    description
     * @param   [string]          $value [图片地址]
     */
    public static function ImagesDownload($value)
    {
        $path = ResourcesService::AttachmentPathHandle($value);
        if(substr($path, 0, 4) == 'http')
        {
            $images_obj = \base\Images::Instance(['is_new_name'=>false]);
            $temp_path = ROOT.'runtime'.DS.'data'.DS.'plugins_orderexportprint'.DS;
            $filename = $images_obj->DownloadImageSave($value, $temp_path);
            if(empty($filename))
            {
                return DataReturn('商品主图远程下载失败', -11);
            }
            $value = $temp_path.$filename;
        } else {
            $value = ROOT.'public'.$path;
        }
        return $value;
    }

    /**
     * 导出 title
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-02-13
     * @desc    description
     * @param   [array]          $config    [插件配置]
     */
    public static function OrderExcelTitle($config)
    {
        // 基础信息
        $base = [
            'order_no'      =>  [
                    'name' => '订单编号',
                    'type' => 'string',
                ],
            'warehouse_name'      =>  [
                    'name' => '出货仓库',
                    'type' => 'string',
                ],
            'order_model_name'      =>  [
                    'name' => '订单模式',
                    'type' => 'string',
                ],
            'client_type_name'      =>  [
                    'name' => '客户端类型',
                    'type' => 'string',
                ],
            'status_name'      =>  [
                    'name' => '订单状态',
                    'type' => 'string',
                ]
        ];

        // 用户信息
        $user = [
            'user_id'           =>  [
                    'name' => '用户ID',
                    'type' => 'string',
                ],
            'user_username'     =>  [
                    'name' => '用户名',
                    'type' => 'string',
                ],
            'user_mobile'       =>  [
                    'name' => '用户手机',
                    'type' => 'string',
                ],
            'user_email'        =>  [
                    'name' => '用户邮箱',
                    'type' => 'string',
                ],
        ];

        // 支付信息
        $payment = [
            'pay_status_name'   =>  [
                    'name' => '支付状态',
                    'type' => 'string',
                ],
            'payment_name'      =>  [
                    'name' => '支付方式',
                    'type' => 'string',
                ],
        ];

        // 金额
        $price = [
            'preferential_price'       =>  [
                    'name' => '优惠金额',
                    'type' => 'string',
                ],
            'price'       =>  [
                    'name' => '订单单价',
                    'type' => 'string',
                ],
            'total_price'       =>  [
                    'name' => '订单总价(订单最终价格)',
                    'type' => 'string',
                ],
            'pay_price'       =>  [
                    'name' => '已支付金额',
                    'type' => 'string',
                ],
            'refund_price'       =>  [
                    'name' => '已退款金额',
                    'type' => 'string',
                ],
            'returned_quantity'       =>  [
                    'name' => '已退货数量',
                    'type' => 'string',
                ],
        ];

        // 其它数据
        $other = [
            'user_note'      =>  [
                    'name' => '用户留言',
                    'type' => 'string',
                ],
            'items_count'       =>  [
                    'name' => '商品总数',
                    'type' => 'int',
                ],
            'buy_number_count'  =>  [
                    'name' => '购买商品总数量',
                    'type' => 'int',
                ],
        ];

        // 快递信息
        $express = [
            'express_name'      =>  [
                    'name' => '快递公司',
                    'type' => 'string',
                ],
            'express_number'      =>  [
                    'name' => '快递单号',
                    'type' => 'string',
                ],
        ];

        // 扩展数据
        $ext = [
            'extension_data_text'   =>  [
                    'name' => '扩展数据',
                    'type' => 'string',
                ],
        ];

        // 商品信息
        $goods = [];
        $goods['goods_id'] = [
                'name' => '商品ID',
                'type' => 'string',
            ];
        $goods['goods_url'] = [
                'name' => '商品地址',
                'type' => 'string',
            ];
        $goods['goods_title'] = [
                'name' => '商品名称',
                'type' => 'string',
            ];

        // 是否开启图片导出
        if(isset($config['is_export_goods_images']) && $config['is_export_goods_images'] == 1)
        {
            $goods['goods_images'] = [
                    'name' => '商品图片',
                    'type' => 'images',
                    'height'    => 60,
                    'width'     => 80,
                ];
        }

        $goods['goods_original_price'] = [
                'name' => '商品原价',
                'type' => 'string',
            ];
        $goods['goods_price'] = [
                'name' => '商品销售价',
                'type' => 'string',
            ];
        $goods['goods_total_price'] = [
                'name' => '总价',
                'type' => 'string',
            ];
        $goods['goods_spec_text'] = [
                'name' => '购买规格',
                'type' => 'string',
            ];
        $goods['goods_model'] = [
                'name' => '商品型号',
                'type' => 'string',
            ];
        $goods['goods_spec_weight'] = [
                'name' => '商品重量',
                'type' => 'string',
            ];
        $goods['goods_spec_volume'] = [
                'name' => '商品体积',
                'type' => 'string',
            ];
        $goods['goods_spec_coding'] = [
                'name' => '商品编码',
                'type' => 'string',
            ];
        $goods['goods_spec_barcode'] = [
                'name' => '商品条形码',
                'type' => 'string',
            ];
        $goods['goods_buy_number'] = [
                'name' => '购买数量',
                'type' => 'string',
            ];
        $goods['goods_returned_quantity'] = [
                'name' => '退货数量',
                'type' => 'string',
            ];
        $goods['goods_refund_price'] = [
                'name' => '退款金额',
                'type' => 'string',
            ];
        $goods['describe'] = [
                'name' => '描述',
                'type' => 'string',
            ];

        // 是否开启图片导出
        if(isset($config['is_export_ordergoodsform']) && $config['is_export_ordergoodsform'] == 1)
        {
            $goods['goods_ordergoodsform'] = [
                    'name' => '订单商品表单',
                    'type' => 'string',
                ];
        }

        // 收件人信息
        $address = [
            'receive_name'      =>  [
                    'name' => '收件人姓名',
                    'type' => 'string',
                ],
            'receive_tel'   =>  [
                    'name' => '收件人电话',
                    'type' => 'string',
                ],
            'receive_province_name'=>   [
                    'name' => '收件人所在省',
                    'type' => 'string',
                ],
            'receive_city_name'        =>  [
                    'name' => '收件人所在市',
                    'type' => 'string',
                ],
            'receive_county_name'         =>  [
                    'name' => '收件人所在区县',
                    'type' => 'string',
                ],
            'receive_address'      =>  [
                    'name' => '收件人详细地址',
                    'type' => 'string',
                ],
            'receive_lng'      =>  [
                    'name' => '收件人经度',
                    'type' => 'string',
                ],
            'receive_lat'      =>  [
                    'name' => '收件人维度',
                    'type' => 'string',
                ],
            'receive_idcard_name'      =>  [
                    'name' => '收件人身份证姓名',
                    'type' => 'string',
                ],
            'receive_idcard_number'      =>  [
                    'name' => '收件人身份证号码',
                    'type' => 'string',
                ],
            'receive_idcard_front'      =>  [
                    'name' => '收件人身份证正面地址',
                    'type' => 'string',
                ],
            'receive_idcard_back'      =>  [
                    'name' => '收件人身份证背面地址',
                    'type' => 'string',
                ],
        ];

        // 汇率信息
        $currency = [
            'currency_name'      =>  [
                    'name' => '货币名称',
                    'type' => 'string',
                ],
            'currency_code'      =>  [
                    'name' => '货币代码',
                    'type' => 'string',
                ],
            'currency_symbol'      =>  [
                    'name' => '货币符号',
                    'type' => 'string',
                ],
            'currency_rate'      =>  [
                    'name' => '货币汇率',
                    'type' => 'string',
                ],
        ];

        // 时间
        $time = [
            'confirm_time'      =>  [
                    'name' => '确认时间',
                    'type' => 'string',
                ],
            'pay_time'      =>  [
                    'name' => '支付时间',
                    'type' => 'string',
                ],
            'delivery_time'      =>  [
                    'name' => '发货时间',
                    'type' => 'string',
                ],
            'collect_time'      =>  [
                    'name' => '完成时间',
                    'type' => 'string',
                ],
            'cancel_time'      =>  [
                    'name' => '取消时间',
                    'type' => 'string',
                ],
            'close_time'      =>  [
                    'name' => '关闭时间',
                    'type' => 'string',
                ],
            'add_time'      =>  [
                    'name' => '创建时间',
                    'type' => 'string',
                ],
            'upd_time'      =>  [
                    'name' => '更新时间',
                    'type' => 'string',
                ],
            'user_is_comments_time'      =>  [
                    'name' => '评论时间',
                    'type' => 'string',
                ],
        ];

        return $base + $user + $payment + $price + $other + $express + $ext + $goods + $address + $currency + $time;
    }
}
?>