<?php
if (!defined('ABSPATH')) exit;

/*
 * ------------------------------------------------------------------------------
 * 配置一些WordPress过滤器和动作
 * ------------------------------------------------------------------------------
 */

//添加钩子 URL自动附加反斜杠
add_filter('user_trailingslashit', 'ayf_filter_auto_trailingslashit', 10, 2);
//添加钩子 保存格式过滤 Tips：此钩子也在 post update() 和 post delete() 上触发，应当注意检查其他插件的兼容性
add_filter('wp_insert_post_data', 'ayf_filter_insert_post_data', 10, 3);

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
    $data['post_content_filtered'] = apply_filters('aya_insert_post_content_filtered', $data['post_content_filtered']);
    $data['post_content'] = apply_filters('aya_insert_post_content', $data['post_content']);

    return $data;
}
