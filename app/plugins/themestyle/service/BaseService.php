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
namespace app\plugins\themestyle\service;

use app\service\PluginsService;

/**
 * 默认主题样式 - 基础服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-09-04
 * @desc    description
 */
class BaseService
{
    // 基础私有字段
    public static $plugins_config_private_field = [];

    // 基础数据附件字段
    public static $plugins_config_attachment_field = [];

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
        return PluginsService::PluginsDataSave(['plugins'=>'themestyle', 'data'=>$params], self::$plugins_config_attachment_field);
    }
    
    /**
     * 基础配置信息
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-12-24
     * @desc    description
     * @param   [boolean]          $is_cache    [是否缓存中读取]
     * @param   [boolean]          $is_private  [是否读取隐私字段]
     */
    public static function BaseConfig($is_cache = true, $is_private = true)
    {
        return PluginsService::PluginsData('themestyle', self::$plugins_config_attachment_field, $is_cache);
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
                'name'      => 'web配置',
                'control'   => 'config',
                'action'    => 'index',
            ],
        ];
    }

    /**
     * 静态数据
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2023-11-06
     * @desc    description
     */
    public static function ConstData($key)
    {
        $data = [
            // 手机端配色列表
            'app_theme_style_list'    => [
                'red'     => [
                    'value'    => 'red',
                    'color'    => '#ff0036',
                    'name'     => '红色',
                    'checked'  => true,
                ],
                'yellow'  => [
                    'value'  => 'yellow',
                    'color'  => '#f6c133',
                    'name'   => '黄色',
                ],
                'black'   => [
                    'value'  => 'black',
                    'color'  => '#333333',
                    'name'   => '黑色',
                ],
                'green'   => [
                    'value'  => 'green',
                    'color'  => '#20a53a',
                    'name'   => '绿色',
                ],
                'orange'  => [
                    'value'  => 'orange',
                    'color'  => '#fe6f04',
                    'name'   => '橙色',
                ],
                'blue'    => [
                    'value'  => 'blue',
                    'color'  => '#1677ff',
                    'name'   => '蓝色',
                ],
                'brown'   => [
                    'value'  => 'brown',
                    'color'  => '#8B4513',
                    'name'   => '棕色',
                ],
                'purple'  => [
                    'value'  => 'purple',
                    'color'  => '#623cec',
                    'name'   => '紫色',
                ],
            ],
        ];
        return array_key_exists($key, $data) ? $data[$key] : null;
    }

    /**
     * 主要配色列表
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2022-12-06
     * @desc    description
     */
    public static function MainColorList()
    {
        return [
            [
                'name'  => '按钮',
                'type'  => 'button',
                'items' => [
                    [
                        'name'  => '默认',
                        'type'  => 'default',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['default', 'default_hover', 'default_focus', 'default_active', 'default_disabled'],
                            ],
                            [
                                'name'  => '边线',
                                'items' => ['default_border', 'default_hover_border', 'default_focus_border', 'default_active_border', 'default_disabled_border'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['default_text', 'default_hover_text', 'default_focus_text', 'default_active_text', 'default_disabled_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '主色',
                        'type'  => 'primary',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['primary', 'primary_hover', 'primary_focus', 'primary_active', 'primary_disabled'],
                            ],
                            [
                                'name'  => '边线',
                                'items' => ['primary_border', 'primary_hover_border', 'primary_focus_border', 'primary_active_border', 'primary_disabled_border'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['primary_text', 'primary_hover_text', 'primary_focus_text', 'primary_active_text', 'primary_disabled_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '次色',
                        'type'  => 'secondary',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['secondary', 'secondary_hover', 'secondary_focus', 'secondary_active', 'secondary_disabled'],
                            ],
                            [
                                'name'  => '边线',
                                'items' => ['secondary_border', 'secondary_hover_border', 'secondary_focus_border', 'secondary_active_border', 'secondary_disabled_border'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['secondary_text', 'secondary_hover_text', 'secondary_focus_text', 'secondary_active_text', 'secondary_disabled_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '成功色',
                        'type'  => 'success',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['success', 'success_hover', 'success_focus', 'success_active', 'success_disabled'],
                            ],
                            [
                                'name'  => '边线',
                                'items' => ['success_border', 'success_hover_border', 'success_focus_border', 'success_active_border', 'success_disabled_border'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['success_text', 'success_hover_text', 'success_focus_text', 'success_active_text', 'success_disabled_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '警告色',
                        'type'  => 'warning',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['warning', 'warning_hover', 'warning_focus', 'warning_active', 'warning_disabled'],
                            ],
                            [
                                'name'  => '边线',
                                'items' => ['warning_border', 'warning_hover_border', 'warning_focus_border', 'warning_active_border', 'warning_disabled_border'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['warning_text', 'warning_hover_text', 'warning_focus_text', 'warning_active_text', 'warning_disabled_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '危险色',
                        'type'  => 'danger',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['danger', 'danger_hover', 'danger_focus', 'danger_active', 'danger_disabled'],
                            ],
                            [
                                'name'  => '边线',
                                'items' => ['danger_border', 'danger_hover_border', 'danger_focus_border', 'danger_active_border', 'danger_disabled_border'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['danger_text', 'danger_hover_text', 'danger_focus_text', 'danger_active_text', 'danger_disabled_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '辅助色',
                        'type'  => 'assist',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['assist', 'assist_hover', 'assist_focus', 'assist_active', 'assist_disabled'],
                            ],
                            [
                                'name'  => '边线',
                                'items' => ['assist_border', 'assist_hover_border', 'assist_focus_border', 'assist_active_border', 'assist_disabled_border'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['assist_text', 'assist_hover_text', 'assist_focus_text', 'assist_active_text', 'assist_disabled_text'],
                            ]
                        ]
                    ],
                ]
            ],
            [
                'name'  => '小徽章',
                'type'  => 'badge',
                'items' => [
                    [
                        'name'  => '默认',
                        'type'  => 'default',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['default', 'default_hover'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['default_text', 'default_hover_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '主色',
                        'type'  => 'primary',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['primary', 'primary_hover'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['primary_text', 'primary_hover_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '次色',
                        'type'  => 'secondary',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['secondary', 'secondary_hover'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['secondary_text', 'secondary_hover_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '成功色',
                        'type'  => 'success',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['success', 'success_hover'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['success_text', 'success_hover_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '警告色',
                        'type'  => 'warning',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['warning', 'warning_hover'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['warning_text', 'warning_hover_text'],
                            ]
                        ]
                    ],
                    [
                        'name'  => '危险色',
                        'type'  => 'danger',
                        'items' => [
                            [
                                'name'  => '背景色',
                                'items' => ['danger', 'danger_hover'],
                            ],
                            [
                                'name'  => '文本',
                                'items' => ['danger_text', 'danger_hover_text'],
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }
}
?>