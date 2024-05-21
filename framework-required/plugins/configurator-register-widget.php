<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Widget_Load extends AYA_Theme_Setup
{
    public $register_widgets;

    public function __construct($args)
    {
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

class AYA_Plugin_Widget_Unload extends AYA_Theme_Setup
{
    public $unload_widgets;

    public function __construct($args)
    {
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
