<?php

if (!defined('ABSPATH')) exit;

/*
 * ------------------------------------------------------------------------------
 * 初始化
 * ------------------------------------------------------------------------------
 */

define('AYA_PATH', get_template_directory() . '/');
define('AYA_CORE_PATH', get_template_directory() . '/framework-required');
define('AYA_CORE_URI', get_template_directory_uri() . '/framework-required');

//引入文件
require_once get_template_directory() . '/framework-required/setup.php';
//方法库
require_once get_template_directory() . '/framework-required/method.php';
//设置框架示例
//require_once get_template_directory() . '/framework-required/sample-config.php';
