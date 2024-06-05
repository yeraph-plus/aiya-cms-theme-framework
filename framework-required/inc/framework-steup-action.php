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
            include_once $framework_dir . '/framework-build-fields.php';
            include_once $framework_dir . '/framework-option-page.php';
            include_once $framework_dir . '/framework-metabox-post.php';
            include_once $framework_dir . '/framework-metabox-term.php';
            include_once $framework_dir . '/framework-quick-editor.php';
            include_once $framework_dir . '/framework-widget-bulider.php';
            include_once $framework_dir . '/framework-shortcode-manager.php';
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
            self::include_plugin();
        }
        //加载组件
        public static function include_plugin()
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
        //替代方法
        protected function add_action($hook, $callback, $priority = 10, $args = 1)
        {
            add_action($hook, array($this, $callback), $priority, $args);
        }
        protected function add_filter($hook, $callback, $priority = 10, $args = 1)
        {
            add_filter($hook, array($this, $callback), $priority, $args);
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

//页脚放置提示
add_filter('admin_footer_text', function ($footer_text) {
    echo $footer_text . '<span id="footer-thankyou"> / ' . __('页面由 <b>AIYA-CMS-CORE</b> 构建。') . ' </span>';
});
