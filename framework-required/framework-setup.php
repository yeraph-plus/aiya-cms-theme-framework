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
 * 方法 get_meta($name, $post_id, $box_id) 获取文章 Metabox 下的字段内容
 * 
 * 读取表单的验证方法：
 * 方法 get_checked($name, $inst) 先判断get_opt方法，然后返回的值是否为选中状态
 * 方法 out_opt($name, $inst) 直接输出get_opt方法返回的内容
 * 方法 out_checked($name, $output, $inst) 先判断get_opt方法，然后输出$output（用于直接输出内容）
 */

/**
 * module()方法的一些说明：
 * 
 * 这是一个类似于插件方式的机制，但究极简化版。所以，它只能使用特定写法才会正常执行。
 * 
 * 用法：
 * 
 * $SET['Class类名'] = 'Class参数';
 * $Setup = new AYA_Plugin_Setup();
 * $Setup->action($SET);
 */

if (!class_exists('AYA_Framework_Setup')) {
    define('AYA_SEP', '_');
    class AYA_Framework_Setup
    {
        private static $include_once = null;

        private static $def_option_cache = [];
        private static $sql_option_cache = [];

        private static $post_metabox_cache = [];

        public static $class_name = 'AYA_Option_Fired_';
        public static $option_key_prefix = 'aya_opt_';
        public static $metabox_key_prefix = 'aya_box_';

        public function __construct()
        {
            //避免重复加载
            if (empty(self::$include_once)) {
                //框架包
                self::include_inc();
                //模块包
                self::include_module();

                self::magic_helper();

                self::add_action('admin_enqueue_scripts', 'enqueue_script');

                self::$include_once = true;
            }
        }

        //引入框架
        public function include_inc()
        {
            //框架主程序
            require_once (__DIR__) . '/inc/framework-build-fields.php';
            require_once (__DIR__) . '/inc/framework-option-page.php';
            require_once (__DIR__) . '/inc/framework-metabox-post.php';
            require_once (__DIR__) . '/inc/framework-metabox-term.php';
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
                'number',
                'group_mult',
                'code_editor',
                'action_checkbox',
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

        //模块实例化方法
        public static function module($module_name, $args)
        {
            //修饰类名前缀
            $module_class = 'AYA_Plugin_' . $module_name;

            //如果类存在
            if (class_exists($module_class)) {
                //参数为布尔型，则不添加参数
                if (is_bool($args)) {
                    if ($args === false) {
                        return;
                    }

                    new $module_class();
                }
                //参数是关联数组，直接使用
                else if (is_array($args)) {

                    new $module_class($args);
                }
                //返回错误提示
                else {
                    self::message_error('notice', __('Plugin class input bad parameter: ') . "\"$module_class\"");
                }
            }
            //返回错误提示
            else {
                self::message_error('notice', __('Plugin class not found: ') . "\"$module_class\"");
            }
        }

        //定位静态文件URL
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

        //验证文件的方法
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

        //验证文件的方法
        public function magic_helper()
        {
            global $F_REFS, $F_OPFS;

            $A_FRCO = 'AY' . 'A' . AYA_SEP . 'H' . 'ASH' . AYA_SEP . 'FRO' . 'M';
            $A_MECO = 'AY' . 'A' . AYA_SEP . 'AU' . 'TH' . AYA_SEP . 'T' . 'YPE';

            $F_DESF = function ($str) {
                return base64_decode($str);
            };
            $F_OPFS = function ($fil, $str) {
                return strstr(file_get_contents($fil), $str);
            };
            $F_REFS = function ($SEP) use ($A_FRCO, $A_MECO, $F_DESF) {
                return $F_DESF(constant(($SEP == true) ? $A_FRCO : $A_MECO));
            };
        }

        //除错方法
        protected static function inspect($array)
        {
            //检查数组是否为空
            if (!is_array($array)) {
                return false;
            }
            if (!empty($array) && count($array) != 0 && $array != array('')) {
                return false;
            }
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

        //加载静态资源
        public function enqueue_script()
        {
            wp_enqueue_style('aiya-cms-framework', self::get_base_url() . '/assets/css/framework-style.css');
            wp_enqueue_script('aiya-cms-framework', self::get_base_url() . '/assets/js/framework-main.js');
        }

        //设置默认值提取到静态变量
        public static function cache_default_option($inst_slug, $field_conf)
        {
            //检查此slug是否已被合并
            if (isset(self::$def_option_cache[$inst_slug])) {
                return;
            }

            //初始化该slug的默认值数组
            self::$def_option_cache[$inst_slug] = [];

            //提取每个字段的默认值，存储到对应slug的数组中
            foreach ($field_conf as $field) {
                if (empty($field['id'])) {
                    continue;
                }

                $field_id = $field['id'];
                $value = isset($field['default']) ? $field['default'] : '';

                self::$def_option_cache[$inst_slug][$field_id] = $value;
            }
        }

        //设置用户值提取到静态变量
        public static function cache_custom_option($tab_slug)
        {
            //检查此slug是否已被查询
            if (isset(self::$sql_option_cache[$tab_slug])) {
                return;
            }

            //初始化该slug的用户值数组
            self::$sql_option_cache[$tab_slug] = [];

            //提取每个字段的用户值，存储到对应slug的数组中
            $query = get_option('aya_opt_' . $tab_slug, false);

            //检查数据存在
            if (!empty($query)) {
                self::$sql_option_cache[$tab_slug] = $query;
            }
        }

        //设置页简化调用
        public static function new_opt($conf = [])
        {
            if (!is_array($conf) || empty($conf)) {
                return;
            }

            //确保必要键存在
            if (!isset($conf['slug']) || !isset($conf['fields'])) {
                return;
            }

            //缓存默认值
            self::cache_default_option($conf['slug'], $conf['fields']);
            //返回设置页面实例
            return new AYA_Framework_Options_Page($conf['fields'], $conf);
        }

        //获取设置
        public static function get_opt($name, $sulg)
        {
            //未定义设置表单时
            if ($sulg === '') {
                return false;
            }

            //缓存用户值
            self::cache_custom_option($sulg);

            //直接从缓存提取用户值
            if (isset(self::$sql_option_cache[$sulg][$name])) {
                return self::$sql_option_cache[$sulg][$name];
            } else if (isset(self::$def_option_cache[$sulg][$name])) {
                return self::$def_option_cache[$sulg][$name];
            } else {
                return false;
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

        //分类Metabox键简化调用
        public static function new_tex($conf = [])
        {
            if (!is_array($conf) || empty($conf))
                return;

            $fields = $conf['fields'];
            $add_meta_in = $conf['add_meta_in'];

            return new AYA_Framework_Term_Meta($fields, $add_meta_in);
        }

        //文章Metabox键简化调用
        public static function new_box($conf = [])
        {
            if (!is_array($conf) || empty($conf))
                return;

            //单独提取组件数组用于调用
            $fields = $conf['fields'];

            unset($conf['fields']);

            return new AYA_Framework_Post_Meta($fields, $conf);
        }

        //获取文章 Metabox 字段
        public static function get_post_meta($name, $box_id = '', $post_id = 0)
        {
            if (empty($post_id) || $box_id === '') {
                return false;
            }

            if (!isset(self::$post_metabox_cache[$post_id])) {
                self::$post_metabox_cache[$post_id] = [];
            }

            // 首次请求该字段组时，整组数据写入静态缓存，后续字段读取直接命中缓存
            if (!array_key_exists($box_id, self::$post_metabox_cache[$post_id])) {
                $meta_key = self::$metabox_key_prefix . $box_id;
                $metabox_data = get_post_meta($post_id, $meta_key, true);

                self::$post_metabox_cache[$post_id][$box_id] = is_array($metabox_data) ? $metabox_data : [];
            }

            $metabox_data = self::$post_metabox_cache[$post_id][$box_id];

            return isset($metabox_data[$name]) ? $metabox_data[$name] : false;
        }

        //获取文章 Metabox 的本次提交动作
        public static function get_post_action($name, $box_id = '')
        {
            if ($name === '' || $box_id === '') {
                return false;
            }

            $nonce_field = $box_id . '_meta_box_nonce';
            $nonce_action = $box_id . '_meta_box_action';

            if (!isset($_POST[$nonce_field]) || !wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
                return false;
            }

            if (!isset($_POST[$name])) {
                return false;
            }

            return wp_unslash($_POST[$name]) === '1';
        }

        //获取分类 Metabox 字段
        public static function get_term_meta($name, $term_id = 0)
        {
            if (empty($term_id)) {
                return false;
            }

            return get_term_meta($term_id, $name, true);
        }

        //包装WP报错
        public static function message_error($line, $message)
        {
            add_action('admin_notices', function () use ($line, $message) {
                echo '<div id="message" class="' . $line . '"><p>[AIYA-Framework] ' . $message . '</p></div>';
            });
        }
    }
}

//不防君子签名术
define('AYA_HASH_FROM', 'L3N0eWxlLmNzcw');
define('AYA_AUTH_TYPE', 'aHR0cHM6Ly93d3cueWVyYXBoLmNvbQ');
