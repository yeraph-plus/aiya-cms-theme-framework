<?php

if (!defined('ABSPATH')) exit;

/*
 * Name: 创建自定义文章类型
 * Version: 1.0.0
 * Author: AIYA-CMS
 * Author URI: https://www.yeraph.com
 */

class AYA_Plugin_Register_Post_Type extends AYA_Theme_Setup
{
    public $register_post_type;
    public $rewrite_html;

    public function __construct($args)
    {
        $this->register_post_type = $args;
        //定义伪静态
        $this->rewrite_html = '.html';
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_register_post_type');

        parent::add_filter('post_type_link', 'aya_theme_custom_post_link', 1, 3);
    }

    public function aya_theme_register_post_type()
    {
        $types = $this->register_post_type;
        $html = $this->rewrite_html;

        if (parent::inspect($types)) return;

        global $aya_post_type;
        $aya_post_type = array();

        //循环
        foreach ($types as $type => $type_args) {

            $name = $type_args['name'];
            $slug = $type_args['slug'];
            $icon = $type_args['icon'];

            //向全局添加
            $aya_post_type[] = $slug;

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
            add_rewrite_rule('' . $slug . '/([0-9]+)?' . $html . '$', 'index.php?post_type=' . $slug . '&p=$matches[1]', 'top');
            //评论规则
            add_rewrite_rule('' . $slug . '/([0-9]+)?' . $html . '/comment-page-([0-9]{1,})$', 'index.php?post_type=' . $slug . '&p=$matches[1]&cpage=$matches[2]', 'top');
        }
    }
    //定义自定义文章的内页路径
    function aya_theme_custom_post_link($link, $post = 0)
    {
        global $aya_post_type;

        $html = $this->rewrite_html;

        $post = get_post($post);
        //比对
        if (is_object($post) && in_array($post->post_type, $aya_post_type)) {
            return home_url('' . $post->post_type . '/' . $post->ID . $html);
        } else {
            return $link;
        }
    }
}
