<?php

if (!defined('ABSPATH')) {
    exit;
}

//引入设置框架
require_once (__DIR__) . '/framework-setup.php';
//实例化框架方法
if (!class_exists('AYF')) {
    class AYF extends AYA_Framework_Setup
    {
        private static $instance;

        public static function instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        //初始父类方法
        private function __construct()
        {
            parent::__construct();
        }
    }
    //启动
    AYF::instance();
}