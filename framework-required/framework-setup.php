<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework 加载框架文件
 * 
 * @version 1.3
 **/

if (!class_exists('AYA_Framework_Setup')) {
    class AYA_Framework_Setup
    {
        private static $include_once;

        public static $inc_dir = (__DIR__) . '/inc';
        public static $class_name = 'AYA_Option_Fired_';

        function __construct()
        {
            if (is_null(self::$include_once)) {
                add_action('plugins_loaded', array(&$this, 'load_textdomain'));
                add_action('admin_enqueue_scripts', array(&$this, 'enqueue_script'));

                self::include();
                self::include_field();
                self::$include_once = true;
            }
        }
        //注册翻译文件
        public function load_textdomain()
        {
            load_plugin_textdomain('aiya-cms-framework', false, (__DIR__) . '/languages');
        }
        //加载样式
        public function enqueue_script()
        {
            wp_enqueue_style('aiya-cms-framework', AYF_URL . 'framework-required/assects/css/framework-style.css');
            wp_enqueue_script('aiya-cms-framework', AYF_URL . 'framework-required/assects/js/framework-main.js');
        }
        //引入框架
        public static function include()
        {
            require_once self::$inc_dir . '/framework-build-fields.php';
            require_once self::$inc_dir . '/framework-option-page.php';
            require_once self::$inc_dir . '/framework-metabox-post.php';
            require_once self::$inc_dir . '/framework-metabox-term.php';
            require_once self::$inc_dir . '/framework-quick-editor.php';
            require_once self::$inc_dir . '/framework-widget-bulider.php';
            require_once self::$inc_dir . '/framework-shortcode-manager.php';
        }
        //设置框架组件
        public static function include_field()
        {
            $fields = array(
                'text',
                'textarea',
                'color',
                'checkbox',
                'radio',
                'select',
                'upload',
                'array',
                'callback',
                'switch',
                'tinymce',
                'group',
                'group-mult',
                'code-editor',
            );

            foreach ($fields as $field) {
                if (!class_exists(self::$class_name . $field) && class_exists('AYA_Field_Action')) {
                    include_once self::$inc_dir . '/fields/' . $field . '.php';
                }
            }
        }
    }
}
