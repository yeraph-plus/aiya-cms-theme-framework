<?php
/*
Plugin Name: AIYA-CMS Options Framework
Plugin URI: https://www.yeraph.com/
Description: 为AIYA-CMS提供的选项框架，用于创建设置页面、分类MetaBox、文章MetaBox，并提供一些快速启动功能。
Version: 0.3
Author: Yeraph Studio
Author URI: https://www.yeraph.com/
License: GPLv3 or later
*/

if (!defined('ABSPATH')) exit;

define('AYA_PATH', get_template_directory() . '/');
define('AYA_CORE_PATH', get_template_directory() . '/framework-required');
define('AYA_CORE_URI', get_template_directory_uri() . '/framework-required');

if (!class_exists('AYA_Framework_Setup')) {
    class AYA_Framework_Setup
    {
        public $fields = array();

        private static $instance;

        function __construct()
        {
            self::include_self();

            self::include_framework_field();

            self::include_plugin();
        }
        //初始化
        public static function init()
        {
            if (is_null(self::$instance)) new self();
        }
        //Include
        private function include_self()
        {
            //根目录
            $root_dir = AYA_CORE_PATH . '/inc';
            //引入框架
            require_once $root_dir . '/option-field-action.php';
            require_once $root_dir . '/option-framework-page.php';
            require_once $root_dir . '/option-metabox-post.php';
            require_once $root_dir . '/option-metabox-term.php';
            //功能组件
            require_once $root_dir . '/action-env-check.php';
            require_once $root_dir . '/action-dashboard.php';
            require_once $root_dir . '/action-register.php';
            require_once $root_dir . '/action-optimize.php';
            require_once $root_dir . '/action-request.php';
            require_once $root_dir . '/action-security.php';
            require_once $root_dir . '/action-template-page.php';
        }
        //设置框架组件
        private function include_framework_field()
        {
            //根目录
            $root_dir = AYA_CORE_PATH . '/inc/fields';

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
                'editor',
                'group',
                //'group_mult',
                'code_editor',
            );

            foreach ($fields as $field) {
                if (!class_exists('AYA_Option_Fired_' . $field) && class_exists('AYA_Field_Action')) {
                    require_once  $root_dir . '/' . $field . '.php';
                }
            }
        }
        //加载组件
        private function include_plugin()
        {
            //根目录
            $root_dir = AYA_CORE_PATH . '/plugin';
            //定位
            $inc_dir = scandir($root_dir);

            sort($inc_dir);
            //执行循环
            foreach ($inc_dir as $file) {
                //检查文件
                if (substr($file, -4) == '.php') {
                    //执行include
                    include_once $root_dir . '/' . $file;
                }
            }
        }
    }
}

/**
 * ThemeSetup的一些说明：
 * 
 * 这是一个类似于插件方式的机制，但究极简化版。所以，它只能使用特定写法才会正常执行。
 * 
 * 举例：
 * 
 * $SET['Class类名'] = 'Class参数';
 * $Setup = new AYA_Theme_Setup();
 * $Setup->action($SET);
 */
if (!class_exists('AYA_Theme_Setup')) {
    class AYA_Theme_Setup extends AYA_Framework_Setup
    {
        //路由伪静态开关
        public static $tag_html = true;

        //实例化方法
        public function action($setings)
        {
            //执行循环
            foreach ($setings as $seting => $args) {

                //修饰类名
                $class = 'AYA_Plugin_' . $seting;

                //如果类未定义且参数不为null，则实例化
                if (class_exists($class) && $args != null) {
                    //如果参数为布尔型，则不添加参数
                    if (is_bool($args) && $args != false) {

                        new $class();
                    }

                    new $class($args);
                }
            }
        }
        //替代方法
        protected function add_action($hook, $callback)
        {
            add_action($hook, array($this, $callback));
        }
        protected function add_filter($hook, $callback)
        {
            add_filter($hook, array($this, $callback));
        }
        //除错方法
        protected function inspect($array)
        {
            //检查数组是否为空
            if (!is_array($array)) return false;

            if (!empty($array) && count($array) != 0 && $array != array('')) return false;

            return true;
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

        public function __construct()
        {
            parent::__construct();
        }
        //简化调用
        public static function new_opt($conf = array())
        {
            if ($conf == array()) return;
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
        public static function new_tex($conf = array())
        {
            if ($conf == array()) return;

            new AYA_Framework_Term_Meta($conf, $inst);
        }
        public static function new_box($conf = array())
        {
            if ($conf == array()) return;

            new AYA_Framework_Post_Meta($conf, $inst);
        }
        public static function new_act($conf)
        {
            self::init();
            $Setup = new AYA_Theme_Setup();

            $Setup->action($conf);
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
