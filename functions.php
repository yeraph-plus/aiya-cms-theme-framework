<?php

if (!function_exists('add_action')) {
    exit();
}

//加载 Composer 依赖
include_once (__DIR__) . '/vendor/autoload.php';

/*
 * ------------------------------------------------------------------------------
 * AIYA-CMS 主题拓展功能加载模板
 * ------------------------------------------------------------------------------
 */

add_action('after_setup_theme', 'aya_plugin_setup_option_filter', 99);

//读取文件
function aya_plugin_require($path)
{
    $in_file = AYA_PATH . '/plugins/' . $path . '/setup.php';

    if (is_file($in_file)) {
        //加载
        require_once $in_file;
    }
}

//注册拓展配置
function aya_add_plugin_opt(...$fields)
{
    $cached_fields = [];

    foreach ($fields as $field) {
        //只接收数组
        if (!is_array($field) || empty($field)) {
            continue;
        }

        $cached_fields[] = $field;
    }

    add_filter('aya_plugin_extra_opt', function ($fields) use ($cached_fields) {
        return array_merge($fields, $cached_fields);
    });
}

//拓展功能选项表单
function aya_plugin_setup_option_filter()
{
    //追加 field 组
    $plugin_fields = [];
    $plugin_fields = apply_filters('aya_plugin_extra_opt', $plugin_fields);

    AYF::new_opt([
        'title' => __('拓展功能', 'aiya-framework'),
        'parent' => 'basic',
        'slug' => 'extra-plugin',
        'desc' => __('AIYA-CMS 主题，额外组件设置', 'aiya-framework'),
        'fields' => $plugin_fields
    ]);
}

//读取设置
function aya_plugin_opt($opt_name, $opt_sub_name = '')
{
    $opt_group = AYF::get_opt($opt_name, 'extra-plugin');

    //没有取到值时
    if (empty($opt_group)) {
        return false;
    }
    //读取分组值
    if ($opt_sub_name !== '' && isset($opt_group[$opt_sub_name])) {
        return $opt_group[$opt_sub_name];
    } else {
        return $opt_group;
    }
}

//引入设置框架和插件组
aya_plugin_require('framework-required');
aya_plugin_require('basic-optimize');
//多域名插件
aya_plugin_require('multi-domain');
//引入图片依赖
aya_plugin_require('image-manager');
//简码图床
aya_plugin_require('internal-pic-bed');
//编辑器拓展插件
aya_plugin_require('classic-editor-modify');
//繁体转换插件
aya_plugin_require('opencc-convert');
//易支付接口订阅拓展插件
aya_plugin_require('sponsor-order-compat');
//信息流拓展插件
//aya_plugin_require('patch-flow-hub-post');
//工单插件
//aya_plugin_require('patch-work-order-post');
