<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-CMS 主题图片处理依赖
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 3.0
 * 
 **/

use Overtrue\PHPOpenCC\OpenCC;

add_action('after_setup_theme', 'aya_opencc_bootstrap', 5);

// 判断OpenCC转换策略
function aya_opencc_get_strategy()
{
    $locale = str_replace('-', '_', (string)  aya_get_user_locale());

    if (stripos($locale, 'zh_HK') === 0) {
        return 's2hk';
    }

    if (stripos($locale, 'zh_TW') === 0) {
        return 's2tw';
    }

    return false;
}

// 主题OpenCC转换方法
function aya_opencc_convert($content)
{
    $strategy = aya_opencc_get_strategy();

    if (!is_string($content) || $content === '' || $strategy === false) {
        return $content;
    }

    static $converter = null;

    if ($converter === null) {
        $converter = new OpenCC();
    }

    return $converter->convert($content, strtoupper($strategy));
}

// 分类和标签的字段转换
function aya_opencc_convert_term($term)
{
    if (is_object($term) && isset($term->name) && is_string($term->name)) {
        $term->name = aya_opencc_convert($term->name);
    }

    return $term;
}

// 主题自动转换过滤器挂载
function aya_opencc_bootstrap()
{
    if (is_admin() || aya_opencc_get_strategy() === false) {
        return;
    }

    add_filter('the_content', 'aya_opencc_convert', 999);
    add_filter('the_title', 'aya_opencc_convert', 999);
    add_filter('the_excerpt', 'aya_opencc_convert', 999);
    add_filter('get_the_excerpt', 'aya_opencc_convert', 999);
    add_filter('document_title', 'aya_opencc_convert', 999);
    add_filter('wp_title', 'aya_opencc_convert', 999);
    add_filter('nav_menu_item_title', 'aya_opencc_convert', 999);
    add_filter('widget_text', 'aya_opencc_convert', 999);
    add_filter('widget_title', 'aya_opencc_convert', 999);
    add_filter('comment_text', 'aya_opencc_convert', 999);
    add_filter('get_comment_text', 'aya_opencc_convert', 999);
    add_filter('get_term', 'aya_opencc_convert_term', 999);
}
