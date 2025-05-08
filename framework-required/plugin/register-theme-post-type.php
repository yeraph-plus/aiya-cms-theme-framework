<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 组件 创建自定义文章类型
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Register_Post_Type extends AYA_Framework_Setup
{
    public $register_post_type;
    public $rewrite_html;

    public function __construct($args, $rewrite_static = true)
    {
        if (!is_array($args)) return;

        $this->register_post_type = $args;

        //添加伪静态
        if (is_bool($rewrite_static) && $rewrite_static) {
            //定义伪静态
            $this->rewrite_html = '.html';
        } else {
            $this->rewrite_html = '';
        }
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_register_post_type');

        parent::add_filter('post_type_link', 'aya_theme_custom_post_link', 1, 3);
    }

    public function aya_theme_post_show_in_homepage($type_args)
    {
        //声明一个全局变量
        if (!isset($GLOBALS['aya_post_type']) || empty($GLOBALS['aya_post_type'])) {
            $GLOBALS['aya_post_type'] = array();
        }
        //注册到全局变量
        if (isset($type_args['in_homepage']) && is_bool($type_args['in_homepage']) && $type_args['in_homepage']) {
            $GLOBALS['aya_post_type'][] = $type_args['slug'];
        }

        return $type_args;
    }

    public function aya_theme_register_post_type()
    {
        if (parent::inspect($this->register_post_type)) return;

        //循环
        foreach ($this->register_post_type as $type => $type_args) {

            //是否加载到首页
            $in_homepage = self::aya_theme_post_show_in_homepage($type_args);

            $name = $type_args['name'];
            $slug = $type_args['slug'];
            $icon = $type_args['icon'];

            //组装文章类型参数
            $labels = array(
                'name' => $name,
                'singular_name' => $name,
                'add_new' => __('发表') . $name,
                'add_new_item' => __('发表') . $name,
                'edit_item' => __('编辑') . $name,
                'new_item' => __('新') . $name,
                'view_item' => __('查看') . $name,
                'search_items' => __('搜索') . $name,
                'not_found' => __('暂无') . $name,
                'not_found_in_trash' => __('没有已删除的') . $name,
                'parent_item_colon' => '',
                'menu_name' => $name,
            );
            $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => array(
                    'slug' => $slug,
                    'with_front' => false
                ),
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => null,
                'menu_icon' => $icon,
                'supports' => array('editor', 'author', 'title', 'custom-fields', 'comments'),
            );

            register_post_type($type, $args);
            //添加路由规则
            add_rewrite_rule('' . $slug . '/([0-9]+)?' . $this->rewrite_html . '$', 'index.php?post_type=' . $slug . '&p=$matches[1]', 'top');
            //评论规则
            add_rewrite_rule('' . $slug . '/([0-9]+)?' . $this->rewrite_html . '/comment-page-([0-9]{1,})$', 'index.php?post_type=' . $slug . '&p=$matches[1]&cpage=$matches[2]', 'top');
        }
    }

    //定义自定义文章的内页链接
    function aya_theme_custom_post_link($link, $post = 0)
    {
        $post = get_post($post);

        //比对
        if (is_object($post) && in_array($post->post_type, $GLOBALS['aya_post_type'])) {
            return home_url('' . $post->post_type . '/' . $post->ID . $this->rewrite_html);
        } else {
            return $link;
        }
    }
}
