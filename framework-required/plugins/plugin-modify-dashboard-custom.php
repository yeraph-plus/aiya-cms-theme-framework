<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

/*
 * Name: WP 后台精简自定义
 * Version: 1.0.0
 * Author: AIYA-CMS
 * Author URI: https://www.yeraph.com
 */

class AYA_Plugin_Admin_Custom extends AYA_Theme_Setup
{
    public $admin_options;

    public function __construct($args)
    {
        $this->admin_options = $args;
    }

    public function __destruct()
    {
        $options = $this->admin_options;

        if ($options['admin_title_format'] == true) {
            parent::add_action('wp_dashboard_setup', 'aya_theme_remove_dashboard_meta_box');
        }
        if ($options['admin_footer_replace'] != null) {
            parent::add_action('wp_before_admin_bar_render', 'aya_theme_remove_admin_bar_logo', 0);
        }
        if ($options['remove_admin_bar'] == true) {
            parent::add_action('init', 'aya_theme_remove_admin_bar');
        }
        if ($options['remove_bar_wplogo'] == true) {
            add_filter('admin_title', array($this, 'aya_theme_custom_admin_title'), 10, 2);
            add_filter('login_title', array($this, 'aya_theme_custom_admin_title'), 10, 2);
        }
        if ($options['remove_bar_wpnews'] == true) {
            //左
            add_filter('admin_footer_text', array($this, 'aya_theme_custom_admin_footer'));
            //右
            add_filter('update_footer', '__return_false', 11);
            //移除后台右上角帮助
            add_action('in_admin_header', function(){
                global $current_screen;
                $current_screen->remove_help_tabs();
            });
        }
        if ($options['admin_add_dashboard_widgets'] == true) {
            parent::add_action('wp_dashboard_setup', 'add_server_status_dashboard_widgets', 0);
        }
    }
    //替换后台标题
    public function aya_theme_custom_admin_title($admin_title, $title)
    {
        //站点名 - 页面
        return get_bloginfo('name') . ' - ' . $title;
    }
    //替换后台页脚信息
    public function aya_theme_custom_admin_footer()
    {
        echo '<span id="footer-thankyou">' . $this->admin_options['admin_footer_replace'] . '</span>';
    }
    //禁用前台顶部工具栏
    public function aya_theme_remove_admin_bar()
    {
        add_action('show_admin_bar', '__return_false');
        //判断管理员显示工具栏
        /*
        add_filter('show_admin_bar', function ($status) {
            return current_user_can('manage_options') ? $status : false;
        });
        */
    }
    //注册工具栏自定义链接
    /*
    public function aya_theme_custom_admin_bar_link($add_bar = array())
    {
        global $wp_admin_bar;
        
        //$add_bar 参数：
        $add_bar = array(
            'parent' => false, //'false'添加主层级，子级需要填写父级菜单ID
            'id' => 'order', //链接ID
            'title' => '订单', //链接标题
            'href' => admin_url('admin.php?page=orders'), //链接地址
            'meta' => false, // array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' )
        );

        //注册新的菜单
        add_action('admin_bar_menu', function ($wp_admin_bar) {
            $wp_admin_bar->add_menu($add_bar);
        }, 90);
    }
    */
    //隐藏左上角WordPress标志
    public function aya_theme_remove_admin_bar_logo()
    {
        global $wp_admin_bar;
        //禁用 Menu Logo
        $wp_admin_bar->remove_menu('wp-logo');
    }
    //隐藏后台欢迎模块和WordPress新闻
    public function aya_theme_remove_dashboard_meta_box()
    {
        //删除 "欢迎" 模块
        remove_action('welcome_panel', 'wp_welcome_panel');
        //删除用户标记
        delete_user_meta(get_current_user_id(), 'show_welcome_panel');
        //删除 "站点健康" 模块
        //remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
        //删除 "概况" 模块
        //remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        //删除 "快速发布" 模块
        //remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        //删除 "引入链接" 模块
        //remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        //删除 "插件" 模块
        //remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        //删除 "动态" 模块
        //remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
        //删除 "近期评论" 模块
        //remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
        //删除 "近期草稿" 模块
        //remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
        //删除 "WordPress 开发日志" 模块
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        //删除 "WordPress 新闻" 模块
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    }
    //注册新的控制台Widget
    public function add_server_status_dashboard_widgets()
    {
        //PHP信息
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            wp_add_dashboard_widget(
                'dashboard_server_widget',
                '服务器信息',
                function () {
                    $widget = new AYA_Dashboard_Server_Status();
                    echo $widget->server_widget();
                }
            );
        }
        //PHP版本
        wp_add_dashboard_widget(
            'dashboard_version_widget',
            '服务器版本',
            function () {
                $widget = new AYA_Dashboard_Server_Status();
                echo $widget->version_widget();
            }
        );
        //PHP拓展
        wp_add_dashboard_widget(
            'dashboard_php_widget',
            'PHP扩展',
            function () {
                $widget = new AYA_Dashboard_Server_Status();
                echo $widget->php_widget();
            }
        );
        //Apache信息
        if ($GLOBALS['is_apache'] && function_exists('apache_get_modules')) {
            wp_add_dashboard_widget(
                'dashboard_apache_widget',
                'Apache信息',
                function () {
                    $widget = new AYA_Dashboard_Server_Status();
                    echo $widget->apache_widget();
                }
            );
        }
        //OPCache信息
        if (function_exists('opcache_get_status')) {
            wp_add_dashboard_widget(
                'dashboard_opcache_usage_widget',
                'OPCache使用率',
                function () {
                    $widget = new AYA_Dashboard_Server_Status();
                    echo $widget->opcache_usage_widget();
                }
            );
            wp_add_dashboard_widget(
                'dashboard_opcache_status_widget',
                'OPCache状态',
                function () {
                    $widget = new AYA_Dashboard_Server_Status();
                    echo $widget->opcache_status_widget();
                }
            );
            wp_add_dashboard_widget(
                'dashboard_opcache_configuration_widget',
                'OPCache配置信息',
                function () {
                    $widget = new AYA_Dashboard_Server_Status();
                    echo $widget->opcache_configuration_widget();
                }
            );
        }
        //Memcached信息
        if (method_exists('WP_Object_Cache', 'get_mc')) {
            wp_add_dashboard_widget(
                'dashboard_memcached_status_widget',
                'Memcached状态',
                function () {
                    $widget = new AYA_Dashboard_Server_Status();
                    echo $widget->memcached_status_widget();
                }
            );
            wp_add_dashboard_widget(
                'dashboard_memcached_usage_widget',
                'Memcached使用率',
                function () {
                    $widget = new AYA_Dashboard_Server_Status();
                    echo $widget->memcached_usage_widget();
                }
            );
            wp_add_dashboard_widget(
                'dashboard_memcached_usage_efficiency_widget',
                'Memcached效率',
                function () {
                    $widget = new AYA_Dashboard_Server_Status();
                    echo $widget->memcached_usage_efficiency_widget();
                }
            );
        }
    }
}
