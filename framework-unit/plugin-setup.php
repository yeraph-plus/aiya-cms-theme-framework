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
            //self::include_plugins('inc');
            self::include_plugins('plugin');
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
        public static function include_plugins($dir)
        {
            //根目录
            $plugin_dir = plugin_dir_path(__FILE__) . $dir;
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

/*
 * ------------------------------------------------------------------------------
 * 预置方法
 * ------------------------------------------------------------------------------
 */

//验证文件MD5的方法
function ayf_get_md5_file_source($filenamesource, $filenamedest)
{
    $sourcefile = md5_file($filenamesource);
    $destfile   = md5_file($filenamedest);
    if ($sourcefile == $destfile) {
        return  true;
    } else {
        return  false;
    }
}
//插件功能转换参数
function ayf_plugin_action($field_array, $plugin_sulg)
{
    if (!is_array($field_array)) {
        return;
    }
    /*
    echo '<table class="section-table-list">';
    echo '<thead><tr><th>title</th><th>desc</th></tr></thead>';
    echo '<tbody>';
    foreach ($field_array as $field) {
        if (empty($field['title'])) {
            continue;
        }
        echo '<tr><td>' . $field['title'] . '</td><td>' . $field['desc'] . '</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
    */

    $action_array = array();

    //遍历
    foreach ($field_array as $field) {
        //跳过
        if (empty($field['id'])) {
            continue;
        }
        //验证选项布尔型
        if ($field['type'] === 'switch') {
            $action_array[$field['id']] = AYF::get_checked($field['id'], $plugin_sulg);
        } else {
            $action_array[$field['id']] = AYF::get_opt($field['id'], $plugin_sulg);
        }
    }

    //返回
    return $action_array;
}
//查询所有短代码
function query_shortcode_items()
{
    $items = [];

    //循环获取所有短代码和回调函数
    foreach ($GLOBALS['shortcode_tags'] as $tag => $callback) {
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                $callback = '<p>' . get_class($callback[0]) . '->' . (string)$callback[1] . '</p>';
            } else {
                $callback = '<p>' . $callback[0] . '->' . (string)$callback[1] . '</p>';
            }
        } elseif (is_object($callback)) {
            $callback = '<pre>' . print_r($callback, true) . '</pre>';
        } else {
            $callback    = wpautop($callback);
        }
        //简码+回调函数
        $items[] = ['tag' => wpautop($tag), 'callback' => $callback];
    }

    //print_r($items);
    //return $items;

    //将获得的数组转换为html表格
    echo '<table class="section-table-list">';
    echo '<thead><tr><th>简码</th><th>回调函数</th></tr></thead>';
    echo '<tbody>';
    foreach ($items as $item) {
        echo '<tr><td>' . $item['tag'] . '</td><td>' . $item['callback'] . '</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
//查询所有路由
function query_rewrite_rules_items($args)
{
    $items = [];
    $rewrite_id = 0;
    //获取WP设置
    $rewrite_rules = get_option('rewrite_rules') ?: [];
    //循环设置
    foreach ($rewrite_rules as $regex => $query) {
        $rewrite_id++;
        $items[] = compact('rewrite_id', 'regex', 'query');
    }

    //print_r($items);
    //return $items;

    //将获得的数组转换为html表格
    echo '<table class="section-table-list">';
    echo '<thead><tr><th>ID</th><th>正则</th><th>查询方法</th></tr></thead>';
    echo '<tbody>';
    foreach ($items as $item) {
        echo '<tr><td>' . $item['rewrite_id'] . '</td><td>' . $item['regex'] . '</td><td>' . $item['query'] . '</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
