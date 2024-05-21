<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Register_Menu extends AYA_Theme_Setup
{
    public $register_menus;

    public function __construct($args)
    {
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
