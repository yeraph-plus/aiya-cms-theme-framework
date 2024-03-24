<?php
/*
Plugin Name: AIYA-CMS Options Framework
Plugin URI: https://www.yeraph.com/
Description: A framework of options provided for AIYA-CMS themes, used to create settings pages, category Meta fields, and article MetaBox components, and provides some quick launch features.
Version: 0.8
Author: Yeraph Studio
Author URI: https://www.yeraph.com/
License: GPLv3 or later
*/

if (!defined('ABSPATH')) exit;

define('AYF_PATH', get_template_directory() . '/framework-required');
define('AYF_URI', get_template_directory_uri() . '/framework-required/assects');

//加载框架文件
if (!class_exists('AYA_Framework_Setup')) {
    class AYA_Framework_Setup
    {
        public $fields = array();

        private static $instance;

        function __construct()
        {
            self::register_textdomain();

            self::include_self();

            self::include_framework_field();
        }
        //初始化
        public static function init()
        {
            if (is_null(self::$instance)) new self();
        }
        //注册翻译文件
        public static function register_textdomain()
        {
            load_plugin_textdomain('aya-framework', false, dirname(__FILE__) . '/languages/');
        }
        //Include
        public function include_self()
        {
            //引入框架
            include_once AYF_PATH . '/inc/option-field-action.php';
            include_once AYF_PATH . '/inc/option-framework-page.php';
            include_once AYF_PATH . '/inc/option-metabox-post.php';
            include_once AYF_PATH . '/inc/option-metabox-term.php';
        }
        //设置框架组件
        public function include_framework_field()
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
                if (!class_exists('AYA_Option_Fired_' . $field) && class_exists('AYA_Field_Action')) {
                    include_once  AYF_PATH . '/inc/fields/' . $field . '.php';
                }
            }
        }
    }
}

/**
 * 封装好的Framework创建方法
 * 
 * 方法 new_opt($conf) 合并$inst和$field参数创建设置页面
 * 方法 new_tex($conf) 合并$inst和$field参数创建分类Metabox键
 * 方法 new_opt($conf) 合并$inst和$field参数创建文章Metabox键
 * 方法 get_opt($name, $inst) 获取设置页面下的设置内容
 * 方法 get_checked($name, $inst) 先判断get_opt方法，然后返回的值是否为选中状态
 * 方法 out_opt($name, $inst) 直接输出get_opt方法返回的内容
 * 方法 out_checked($name, $inst, $output) 先判断get_opt方法，然后输出$output
 */
if (!class_exists('AYF')) {
    class AYF extends AYA_Framework_Setup
    {
        private static $self_inst = 'aya_option';

        private static $instance;

        public function __construct()
        {
            parent::__construct();
        }
        //初始化
        public static function init()
        {
            if (is_null(self::$instance)) new self();
        }
        //简化调用
        public static function new_opt($conf = array())
        {
            if ($conf == array()) return;

            self::init();

            //创建设置页面参数
            $inst = array(
                'title' => $conf['title'],
                'slug' => ($conf['slug'] != '') ?  self::$self_inst . '_' . $conf['slug'] : self::$self_inst,
                'parent' => ($conf['slug'] != '') ?  self::$self_inst : '',
                'desc' => $conf['desc'],
            );
            $field_conf = $conf['fields'];

            new AYA_Framework_Options_Page($field_conf, $inst);
        }
        public static function new_tex($conf = array(), $inst = array())
        {
            if ($conf == array()) return;

            self::init();

            new AYA_Framework_Term_Meta($conf, $inst);
        }
        public static function new_box($conf = array(), $inst = array())
        {
            if ($conf == array()) return;

            self::init();

            new AYA_Framework_Post_Meta($conf, $inst);
        }
        //提取设置
        public static function get_opt($name, $opt_inst = '', $default = null)
        {
            if ($opt_inst != '') {
                $config = get_option('aya_opt_' . self::$self_inst . '_' . $opt_inst);
            } else {
                $config = get_option('aya_opt_' . self::$self_inst);
            }

            if ($config && isset($config[$name])) {
                return $config[$name];
            }
            return $default;
        }
        //检查是否选择
        public static function get_checked($name, $opt_inst = '', $default = false)
        {
            $value = self::get_opt($name, $opt_inst);

            if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
                return true;
            }
            if ($value === null) {
                return $default;
            }
            return false;
        }
        //直接输出
        public static function out_opt($name, $opt_inst = '', $default = null)
        {
            $output = self::get_opt($name, $opt_inst, $default);
            if ($output != null) {
                echo $output;
            }
        }
        //判断输出
        public static function out_checked($name, $opt_inst = '', $output = '', $default = null)
        {
            if (self::get_checked($name, $opt_inst, $default)) {
                echo $output;
            }
        }
    }
}
