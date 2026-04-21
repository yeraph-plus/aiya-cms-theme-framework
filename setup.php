<?php

if (!defined('ABSPATH')) {
    exit;
}


if (my_theme_is_traditional_chinese()) {
    // 1. 文章正文
    add_filter('the_content', 'my_theme_opencc_convert', 999);
    
    // 2. 文章标题
    add_filter('the_title', 'my_theme_opencc_convert', 999);
    
    // 3. 文章摘要
    add_filter('the_excerpt', 'my_theme_opencc_convert', 999);
    add_filter('get_the_excerpt', 'my_theme_opencc_convert', 999);
    
    // 4. 页面标题（浏览器标签页上的标题）
    add_filter('document_title', 'my_theme_opencc_convert', 999);
    add_filter('wp_title', 'my_theme_opencc_convert', 999);
    
    // 5. 菜单项名称
    add_filter('nav_menu_item_title', 'my_theme_opencc_convert', 999);
    
    // 6. 小工具文本
    add_filter('widget_text', 'my_theme_opencc_convert', 999);
    add_filter('widget_title', 'my_theme_opencc_convert', 999);
    
    // 7. 评论内容
    add_filter('comment_text', 'my_theme_opencc_convert', 999);
    add_filter('get_comment_text', 'my_theme_opencc_convert', 999);
    
    // 8. 分类/标签名称
    add_filter('get_term', function($term) {
        if (isset($term->name)) {
            $term->name = my_theme_opencc_convert($term->name);
        }
        return $term;
    }, 999);
}
