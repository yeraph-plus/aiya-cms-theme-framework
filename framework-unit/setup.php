<?php

if (!defined('ABSPATH')) exit;

//引入插件框架
require_once (__DIR__) . '/plugin-setup.php';
require_once (__DIR__) . '/plugin-filter-hub.php';

class AYP extends AYA_Theme_Setup
{
    private static $instance;
    //实例化
    public static function instance()
    {
        if (is_null(self::$instance)) new self();
    }
}

//启动
AYP::instance();
