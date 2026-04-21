<?php
if (!defined('ABSPATH')) exit;

/**
 * Plugin Name: AIYA-Optimize 优化插件
 * Plugin URI: https://www.yeraph.com/
 * Description: 又一款WordPress优化插件
 * Version: 3.0.1
 * Author: Yeraph Studio
 * Author URI: https://www.yeraph.com/
 * License: GPLv3 or later
 * Requires at least: 6.1
 * Tested up to: 6.5
 * Requires PHP: 8.2
 */

//在插件中加载时，兼容WP的权限验证
if (!function_exists('is_user_logged_in')) require_once(ABSPATH . WPINC . '/pluggable.php');

define('AYF_RELEASE', '2.0');

$aya_plugin_dir = plugin_dir_path(__FILE__);
$aya_required_files = [
    'vendor/autoload.php' => 'Composer 依赖',
    'framework-required/setup.php' => 'framework-required 组件',
    'basic-optimize/setup.php' => 'basic-optimize 组件',
];
$aya_missing_deps = [];

foreach ($aya_required_files as $relative_file => $label) {
    if (!is_file($aya_plugin_dir . $relative_file)) {
        $aya_missing_deps[] = $label . ' (' . $relative_file . ')';
    }
}

if (!empty($aya_missing_deps)) {
    if (is_admin()) {
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (function_exists('deactivate_plugins')) {
            deactivate_plugins(plugin_basename(__FILE__));
        }

        add_action('admin_notices', static function () use ($aya_missing_deps) {
            $deps_text = implode('、', array_map('esc_html', $aya_missing_deps));
            echo '<div class="notice notice-error"><p>';
            echo esc_html('AIYA-Optimize 插件加载失败：缺少必要依赖文件，插件已自动停用。');
            echo '<br />';
            echo esc_html('缺少依赖：' . $deps_text);
            echo '</p></div>';
        });
    }

    return;
}

//加载 Composer 依赖
require_once $aya_plugin_dir . 'vendor/autoload.php';
//引入设置框架
require_once $aya_plugin_dir . 'framework-required/setup.php';
//组件模板
//require_once $aya_plugin_dir . 'framework-required/sample-config.php';
//引入插件组
require_once $aya_plugin_dir . 'basic-optimize/setup.php';

//运行环境检查
AYP::action('EnvCheck', array(
    //PHP最低版本
    'php_last' => '8.2',
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
