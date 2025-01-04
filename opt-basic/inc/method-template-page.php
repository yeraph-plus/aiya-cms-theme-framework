<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 组件 自定义单页和路由模板
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Template_New_Page extends AYA_Theme_Setup
{
    public $register_page_type;
    public $rewrite_html;
    public $template_page_path;

    public function __construct($args)
    {
        $this->register_page_type = $args;
        //定义伪静态
        $this->rewrite_html = '.html';
        //定义模板位置
        $this->template_page_path = 'pages';
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_html_page_permalink', -1);
        parent::add_action('init', 'aya_theme_register_page_template');
        parent::add_filter('redirect_canonical', 'aya_theme_page_cancel_redirect_canonical');
        parent::add_filter('template_include', 'aya_page_template_include', 99);
    }
    //加载自定义模板
    function aya_page_template_include($template)
    {
        $page_type = get_query_var('page_type');

        global $aya_page_type;

        if ($page_type != '' && in_array($page_type, $aya_page_type)) {
            //定义模板位置
            $new_template = array();
            $new_template[] = $this->template_page_path . '/' . $page_type . '.php';

            $template = locate_template($new_template);

            //验证模板是否存在
            if ($template != '') {
                return $template;
            }
        }

        return $template;
    }
    //注册永久链接
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
    //注册新的页面模板
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
