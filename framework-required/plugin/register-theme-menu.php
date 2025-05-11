<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-Framework 组件 注册主题菜单栏位插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Register_Menu extends AYA_Framework_Setup
{
    public $register_menus;

    public function __construct($args)
    {
        if (!is_array($args)) return;

        $this->register_menus = $args;
    }

    public function __destruct()
    {
        parent::add_action('after_setup_theme', 'aya_theme_register_nav_menu');
    }

    public function aya_theme_register_nav_menu()
    {
        $menus = $this->register_menus;

        if (parent::inspect($menus)) return;

        register_nav_menus($menus);
    }
}
