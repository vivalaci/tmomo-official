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
namespace app\plugins\multilingual\service;

use think\facade\Db;
use app\plugins\multilingual\service\BaseService;

/**
 * 多语言 - 翻译服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class TranslateService
{
    /**
     * 翻译
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-11-01
     * @desc    description
     * @param   [array]           $config [插件配置]
     * @param   [array]           $params [输入参数]
     */
    public static function Run($config, $params = [])
    {
        // 参数配置
        if(empty($config['appid']) || empty($config['appkey']))
        {
            return DataReturn(MyLang('plugins_config_error_tips'), -1);
        }

        // 开始处理
        $result = [
            'from'          => empty($params['from']) ? 'zh' : $params['from'],
            'to'            => empty($params['to']) ? '' : $params['to'],
            'trans_result'  => [],
        ];
        if(!empty($params['q']))
        {
            // 参数处理
            $qurest_arr = [];
            $result_arr = [];
            $q_arr = array_map(function($v){return htmlspecialchars_decode($v);}, explode("\n", $params['q']));

            // 缓存key
            $key_first = BaseService::$multilingual_data_cache_key.$result['from'].'_'.$result['to'].'_';

            // 是否数据库模式
            $is_db_storage = isset($config['is_db_storage']) && $config['is_db_storage'] == 1;
            if($is_db_storage)
            {
                // key处理
                $md5_key_arr = [];
                foreach($q_arr as $v)
                {
                    $md5_key_arr[] = md5($key_first.$v);
                }
                // 获取数据库数据
                $db_data = Db::name('PluginsMultilingualTrData')->where(['md5_key'=>array_unique($md5_key_arr)])->column('to_value', 'md5_key');
                if(empty($db_data))
                {
                    $qurest_arr = $q_arr;
                    $result_arr = array_map(function($v) {return ['src'=>$v, 'dst'=>$v];}, $q_arr);
                } else {
                    foreach($q_arr as $v)
                    {
                        // 是否存在
                        $key = md5($key_first.$v);
                        $temp_v = MyCache($key);
                        if(array_key_exists($key, $db_data))
                        {
                            $temp_v = $db_data[$key];
                        } else {
                            $qurest_arr[$key] = $v;
                            $temp_v = $v;
                        }
                        // 加入返回数据
                        $result_arr[md5($v)] = [
                            'src'   => $v,
                            'dst'   => $temp_v,
                        ];
                    }
                }
            } else {
                // 数据匹配
                foreach($q_arr as $v)
                {
                    // 是否存在
                    $key = $key_first.md5($v);
                    $temp_v = MyCache($key);
                    if(empty($temp_v))
                    {
                        $qurest_arr[$key] = $v;
                        $temp_v = $v;
                    }
                    // 加入返回数据
                    $result_arr[md5($v)] = [
                        'src'   => $v,
                        'dst'   => $temp_v,
                    ];
                }
            }
            $qurest_arr = array_filter(array_values($qurest_arr));

            // 需要翻译的字符为空则直接返回
            if(!empty($qurest_arr))
            {
                // 分批翻译
                foreach(array_chunk($qurest_arr, 300) as $qurest_v)
                {
                    // 调用翻译
                    $ret = self::TranslateDataHandle($config, $qurest_v, $result['from'], $result['to'], $key_first, $is_db_storage);
                    if($ret['code'] != 0)
                    {
                        return $ret;
                    }
                    // 匹配到返回的数组中去
                    foreach($result_arr as $k=>$a)
                    {
                        foreach($ret['data'] as $v)
                        {
                            if($a['src'] == $v['src'])
                            {
                                $result_arr[$k]['dst'] = $v['dst'];
                                break;
                            }
                        }
                    }
                }
            }

            // 数据赋值
            $result['trans_result'] = $result_arr;
        }
        return DataReturn('success', 0, $result);
    }

    /**
     * 批次翻译
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-03-24
     * @desc    description
     * @param   [array]          $config        [插件配置]
     * @param   [array]          $qurest_arr    [翻译数据]
     * @param   [string]         $form          [原始数据]
     * @param   [string]         $to            [翻译目标数据]
     * @param   [string]         $key_first     [缓存key]
     * @param   [boolean]        $is_db_storage [是否存储数据库]
     */
    public static function TranslateDataHandle($config, $qurest_arr, $form, $to, $key_first, $is_db_storage)
    {
        $data = [
            'appid'     => $config['appid'],
            'appkey'    => $config['appkey'],
            'salt'      => time(),
            'q'         => implode("\n", $qurest_arr),
            'from'      => $form,
            'to'        => $to,
        ];
        $data['sign'] = md5($data['appid'].$data['q'].$data['salt'].$data['appkey']);
        $ret = CurlPost('https://fanyi-api.baidu.com/api/trans/vip/translate', $data);
        if($ret['code'] != 0)
        {
            return $ret;
        }
        $res = empty($ret['data']) ? [] : json_decode($ret['data'], true);
        if(empty($res))
        {
            return DataReturn('翻译失败、无响应数据', -1);
        }
        if(isset($res['error_code']) && $res['error_code'] != 52000)
        {
            return DataReturn('翻译失败('.$res['error_code'].'-'.$res['error_msg'].')', -1);
        }
        if(!empty($res['trans_result']) && is_array($res['trans_result']))
        {
            // 存储缓存、是否数据库模式
            if($is_db_storage)
            {
                $insert_data = [];
                foreach($res['trans_result'] as $v)
                {
                    $insert_data[] = [
                        'md5_key'       => md5($key_first.$v['src']),
                        'from_type'     => $form,
                        'to_type'       => $to,
                        'from_value'    => $v['src'],
                        'to_value'      => $v['dst'],
                        'add_time'      => time(),
                    ];
                }
                Db::name('PluginsMultilingualTrData')->insertAll($insert_data);
            } else {
                foreach($res['trans_result'] as $v)
                {
                    $key = $key_first.md5($v['src']);
                    MyCache($key, $v['dst']);
                }
            }
            return DataReturn('success', 0, $res['trans_result']);
        }
        return DataReturn('翻译失败、无响应翻译数据', -1);
    }

    /**
     * 页面html翻译
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-18
     * @desc    description
     * @param   [array]          $config      [插件配置]
     * @param   [string]         $to_value    [当前语言]
     * @param   [string]         $data        [页面html结构数据]
     */
    public static function ViewTranslate($config, $to_value, &$data = [])
    {
        if($to_value != 'zh')
        {
            // 数据按照标签分隔为数组处理
            $flags = PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY;
            $regex = '/(<[a-z0-9=\-:." ^\/]+\/>)|(<[^\/]+>[^<\/]+<\/[a-z0-9]+>)|(<[a-z0-9=\-:." ^\/]+>)/';
            $parts = preg_split($regex, $data, -1, $flags);

            // 获取需要翻译的数据
            $result = [];
            foreach($parts as $v)
            {
                $result = array_merge($result, self::StayTranslatePregMatch($v, true));
            }
            if(!empty($result))
            {
                $translate = self::TranslateHandle($config, $result, $to_value);
                if(!empty($translate))
                {
                    $str = '';
                    foreach($parts as $k=>$v)
                    {
                        // 匹配中文
                        preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $v, $matches);
                        if(!empty($matches) && !empty($matches[0]))
                        {
                            // 数据排序处理
                            $res = [];
                            foreach($matches[0] as $tv)
                            {
                                $key = md5($tv);
                                $res[] =[
                                    'len'   => strlen($tv),
                                    'src'   => $tv,
                                    'dst'   => (!empty($translate[$key]) && isset($translate[$key]['dst'])) ? $translate[$key]['dst'] : $tv,
                                ];
                            }

                            // 排序按照数据最长的开始替换，避免长的包含短的影响最终的替换数据
                            foreach(array_chunk($res, 50) as $rv)
                            {
                                $temp_res = ArrayQuickSort($rv, 'len', 1);
                                $search = array_column($temp_res, 'src');
                                $replace = array_column($temp_res, 'dst');
                                $v = str_replace($search, $replace, $v);
                            }

                            // 属性数据处理
                            $ueditor_arr = ['data-origin-[A-Za-z0-9-_]{1,}', 'value', 'id', 'json'];
                            foreach($ueditor_arr as $tv)
                            {
                                // 双引号
                                preg_match_all('/\s?'.$tv.'\=\"[^".]*"/u', $v, $p1);
                                preg_match_all('/\s?'.$tv.'\=\"[^".]*"/u', $parts[$k], $p2);
                                if(!empty($p1[0]) && !empty($p2[0]))
                                {
                                    $v = str_replace($p1[0], $p2[0], $v);
                                }

                                // 单引号
                                preg_match_all('/\s?'.$tv.'\=\'[^".]*\'/u', $v, $p1);
                                preg_match_all('/\s?'.$tv.'\=\'[^".]*\'/u', $parts[$k], $p2);
                                if(!empty($p1[0]) && !empty($p2[0]))
                                {
                                    $v = str_replace($p1[0], $p2[0], $v);
                                }
                            }

                            // textarea编辑器处理
                            preg_match_all('/<textarea[^>.]*>(.*)/u', $v, $p1);
                            preg_match_all('/<textarea[^>.]*>(.*)/u', $parts[$k], $p2);
                            if(!empty($p1[1]) && !empty($p2[1]))
                            {
                                $v = str_replace($p1[1], $p2[1], $v);
                            }

                            // textarea处理
                            preg_match_all('/<textarea[^>.]*>([^<.]*)\</u', $v, $p1);
                            preg_match_all('/<textarea[^>.]*>([^<.]*)\</u', $parts[$k], $p2);
                            if(!empty($p1[1]) && !empty($p2[1]))
                            {
                                $v = str_replace($p1[1], $p2[1], $v);
                            }

                            $str .= $v;
                        } else {
                            $str .= $v;
                        }
                    }
                    $data = $str;
                }
            }
        }
    }

    /**
     * 数据翻译
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-15
     * @desc    description
     * @param   [array]          $config      [插件配置]
     * @param   [string]         $to_value    [当前语言]
     * @param   [array|string]   $params      [输入参数]
     */
    public static function DataTranslate($config, $to_value, &$params = [])
    {
        if($to_value != 'zh')
        {
            // 当前数组形式赋值
            if(!empty($params) && is_array($params))
            {
                $result = self::TranslateStayData($params);
                if(!empty($result))
                {
                    $assign = self::TranslateHandle($config, $result, $to_value);
                    if(!empty($assign))
                    {
                        self::TranslateAssignData($params, $assign);
                    }
                }
            }

            // data数组形式赋值
            if(!empty($params['data']) && is_array($params['data']))
            {
                $result = self::TranslateStayData($params['data']);
                if(!empty($result))
                {
                    $assign = self::TranslateHandle($config, $result, $to_value);
                    if(!empty($assign))
                    {
                        self::TranslateAssignData($params['data'], $assign);
                    }
                }
            }

            // 单个数据赋值
            if(!empty($params['value']))
            {
                $result = self::TranslateStayData($params['value']);
                if(!empty($result))
                {
                    $assign = self::TranslateHandle($config, $result, $to_value);
                    if(!empty($assign))
                    {
                        self::TranslateAssignData($params['value'], $assign);
                    }
                }
            }

            // 仅字符串
            if(!empty($params) && is_string($params))
            {
                $result = self::TranslateStayData($params);
                if(!empty($result))
                {
                    $assign = self::TranslateHandle($config, $result, $to_value);
                    if(!empty($assign))
                    {
                        self::TranslateAssignData($params, $assign);
                    }
                }
            }
        }
    }

    /**
     * 数据翻译
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-15
     * @desc    description
     * @param   [array]          $config    [插件配置]
     * @param   [array|string]   $data      [需要翻译的数据]
     * @param   [string]         $to        [目标语言]
     * @param   [string]         $from      [当前语言]
     */
    public static function TranslateHandle($config, $data, $to, $from = 'zh')
    {
        $result = [];
        $params = [
            'from'  => $from,
            'to'    => $to,
            'q'     => is_array($data) ? implode("\n", $data) : $data,
        ];
        $ret = self::Run($config, $params);
        if(!empty($ret['data']) && !empty($ret['data']['trans_result']))
        {
            $result = $ret['data']['trans_result'];
        }
        return $result;
    }


    /**
     * 待翻译的数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-15
     * @desc    description
     * @param   [array]          $data   [待处理的数据]
     * @param   [array]          $result [已匹配的数据]
     */
    public static function TranslateStayData($data, $result = [])
    {
        if(!empty($data))
        {
            if(is_array($data))
            {
                foreach($data as $v)
                {
                    if(!empty($v))
                    {
                        if(is_array($v))
                        {
                            $result = array_merge($result, self::TranslateStayData($v, $result));
                        } else {
                            $result = array_merge($result, self::StayTranslatePregMatch($v, true));
                        }
                    }
                }
            } else {
                $result = array_merge($result, self::StayTranslatePregMatch($data, true));
            }
        }
        return $result;
    }

    /**
     * 字符串中文匹配
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-15
     * @desc    description
     * @param   [string]          $value        [待匹配的字符串]
     * @param   [string]          $is_md5key    [是否MD5KEY处理]
     */
    public static function StayTranslatePregMatch($value, $is_md5key = false)
    {
        $result = [];
        if(!is_numeric($value) && !is_object($value))
        {
            preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $value, $matches);
            if(!empty($matches) && !empty($matches[0]))
            {
                if($is_md5key)
                {
                    foreach($matches[0] as $tv)
                    {
                        $result[md5($tv)] = $tv;
                    }
                } else {
                    $result = $matches[0];
                }
            }
        }
        return $result;
    }

    /**
     * 翻译数据赋值
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-08-15
     * @desc    description
     * @param   [array]          &$data     [待赋值的数据]
     * @param   [array]          $translate [已翻译的数据]
     */
    public static function TranslateAssignData(&$data, $translate)
    {
        if(!empty($data))
        {
            if(is_array($data))
            {
                foreach($data as &$v)
                {
                    if(!empty($v))
                    {
                        if(is_array($v))
                        {
                            self::TranslateAssignData($v, $translate);
                        } else {
                            $matches = self::StayTranslatePregMatch($v);
                            if(!empty($matches))
                            {
                                $replace = [];
                                foreach($matches as $tv)
                                {
                                    $key = md5($tv);
                                    $replace[] = (!empty($translate[$key]) && isset($translate[$key]['dst'])) ? $translate[$key]['dst'] : $tv;
                                }
                                $v = str_replace($matches, $replace, $v);
                            }
                        }
                    }
                }
            } else {
                $matches = self::StayTranslatePregMatch($data);
                if(!empty($matches))
                {
                    $replace = [];
                    foreach($matches as $tv)
                    {
                        $key = md5($tv);
                        $replace[] = (!empty($translate[$key]) && isset($translate[$key]['dst'])) ? $translate[$key]['dst'] : $tv;
                    }
                    $data = str_replace($matches, $replace, $data);
                }
            }
        }
    }
}
?>