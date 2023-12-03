<?php
if (!defined('ABSPATH')) exit;

//Fix：全局链接附加的斜杠
function aya_theme_do_trailingslashit($string, $type)
{
    //排除文章和页面
    if (get_query_var('page_type')) return $string;
    if ($type == 'single' || $type == 'page') return $string;

    return trailingslashit($string);
}
add_filter('user_trailingslashit', 'aya_theme_do_trailingslashit', 10, 2);

//挂载页面的模板
function aya_theme_page_template_redirects()
{
    if (get_query_var('page_type')) {

        $page_type = get_query_var('page_type');

        get_template_part('template-parts/page/' . $page_type);

        exit;
    }
}
add_filter('template_redirect', 'aya_theme_page_template_redirects');

//禁止wp-sitemap.xml生成users列表
function remove_sitemaps_add_provider($provider, $name)
{
    return ($name == 'users') ? false : $provider;
}
add_filter('wp_sitemaps_add_provider', 'remove_sitemaps_add_provider', 10, 2);

//过滤掉函数 comment_class() 和 body_class() 中输出的 "comment-author-" 和 "author-"避免 WordPress 登录用户名被暴露
function comment_body_class($content)
{
    $content = preg_replace("/(.*?)([^>]*)author-([^>]*)(.*?)/i", '$1$4', $content);
    return $content;
}
add_filter('comment_class', 'comment_body_class');
add_filter('body_class', 'comment_body_class');

/**
 * ————————————————————
 * 《速效救心丸Pt.1》
 * ————————————————————
 * 用于解决Feed和Sitemap等各种XML中找不到问题在哪儿的的空白字符输出 -_-
 * 
 */
function xml_whitespace_fix($input)
{
    $allowed = false;
    $found = false;

    foreach (headers_list() as $header) {
        if (preg_match("/^content-type:\\s+(text\\/|application\\/((xhtml|atom|rss)\\+xml|xml))/i", $header)) {
            $allowed = true;
        }

        if (preg_match("/^content-type:\\s+/i", $header)) {
            $found = true;
        }
    }

    if ($allowed || !$found) {
        return preg_replace("/\\A\\s*/m", "", $input);
    } else {
        return $input;
    }
}
//直接将此方法添加到缓冲区
//ob_start('xml_whitespace_fix');

/**
 * ————————————————————
 * 《速效救心丸Pt.2》
 * ————————————————————
 * 压缩html节约流量
 * 
 */
function html_compress_main($buffer)
{
    $initial = strlen($buffer);
    $buffer = explode("<!--wp-compress-html-->", $buffer);
    $count = count($buffer);
    $out = "";
    for ($i = 0; $i <= $count; $i++) {
        if (stristr($buffer[$i], '<!--wp-compress-html no compression-->')) {
            $buffer[$i] = (str_replace("<!--wp-compress-html no compression-->", " ", $buffer[$i]));
        } else {
            $buffer[$i] = (str_replace("\t", " ", $buffer[$i]));
            $buffer[$i] = (str_replace("\n\n", "\n", $buffer[$i]));
            $buffer[$i] = (str_replace("\n", "", $buffer[$i]));
            $buffer[$i] = (str_replace("\r", "", $buffer[$i]));
            while (stristr($buffer[$i], '  ')) {
                $buffer[$i] = (str_replace("  ", " ", $buffer[$i]));
            }
        }
        $out .= $buffer[$i];
    }
    $final = strlen($out);
    $savings = ($initial - $final) / $initial * 100;
    $savings = round($savings, 2);
    $info = "<!--压缩前为:{$initial}bytes;压缩后为:{$final}bytes;节约:{$savings}%-->";
    return $out . $info;
}
//直接将此方法添加到缓冲区
//ob_start('html_compress_main');
