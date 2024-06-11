<?php
if (!defined('ABSPATH')) exit;

/*
 * ------------------------------------------------------------------------------
 * 父级设置页面
 * ------------------------------------------------------------------------------
 */

$AYF = new AYF();

$PLUGIN_SETUP = new AYA_Theme_Setup();
$PLUGIN_SETUP->include_plugins('plugin');

//设置页面和内容
$AYF_PARENT_FIELDS = array(
    array(
        'desc' => '禁用拓展',
        'type' => 'title_2',
    ),
    array(
        'title' => '全局禁用',
        'desc' => '全局禁用所有后台功能和插件，以使用其他插件代替',
        'id' => 'all_plugin_off',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'desc' => '插件',
        'type' => 'title_2',
    ),
    array(
        'title' => '外部功能加速',
        'desc' => '将Gravatar头像服务、谷歌字体服务 替换为国内CDN',
        'id' => 'plugin_add_avatar_speed',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '简单SEO组件',
        'desc' => '替代页面标题配置器并支持一些基础的SEO功能',
        'id' => 'plugin_add_seo_stk',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '额外代码',
        'desc' => '为站点增加额外JS/CSS代码，支持最小化添加百度统计和谷歌统计',
        'id' => 'plugin_add_site_statistics',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => ' STMP 送信',
        'desc' => '通过 STMP 发送站点通知',
        'id' => 'plugin_add_stmp_mail',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '经典编辑器拓展',
        'desc' => '编辑增强插件，按钮重排、支持表格、自动上传 Tips：仅支持TinyMCE（经典编辑器）',
        'id' => 'plugin_tinymce_add_modify',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'desc' => '此功能完全重建了分类的页面的路由方法，请自行测试你的主题 / 插件是否兼容',
        'type' => 'message',
    ),
    array(
        'title' => '分类 URL 重建',
        'desc' => '移除分类URL中 <code>/category/</code> 层级，启用此项功能后，需要在 <a href="options-permalink.php">固定链接</a> 设置中重新保存一次',
        'id' => 'plugin_no_category_url',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'desc' => '开发者功能',
        'type' => 'title_2',
    ),
    array(
        'title' => '服务器状态信息',
        'desc' => '在仪表盘中显示服务器状态信息组件（仅在打开仪表盘时读取一次，无监控功能）',
        'id' => 'dashboard_server_monitor',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => 'DEBUG模式',
        'desc' => '在wp_footer中输出SQL和include等调试信息',
        'id' => 'debug_mode',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '简码列表',
        'desc' => '列出WP当前的全部固定链接（ Rewrite 规则）和查询方法',
        'id' => 'debug_shortcode_items',
        'type' => 'switch',
        'default' => false,
    ),
    array(
        'title' => '路由列表',
        'desc' => '列出WP当前的全部简码功能（ Shortcode 字段），并列出回调函数',
        'id' => 'debug_rules_items',
        'type' => 'switch',
        'default' => false,
    ),
);
$AYF_ALL_OFF_FIELDS = array(
    array(
        'desc' => '禁用拓展',
        'type' => 'title_2',
    ),
    array(
        'title' => '全局禁用',
        'desc' => '全局禁用所有后台功能和插件，以使用其他插件代替',
        'id' => 'all_plugin_off',
        'type' => 'switch',
        'default' => false,
    ),
);

//创建父级设置页面和内容
if (AYF::get_checked('all_plugin_off', 'plugin')) {
    AYF::new_opt(
        array(
            'title' => 'AIYA-Optimize',
            'slug' => 'plugin',
            'desc' => 'AIYA-CMS 主题，全局功能组件',
            'fields' => $AYF_ALL_OFF_FIELDS,
        )
    );

    //退出当前脚本
    return;
} else {
    AYF::new_opt(
        array(
            'title' => 'AIYA-Optimize',
            'slug' => 'plugin',
            'desc' => 'AIYA-CMS 主题，全局功能组件',
            'fields' => $AYF_PARENT_FIELDS,
        )
    );
}
