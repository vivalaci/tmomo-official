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
namespace app\plugins\membershiplevelvip\api;

use app\service\ResourcesService;
use app\plugins\membershiplevelvip\api\Common;
use app\plugins\membershiplevelvip\service\BaseService;
use app\plugins\membershiplevelvip\service\IntroduceService;


/**
 * 会员等级增强版插件
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function __construct($params = [])
    {
        // 调用父类前置方法
        parent::__construct($params);
    }

    /**
     * 首页
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-10-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public function Index($params = [])
    {
        // 富文本处理
        if(!empty($this->plugins_config['banner_bottom_content']))
        {
            $this->plugins_config['banner_bottom_content'] = ResourcesService::ApMiniRichTextContentHandle($this->plugins_config['banner_bottom_content']);
        }

        // 默认图片数据
        $default_images_data = BaseService::DefaultImagesData($this->plugins_config);

        // 介绍列表
        $introduce = IntroduceService::DataList();

        // 返回数据
        $result = [
            'base'                 => $this->plugins_config,
            'default_images_data'  => $default_images_data,
            'introduce_data'       => $introduce['data'],
        ];
        return DataReturn(MyLang('handle_success'), 0, $result);
    }
}
?>