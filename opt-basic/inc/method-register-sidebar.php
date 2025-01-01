<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 组件 注册主题侧边栏位
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Register_Sidebar extends AYA_Theme_Setup
{
    public $register_sidebars;

    public function __construct($args)
    {
        if (!is_array($args)) return;
        
        $this->register_sidebars = $args;
    }

    public function __destruct()
    {
        parent::add_action('after_setup_theme', 'aya_theme_register_sidebar');
    }

    public function aya_theme_register_sidebar()
    {
        $sidebars = $this->register_sidebars;

        if (parent::inspect($sidebars)) return;

        //循环
        foreach ($sidebars as $id => $name) {
            register_sidebar(array(
                'name' => $name,
                'id' => $id,
                'description' => $name . __('侧边栏'),
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ));
        }
    }
}
