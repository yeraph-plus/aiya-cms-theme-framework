<?php

/**
 * Plugin Name: AIYA-Optimize 优化插件
 * Plugin URI: https://www.yeraph.com/
 * Description: 又一款WordPress优化插件
 * Version: 1.1
 * Author: Yeraph Studio
 * Author URI: https://www.yeraph.com/
 * License: GPLv3 or later
 */

//在插件中加载
define('AYF_PATH', plugin_dir_path(__FILE__) . 'framework-required');
define('AYF_URI', plugins_url('framework-required/assects', __FILE__));

//在插件中加载时，兼容框架的权限验证
if (!function_exists('is_user_logged_in')) require(ABSPATH . WPINC . '/pluggable.php');

//IF：主题内加载时
//define('AYF_PATH', get_template_directory() . '/framework-required');
//define('AYF_URI', get_template_directory_uri() . '/framework-required/assects');

//引入设置框架
require_once AYF_PATH . '/framework-setup.php';
require_once AYF_PATH . '/setup.php';
//组件模板
//require_once AYF_PATH . '/sample-config.php';

//加载插件组
require_once AYF_PATH . '/framework-plugin-setup.php';

//定义了一些全局变量
global $aya_post_type, $aya_tax_type;

require_once AYF_PATH . '/plugin-config.php';
