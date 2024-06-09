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
        private $instance;

        //初始化
        public function instance()
        {
            if (is_null(self::$instance)) new self();
        }

        function __construct()
        {
            self::include_plugins();
        }
        //实例化方法
        public function action($setings)
        {
            //执行循环
            foreach ($setings as $seting => $args) {

                //修饰类名
                $class = 'AYA_Plugin_' . $seting;
                //验证参数为null直接跳过
                if ($args == null) continue;
                //如果类未定义且参数不为false，则实例化
                if (class_exists($class) && $args != false) {

                    //如果参数为布尔型，则不添加参数
                    if (is_bool($args)) {
                        new $class();
                    }
                    //否则验证参数格式
                    else if (is_array($args)) {
                        new $class($args);
                    }
                    //不接受字符串参数直接跳过
                    else {
                        continue;
                    }
                }
            }
        }
        //加载组件
        public static function include_plugins()
        {
            //根目录
            $plugin_dir = AYF_PATH . '/plugins';
            //验证目录存在
            if (is_dir($plugin_dir)) {
                //定位其他组件
                $plugin_inc_dir = scandir($plugin_dir);
                //列出文件
                sort($plugin_inc_dir);
                //遍历
                foreach ($plugin_inc_dir as $file) {
                    //检查文件
                    if (substr($file, -4) == '.php') {
                        //执行include
                        include_once $plugin_dir . '/' . $file;
                    }
                }
            }
        }
        //除错方法
        protected function inspect($array)
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
