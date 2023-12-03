<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Template_New_Page extends AYA_Theme_Setup
{
    var $register_page_type;
    var $rewrite_html;

    public function __construct($args)
    {
        $this->register_page_type = $args;

        if (parent::$tag_html) $this->rewrite_html = '.html';
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_html_page_permalink', -1);
        parent::add_action('init', 'aya_theme_register_page_template');
        parent::add_filter('redirect_canonical', 'aya_theme_page_cancel_redirect_canonical');
    }

    public function aya_theme_html_page_permalink()
    {
        if (get_query_var('page_type')) return;

        $html = $this->rewrite_html;

        global $wp_rewrite;

        //防止循环
        if (!strpos($wp_rewrite->get_page_permastruct(), $html)) {

            $wp_rewrite->page_structure = $wp_rewrite->page_structure . $html;
        }
    }

    public function aya_theme_register_page_template()
    {
        $pages = $this->register_page_type;

        if (parent::inspect($pages)) return;

        global $page_type, $aya_page_type;

        //添加到全局变量
        $aya_page_type = $pages;
        //循环
        foreach ($pages as $page => $value) {
            $html = ($value) ? $this->rewrite_html : null;

            //添加路由规则
            add_rewrite_rule($page . $html . '$', 'index.php?post_type=' . $page, 'top');
            //应用过滤器
            $page_type = apply_filters('page_type', $page);
        }
    }
    //DEBUG：禁用页面自动重定向
    public function aya_theme_page_cancel_redirect_canonical($redirect_url)
    {
        if (get_query_var('page_type')) return false;

        return $redirect_url;
    }
}
