<?php

if (!defined('ABSPATH')) exit;

/**
 * ThemeSetup的一些说明：
 * 
 * 这是一个类似于插件方式的机制，但究极简化版。所以，它只能使用特定写法才会正常执行。
 * 
 * 用法：
 * 
 * $SET['Class类名'] = 'Class参数';
 * $Setup = new AYA_Theme_Setup();
 * $Setup->action($SET);
 */

if (!class_exists('AYA_Theme_Setup')) {
    class AYA_Theme_Setup
    {
        private static $include_once = array();
        private static $plugin_array = array();

        public static $inc_dir = (__DIR__);

        //实例化方法
        public static function action($plugin_name, $args)
        {
            //修饰类名
            $class = 'AYA_Plugin_' . $plugin_name;

            //如果类存在
            if (class_exists($class)) {
                //参数是否是数组
                if (is_array($args)) {
                    new $class($args);
                }
                //参数为布尔型，则不添加参数
                else if (is_bool($args) && $args !== false) {
                    new $class();
                }
                //返回错误提示
                else {
                    self::message_error('notice', __('Plugin class input bad parameter: ') . "\"$class\"");
                }
            }
            //返回错误提示
            else {
                self::message_error('notice', __('Plugin class not found: ') . "\"$class\"");
            }
        }
        //注册实例方法
        public static function action_register($plugin_name, $args)
        {
            $add_array = &self::$plugin_array;
            //增加一组
            $add_array[$plugin_name] = $args;
        }
        //实例化全部
        public static function action_all()
        {
            //执行循环
            foreach (self::$plugin_array as $plugin => $args) {
                //验证参数为null直接跳过
                if ($args === null) continue;
                //实例化
                self::action($plugin, $args);
            }
        }
        //Include方法
        public static function include_plugins($dir)
        {
            //跳过已加载
            if (in_array($dir, self::$include_once)) return;

            //根目录
            $plugin_dir = self::$inc_dir . '/' . $dir;
            //验证目录存在
            if (is_dir($plugin_dir)) {
                //定位其他组件
                $plugin_inc_dir = scandir($plugin_dir);
                //列出文件
                sort($plugin_inc_dir);
                //遍历
                foreach ($plugin_inc_dir as $file) {
                    //检查文件
                    if (substr($file, -4) === '.php') {
                        //执行include
                        include_once $plugin_dir . '/' . $file;
                    }
                }
                //标记已加载
                array_push(self::$include_once, $dir);
            }
            //目录不存在报错
            else {
                self::message_error('notice', __('Include directory not found: ') . "\"$plugin_dir\"");
            }
        }
        //包装WP报错
        public static function message_error($line, $message)
        {
            add_action('admin_notices', function () use ($line, $message) {
                echo '<div id="message" class="' . $line . '"><p>[AIYA-THEME] ' . $message . '</p></div>';
            });
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
        //除错方法
        protected static function inspect($array)
        {
            //检查数组是否为空
            if (!is_array($array)) return false;

            if (!empty($array) && count($array) != 0 && $array != array('')) return false;

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
    }
}
