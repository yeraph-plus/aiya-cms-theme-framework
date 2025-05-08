<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 组件 注册主题小工具 注销主题小工具
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Widget_Load extends AYA_Framework_Setup
{
    public $register_widgets;

    public function __construct($args)
    {
        if (!is_array($args)) return;
        
        $this->register_widgets = $args;
    }

    public function __destruct()
    {
        parent::add_action('widgets_init', 'aya_theme_register_widget');
    }

    public function aya_theme_register_widget()
    {
        $widgets = $this->register_widgets;

        if (parent::inspect($widgets)) return;

        //循环
        foreach ($widgets as $widget) {
            register_widget($widget);
        }
    }
}

class AYA_Plugin_Widget_Unload extends AYA_Framework_Setup
{
    public $unload_widgets;

    public function __construct($args)
    {
        if (!is_array($args)) return;
        
        $this->unload_widgets = $args;
    }

    public function __destruct()
    {
        parent::add_action('widgets_init', 'aya_theme_unregister_wp_widgets');
    }

    public function aya_theme_unregister_wp_widgets()
    {
        $widgets = $this->unload_widgets;

        if (parent::inspect($widgets)) return;

        //循环
        foreach ($widgets as $widget) {
            unregister_widget($widget);
        }
    }
}
