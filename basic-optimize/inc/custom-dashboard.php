<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 WP后台精简和自定义文字
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.1
 **/

class AYA_Plugin_Admin_Custom
{
    public $admin_options;

    public function __construct($args)
    {
        $this->admin_options = $args;
    }

    public function __destruct()
    {
        $options = $this->admin_options;

        //禁用前台顶部工具栏
        if ($options['remove_admin_bar'] == true) {
            add_action('show_admin_bar', '__return_false');
        }
        if ($options['admin_title_format'] == true) {
            add_filter('admin_title', array($this, 'aya_theme_custom_admin_title'), 10, 2);
            add_filter('login_title', array($this, 'aya_theme_custom_admin_title'), 10, 2);
        }
        //移除后台仪表盘欢迎模块和WordPress新闻
        if ($options['remove_admin_dashboard_wp_news'] == true) {
            add_action('wp_dashboard_setup', array($this, 'aya_theme_remove_dashboard_meta_box'));
        }
        //增加后台页脚左侧文字
        add_filter('admin_footer_text', array($this, 'aya_theme_custom_admin_footer'));
        //移除后台页脚WP版本提示
        add_filter('update_footer', '__return_false', 11);
        //移除后台右上角帮助
        add_action('in_admin_header', array($this, 'aya_theme_remove_help_tabs'));
        //注册工具栏自定义链接
        add_action('admin_bar_menu', array($this, 'aya_theme_custom_admin_bar_link'), 90);
    }
    //替换后台标题
    public function aya_theme_custom_admin_title($admin_title, $title)
    {
        //站点名 - 页面
        return get_bloginfo('name') . ' - ' . $title;
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
    //替换后台页脚信息
    public function aya_theme_custom_admin_footer($footer_text)
    {
        $options = $this->admin_options;

        if ($options['admin_footer_replace'] != '') {
            $theme_info_text = '<span id="footer-thankyou">/ ' . $this->admin_options['admin_footer_replace'] . '</span>';
        }

        echo $footer_text . $theme_info_text;
    }
    //隐藏右上角帮助
    public function aya_theme_remove_help_tabs()
    {
        global $current_screen;

        $current_screen->remove_help_tabs();
    }
    //注册工具栏自定义链接
    public function aya_theme_custom_admin_bar_link()
    {
        $options = $this->admin_options;

        global $wp_admin_bar;

        //禁用 WP-LOGO
        if ($options['remove_admin_bar_wp_logo'] == true) {
            $wp_admin_bar->remove_menu('wp-logo');
        }
        //禁用 评论提醒
        //$wp_admin_bar->remove_menu('comments');
        //禁用 更新提醒
        //$wp_admin_bar->remove_menu('updates');

        //注册新的菜单
        /*参数：
        $add_bar = array(
            'parent' => false, //'false'添加主层级，子级需要填写父级菜单ID
            'id' => 'order', //链接ID
            'title' => '订单', //链接标题
            'href' => admin_url('admin.php?page=orders'), //链接地址
            'meta' => false, // array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' )
        );
        */
        //$wp_admin_bar->add_menu($add_bar);
    }
}
