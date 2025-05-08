<?php

if (!defined('ABSPATH'))
    exit;

/**
 * AIYA-CMS Theme Options Framework 组件功能
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/

 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.4
 **/

/**
 * ThemeSetup的一些说明：
 * 
 * 这是一个类似于插件方式的机制，但究极简化版。所以，它只能使用特定写法才会正常执行。
 * 
 * 用法：
 * 
 * $SET['Class类名'] = 'Class参数';
 * $Setup = new AYA_Plugin_Setup();
 * $Setup->action($SET);
 */

if (!class_exists('AYA_Plugin_Setup')) {
    class AYA_Plugin_Setup extends AYA_Framework_Setup
    {
        private static $include_once = null;
        private static $plugin_array = array();

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
                else if (is_bool($args)) {
                    if ($args === false)
                        return;
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
                if ($args === null)
                    continue;
                //实例化
                self::action($plugin, $args);
            }
        }
        //Include方法
        public static function include_plugins()
        {
            if (is_null(self::$include_once)) {
                //根目录
                $plugin_dir = (__DIR__) . '/inc';
                //遍历
                foreach (glob($plugin_dir . '/*.php') as $plugin_file) {
                    //验证文件是否存在
                    if (!is_file($plugin_file)) {
                        //print('Error File: ' . $plugin_file);
                        continue;
                    }

                    include $plugin_file;
                }
                //标记已加载
                self::$include_once = true;

            }
        }
        //包装WP报错
        public static function message_error($line, $message)
        {
            add_action('admin_notices', function () use ($line, $message) {
                echo '<div id="message" class="' . $line . '"><p>[AIYA-THEME] ' . $message . '</p></div>';
            });
        }
    }
}
