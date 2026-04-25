<?php

if (!defined('ABSPATH'))
    exit;

/**
 * AIYA-Framework 拓展 简易SEO功能插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.4
 **/

class AYA_Plugin_Head_SEO
{
    public $seo_action;

    public function __construct($args)
    {
        $this->seo_action = $args;

        $action = $this->seo_action;

        add_action('pre_get_document_title', array($this, 'aya_theme_site_title'));

        if ($action['site_seo_action'] == true) {
            add_action('wp_head', array($this, 'aya_theme_site_seo_action'));
        }
        if ($action['site_seo_auto_replace'] == true) {
            add_filter('the_content', array($this, 'aya_theme_site_replace_text_wps'));
        }
        if ($action['site_seo_auto_tag_link'] == true) {
            add_filter('the_content', array($this, 'aya_theme_content_auto_tags_re_link'));
        }
        if ($action['site_seo_robots_switch'] && $action['site_seo_robots_txt'] != '') {
            add_filter('robots_txt', array($this, 'aya_theme_custom_robots_txt'), 10, 2);
        }
    }

    public function __destruct() {}

    //站点标题选择器
    public function aya_theme_site_title($title)
    {
        //弃用原本的Title格式

        $action = $this->seo_action;
        //配置Title
        $site_title = ($action['site_title'] !== '') ? $action['site_title'] : get_bloginfo('name');
        //配置副标题
        $sub_title = ($action['site_title_sub'] !== '') ? $action['site_title_sub'] : get_bloginfo('description');
        $sub_title = ($action['site_title_sub_true'] == true) ? $sub_title : '';
        //配置分隔符
        $sep_title = $action['site_title_sep'];

        //标题分隔符
        switch ($sep_title) {
            case 'nbsp':
                $sep = ' ';
                break;
            case 'hyphen':
                $sep = ' - ';
                break;
            case 'y-line':
                $sep = ' | ';
                break;
            case 'u-line':
                $sep = '_';
                break;
            default:
                $sep = ' - ';
                break;
        }

        //标题页码
        $paged_title = '';
        if (get_query_var('paged') && get_query_var('paged') >= 2) {
            $paged_title = $sep . __('第') . get_query_var('paged') . __('页');
        }

        //开始创建标题
        if (is_404()) {
            $head_title = __('你访问的资源不存在', 'aiya-framework');
        }
        //首页
        else if (is_home() || is_front_page()) {
            $head_title = $site_title . $paged_title . ((!empty($sub_title)) ? $sep . $sub_title : '');
        }
        //搜索
        else if (is_search()) {
            $head_title = sprintf(__('搜索"%s"的结果', 'aiya-framework'), get_search_query()) . $sep . $site_title;
        }
        //文章和页面
        else if (is_single() || is_page()) {
            $head_title = single_post_title('', false) . $sep . $site_title;
        }
        //附件
        else if (is_attachment()) {
            $head_title = __('附件：', 'aiya-framework') . single_post_title('', false) . '' . $sep . $site_title;
        }
        //自定义文章类型的归档页面
        else if (is_post_type_archive()) {
            $head_title = post_type_archive_title('', false) . '' . $paged_title . $sep . $site_title;
        }
        //分类
        else if (is_category()) {
            $head_title = __('分类：', 'aiya-framework') . single_term_title('', false) . '' . $paged_title . $sep . $site_title;
        }
        //标签
        else if (is_tag()) {
            $head_title = __('标签：', 'aiya-framework') . single_term_title('#', false) . '' . $paged_title . $sep . $site_title;
        }
        //自定义分类法
        else if (is_tax()) {
            $head_title = __('归档：', 'aiya-framework') . single_term_title('', false) . '' . $paged_title . $sep . $site_title;
        }
        //用户
        else if (is_author() && get_queried_object()) {
            $author = get_queried_object()->display_name;
            $head_title = __('用户：「', 'aiya-framework') . $author . __('」', 'aiya-framework') . '' . $paged_title . $sep . $site_title;
        }
        //归档（年）
        else if (is_year()) {
            $head_title = get_the_time('Y') . __('年的所有文章', 'aiya-framework') . $paged_title . $sep . $site_title;
        }
        //归档（月）
        else if (is_month()) {
            $head_title = get_the_time('m') . __('月的所有文章', 'aiya-framework') . $paged_title . $sep . $site_title;
        }
        //归档（日）
        else if (is_day()) {
            $head_title = get_the_time('Y-m-d') . __('的所有文章', 'aiya-framework') . $paged_title . $sep . $site_title;
        }
        //默认首页标题
        else {
            $head_title = $site_title . ((!empty($sub_title)) ? $sep . $sub_title : '');
        }
        return $head_title;
        //输出
        //echo '<title>' . $head_title . '</title>' . "\n";
    }

    //SEO功能
    public function aya_theme_site_seo_action()
    {
        $action = $this->seo_action;

        if ($action['site_seo_action'] == false)
            return;

        //首页关键词
        $seo_keywords = $action['site_seo_keywords'];
        $seo_desc = $action['site_seo_description'];

        //文章页关键词
        if (is_single()) {
            $post = get_post();
            //提取Metabox中的设置
            $single_keywords = AYF::get_post_meta('seo_keywords', 'post_seo', $post->ID);
            //检查设置
            if ($single_keywords != null && !empty(trim($single_keywords))) {
                $seo_keywords = trim($single_keywords);
            } else {
                //提取文章标签
                $tags_list = get_the_tags();
                //检查标签
                if ($tags_list != null && count($tags_list) > 0) {
                    $tags_str = "";
                    //格式化数组
                    foreach (get_the_tags() as $tag_item) {
                        $tags_str .= $tag_item->name . ',';
                    };
                    $tags_str = substr($tags_str, 0, strlen($tags_str) - 1);
                    //输出
                    $seo_keywords = $tags_str;
                }
            }
            //提取Metabox中的设置
            $single_desc = AYF::get_post_meta('seo_desc', 'post_seo', $post->ID);
            //检查设置
            if ($single_desc != null && !empty(trim($single_desc))) {
                $seo_desc = trim($single_desc);
            } else {
                //提取文章内容
                $seo_desc = wp_trim_words(do_shortcode(get_the_content($post->ID)), 147, '...');
            }
        }
        //分类页关键词
        if (is_category()) {
            $category = get_category(get_query_var('cat'), false);
            //提取设置
            $category_keywords = AYF::get_term_meta('seo_cat_keywords', $category->term_id);
            //检查设置
            if (!empty(trim($category_keywords))) {
                $seo_keywords = $category_keywords;
            } else {
                $seo_keywords = $category->name;
            }
            //提取设置
            $category_desc = AYF::get_term_meta('seo_cat_desc', $category->term_id);
            //检查设置
            if (!empty(trim($category_desc))) {
                $seo_desc = $category_desc;
            } else if (!empty($category->description)) {
                $seo_desc = $category->description;
            }
        }

        $seo_keywords = wp_strip_all_tags((string) $seo_keywords);
        $seo_desc = wp_strip_all_tags((string) $seo_desc);

        $head_seo = '';
        if ($seo_keywords !== '') {
            $head_seo .= '<meta name="keywords" content="' . esc_attr($seo_keywords) . '" />' . "\n";
        }
        if ($seo_desc !== '') {
            $head_seo .= '<meta name="description" content="' . esc_attr($seo_desc) . '" />' . "\n";
        }

        //输出
        echo $head_seo;
    }

    //配置canonical标签
    public function aya_theme_site_seo_canonical()
    {
        $url = wp_get_canonical_url();
        if (empty($url)) {
            if (is_home()) {
                $url = home_url();
            } else {
                $url = get_permalink();
            }
        }
        //输出
        if (!empty($url)) {
            echo '<link rel="canonical" href="' . esc_url($url) . '" />' . "\n";
        }
    }

    //robots.txt
    public function aya_theme_custom_robots_txt($output, $public)
    {
        $action = $this->seo_action;

        //替换为自定义输出
        $output = sanitize_textarea_field($action['site_seo_robots_txt']);
        return $output;
    }

    //额外内容过滤器

    //文内关键词替换
    function aya_theme_site_replace_text_wps($content)
    {
        $action = $this->seo_action;

        //重建数组
        $replace_input = trim($action['site_replace_text_wps']);
        //按换行符拆分
        if (!empty($replace_input)) {
            $lines = explode("\n", $replace_input);
        } else {
            return $content;
        }

        $assoc_array = array();
        //遍历拆分为关联数组
        foreach ($lines as $line) {
            $line = trim($line);

            if (!empty($line)) {
                //按'|'拆分字符串
                $parts = explode('|', $line);

                //确保拆分后的数组有两个元素
                $parts[0] = (empty($parts[0])) ? 'NULL' : $parts[0];
                $parts[1] = (empty($parts[1])) ? 'NULL' : $parts[1];

                //添加到结果数组中
                $assoc_array[$parts[0]] = $parts[1];
            }
        }
        $content = str_replace(array_keys($assoc_array), $assoc_array, $content);


        return $content;
    }

    //文章载入时自动检索匹配的标签添加链接
    public function aya_theme_content_auto_tags_re_link($content)
    {
        if (!is_singular()) {
            return $content;
        }

        $post_id = get_the_ID();
        if (empty($post_id)) {
            return $content;
        }

        $the_tags = wp_get_post_terms($post_id, 'post_tag');
        if (empty($the_tags) || is_wp_error($the_tags)) {
            return $content;
        }

        usort($the_tags, function ($a, $b) {
            if ($a->name === $b->name) {
                return 0;
            }
            $len_a = function_exists('mb_strlen') ? mb_strlen($a->name) : strlen($a->name);
            $len_b = function_exists('mb_strlen') ? mb_strlen($b->name) : strlen($b->name);
            if ($len_a === $len_b) {
                return strcmp($a->name, $b->name);
            }
            return ($len_a > $len_b) ? -1 : 1;
        });

        if (!class_exists('DOMDocument')) {
            return $content;
        }

        $min_occurrences = 2;
        $max_links_per_tag = 1;

        $charset = get_bloginfo('charset');
        if (empty($charset)) {
            $charset = 'UTF-8';
        }

        $wrapped = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=' . esc_attr($charset) . '"></head><body><div id="aya-seo-wrap">' . $content . '</div></body></html>';

        $previous_libxml = libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $loaded = $doc->loadHTML($wrapped, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous_libxml);

        if (!$loaded) {
            return $content;
        }

        $xpath = new DOMXPath($doc);

        foreach ($the_tags as $tag) {
            if ($max_links_per_tag < 1) {
                break;
            }

            $keyword = (string) $tag->name;
            $keyword = trim($keyword);
            if ($keyword === '') {
                continue;
            }

            $text_nodes = $xpath->query('//div[@id="aya-seo-wrap"]//text()[not(ancestor::a) and not(ancestor::pre) and not(ancestor::code) and not(ancestor::script) and not(ancestor::style) and not(ancestor::textarea)]');
            if (!$text_nodes || $text_nodes->length === 0) {
                continue;
            }

            $total_occurrences = 0;
            foreach ($text_nodes as $node) {
                $total_occurrences += $this->aya_theme_mb_substr_count($node->nodeValue, $keyword);
                if ($total_occurrences >= $min_occurrences) {
                    break;
                }
            }

            if ($total_occurrences < $min_occurrences) {
                continue;
            }

            $link = get_tag_link($tag->term_id);
            if (empty($link) || is_wp_error($link)) {
                continue;
            }

            $title_attr = sprintf(__('更多%s相关文章', 'aiya-framework'), $keyword);
            $links_added = 0;

            $text_nodes = $xpath->query('//div[@id="aya-seo-wrap"]//text()[not(ancestor::a) and not(ancestor::pre) and not(ancestor::code) and not(ancestor::script) and not(ancestor::style) and not(ancestor::textarea)]');
            if (!$text_nodes || $text_nodes->length === 0) {
                continue;
            }

            foreach ($text_nodes as $node) {
                if ($links_added >= $max_links_per_tag) {
                    break;
                }

                $text = (string) $node->nodeValue;
                $pos = $this->aya_theme_mb_strpos($text, $keyword);
                if ($pos === false) {
                    continue;
                }

                $before = $this->aya_theme_mb_substr($text, 0, $pos);
                $after = $this->aya_theme_mb_substr($text, $pos + $this->aya_theme_mb_strlen($keyword));

                $parent = $node->parentNode;
                if (!$parent) {
                    continue;
                }

                if ($before !== '') {
                    $parent->insertBefore($doc->createTextNode($before), $node);
                }

                $a = $doc->createElement('a');
                $a->setAttribute('href', esc_url($link));
                $a->setAttribute('title', esc_attr($title_attr));
                $a->appendChild($doc->createTextNode($keyword));
                $parent->insertBefore($a, $node);

                if ($after !== '') {
                    $parent->insertBefore($doc->createTextNode($after), $node);
                }

                $parent->removeChild($node);
                $links_added++;
            }
        }

        $wrap_query = $xpath->query('//div[@id="aya-seo-wrap"]');
        $wrap = ($wrap_query && $wrap_query->length > 0) ? $wrap_query->item(0) : null;
        if (!$wrap) {
            return $content;
        }

        $out = '';
        foreach ($wrap->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    private function aya_theme_mb_strlen($str)
    {
        return function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);
    }

    private function aya_theme_mb_strpos($haystack, $needle)
    {
        return function_exists('mb_strpos') ? mb_strpos($haystack, $needle) : strpos($haystack, $needle);
    }

    private function aya_theme_mb_substr($str, $start, $length = null)
    {
        if (function_exists('mb_substr')) {
            if ($length === null) {
                return mb_substr($str, $start);
            }
            return mb_substr($str, $start, $length);
        }

        if ($length === null) {
            return substr($str, $start);
        }
        return substr($str, $start, $length);
    }

    private function aya_theme_mb_substr_count($haystack, $needle)
    {
        if ($needle === '') {
            return 0;
        }
        if (function_exists('mb_substr_count')) {
            return mb_substr_count($haystack, $needle);
        }

        $count = 0;
        $offset = 0;
        $needle_len = strlen($needle);
        while (true) {
            $pos = strpos($haystack, $needle, $offset);
            if ($pos === false) {
                break;
            }
            $count++;
            $offset = $pos + $needle_len;
        }
        return $count;
    }
}
