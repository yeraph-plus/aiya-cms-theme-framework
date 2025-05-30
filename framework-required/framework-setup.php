<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-CMS Theme Options Framework 加载框架文件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.3
 **/

/**
 * 封装好的Framework创建方法：
 * 
 * 创建表单方法：
 * 方法 new_opt($conf) 合并$inst和$field参数创建设置页面
 * 方法 new_tex($conf) 合并$inst和$field参数创建分类Metabox
 * 方法 new_opt($conf) 合并$inst和$field参数创建文章Metabox
 * 
 * 读取表单方法：
 * 方法 get_opt($name, $inst) 获取设置页面下的设置内容
 * 方法 get_meta($name) 内部判断是文章页面还是object然后读取Metabox
 * 
 * 读取表单的验证方法：
 * 方法 get_checked($name, $inst) 先判断get_opt方法，然后返回的值是否为选中状态
 * 方法 out_opt($name, $inst) 直接输出get_opt方法返回的内容
 * 方法 out_checked($name, $output, $inst) 先判断get_opt方法，然后输出$output（用于直接输出内容）
 */

if (!class_exists('AYA_Framework_Setup')) {
    class AYA_Framework_Setup
    {
        private static $include_once = null;
        //private static $header_inst = 'aya_option';

        private static $cache_mode = true;
        private static $cache_load;
        private static $cache_success;

        private static $cache_tab_slug = array();
        private static $cache_def_option = array();
        private static $cache_get_option = array();

        public static $class_name = 'AYA_Option_Fired_';

        public function __construct()
        {
            if (is_null(self::$include_once)) {
                //框架包
                self::include_inc();
                //模块包
                self::include_module();

                self::add_action('admin_enqueue_scripts', 'enqueue_script');

                self::$include_once = true;
            }
        }
        //引入框架
        public function include_inc()
        {
            //框架主程序
            require (__DIR__) . '/inc/framework-build-fields.php';
            require (__DIR__) . '/inc/framework-option-page.php';
            require (__DIR__) . '/inc/framework-metabox-post.php';
            require (__DIR__) . '/inc/framework-metabox-term.php';
            require (__DIR__) . '/inc/framework-quick-editor.php';
            //注册框架组件
            $fields = array(
                'text',
                'textarea',
                'color',
                'checkbox',
                'radio',
                'select',
                'upload',
                'array',
                'hidden',
                'callback',
                'switch',
                'tinymce',
                'group',
                'group-mult',
                'code-editor',
            );
            //遍历
            foreach ($fields as $field) {
                if (!class_exists(self::$class_name . $field)) {
                    include (__DIR__) . '/inc/fields/' . $field . '.php';
                }
            }
        }
        //引入模块
        public function include_module()
        {
            //组件目录
            $module_path = (__DIR__) . '/plugin';
            //遍历
            foreach (glob($module_path . '/*.php') as $module_file) {
                //验证文件是否存在
                if (!is_file($module_file)) {
                    //print('Error File: ' . $module_file);
                    continue;
                }

                require $module_file;
            }

        }
        //根据上下文判断静态文件URL
        public static function get_base_url()
        {
            //获取当前文件的标准化路径及其目录
            $current_file = wp_normalize_path(__FILE__);
            $current_dir = wp_normalize_path(dirname($current_file));
            //获取插件和主题目录
            $plugin_dir = wp_normalize_path(WP_PLUGIN_DIR);
            $theme_dir = wp_normalize_path(get_stylesheet_directory());

            //在插件目录
            if (strpos($current_dir, $plugin_dir) === 0) {
                return untrailingslashit(plugin_dir_url(__FILE__));
            }
            //在主题目录
            elseif (strpos($current_dir, $theme_dir) === 0) {
                //截取相对于主题目录的路径
                $relative_dir = ltrim(str_replace($theme_dir, '', $current_dir), '');
                return trailingslashit(get_stylesheet_directory_uri()) . trailingslashit($relative_dir);
            }
            //其他情况
            else {
                return untrailingslashit($current_dir);
            }
        }
        //验证文件方法
        public static function include_file_helper($file, $load = true)
        {
            $path = '';

            $file = ltrim($file, '/');
            $dir = plugin_dir_path(__FILE__);

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
        //加载样式
        public function enqueue_script()
        {
            wp_enqueue_style('aiya-cms-framework', self::get_base_url() . '/assets/css/framework-style.css');
            wp_enqueue_script('aiya-cms-framework', self::get_base_url() . '/assets/js/framework-main.js');
        }
        //设置默认值提取到静态变量
        public static function cache_all_default($field_conf, $inst_slug)
        {
            if (self::$cache_load)
                return;

            //提取表名存入$cache_tab_option，提取默认值存入$cache_def_option

            self::$cache_tab_slug[] = $inst_slug;

            //foreach 循环去除层级
            foreach ($field_conf as $field) {
                //跳过
                if (empty($field['id']))
                    continue;
                //存入
                self::$cache_def_option[$field['id']] = (empty($field['default'])) ? '' : $field['default'];
            }
            //print_r(self::$cache_tab_slug);
            //print_r(self::$cache_def_option);

            //标记位
            self::$cache_load = true;
        }
        //设置用户值提取到静态变量
        public static function cache_all_option()
        {
            if (self::$cache_success)
                return;

            //根据上一步的表名，一次性提取用户值存入$cache_get_option

            foreach (self::$cache_tab_slug as $tab_slug) {
                //读取设置
                $config = get_option('aya_opt_' . $tab_slug, false);

                if ($config) {
                    //合并存入
                    self::$cache_get_option[$tab_slug] = $config;
                }
            }
            //print_r(self::$cache_get_option);

            //标记位
            self::$cache_success = true;
        }
        //除错方法
        protected static function inspect($array)
        {
            //检查数组是否为空
            if (!is_array($array))
                return false;

            if (!empty($array) && count($array) != 0 && $array != array(''))
                return false;

            return true;
        }
        //替代方法
        protected function add_action($hook, $callback, $priority = 10, $args = 1)
        {
            add_action($hook, array($this, $callback), $priority, $args);
        }
        protected function add_filter($hook, $callback, $priority = 10, $args = 1)
        {
            add_filter($hook, array($this, $callback), $priority, $args);
        }
        protected function remove_action($hook, $callback, $priority = 10, $args = 1)
        {
            add_action($hook, array($this, $callback), $priority, $args);
        }
        protected function remove_filter($hook, $callback, $priority = 10, $args = 1)
        {
            add_filter($hook, array($this, $callback), $priority, $args);
        }
        //设置页简化调用
        public static function new_opt($conf = array())
        {
            if (!is_array($conf) || empty($conf))
                return;

            //缓存模式
            if (self::$cache_mode) {
                self::cache_all_default($conf['fields'], $conf['slug']);
                //刷新缓存表单
                self::$cache_load = false;
            }
            //print_r(self::$cache_tab_option);

            //单独提取组件数组用于调用
            return new AYA_Framework_Options_Page($conf['fields'], $conf);
        }
        //分类Metabox键简化调用
        public static function new_tex($conf = array())
        {
            if (!is_array($conf) || empty($conf))
                return;

            $fields = $conf['fields'];
            $add_meta_in = $conf['add_meta_in'];

            return new AYA_Framework_Term_Meta($fields, $add_meta_in);
        }
        //文章Metabox键简化调用
        public static function new_box($conf = array())
        {
            if (!is_array($conf) || empty($conf))
                return;

            //单独提取组件数组用于调用
            $fields = $conf['fields'];

            unset($conf['fields']);

            return new AYA_Framework_Post_Meta($fields, $conf);
        }
        //提取设置
        public static function used_option($option_sulg)
        {
            //验证缓存
            if (array_key_exists($option_sulg, self::$cache_get_option)) {
                return self::$cache_get_option[$option_sulg];
            }
            //读取设置
            $config = get_option('aya_opt_' . $option_sulg, false);
            //存入缓存
            self::$cache_get_option[$option_sulg] = $config;
            //返回
            return $config;
        }
        //提取默认值
        public static function default_option($option_name)
        {
            //缓存模式
            if (!self::$cache_mode)
                return false;
            //读取设置
            $default = self::$cache_def_option;
            //返回
            if (isset($default[$option_name])) {
                return $default[$option_name];
            } else {
                return false;
            }
        }
        //应用设置
        public static function get_opt($name, $sulg)
        {
            //未定义设置表单时
            if ($sulg === '')
                return null;

            $option_config = self::used_option($sulg);

            //验证已设置
            if ($option_config && isset($option_config[$name])) {
                return $option_config[$name];
            }
            //返回默认值
            $option_default = self::default_option($name);

            return $option_default;
        }
        //应用Meta
        public static function get_meta($name)
        {
            //判断页面类型
            if (is_singular()) {
                return get_post_meta(get_the_ID(), $name, true);
            }
            //判断页面类型
            else if (is_tax() || is_category() || is_tag()) {
                return get_term_meta(get_queried_object_id(), $name, true);
            }
        }
        //直接输出
        public static function out_opt($name, $opt_sulg = '')
        {
            $value = self::get_opt($name, $opt_sulg);

            echo (empty($value)) ? 'null' : $value;
        }
        //检查是否选择
        public static function get_checked($name, $opt_sulg = '')
        {
            $value = self::get_opt($name, $opt_sulg);

            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        //判断输出
        public static function out_checked($name, $opt_sulg = '', $output = '')
        {
            $value = self::get_checked($name, $opt_sulg);

            echo ($value) ? '' : $output;
        }
    }
}
//不防君子签名术
define('AYA_NAME_FILE', 'L3N0eWxlLmNzcw');
define('AYA_NAME_SIGN', 'aHR0cHM6Ly93d3cueWVyYXBoLmNvbQ');
