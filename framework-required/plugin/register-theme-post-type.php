<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-Framework 组件 创建自定义文章类型插件
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

    public function __construct($args)
    {
        if (!is_array($args))
            return;

        $this->register_post_type = $args;

        add_action('init', [$this, 'aya_theme_register_post_type']);
        add_filter('the_posts', [$this, 'aya_theme_make_sticky_work_in_archives'], 10, 2);
    }

    public function aya_theme_register_post_type()
    {
        if (parent::inspect($this->register_post_type))
            return;

        foreach ($this->register_post_type as $type => $type_args) {

            $name = $type_args['name'];
            $slug = $type_args['slug'];
            $icon = $type_args['icon'];
            $public = isset($type_args['public']) ? (bool) $type_args['public'] : true;
            $has_archive = isset($type_args['has_archive']) ? $type_args['has_archive'] : true;
            $query_var = array_key_exists('query_var', $type_args) ? $type_args['query_var'] : true;

            // 支持的功能
            $supports = array('editor', 'author', 'title', 'custom-fields', 'comments');

            $rewrite = false;

            if ($public) {
                if (array_key_exists('rewrite', $type_args)) {
                    if ($type_args['rewrite'] === false) {
                        $rewrite = false;
                    } elseif (is_array($type_args['rewrite'])) {
                        $rewrite = wp_parse_args($type_args['rewrite'], array(
                            'slug' => $slug,
                            'with_front' => true,
                        ));
                    } else {
                        $rewrite = array(
                            'slug' => $slug,
                            'with_front' => true,
                        );
                    }
                } else {
                    $rewrite = array(
                        'slug' => $slug,
                        'with_front' => true,
                    );
                }
            }

            $labels = array(
                'name' => $name,
                'singular_name' => $name,
                'add_new' => __('发表', 'aiya-framework') . $name,
                'add_new_item' => __('发表', 'aiya-framework') . $name,
                'edit_item' => __('编辑', 'aiya-framework') . $name,
                'new_item' => __('新', 'aiya-framework') . $name,
                'view_item' => __('查看', 'aiya-framework') . $name,
                'search_items' => __('搜索', 'aiya-framework') . $name,
                'not_found' => __('暂无', 'aiya-framework') . $name,
                'not_found_in_trash' => __('没有已删除的', 'aiya-framework') . $name,
                'parent_item_colon' => '',
                'menu_name' => $name,
            );

            $args = array(
                'labels' => $labels,
                'public' => $public,
                'publicly_queryable' => $public,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => $query_var,
                'rewrite' => $rewrite,
                'capability_type' => 'post',
                'has_archive' => $has_archive,
                'hierarchical' => false,
                'menu_position' => null,
                'menu_icon' => $icon,
                'supports' => $supports,
            );

            register_post_type($type, $args);
        }
    }

    public function aya_theme_make_sticky_work_in_archives($posts, $query)
    {
        if (is_admin() || ! $query->is_main_query() || $query->is_feed()) {
            return $posts;
        }

        if ($query->get('paged') > 1) {
            return $posts;
        }

        foreach ($this->register_post_type as $type => $type_args) {
            if (!$query->is_post_type_archive($type)) {
                continue;
            }

            // 检查置顶设置
            $sticky_posts = get_option('sticky_posts');
            $sticky_posts = array_map('absint', $sticky_posts);

            if (empty($sticky_posts) || ! is_array($sticky_posts)) {
                return $posts;
            }

            $stickies = [];
            $non_stickies = [];

            foreach ($posts as $post) {
                if (in_array($post->ID, $sticky_posts, true)) {
                    $stickies[] = $post;
                } else {
                    $non_stickies[] = $post;
                }
            }

            if (is_user_logged_in()) {
                $current_user_id = get_current_user_id();
                $query_author_id = (int) $query->get('author');

                if ($query_author_id === $current_user_id) {
                    $non_published_posts = get_posts(array(
                        'post_type' => $type,
                        'post_status' => array('draft', 'pending', 'future', 'private', 'trash'),
                        'author' => $current_user_id,
                        'posts_per_page' => -1,
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'fields' => 'all',
                    ));

                    if (!empty($non_published_posts)) {
                        $existing_post_ids = array_map(function ($item) {
                            return (int) $item->ID;
                        }, $posts);

                        $non_published_posts = array_values(array_filter($non_published_posts, function ($item) use ($existing_post_ids) {
                            return !in_array((int) $item->ID, $existing_post_ids, true);
                        }));

                        if (!empty($non_published_posts)) {
                            $posts = array_merge($non_published_posts, $posts);
                        }
                    }
                }
            }

            if (! empty($stickies)) {
                $posts = array_merge($stickies, $non_stickies);
            }
        }

        return $posts;
    }
}
