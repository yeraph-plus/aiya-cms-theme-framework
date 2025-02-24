<?php
if (!defined('ABSPATH')) exit;

/**
 * Plugin Name: AIYA-Optimize 优化插件
 * Plugin URI: https://www.yeraph.com/
 * Description: 又一款WordPress优化插件
 * Version: 1.2.4
 * Author: Yeraph Studio
 * Author URI: https://www.yeraph.com/
 * License: GPLv3 or later
 * Requires at least: 6.1
 * Tested up to: 6.5
 * Requires PHP: 8.2
 */

//在插件中加载时，兼容WP的权限验证
if (!function_exists('is_user_logged_in')) require_once(ABSPATH . WPINC . '/pluggable.php');
//用于识别插件存在
define('AYF_RELEASE', '1.2');

//引入设置框架
require_once plugin_dir_path(__FILE__) . 'framework-required/setup.php';
//组件模板
//require_once plugin_dir_path(__FILE__) . 'framework-required/sample-config.php';
//引入插件组
require_once plugin_dir_path(__FILE__) . 'basic-optimize/setup.php';

//运行环境检查
AYP::action('EnvCheck', array(
    //PHP最低版本
    'php_last' => '7.4',
    //PHP扩展
    'php_ext' => array(),
    //WP最低版本
    'wp_last' => '6.1',
));

/*
//注册翻译文件
function aya_framework_load_textdomain()
{
    $domain = 'aiya-cms-framework';
    $locale = apply_filters('plugin_locale', get_locale(), $domain);

    load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
    load_plugin_textdomain($domain, false, (__DIR__) . '/languages/');
}
add_action('admin_init', 'aya_framework_load_textdomain');
*/