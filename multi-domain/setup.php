<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-CMS 主题拓展 多域名兼容
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.1
 * 
 **/

//插件设置
aya_add_plugin_opt(
    [
        'desc' => '多域名绑定',
        'type' => 'title_2',
    ],
    [
        'title' => '前台多域名',
        'desc' => '拓展插件，操作 WP 过滤器使站点前台可以使用多个域名，请填[b]不包含[/b] http:// 头的完整域名（不支持通配符），以 [code],[/code] 分隔',
        'id' => 'site_plugin_multi_domain',
        'type' => 'array',
        'default' => '',
    ]
);

//使用过滤器替换站点设置
add_action('init', 'aya_handle_multi_domain_filter');

function aya_handle_multi_domain_filter()
{
    // 跳过后台管理页面（除了AJAX和REST请求）
    if (is_admin()) {
        return;
    }

    $current_host = $_SERVER['HTTP_HOST'] ?? '';

    //获取主域名
    $primary_domain = parse_url(get_option('siteurl'), PHP_URL_HOST);

    if (!$primary_domain || ($current_host === $primary_domain)) {
        return;
    }

    //设置的域名白名单
    $allowed_domains = aya_plugin_opt('site_plugin_multi_domain');

    if (!is_array($allowed_domains) && empty($allowed_domains)) {
        return;
    }

    if (empty($allowed_domains) || !in_array($current_host, $allowed_domains)) {
        return;
    }

    //域名替换
    //echo $current_host . $primary_domain;
    $current_ssl = (is_ssl()) ? 'https://' : 'http://';

    //过滤器替换
    add_filter('pre_option_home', function () use ($current_host, $current_ssl) {
        return $current_ssl . $current_host;
    });

    add_filter('pre_option_siteurl', function () use ($current_host, $current_ssl) {
        return $current_ssl . $current_host;
    });

    add_filter('theme_root_uri', function ($theme_root_uri, $siteurl, $stylesheet_or_template) use ($current_host, $primary_domain) {
        return str_replace($primary_domain, $current_host, $theme_root_uri);
    }, 10, 3);

    add_filter('the_content', function ($content) use ($primary_domain, $current_host) {
        return str_replace($primary_domain, $current_host, $content);
    }, 99);

    //发生域名替换的情况下，配置wp_redirect过滤器
    /*
    add_filter('wp_redirect', function ($location, $status) use ($current_host, $primary_domain, $allowed_domains) {
        return aya_filter_redirect_url($location, $status, $current_host, $primary_domain, $allowed_domains);
    }, 10, 2);
    */
}

//重定向URL时添加的过滤器
function aya_filter_redirect_url($location, $status, $current_host, $primary_domain, $allowed_domains)
{
    if (empty($location) || !is_string($location)) {
        return $location;
    }

    //如果重定向URL的域名不在允许的域名列表中
    if (!in_array($current_host, $allowed_domains)) {
        return $location;
    }

    //解析重定向URL
    $parsed_url = parse_url($location);

    //如果是相对URL，直接返回
    if (!isset($parsed_url['host'])) {
        return $location;
    }

    $redirect_host = $parsed_url['host'];

    //记录重定向到日志
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Multi domain redirect: {$redirect_host} -> {$current_host}");
    }

    //替换为当前访问的域名
    if ($redirect_host === $primary_domain) {
        $new_location = str_replace($primary_domain, $current_host, $location);

        //DEBUG: 验证新URL的有效性
        if (filter_var($new_location, FILTER_VALIDATE_URL)) {
            return $new_location;
        }
    }

    //其他情况时不处理
    return $location;
}
