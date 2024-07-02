<?php

if (!defined('ABSPATH')) exit;

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
        private static $include_once;
        private static $header_inst = 'aya_option';

        private static $cache_mode = true;
        private static $cache_load;
        private static $cache_success;

        private static $cache_tab_option = array();
        private static $cache_def_option = array();

        private static $cache_get_option = array();

        public static $inc_dir = (__DIR__) . '/inc';
        public static $class_name = 'AYA_Option_Fired_';

        function __construct()
        {
            if (is_null(self::$include_once)) {
                add_action('init', array(&$this, 'load_textdomain'));
                add_action('admin_enqueue_scripts', array(&$this, 'enqueue_script'));

                self::include();
                self::include_field();
                self::$include_once = true;
            }
        }
        //注册翻译文件
        public function load_textdomain()
        {
            $domain = 'aiya-cms-framework';
            $locale = apply_filters('plugin_locale', get_locale(), $domain);

            load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
            load_plugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }
        //加载样式
        public function enqueue_script()
        {
            wp_enqueue_style('aiya-cms-framework', AYF_URI . 'framework-required/assects/css/framework-style.css');
            wp_enqueue_script('aiya-cms-framework', AYF_URI . 'framework-required/assects/js/framework-main.js');
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
        //设置页简化调用
        public static function new_opt($conf = array())
        {
            if ($conf === array()) return;

            //创建设置页面参数
            $inst = array(
                'title' => (empty($conf['title']) ? __('Settings') : $conf['title']),
                'slug' => (empty($conf['slug'])) ? self::$header_inst . '_' : self::$header_inst . '_' . $conf['slug'],
                'icon' => (empty($conf['icon'])) ? '' : $conf['icon'],
                'parent' => (empty($conf['parent'])) ? '' : self::$header_inst . '_' . $conf['parent'],
                'desc' => (empty($conf['desc'])) ? '' : $conf['desc'],
            );

            $field_conf = $conf['fields'];

            //缓存模式
            if (self::$cache_mode) {
                self::cache_all_default($field_conf, $inst);
                //刷新缓存表单
                self::$cache_load = false;
                //print_r(self::$cache_tab_option);
            }
            new AYA_Framework_Options_Page($field_conf, $inst);
        }
        //设置默认值缓存到静态方法
        public static function cache_all_default($field_conf, $inst)
        {
            if (self::$cache_load) return;

            //提取表名存入$cache_tab_option，提取默认值存入$cache_def_option

            self::$cache_tab_option[] = $inst['slug'];

            //foreach 循环去除层级
            foreach ($field_conf as $field) {
                //跳过
                if (empty($field['id'])) continue;
                //存入
                self::$cache_def_option[$field['id']] = (empty($field['default'])) ? '' : $field['default'];
            }

            //标记位
            self::$cache_load = true;
        }
        //设置用户值缓存到静态方法
        public static function cache_all_option()
        {
            if (self::$cache_success) return;

            //根据上一步的表名，一次性提取用户值存入$cache_get_option

            foreach (self::$cache_tab_option as $option_name) {
                //读取设置
                $config = get_option('aya_opt_' . $option_name, false);

                if ($config) {
                    //合并存入
                    self::$cache_get_option[$option_name] = $config;
                }
            }

            //标记位
            self::$cache_success = true;
        }
        //分类Metabox键简化调用
        public static function new_tex($conf = array(), $inst = array())
        {
            if ($conf === array() || $inst === array()) return;

            new AYA_Framework_Term_Meta($conf, $inst);
        }
        //文章Metabox键简化调用
        public static function new_box($conf = array(), $inst = array())
        {
            if ($conf === array() || $inst === array()) return;

            new AYA_Framework_Post_Meta($conf, $inst);
        }
        //提取设置
        public static function used_option($option_name)
        {
            //验证缓存
            if (array_key_exists($option_name, self::$cache_get_option)) {
                return self::$cache_get_option[$option_name];
            }
            //读取设置
            $config = get_option('aya_opt_' . $option_name, false);
            //存入缓存
            self::$cache_get_option[$option_name] = $config;
            //返回
            return $config;
        }
        //提取默认值
        public static function default_option($option_name)
        {
            //缓存模式
            if (!self::$cache_mode) return false;
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
        public static function get_opt($name, $opt_sulg = '')
        {
            //未定义设置表单时
            if ($opt_sulg === '') return null;

            $option_name = self::$header_inst . '_' . $opt_sulg;

            $option_config = self::used_option($option_name);

            //验证已设置
            if ($option_config && isset($option_config[$name])) {
                return $option_config[$name];
            }
            //返回默认值
            $option_default = self::default_option($option_name);

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

            if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
                return true;
            }
            return false;
        }
        //判断输出
        public static function out_checked($name, $opt_sulg = '', $output = '')
        {
            $value = self::get_checked($name, $opt_sulg);
            echo ($value) ? '' : $output;
        }
    }
}
