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

class AYA_Plugin_Template_New_Page extends AYA_Framework_Setup
{
    public $route_page_type;

    public function __construct($args)
    {
        $this->route_page_type = $args;
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_custom_rewrite_page_rule');
        parent::add_filter('query_vars', 'aya_theme_register_page_query_vars');
        parent::add_filter('template_include', 'aya_theme_custom_route_template');
        parent::add_filter('redirect_canonical', 'aya_theme_page_cancel_redirect_canonical');
    }
    //注册路由
    public function aya_theme_custom_rewrite_page_rule()
    {
        //循环添加路由规则
        foreach ($this->route_page_type as $page => $value) {
            //使用最小查询参数
            add_rewrite_rule($page . '$', 'index.php?page_slug_vars=' . $page, 'top');
        }
    }
    //注册查询变量
    function aya_theme_register_page_query_vars($vars)
    {
        $vars[] = 'page_slug_vars';

        return $vars;
    }
    //强制加载自定义模板
    public function aya_theme_custom_route_template($template)
    {
        //获取到查询参数时
        $page_type = get_query_var('page_slug_vars');

        if ($page_type != '') {
            //找到参数对应的模板路径
            if (array_key_exists($page_type, $this->route_page_type)) {
                //定义模板位置
                $this_template_name = $this->route_page_type[$page_type] . '.php';
                $slug_template = locate_template($this_template_name, false, true);

                //验证模板是否存在
                if ($template != '') {
                    return $slug_template;
                }
            }
        }

        return $template;
    }
    //DEBUG：取消自定义页面自动重定向
    public function aya_theme_page_cancel_redirect_canonical($redirect_url)
    {
        //获取到查询参数时
        $page_type = get_query_var('page_slug_vars');

        if ($page_type != '') {
            return false;
        }

        return $redirect_url;
    }
}
