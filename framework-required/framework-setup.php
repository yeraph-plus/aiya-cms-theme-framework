<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework 加载框架文件
 * 
 * @version 1.0
 **/

if (!class_exists('AYA_Framework_Setup')) {
    class AYA_Framework_Setup
    {
        private static $instance;

        //初始化
        public static function instance()
        {
            if (is_null(self::$instance)) new self();
        }

        function __construct()
        {
            self::include_self();
            self::include_framework_field();
            self::register_textdomain();
        }
        //注册翻译文件
        public static function register_textdomain()
        {
            load_plugin_textdomain('aya-framework', false, AYF_PATH . '/languages');
        }
        //Include
        public static function include_self()
        {
            $framework_dir = AYF_PATH . '/inc';
            //引入框架
            require_once $framework_dir . '/framework-build-fields.php';
            require_once $framework_dir . '/framework-option-page.php';
            require_once $framework_dir . '/framework-metabox-post.php';
            require_once $framework_dir . '/framework-metabox-term.php';
            require_once $framework_dir . '/framework-quick-editor.php';
            require_once $framework_dir . '/framework-widget-bulider.php';
            require_once $framework_dir . '/framework-shortcode-manager.php';
        }
        //设置框架组件
        public static function include_framework_field()
        {
            $framework_field_dir = AYF_PATH . '/inc/fields';

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
                if (!class_exists('AYA_Option_Fired_' . $field) && class_exists('AYA_Field_Action')) {
                    include_once  $framework_field_dir . '/' . $field . '.php';
                }
            }
        }
        //验证文件
        public static function include_file_helper($file, $load = true)
        {
            $path = '';

            $file = ltrim($file, '/');
            $dir = AYF_PATH . '/';

            //验证父主题位置
            if (file_exists(get_parent_theme_file_path($file))) {
                $path = get_parent_theme_file_path($file);
            }
            //验证主题位置
            elseif (file_exists(get_theme_file_path($file))) {
                $path = get_theme_file_path($file);
            }
            //直接验证当前文件
            elseif (file_exists($dir . $file)) {
                $path = $dir . $file;
            }

            if (!empty($path) && !empty($file) && $load) {
                //执行include
                require_once($path);
            } else {
                return $file;
            }
        }
    }
}
