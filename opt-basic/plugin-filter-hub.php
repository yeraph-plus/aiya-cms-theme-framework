<?php
if (!defined('ABSPATH')) exit;

/*
 * ------------------------------------------------------------------------------
 * 配置一些WordPress过滤器和动作
 * ------------------------------------------------------------------------------
 */

//将 WordPress 的 AJAX URL 和 nonce 传递给前端
//add_action('wp_enqueue_scripts', 'ayf_ajax_enqueue_scripts');

function ayf_ajax_enqueue_scripts()
{
    wp_localize_script('ajax-script', 'ajax_url', array(
        'home_url' => home_url(),
        'ajax_url' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce('ajax_nonce'),
    ));
}

//添加钩子 URL自动附加反斜杠
add_filter('user_trailingslashit', 'ayf_filter_auto_trailingslashit', 10, 2);
//添加钩子 保存格式过滤 Tips：此钩子也在 post_update() 和 post_delete() 上触发，应当注意检查其他插件的兼容性
//add_filter('wp_insert_post_data', 'ayf_filter_insert_post_data', 10, 3);
//添加钩子 排除评论表单字段
//add_filter('comment_form_default_fields', 'ayf_filter_insert_comment_form_unset_field');
//添加钩子 过滤评论和body输出html的css
add_filter('body_class', 'ayf_filter_insert_body_class');
add_filter('comment_class', 'ayf_filter_insert_body_class');
//添加钩子 过滤菜单和页面输出html的css
//add_filter('nav_menu_css_class', 'ayf_filter_insert_menu_class');
//add_filter('nav_menu_item_id', 'ayf_filter_insert_menu_class');
//add_filter('page_css_class', 'ayf_filter_insert_menu_class');
//URL自动附加反斜杠
function ayf_filter_auto_trailingslashit($string, $type)
{
    //排除文章和页面
    if (get_query_var('page_type')) return $string;

    if ($type == 'single' || $type == 'page') return $string;

    //使用WP内置过滤器
    $string = trailingslashit($string);

    return $string;
}
//注册两个文章保存格式的过滤器
function ayf_filter_insert_post_data($data, $postarr, $unsanitized_postarr)
{
    $data['post_content_filtered'] = apply_filters('ayf_insert_post_content_filtered', $data['post_content_filtered']);
    $data['post_content'] = apply_filters('ayf_insert_post_content', $data['post_content']);

    return $data;
}
//排除评论表单站点字段
function ayf_filter_insert_comment_form_unset_field($fields)
{
    if (isset($fields['url'])) unset($fields['url']);

    return $fields;
}
//过滤body的css
function ayf_filter_insert_body_class($classes)
{
    $content = preg_replace("/(.*?)([^>]*)author-([^>]*)(.*?)/i", '$1$4', $classes);
    return $content;
}
//过滤菜单的css
function ayf_filter_insert_menu_class($classes)
{
    $can_sect = array(
        //'current-menu-item',
        //'current-post-ancestor',
        //'current-menu-ancestor',
        //'current-menu-parent',
        //'menu-item-has-children',
        'menu-item'
    );
    return is_array($classes) ? array_intersect($classes, $can_sect) : '';
}
