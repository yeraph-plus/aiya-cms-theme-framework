<?php
if (!defined('ABSPATH'))
    exit;

if (!class_exists('AYP')) {
    //引入插件框架
    require_once (__DIR__) . '/plugin-setup.php';
    require_once (__DIR__) . '/plugin-filter-hub.php';
    //实例化
    class AYP extends AYA_Theme_Setup
    {
        private static $instance;

        public static function instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
    //启动
    AYP::instance();
    //加载插件组
    AYP::include_plugins('plugin');
    //加载插件设置
    include_once (__DIR__) . '/plugin-config-parent.php';
    include_once (__DIR__) . '/plugin-config.php';
}
