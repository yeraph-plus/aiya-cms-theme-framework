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
        'desc' => __('多域名插件', 'aiya-framework'),
        'type' => 'title_2',
    ],
    [
        'title' => __('前台多域名', 'aiya-framework'),
        'desc' => __('拓展插件，操作 WP 过滤器使站点前台可以使用多个域名，请填[b]不包含[/b] http:// 头的完整域名（不支持通配符），以 [code],[/code] 分隔', 'aiya-framework'),
        'id' => 'site_plugin_multi_domain',
        'type' => 'array',
        'default' => '',
    ]
);

add_action('template_redirect', 'aya_multi_domain_start_buffer', 0);

function aya_multi_domain_split($key, $default = [])
{
    $val = function_exists('aya_plugin_opt') ? aya_plugin_opt($key) : '';
    if (is_string($val) && $val !== '') {
        $list = preg_split('/[\s,]+/', $val, -1, PREG_SPLIT_NO_EMPTY);
        return is_array($list) ? $list : [];
    }
    return is_array($val) ? $val : $default;
}

function aya_multi_domain_get_current_host()
{
    if (empty($_SERVER['HTTP_HOST']) || !is_string($_SERVER['HTTP_HOST'])) {
        return '';
    }
    $host = trim($_SERVER['HTTP_HOST']);
    return preg_replace('/:\d+$/', '', $host);
}

function aya_multi_domain_get_allowed_domains()
{
    $domains = aya_multi_domain_split('site_plugin_multi_domain');
    if (!is_array($domains)) {
        return [];
    }

    $normalized = [];
    foreach ($domains as $domain) {
        if (!is_string($domain) || $domain === '') {
            continue;
        }
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = preg_replace('#/.*$#', '', $domain);
        $domain = preg_replace('/:\d+$/', '', $domain);
        if ($domain !== '') {
            $normalized[$domain] = true;
        }
    }

    return array_keys($normalized);
}

function aya_multi_domain_start_buffer()
{
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || is_feed() || is_trackback()) {
        return;
    }

    $current_host = strtolower(aya_multi_domain_get_current_host());
    if ($current_host === '') {
        return;
    }

    $allowed_domains = aya_multi_domain_get_allowed_domains();
    if (empty($allowed_domains) || !in_array($current_host, $allowed_domains, true)) {
        return;
    }

    $home_url = home_url('/');
    if (!is_string($home_url) || $home_url === '') {
        return;
    }

    $home_host = parse_url($home_url, PHP_URL_HOST);
    if (!is_string($home_host) || $home_host === '' || strtolower($home_host) === $current_host) {
        return;
    }

    ob_start(function ($buffer) use ($home_url, $home_host, $current_host) {
        if (!is_string($buffer) || $buffer === '') {
            return $buffer;
        }

        $target_home_url = str_replace($home_host, $current_host, $home_url);
        if (!is_string($target_home_url) || $target_home_url === $home_url) {
            return $buffer;
        }

        return str_replace($home_url, $target_home_url, $buffer);
    });
}
