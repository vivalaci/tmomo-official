ShopXO 是国内领先的企业级免费开源电商系统，秉持 "求实进取、创新专注" 的理念，依托自主研发技术，深度聚焦企业信息化、数字化与电商一体化解决方案。

遵循MIT开源协议发布，无需授权、可商用、可二次开发、满足99%的电商运营需求。

支持PC+手机自适应，独立H5、小程序（支付宝、微信、百度、头条&抖音、QQ、快手），APP（IOS、Android）。

功能架构上，ShopXO 创新性集成多仓库管理、多商户入驻、多门店、进销存运营体系，结合组件化插件实现即插即用。此外，系统还搭载可视化 DIY 拖拽装修功能，助力企业快速搭建个性化、差异化的电商平台，为商业增长提供坚实技术支撑。



后端采用PHP ThinkPHP v8.0框架开发

查看教程 https://doc.thinkphp.cn/v8_0/preface.html



ThinkPHP框架web端MVC模板

查看教程 https://doc.thinkphp.cn/@think-template/default.html



web前端采用AmazeUI HTML5框架开发

查看教程 https://amazeui.shopxo.net/



手机端采用uniapp框架开发（支持多端小程序、APP、H5）

查看教程 https://uniapp.dcloud.net.cn/



DIY装修采用VUE Element v3.0框架开发

查看教程 https://element-plus.org/

----------------------------------------------------------------------------------------

开发模式
修改配置文件

文件位置 config/shopxo.php   其中  is_develop 项的值由  false  改为  true

开启后可在商城后台应用管理里面直接创建插件、页面出现相应的钩子提示名称

QQ20250420-230538.png



页面钩子

开启开发者模式后，页面有钩子的地方会出现黑点、鼠标放在黑点上会显示钩子名称

调试模式
v2.2.0及以上 修改配置文件

系统根目录与 app 目录同级的 example.env 文件重命名为 .env ，其中的 APP_DEBUG 等于 true 即可

如果 example.env 文件不存在，则可以自行创建 .env 文件，里面的内容写  APP_DEBUG=true 保存即可

QQ20250420-230228.png



v2.1.0及以下 修改配置文件

config/app.php 文件中 app_debug的值【 true 开启，false 关闭】

QQ20250420-225638.png



查看错误

1. 页面错误、直接再次访问即可看见错误信息。

2. 异步请求、鼠标右击（检查|审查元素）、重新触发错误请求、可以参考下面是方式查看错误



操作流程

1.打开chrome浏览器

2.在键盘上按F12快捷键

3.network 下勾选 XHR

4.页面执行操作,name下找到请求链接、点击一下,右侧的headers、preview、response（response信息复制出来，看看错误）

--------------------------------------------------------------------------------

数据库表说明
sxo_admin                               管理员
sxo_app_center_nav                      手机 - 用户中心导航
sxo_app_home_nav                        手机 - 首页导航
sxo_app_tabbar                          手机底部菜单
sxo_article                             文章
sxo_article_category                    文章分类
sxo_attachment                          附件
sxo_attachment_category                 附件分类
sxo_brand                               品牌
sxo_brand_category                      品牌分类
sxo_brand_category_join                 品牌分类关联
sxo_cart                                购物车
sxo_config                              基本配置参数
sxo_custom_view                         自定义页面
sxo_design                              页面设计
sxo_diy                                 DIY装修
sxo_email_log                           邮件日志
sxo_error_log                           错误日志
sxo_express                             快递公司
sxo_form_table_user_fields              动态表格用户自定义字段
sxo_goods                               商品
sxo_goods_browse                        用户商品浏览
sxo_goods_category                      商品分类
sxo_goods_category_join                 商品分类关联
sxo_goods_comments                      商品评论
sxo_goods_content_app                   商品手机详情
sxo_goods_favor                         用户商品收藏
sxo_goods_give_integral_log             商品积分赠送日志
sxo_goods_params                        商品参数
sxo_goods_params_template               商品参数模板
sxo_goods_params_template_config        商品参数模板值
sxo_goods_photo                         商品相册图片
sxo_goods_spec_base                     商品规格基础
sxo_goods_spec_template                 商品规格模板
sxo_goods_spec_type                     商品规格类型
sxo_goods_spec_value                    商品规格值
sxo_layout                              布局配置
sxo_link                                友情链接
sxo_message                             消息
sxo_navigation                          导航
sxo_order                               订单
sxo_order_address                       订单地址
sxo_order_aftersale                     订单售后
sxo_order_aftersale_status_history      订单售后状态历史纪录
sxo_order_currency                      订单货币
sxo_order_detail                        订单详情
sxo_order_express                       订单快递
sxo_order_extraction_code               订单自提取货码关联
sxo_order_fictitious_value              订单虚拟销售数据关联
sxo_order_goods_inventory_log           订单商品库存变更日志
sxo_order_service                       订单服务
sxo_order_status_history                订单状态历史纪录
sxo_payment                             支付方式
sxo_pay_log                             支付日志
sxo_pay_log_value                       支付日志关联业务数据
sxo_pay_request_log                     支付请求日志
sxo_plugins                             应用
sxo_plugins_category                    应用分类
sxo_plugins_data_config                 插件数据配置
sxo_power                               权限
sxo_quick_nav                           快捷导航
sxo_refund_log                          退款日志
sxo_region                              地区
sxo_role                                角色组
sxo_role_plugins                        角色与权限插件
sxo_role_power                          角色与权限管理
sxo_screening_price                     筛选价格
sxo_search_history                      搜索日志
sxo_shortcut_menu                       常用功能菜单
sxo_slide                               轮播图片
sxo_sms_log                             短信日志
sxo_theme_data                          主题数据
sxo_user                                用户
sxo_user_address                        用户地址
sxo_user_integral_log                   用户积分日志
sxo_user_platform                       用户平台
sxo_warehouse                           仓库
sxo_warehouse_goods                     仓库商品
sxo_warehouse_goods_spec                仓库商品规格


---------------------------------------------------------------------------

DIY装修自定义组件配置
# 动态数据
    type                业务类型（如 商品goods, 文章article, 品牌brand）
    config              配置数据
    const               静态数据（筛选，搜索的数据都放这里）
        key             数据const_key作为数据索引
            type        数据类型（空或无则直接使用data, common diy公共初始化中读取）
            data        具体数据
        goods_category: {
            type: common
            data: {}
        }
# config  配置数据
# 显示设置
show_type               多个已半角逗号分割或者数组（未设置则默认全部）
    null                强等判断 === null则表示不需要这个选择（默认就是 vertical 纵向）
    vertical            纵向
    vertical-scroll     纵向滚动
    horizontal          横向
# 每屏显示数量
show_number             多个已半角逗号分割或者数组（未设置则默认全部）
    null    强等判断 === null则表示不需要这个选择（默认就是1 单行全屏）
    1       单行
    2       两行
    3       三行
    4       四行
# 数据读取方式
data_type
    0             指定数据
    1             筛选数据
# data_type只有一个的时候，是否显示data_type数据
is_type_show      0 （0不显示，1显示，字段不设置默认为1显示）
data_list
data_auot_list 
    # 指定类型配置
    appoint_config      指定数据结构
        data_url        数据接口
        show_data       显示数据结构
            data_key        数据key（默认 id ）
            data_name       数据名称（默认 name ）
            data_logo       数据图片（默认 logo ，空则不显示图片）
        header          头信息定义（二维数组）
            field       字段名称
            name        显示名称
            type        数据类型（文本text, 图片images）
            width       宽度（默认没有则不限制宽度，限制了则超出截断）
        data_key        数据key（默认 id ）
        page_size       分页展示数量
        filter_form     筛选条件（二维数组）
            const_key   静态数据key（从const中读取）
            type        类型（下拉select, input输入, checkbox多选, radio单选）
            default         默认值（checkbox和select多个值使用半角逗号分割或者数组）
                select      下拉
                    is_multiple     是否支持多选（0否，1是）
                    placeholder     默认占位名称
                    data_level      数据层级（1～3，默认 1 或者自动处理不需要这个字段定义）
                input       输入框
                    placeholder     默认占位名称
                    type            文本类型（默认文本text, 数字number）
                radio       单选
                checkbox    多选
            title       筛选名称（如 分类 默认空不展示）
            form_name   表单名称（如 category_id）
            data        条件数据（二维数组）
            data_key    数据key（默认 id 表单和选择最终使用的数据）
            data_name   数据名称（默认 name ）
    # 筛选类型配置
    filter_config       筛选数据结构
        data_url        数据接口
        filter          参考 appoint_structure 下的 filter
const default_type_data = {
    show_type: ['vertical', 'vertical-scroll', 'horizontal'], // 显示类型
    show_number: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],   // 显示数量
    data_type: ['appoint', 'filter'], // 筛选类型
    appoint_config: {
        data_url: '',
        show_data: {
            data_key: 'id',
            data_name: 'title',
            data_logo: 'cover',
        },
        header: [
            {
                field: 'id',
                name: '中文',
                type: 'text',
                width: '100',
            }
        ],
        page_size: 10,
        filter_list: [
            {
                const_key: 'goods_category', // 从哪个公共字段中获取数据
                type: 'select', // 表单的数据类型
                default: {
                    select: {
                        is_multiple: '0', // 是否多选
                        placeholder: '请输入内容', // 选择框提示内容
                        is_level: '0', // 具体的层级
                        children: 'items', // 层级的子级字段名称
                    },
                    input: {
                        type: 'input', // 输入框类型
                        placeholder: '请输入内容', // 输入框提示内容
                    }
                },
                title: '多选', // 表单内容的标题
                form_name: 'category_cascader_id', // 调用接口存放的字段
                data: [], // 公共字段的数据
                data_key: 'id', // 数据的key
                data_name: 'name', // 数据的名称
            }
        ]
    },
    filter_config: {
        data_url: '',
        filter_list: [
            {
                const_key: 'goods_category', // 从哪个公共字段中获取数据
                type: 'select', // 表单的数据类型
                default: {
                    select: {
                        is_multiple: '0', // 是否多选
                        placeholder: '请输入内容', // 选择框提示内容
                        is_level: '0', // 具体的层级
                        children: 'items', // 层级的子级字段名称
                    },
                    input: {
                        type: 'input', // 输入框类型
                        placeholder: '请输入内容', // 输入框提示内容
                    }
                },
                title: '多选', // 表单内容的标题
                form_name: 'category_cascader_id', // 调用接口存放的字段
                data: [], // 公共字段的数据
                data_key: 'id', // 数据的key
                data_name: 'name', // 数据的名称
            }
        ]
    }
};

----------------------------------------------------------------------------------------------

动态表格列表配置   form配置文件与页面控制器的文件名称保持一致，比如 后台商品管理 
 如果多个单词组合 所有单词小写，仅首个字母大写，如文件名称：Goodscart.php 如类名：可以按照单词首字母驼峰法：GoodsCart  app/admin/controller/Goods.php       控制器文件名称，类名也为 Goods
app/admin/form/Goods.php             动态表格配置文件，类名也为 Goods 
 后台管理form配置文件目录 app/admin/form 
 用户端form配置文件目录 app/index/form 
 应用插件form配置文件目录   plugins_xxx为插件标识 app/plugins/plugins_xxx/form/admin   后端
app/plugins/plugins_xxx/form/index   前端 
 配置说明 base
    key_field               主键字段（唯一数据 id 字段名称）  必传
    status_field            数据主状态字段
    is_search               是否开启搜索（0|1）
    search_url              搜索 url 地址
    is_delete               是否开启删除操作（0|1）
    is_alldelete            是否开启全部删除操作（0|1）
    delete_url              删除 url 地址
    delete_form             删除列 name=字段名称key（默认自动取第一个 checkbox|radio 的 name 名称）未匹配到则 ids
    delete_key              请求 post 的 form字段key名称（默认使用 delete_form 的值）
    confirm_title           确认框提示（默认 温馨提示）
    confirm_msg             确认框提示信息（默认 删除后不可恢复、确认操作吗？）
    timeout                 异步请求超时时间（默认30000 毫秒）
    detail_title            加载详情的弹窗标题（可空）
    is_fields_sel           是否开启字段用户选择（0|1）、默认开启
    is_sync_search          是否同步搜索默认（0否，1是）
    is_detail_nav_operate   是否详情页面展示操作导航（0否，1是）
    # v2.3.1新增 开始
    # 可开启导出excel
    is_data_export_excel    开启导出excel（使用数据配置模式下可用）
    # 可开启数据打印
    is_data_print           开启数据打印（页面需要自行开启引入打印组件）
    is_data_export_pdf      开启数据导出PDF（页面需要自行开启引入打印组件）
    data_print_template     数据打印模板（可这里配置或页面自行增加js变量 名称：print_template）
    # 详情avg页面
    detail_avg_sm_value     详情avg页面小屏展示数据数量值（默认 2）
    detail_avg_md_value     详情avg页面小屏展示数据数量值（默认 3）
    detail_avg_lg_value     详情avg页面小屏展示数据数量值（默认 4）
    # v2.3.1新增 结束
form
    label                   标题名称
    view_type               field 字段取值, many_text 多文本, many_images 多图展示, images 单图展示, qrcode 二维码生成展示, module 模块文件引入内容, status 状态操作
    view_key                展示数据的 key名称, 多个字段内容展示传数组, view_type为 module 的时候这里写文件路径
    template                模板路径（module类型下）
    params_where_name       指定参数、key值
    view_data               指定数据转换（一维/二维数组、一维数据值索引=>显示的数据）
    view_data_key           指定数据转换二维数组取值字段key名称，多图展示二级数据key名称
    view_key_join           view_key为多个字段（一维数组的时候），内容之间拼接的字符
    view_join_first         拼接展示的值（前面）仅 field 类型有效
    view_join_last          拼接展示的值（后面）仅 field 类型有效
    align                   内容位置(left 居左, center 居中, right 居右)默认 left
    grid_size               格子大小, xxxl 650px, xxl 550px, xl 450px, lg 350px, sm 200px, xs 150px, 默认空(100px)
    is_middle               上下居中（默认1居中 0或1）
    key_field               主键key字段（默认使用 base 中主键字段）
    post_url                接口地址
    is_form_su              status 状态更新组件 是否需要更新数据列表状态颜色（默认0, 0或1）
    is_loading              是否加载弹层（默认0, 0或1）（列表内置操作组件可用、如 status状态操作组件）
    loading_msg             加载弹层提示信息（列表内置操作组件可用、如 status状态操作组件）
    not_show_data           是否不显示控件数据（仅对checkbox | radio）有效
    not_show_key            是否不显示控件字段 key（仅对checkbox | radio）有效（默认主键 id）
    not_show_type           是否不显示控件的条件（0 eq 等于、 1 gt 大于、 2 lt 小于）、这个时候not_show_data不要传数组
    fixed                   固定（left|right  左固定|右固定）
    width                   设定宽度
    is_sort                 是否开启排序操作（0|1）、默认开启
    sort_field              排序指定字段名称（空则使用搜索条件的字段）
    is_list                 是否列表展示（0否, 1是）默认1
    is_detail               是否详情展示（0否, 1是）默认1
    # v2.3.1新增 开始
    # 文本截断和弹出提示
    text_truncate           文本截断（1 一行、2 两行）
    is_truncate_detail      详情是否文本截断（0否，1是）未设置则截断
    is_popover              显示弹出提示（仅field类型字段有效）
    popover_field           指定显示弹出提示数据字段（默认取view_key）
    # 图片参数
    images_width            指定图片宽度
    images_height           指定图片高度
    images_shape            round 椭圆, circle 圆形, radius 圆角
    # 圆点
    is_round_point          是否开启圆点（0否, 1是）默认0不开启，仅对field类型有效
    round_point_key         数据key
    round_point_style       圆点颜色数据（primary 主色, secondary 次色, success 绿色, warning 橙色, danger 红色），也可以直接写颜色值如：#f00,（默认空则黑色）
    # 小微章
    is_badge                是否开启小微章（0否, 1是）默认0不开启，仅对field类型有效
    badge_key               数据key
    badge_style             背景颜色数据（primary 主色, secondary 次色, success 绿色, warning 橙色, danger 红色），也可以直接写颜色值如：#f00,（默认空则黑色）
    badge_shape             round 椭圆, radius 圆角
    # 颜色
    is_color                是否开启颜色（0否, 1是）默认0不开启，仅对field类型有效
    color_key               数据key
    color_style             颜色数据、数组[0='color_style', 1=>secondary]（primary 主色, secondary 次色, success 绿色, warning 橙色, danger 红色），也可以直接写颜色值如：#f00,（默认空则黑色）
    # 图标+提示
    is_first_tips           数据前面增加提示（0否, 1是）默认0不开启，仅对field类型有效
    first_tips_icon         提示图标class（默认 exclamation-circle）
    first_tips_key          提示数据key
    first_tips_data         提示数据、如同时设置则（优先级低于first_tips_key）
    first_tips_style        提示样式（primary 主色, secondary 次色, success 绿色, warning 橙色, danger 红色）
    # 链接+提示
    is_first_link           是否开启后面展示连接
    first_link_icon         提示图标class（默认 external-link）
    first_link_key          链接数据key
    first_link_data         提示数据、如同时设置则（优先级低于first_link_key）
    first_link_popover      弹出提示信息
    first_link_style        提示样式（primary 主色, secondary 次色, success 绿色, warning 橙色, danger 红色）
    # v2.3.1新增 结束
    # v3.0.0新增 开始
    # 二维码生成并展示
    qrcode_type             二维码展示类型（0 二维码图标+内容文本+弹窗展示 / 默认, 1直接展示二维码+内容文本）
    is_qrcode_text          直接展示二维码 - 是否展示文本（0否 / 默认, 1是）
    images_width            直接展示二维码 - 指定图片宽度
    images_height           直接展示二维码 - 指定图片高度
    images_shape            直接展示二维码 - round 椭圆, circle 圆形, radius 圆角
    # 进度条
    progress_data_key       指定数据key（未定义则采用 view_key ）
    progress_size           进度条大小（xs 小, sm 中, 不定义或空则默认大）
    is_radius               是否圆角（0否, 1是）
    is_striped              是否条纹（0否, 1是）
    is_active               是否激活（0否, 1是）
    color_style             样式class（primary 主色 / 默认, secondary 次色, success 绿色, warning 橙色, danger 红色）
    progress_text_key       展示文本key（未定义则不展示）
    progress_text_unit      展示文本单位（未定义则不展示）
    # 评分星星
    star_data_key           指定数据key（未定义则采用 view_key ）
    star_max                星星最大数（默认 5）
    color_style             选中样式class（primary 主色 / 默认, secondary 次色, success 绿色, warning 橙色, danger 红色）
    color_value             选中样式颜色（色值，如 #f00）
    star_text_key           展示文本key（未定义则不展示）
    star_text_unit          展示文本单位（未定义则不展示）
    # v3.0.0新增 结束
    # v4.1.0 新增 开始
    # popup弹窗
    is_popup                是否开启popup窗口（0否, 1是）
    popup_url               popup窗口url
    popup_url_key           popup窗口url数据key（优先级高于 popup_url）
    popup_title             popup窗口标题
    popup_class             popup窗口添加的class
    popup_full              popup窗口满屏（0否, 1是）
    popup_full_max          popup窗口满屏系统最大宽度限制（0否, 1是）
    popup_full_max_size     popup窗口大小（默认空 最大1200、有效值 xs 400, sm 500, md 800, lg 1000）
    popup_offcanvas         popup窗口侧边栏(right, left)
    # modal弹窗
    is_modal                是否开启modal窗口（0否, 1是）
    modal_url               modal窗口url
    modal_url_key           modal窗口url数据key（优先级高于 modal_url）
    modal_width             modal宽度
    modal_height            modal高度
    # v4.1.0 新增 结束
    # v6.0.0 开始
    is_copy                 是否支持复制（仅 view_type=field下纯数据展示和数组数据解析展示模式下有效）
    # v6.0.0 结束
    search_config
        form_type               表单类型（input, select, section, datetime, date, ym）
        form_name               表单字段名称
        placeholder             提示信息
        is_seat_select          是否开启占位选择框
        seat_select_value       选择占位值（默认空）
        seat_select_text        选择占位文本（默认 placeholder 值）
        data                    条件数据（一维/二维数组、一维数据值索引=>显示的数据）
        data_key                二维数组数据key字段名称（默认取 id）
        data_name               二维数组数据 name 字段名称（默认取 name）
        template                模板路径（module类型下）
        where_type              条件类型（input|ym默认=, select默认in）=,like,in,section,datetime,date
        where_type_custom       条件符号自定义处理（未指定则使用 条件类型where_type，也可以定义方法接收 form_key, params，或者直接协条件符号值，模块中就不要定义方法）
        where_value_custom      条件值自定义处理（填写方法名称 接收参数 value, params）
        where_object_custom     条件处理自定义对象
        is_multiple             是否开启多选（开启后 is_seat_select 将失效）
        is_point                input 是否支持小数点
        is_disabled             是否禁止操作（0否、1是）
    view_type
        checkbox
            is_checked              是否选中（0|1）
            checked_text            选中文本
            not_checked_text        未选中文本
            view_key                默认（form_checkbox_value）
        radio
            label                   默认（单选）
                view_key                默认（form_radio_value）
data
    list_action             列表方法名称（默认['index']）
    detail_action           详情方法名称（默认['detail', 'saveinfo', 'save', 'delete', 'statusupdate']）
    detail_dkey             详情数据库数据条件key字段（默认id）
    detail_pkey             详情参数数据条件key字段（默认id）
    detail_where            详情额外条件（二维数组形式传递）
    table_name              表名称
    table_obj               数据库对象（一般连表使用）
    select_field            读取字段（默认 *）
    page_tips_handle        自定义分页提示信息处理（服务层::方法）
    data_handle             自定义数据处理（服务层::方法）
    pages_params            分页组件额外参数
    data_params             数据外参数
    detail_params           指定详情参数（最终合并到data_params）
    list_params             指定列表参数（最终合并到data_params）
    order_by                排序规则（默认 id desc）
    group                   分组去重（用于查询GROUP）
    distinct                去重（一般用于count获取总数使用）
    is_page                 是否使用分页功能（默认使用、0否、1是）
    # v2.3.1新增 开始
    # 是否处理时间字段
    is_handle_time_field    是否处理时间字段数据
    handle_time_format      指定时间字段数据格式（可字符串表示全部、数组key=>val指定字段）
    # 是否处理静态数据名称字段
    is_fixed_name_field     是否处理固定名称字段数据
    fixed_name_data         指定固定字段数据定义的数据和字段
        data        数据列表
        field       指定赋值字段（默认字段+_name、如：status_name）
        key         如果数据是二维、则读取名称（默认 name）
    # 是否处理附件字段
    is_handle_annex_field   是否处理附件字段
    handle_annex_fields     指定附件字段、默认（icon、images、images_url、video、video_url）
    data_merge              合并数据（数组）
    # v2.3.1新增 结束
    # v2.3.2新增 开始
    # 分页统计数据
    is_page_stats           是否分页统计数据（默认使用、0否、1是）
    page_stats_data         二维数组指定字段、名称、类型（[['name'=>'总价', 'unit'=>'元']]）
        name    显示名称
        field   数据字段、默认（id）
        fun     统计方法（常用 sum 总合(默认)、count 总数）
        unit    单位
    # v2.3.2新增 结束
    # v6.0.0新增 开始
    # 是否处理用户信息
    is_handle_user_field    是否处理用户字段数据
    handle_user_data        指定处理用户字段数据定义的字段（二维数组）
       key          数据key（未指定则默认 user_id）
       field        指定赋值字段（未指定则默认 user， 已指定key但未指定field则去除后面的_id作为field、不为_id结尾则使用key追加_user）
    # json数据处理
    is_json_data_handle     是否处理json数据（默认使用、0否、1是）
    json_config_data        二维数组 field => ['type'=>'annex', 'key'=>'url']
        field               定义数据字段名称（如果只是处理json数据解析则值为空即可 field => []）
            type            类型（annex 附件包含文件图片， 暂无更多类型）
            key             数据为二维数组、二级字段名称
    # 换行数据转为数组
    is_ln_to_array_handle   是否处理换行数据转为数组
    ln_to_array_fields      换行数据转为数组的字段（数组 ['field1', 'field2']）
    # v6.0.0新增 结束
    # v6.3.0新增 开始
    # 是否处理附件字段
    is_handle_annex_size_unit       是否附件字节转单位
    handle_annex_size_unit_fields   指定附件字节转单位字段、默认（size、file_size、images_size、image_size、video_size）
    # 是否处理商品信息
    is_handle_goods_field    是否处理商品字段数据
    handle_goods_data        指定处理商品字段数据定义的字段（二维数组）
       key          数据key（未指定则默认 goods_id）
       field        指定赋值字段（未指定则默认 goods， 已指定key但未指定field则去除后面的_id作为field、不为_id结尾则使用key追加_goods）
    # v6.3.0新增 结束

----------------------------------------------------------------------------------------------------

模板引擎常见方法及变量
常见方法

方法	说明	举例
empty	变量值是否为空（0、null、变量不存在都视为真）	{{if empty($val)}} ok {{else /}} no {{/if}}
isset	是否存在变量	{{if isset($val)}} ok {{else /}} no {{/if}}
is_array	是否为数组	{{if is_array($val)}} ok {{else /}} no {{/if}}
array_key_exists	key是否存在数组中	{{if array_key_exists('key', $array)}} ok {{else /}} no {{/if}}
json_encode	数组转json字符串	{{:json_encode($val)}}
urlencode	字符串编码urlencode	{{:urlencode($val)}}
urldecode	字符串解码urldecode	{{:urldecode($val)}}
base64_encode	字符串编码base64	{{:base64_encode($val)}}
base64_decode	字符串解码base64	{{:base64_decode($val)}}
print_r	数据打印（数组、字符串、数字均可使用）	{{:print_r($val)}}
MyLang	语言读取方法、参数语言key	{{:MyLang('hello')}}
MyConst	常量数据读取方法、参数常量key	{{:MyConst('hello')}}
MyC	配置读取方法、参数配置key	{{:MyC('hello')}}


常见变量

变量	说明
currency_symbol	货币符号（举例 ￥）
common_site_type	站点类型、默认快递（0快递, 1展示型, 2自提点, 3虚拟销售, 4销售+自提）
common_order_is_booking	是否预约模式（0否 1是）
common_customer_store_tel	商店信息（电话）
common_customer_store_email	商店信息（邮箱）
common_customer_store_address	商店信息（地址）
common_customer_store_qrcode	商店信息（二维码）
default_theme	默认模板
module_name	当前模块名称
controller_name	当前控制器名称
action_name	当前方法名称
plugins_module_name	当前插件模块名称
plugins_controller_name	当前插件控制器名称
plugins_action_name	当前插件方法名称
page	分页页码
page_size	分页读取数量
nav_header	主导航
nav_footer	底部导航
nav_quick	快捷导航
is_header	是否显示头部
is_footer	是否显示底部
common_goods_category_hidden	左侧大分类是否隐藏展开
default_price_regex	价格正则
attachment_host	附件host地址
public_host	css/js引入host地址
my_domain	当前url地址
my_url	当前站点url地址
my_view_url	当前完整url地址
my_public_url	项目public目录URL地址
my_http	当前http类型
home_url	首页地址
url_model	url模式
home_seo_site_title	seo标题
home_seo_site_keywords	seo关键字
home_seo_site_description	seo描述
home_seo_site_title	seo标题
user	用户信息
multilingual_default_code	多语言值
home_user_login_type	登录方式（数组 ['username', 'sms', 'email']）
home_user_reg_type	注册方式（数组 ['username', 'sms', 'email']）
page_pure	是否纯净模式
env_max_input_vars_count	系统环境参数最大数

-------------------------------------------------------------------------------------------------------

模板引擎常见方法及变量
常见方法

方法	说明	举例
empty	变量值是否为空（0、null、变量不存在都视为真）	{{if empty($val)}} ok {{else /}} no {{/if}}
isset	是否存在变量	{{if isset($val)}} ok {{else /}} no {{/if}}
is_array	是否为数组	{{if is_array($val)}} ok {{else /}} no {{/if}}
array_key_exists	key是否存在数组中	{{if array_key_exists('key', $array)}} ok {{else /}} no {{/if}}
json_encode	数组转json字符串	{{:json_encode($val)}}
urlencode	字符串编码urlencode	{{:urlencode($val)}}
urldecode	字符串解码urldecode	{{:urldecode($val)}}
base64_encode	字符串编码base64	{{:base64_encode($val)}}
base64_decode	字符串解码base64	{{:base64_decode($val)}}
print_r	数据打印（数组、字符串、数字均可使用）	{{:print_r($val)}}
MyLang	语言读取方法、参数语言key	{{:MyLang('hello')}}
MyConst	常量数据读取方法、参数常量key	{{:MyConst('hello')}}
MyC	配置读取方法、参数配置key	{{:MyC('hello')}}


常见变量

变量	说明
currency_symbol	货币符号（举例 ￥）
common_site_type	站点类型、默认快递（0快递, 1展示型, 2自提点, 3虚拟销售, 4销售+自提）
common_order_is_booking	是否预约模式（0否 1是）
common_customer_store_tel	商店信息（电话）
common_customer_store_email	商店信息（邮箱）
common_customer_store_address	商店信息（地址）
common_customer_store_qrcode	商店信息（二维码）
default_theme	默认模板
module_name	当前模块名称
controller_name	当前控制器名称
action_name	当前方法名称
plugins_module_name	当前插件模块名称
plugins_controller_name	当前插件控制器名称
plugins_action_name	当前插件方法名称
page	分页页码
page_size	分页读取数量
nav_header	主导航
nav_footer	底部导航
nav_quick	快捷导航
is_header	是否显示头部
is_footer	是否显示底部
common_goods_category_hidden	左侧大分类是否隐藏展开
default_price_regex	价格正则
attachment_host	附件host地址
public_host	css/js引入host地址
my_domain	当前url地址
my_url	当前站点url地址
my_view_url	当前完整url地址
my_public_url	项目public目录URL地址
my_http	当前http类型
home_url	首页地址
url_model	url模式
home_seo_site_title	seo标题
home_seo_site_keywords	seo关键字
home_seo_site_description	seo描述
home_seo_site_title	seo标题
user	用户信息
multilingual_default_code	多语言值
home_user_login_type	登录方式（数组 ['username', 'sms', 'email']）
home_user_reg_type	注册方式（数组 ['username', 'sms', 'email']）
page_pure	是否纯净模式
env_max_input_vars_count	系统环境参数最大数

-----------------------------------------------------------


前端开发规范
基础

table采用4个空格

标签层级对齐

所有取名使用英文、请勿使用拼音



html

class/id及属性自定义命名使用小写、多个单词使用横杠区分( - )、见名取意

form name命名使用小写、多个单词使用下划线区分( _ )、单标签闭合前面使用空格分开

属性之间使用空格分开、属性值使用双引号包裹

不同业务写好注释合理换行、避免变成一大坨

列子如下：

<div class="am-text-center goods-info" data-goods-id="10" data-goods-title="商品名称">
    <p class="goods-title">商品名称</p>
    <p class="goods-price">￥100</p>
</div>
<form>
    <input type="text" name="name" value="" />
    <input type="text" name="goods_title" value="" />
</form>


css

样式写在对应css文件中，标签选择器及多级选择器使用空格分开

选择器结尾使用空格花括号换行中

样式冒号与样式值使用空格分开

列子如下：

.hello .sub {
    color: #f00;
    font-size: 20px;
}


js

js写在对应js文件中，标签选择器及多级选择器使用空格分开

选择器结尾直接换行再花括号

方法命名单词首字母大写、参数默认值和多个参数使用空格分开

变量名称小写、多个单词下划线区分( _ )、变量与等于左右使用一个空格分开

列子如下：

// 选择器
$('.hello .sub').on('click', function()
{
    console.log(1)
});
/**
 * 方法名称
 * @author  Devil
 * @version 1.0.0
 * @date     2020-09-02
 * @desc     方法的详细描述或使用方式
 * @param   {[string]}   name            [名称]
 * @param   {[int]}        age               [年龄]
 * @param   {[string]}   hello_world   [测试字符串]
 */
function HelloWorld(name, age = 0, hello_world = '默认值')
{
    // 商品价格
    var goods_price = 100;
    // 商品名称
    var goods_title = '商品名称';
    // 商品编码
    var code = 200;
    console.log(name, age, hello_world);
    // 循环测试
    var data = [100, 200, 300];
    for(var i in data)
    {
        console.log(1)
    }
}


注释

注释写在上方、//注释使用空格分开、闭合注释内容头尾使用空格分开

列子如下：

// 注释信息
var name = 'devil';
/* 注释信息 */
.hello .sub {
}
<!-- 注释信息 -->
<div>
    <p>hello world</p>
    <!-- 测试模板引擎写法、注释信息 -->
    {{if $age > 18}}
        <p>成年人</p>
    {{else /}}
        </p>未成年人</p>
    {{/if}}
</div>

---------------------------------------------

ShopXO v2.2.0+

www  WEB部署目录（或者子目录）
├─app                   应用目录
├─admin                 后台管理目录
│  │  ├─common.php      函数文件
│  │  ├─controller      控制器目录
│  │  ├─config          配置目录
│  │  ├─route           路由目录
│  │  ├─form            动态数据表格列表
│  │  ├─lang            语言
│  │  ├─view            视图目录
│  │  └─ ...            更多类库目录
│  ├─index              前端目录
│  │  ├─common.php      函数文件
│  │  ├─controller      控制器目录
│  │  ├─config          配置目录
│  │  ├─route           路由目录
│  │  ├─form            动态数据表格列表
│  │  ├─lang            语言
│  │  ├─view            视图目录
│  │  └─ ...            更多类库目录
│  │
│  ├─common.php         公共函数文件
│  └─event.php          事件定义文件
│  ├─api                api接口目录
│  │  ├─common.php      函数文件
│  │  ├─controller      控制器目录
│  │  ├─config          配置目录
│  │  ├─route           路由目录
│  │  ├─form            动态数据表格列表
│  │  ├─lang            语言
│  │  ├─view            视图目录
│  │  └─ ...            更多类库目录
│  │
│  ├─install            系统安装目录
│  │  ├─common.php      函数文件
│  │  ├─controller      控制器目录
│  │  ├─config          配置目录
│  │  ├─route           路由目录
│  │  ├─form            动态数据表格列表
│  │  ├─lang            语言
│  │  ├─view            视图目录
│  │  └─ ...            更多类库目录
│  │
│  ├─plugins            插件
│  │  ├─test_demo       测试插件目录
│  │  ├─view            插件视图目录
│  │  │  ├─test_demo    测试插件视图目录
│  │  │  └─ ...         更多插件视图目录
│  │  └─ ...            更多插件目录
│  ├─lang               语言
│  ├─layout             可视化DIY拖拽布局模块
│  ├─module             自定义模块类库
│  ├─route              路由配置目录
│  │  └─route.config    路由定义配置文件
│  │
│  ├─common.php         公共函数文件
│  └─event.php          事件定义文件
│  ├─common.php         公共函数文件
│  └─event.php          事件定义文件
│  ├─common.php         公共函数文件
│  └─event.php          事件定义文件
│
├─config                全局配置目录
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─console.php        控制台配置
│  ├─cookie.php         Cookie配置
│  ├─database.php       数据库配置
│  ├─filesystem.php     文件磁盘配置
│  ├─lang.php           多语言配置
│  ├─log.php            日志配置
│  ├─middleware.php     中间件配置
│  ├─route.php          URL和路由配置
│  ├─session.php        Session配置
│  ├─trace.php          Trace配置
│  └─view.php           视图配置
│
├─public                WEB目录（对外访问目录）
│  ├─core.php           公共入口文件
│  ├─index.php          前端入口文件
│  ├─api.php            后台管理入口文件
│  ├─admin.php          api接口入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─resources             资源存放目录
├─raskeys               证书存放目录
├─sourcecode            小程序源码存放目录
├─extend                扩展类库目录
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                Composer类库目录
├─.example.env          环境变量示例文件
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
├─index.php             前端入口文件
├─api.php               api接口入口文件
├─admin.php             后台管理入口文件

-----------------------------------------------------------------
以下是插件开发相关：

后端目录结构

如果插件处理的业务较多，建议在app/plugins/test_xxx/ 下增加 service 服务层处理核心业务，控制层仅处理逻辑。

app/plugins/test_xxx/
    admin                       后台管理控制器
        Admin.php               插件管理入口控制器（必须）
    index                       前端控制器
        Index.php               前端插件入口文件（非必须）
    api                         API控制器
    form                        动态表格目录（可以不分组、可以直接存放文件）
        admin                   后端动态表格
        index                   前端动态表格
    view                        视图
        admin                   后端视图
            admin               后台管理控制器名称目录
                index.html      index方法对应的html文件
                saveinfo.html   saveinfo方法对应的html文件
        index                   前端视图
            index               前端入口控制器名称目录
                index.html      index方法对应的html文件
    service                     服务层（可选）
    lang                        语言目录
        zh.php                  中文语言
        en.php                  英文语言
    Hook.php                    钩子文件响应文件
    Event.php                   事件回调文件（v2.0+支持）
    config.json                 配置文件
    install.sql                 安装 sql 语句（插件安装的时候会自动执行）
    uninstall.sql               卸载 sql 语句（插件删除的时候会自动执行、具体看是否删除数据）
    update.sql                  插件版本更新 sql 语句（更新插件的时候会自动执行、v2.0+支持）


css/js/图片/静态资源位置

当存在以 [控制器.方法] 定义的静态文件后、以 [控制器] 命名的文件就不会加载

public/static/plugins/test_xxx/
    css                                 css目录
        admin                           后台管理 css 文件存放目录
            common.css                  公共的 css 文件、加载插件都会自动加载这个文件，在控制器 css 前面
            admin.css                   后台 Admin.php 控制器对应的 css 文件
            admin.index.css             后台 Admin.php 控制器 中 Index 方法对应的 css 文件
            admin.saveinfo.css          后台 Admin.php 控制器 中 SaveInfo 方法对应的 css 文件
        index                           后台管理 css 文件存放目录
            common.css                  公共的 css 文件、加载插件都会自动加载这个文件，在控制器 css 前面
            index.css                   后台 Admin.php 控制器对应的 css 文件
            index.index.css             后台 Admin.php 控制器 中 Index 方法对应的 css 文件
            index.saveinfo.css          后台 Admin.php 控制器 中 SaveInfo 方法对应的 css 文件
    js                                  js目录
        admin                           后台管理 js 文件存放目录
            common.js                   公共的 js 文件、加载插件都会自动加载这个文件，在控制器 js 前面
            admin.js                    后台 Admin.php 控制器对应的 js 文件
            admin.index.js              后台 Admin.php 控制器 中 Index 方法对应的 js 文件
            admin.saveinfo.js           后台 Admin.php 控制器 中 SaveInfo 方法对应的 js 文件
        index                           后台管理 js 文件存放目录
            common.js                   公共的 js 文件、加载插件都会自动加载这个文件，在控制器 js 前面
            index.js                    后台 Admin.php 控制器对应的 js 文件
            index.index.js              后台 Admin.php 控制器 中 Index 方法对应的 js 文件
            index.saveinfo.js           后台 Admin.php 控制器 中 SaveInfo 方法对应的 js 文件
    images                              存放的图片
    或者自己创建更多的目录存放其他数据


-------------------------------------------------------------------------------------

通过系统上传的附件位置

附件为系统自动创建，位于 upload 目录下以 plugins_加当前插件唯一标识符命名、如[ plugins_test_xxx ]

如编辑器中上传的文件，或者使用公共的附件上传组件上传的文件都存储在这里

public/static/upload/images/plugins_test_xxx/       图片附件
public/static/upload/file/plugins_test_xxx/         文件附件
public/static/upload/video/plugins_test_xxx/        视频附件


钩子响应
当系统调用插件的时候会自动执行钩子入口文件方法，可以在方法里面自行判断钩子类型，进行业务的处理。所有响应都会带上 hook_name 参数、也就是定义在配置文件 [config.json] 的钩子名称 

app/plugins/test_xxx/Hook.php  钩子响应入口文件


响应方法

v2.2.0及以上  handle 方法
v2.2.0以下    run    方法

回调事件
回调事件 Event.php、定义以下方法即可，当插件发生以下情况会回调（仅2.0+版本支持）

Upload          上传
Install         安装
Uninstall       卸载
Download        下载
Delete          删除
Upgrade         更新
v2.3.0版本起新增事件
BeginInstall    安装前（验证返回状态）
BeginUpgrade    更新前（验证返回状态）

URL生成
生成URL方法

方法名称	描述
PluginsHomeUrl	前端URL生成方法
PluginsAdminUrl	后端URL生成方法
PluginsApiUrl	api接口URL生成方法


生成URL参数

参数名称	描述
$plugins_name	应用插件唯一标记名称
$plugins_control	应用控制器
$plugins_action	应用方法
$params	参数(一维数组)


生成实例

生成前端首页     PluginsHomeUrl('test_xxx', 'index', 'index')
生成后端首页     PluginsAdminUrl('test_xxx', 'index', 'index', ['test'=>'hello'])
生成api接口首页  PluginsApiUrl('test_xxx', 'index', 'index', ['test'=>'hello'])


命名规则
后端php文件

Admin 和 Adminuser     控制器文件名称

index                         方法名称（文件中的方法名称可以为驼峰法，单词首字母大写）

控制器文件名称以 首字母大写格式创建文件、如果存在多个单词也一样，按照文件整个名称为单位。如：

Admin.php 、Adminuser.php 、Userorder.php  、 User.php


静态文件

css/js 以后端控制器文件名称命名全小写，具体到方法如：

admin 和 adminuser     控制器文件名称

index                         方法名称（不管后端文件中的命名规则，这里全部小写命名）

控制器名称.方法名称.css   或   js
admin.css 、admin.index.css   或   adminuser.js、adminuser.index.js


css和js按照标准名称对应创建后，系统会自动引入

当存在以 控制器.方法.css   或者 控制器.方法.js   定义的静态文件后、以 控制器.css 或 控制器.js 命名的文件就不会加载



创建插件

开启开发者模式后，插件会多出额外的功能、创建、编辑、下载 等操作项



新增插件

商城管理后台左侧  应用 -> 应用管理页面 左上角  点击新增

填写插件的唯一标识（以数字，字母，下划线）格式

QQ20250421-151250.png



填写插件基础信息

按照要求带 * 号的都必填，填写好后，点击 保存 即可

QQ20250421-151517.png

QQ20250421-151938.png



回到应用管理页面

页面滚动到最底部 我们可以看见刚才创建好的插件在未安装的列表，点击 安装 后插件到上面已安装列表

QQ20250421-152515.png



安装并启用插件

在已安装的列表找到我们刚才安装好的插件，可以操作开启或者关闭（可以点住插件拖动排序）

插件钩子使用定义改变后，需要来重新操作插件状态让钩子生效

QQ20250421-160115.png



查看创建好的插件源码

我们也可以直接在源码里面手动创建插件（目录和相应的文件），不是必须要通过图形界面的方式来创建

插件源码位置   test_xxx为当前的插件唯一标识

前后端代码：app/plugins/test_xxx

插件配置文件：app/plugins/test_xxx/config.json

钩子使用文件：app/plugins/test_xxx/Hook.php

前端代码：app/plugins/test_xxx/view

静态文件：public/static/plugins/test_xxx

上传的图片：public/static/upload/images/plugins_test_xxx

上传的视频：public/static/upload/video/plugins_test_xxx

上传的文件：public/static/upload/file/plugins_test_xxx

{
    "base":{
        "plugins":"test_xxx",
        "name":"测试插件",
        "logo":"\/static\/upload\/images\/plugins_test_xxx\/2025\/04\/21\/1745219584289850.jpg",
        "author":"Devil",
        "author_url":"https:\/\/shopxo.net\/",
        "version":"1.0.0",
        "desc":"测试插件的开发",
        "apply_terminal":[
            "pc",
            "h5"
        ],
        "apply_version":[
            "6.5.0"
        ],
        "is_home":false
    },
    "extend":"",
    "hook":{
    }
}
QQ20250421-153233.png



PS：config.json 文件增加或移除钩子后，需要在后台左侧 应用 -> 应用管理里面 重启插件



使用前端钩子
我们来使用一个页面钩子

比如我们这里使用 plugins_view_home_floor_top 钩子，在首页轮播下面加一些我们需要展示的内容。

我们打开开发者模式后   打开开发者模式教程 >>，访问首页会看见很多的黑圆点，鼠标放上去会显示黑圆点位置的钩子名称。

可以复制下来配置到插件 config.json 文件中定义后、在 Hook.php 文件中使用，我们继续往下看

QQ20250421-154709.png



钩子使用定义

config.json 文件中定义需要使用的钩子，定义在 hook 里面

{
    "base":{
        "plugins":"test_xxx",
        "name":"测试插件",
        "logo":"\/static\/upload\/images\/plugins_test_xxx\/2025\/04\/21\/1745219584289850.jpg",
        "author":"Devil",
        "author_url":"https:\/\/shopxo.net\/",
        "version":"1.0.0",
        "desc":"测试插件的开发",
        "apply_terminal":[
            "pc",
            "h5"
        ],
        "apply_version":[
            "6.5.0"
        ],
        "is_home":false
    },
    "extend":"",
    "hook":{
        "plugins_view_home_floor_top":[
            "app\\plugins\\test_xxx\\Hook"
        ]
    }
}
QQ20250421-155211.png



钩子使用

Hook.php 文件中 handle 方法里面使用钩子，根据业务处理需要返回显示的数据

hook_name 参数是一定存在的，当前响应的钩子名称，可以根据钩子名称区分去处理不同钩子的业务

<?php
namespace app\plugins\test_xxx;
// 测试插件 - 钩子入口
class Hook
{
    // 应用响应入口
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 首页轮播下面页面钩子使用测试
                case 'plugins_view_home_floor_top' :
                    $ret = $this->TestViewHandle($params);
                    break;
            }
            return $ret;
        }
    }
    // 单独定义一个text处理方法
    public function TestTextHandle($params = [])
    {
        // 直接返回内容会在页面上显示出来
        // 或者通过view方法渲染后的内容
        return '我是 plugins_view_home_floor_top 页面钩子测试返回的内容！';
    }
}
?>
QQ20250421-160409.png



钩子返回数据效果展示

我们在 Hook.php 文件中返回的内容、就在钩子的位置显示出来了

QQ20250421-160716.png



我们来使用模板引擎渲染返回数据

使用 MyView 方法模板引擎渲染html并传递变量数据

QQ20250421-161639.png



html中使用模板引擎

Hook.php 文件中传递过来的变量，可以直接在当前html文件中使用，这里面可以使用模板引擎语法

<div class="am-padding-xl">
    <p class="am-color-red am-text-xl">变量：{{$test_txt}}</p>
    <p class="am-text-lg">{{$name}}</p>
    <p>我是 plugins_view_home_floor_top 页面钩子测试返回和view一起的内容！</p>
</div>
QQ20250421-161826.png



查看模板引擎渲染后的效果

这里就是我们刚才使用模板引擎处理返回数据，显示在使用的钩子位置了

QQ20250421-161936.png



PS：config.json 文件增加或移除钩子后，需要在后台左侧 应用 -> 应用管理里面 重启插件

使用后端钩子   我们再来使用一个后端的钩子 改变商品的价格和名称，我们这里使用商品处理钩子 plugins_service_goods_handle_end 这个钩子名称怎么得到的？ 继续往下看  ->  当前教程最后面有说明！  
 先在config.json文件中定义钩子的使用 {
    "base":{
        "plugins":"test_xxx",
        "name":"测试插件",
        "logo":"\/static\/upload\/images\/plugins_test_xxx\/2025\/04\/21\/1745219584289850.jpg",
        "author":"Devil",
        "author_url":"https:\/\/shopxo.net\/",
        "version":"1.0.0",
        "desc":"测试插件的开发",
        "apply_terminal":[
            "pc",
            "h5"
        ],
        "apply_version":[
            "6.5.0"
        ],
        "is_home":false
    },
    "extend":"",
    "hook":{
        "plugins_view_home_floor_top":[
            "app\\plugins\\test_xxx\\Hook"
        ],
        "plugins_service_goods_handle_end":[
            "app\\plugins\\test_xxx\\Hook"
        ]
    }
}  
 然后Hook.php文件中使用钩子 <?php
namespace app\plugins\test_xxx;
// 测试插件 - 钩子入口
class Hook
{
    // 应用响应入口
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 首页轮播下面页面钩子使用测试
                case 'plugins_view_home_floor_top' :
                    $ret = $this->TestViewHandle($params);
                    break;
                // 商品数据处理钩子
                case 'plugins_service_goods_handle_end' :
                    $this->GoodsDataHandle($params);
                    break;
            }
            return $ret;
        }
    }
    // 商品数据处理钩子，这些参数是当前钩子传过来的，变量带 &的表示引用可以更改数据
    // 'hook_name'     => $hook_name,
    // 'is_backend'    => true,
    // 'params'        => &$params,
    // 'goods'         => &$v,
    // 'goods_id'      => isset($data_id) ? $data_id : 0,
    public function GoodsDataHandle($params = [])
    {
        if($params['goods_id'] == 7)
        {
            $params['goods']['title'] = '商品名称自定义了，测试的插件！';
            $params['goods']['price'] = '设置一个无效的价格，测试插件！';
        }
    }
    // 单独定义一个text处理方法
    public function TestTextHandle($params = [])
    {
        // 直接返回内容会在页面上显示出来
        // 或者通过view方法渲染后的内容
        return '我是 plugins_view_home_floor_top 页面钩子测试返回的内容！';
    }
    // 单独定义一个view处理方法，模板引擎处理数据
    public function TestViewHandle($params = [])
    {
        // 加一个变量view层接收使用
        MyViewAssign('test_txt', 'Hello Word!');
        return MyView('../../../plugins/test_xxx/view/index/public/home_test', [
            // 局部变量
            'name'  => '我是ShopXO测试插件'
        ]);
    }
}
?>  
 去商品页面看效果 我们可以看见 名称和售价已经变成我们刚才在插件里面设置的名称和售价了  
 1. 我们可以在源码里面全局搜索 MyEventTrigger 方法，前后端所有预埋的钩子都会通过这个方法实现。 2. 钩子是ShopXO官方预埋的，您也可以自己预埋实现小量修改源码的方式，然后插件里面某些环节做一些实现自己业务的需求。 3. 如有好的场景，系统没有的钩子，也可以联系我们客服提建议，让我们在新版本上增加钩子的预埋。  
 PS：config.json 文件增加或移除钩子后，需要在后台左侧 应用 -> 应用管理里面 重启插件


 钩子引入静态css/js
如果一些小插件，没有控制器，但是页面显示的内容可能需要css或者js，我们也可以在config.json里面定义 plugins_css 或 plugins_js 钩子

{
    "base":{
        "plugins":"test_xxx",
        "name":"测试插件",
        "logo":"\/static\/upload\/images\/plugins_test_xxx\/2025\/04\/21\/1745219584289850.jpg",
        "author":"Devil",
        "author_url":"https:\/\/shopxo.net\/",
        "version":"1.0.0",
        "desc":"测试插件的开发",
        "apply_terminal":[
            "pc",
            "h5"
        ],
        "apply_version":[
            "6.5.0"
        ],
        "is_home":false
    },
    "extend":"",
    "hook":{
        "plugins_css":[
            "app\\plugins\\test_xxx\\Hook"
        ],
        "plugins_js":[
            "app\\plugins\\test_xxx\\Hook"
        ]
    }
}
Hook.php里面使用指定自己随便创建的css或js文件，路径位置都没要求，如下：

public/static/plugins/text_xxx/css/index/public/style.css

public/static/plugins/text_xxx/js/index/public/style.js

public/static/plugins/text_xxx/js/index/public/style2.js

public/static/plugins/text_xxx/js/index/public/style3.js

<?php
namespace app\plugins\test_xxx;
// 测试插件 - 钩子入口
class Hook
{
    // 应用响应入口
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                case 'plugins_css' :
                    $ret = 'static/plugins/text_xxx/css/index/public/style.css';
                    break;
                case 'plugins_js' :
                    // 我们也可以自己通过一些条件限制，哪些页面需要引入在这里做判断即可
                    // 单个文件引入
                    $ret = 'static/plugins/text_xxx/js/index/public/style.js';
                    // 如果要同时引入多个文件也可以使用数组的方式，如：
                    $ret = [
                        'static/plugins/text_xxx/js/index/public/style.js',
                        'static/plugins/text_xxx/js/index/public/style2.js',
                        'static/plugins/text_xxx/js/index/public/style3.js',
                    ];
                    break;
            }
            return $ret;
        }
    }
}
?>


如果静态的代码太少，您觉得引入文件太麻烦了，也可以直接写css或者js，直接使用头尾钩子 plugins_common_header 和 plugins_common_page_bottom  参考如下：

config.json 文件

{
    "base":{
        "plugins":"test_xxx",
        "name":"测试插件",
        "logo":"\/static\/upload\/images\/plugins_test_xxx\/2025\/04\/21\/1745219584289850.jpg",
        "author":"Devil",
        "author_url":"https:\/\/shopxo.net\/",
        "version":"1.0.0",
        "desc":"测试插件的开发",
        "apply_terminal":[
            "pc",
            "h5"
        ],
        "apply_version":[
            "6.5.0"
        ],
        "is_home":false
    },
    "extend":"",
    "hook":{
        "plugins_common_header":[
            "app\\plugins\\test_xxx\\Hook"
        ],
        "plugins_common_page_bottom":[
            "app\\plugins\\test_xxx\\Hook"
        ]
    }
}
Hook.php 文件

<?php
namespace app\plugins\test_xxx;
// 测试插件 - 钩子入口
class Hook
{
    // 应用响应入口
    public function handle($params = [])
    {
        // 钩子名称
        if(!empty($params['hook_name']))
        {
            // 走钩子
            $ret = '';
            switch($params['hook_name'])
            {
                // 网站head内 插入css代码
                case 'plugins_common_header' :
                    $ret = '<style type="text/css">
                                .name {
                                    font-size: 200px;
                                    color: #f00;
                                }
                            </style>';
                    break;
                // 网站底部插入js代码
                case 'plugins_common_page_bottom' :
                    $ret = '<script type="text/javascript">
                                alert("test_xxx js");
                            </script>';
                    break;
            }
            return $ret;
        }
    }
}
?>
