<?php

if (!defined('ABSPATH')) exit;

/*
 * Name: WP 简易SEO功能插件
 * Version: 1.0.0
 * Author: AIYA-CMS
 * Author URI: https://www.yeraph.com
 */

class AYA_Plugin_Head_SEO extends AYA_Theme_Setup
{
    public $seo_action;

    public function __construct($args)
    {
        $this->seo_action = $args;
    }

    public function __destruct()
    {
        $action = $this->seo_action;

        parent::add_action('pre_get_document_title', 'aya_theme_site_title');

        if ($action['site_seo_action'] == true) {
            //移除本页链接
            remove_action('wp_head', 'rel_canonical');
            parent::add_action('wp_head', 'aya_theme_site_seo_action');
            parent::add_action('wp_head', 'aya_theme_site_seo_canonical');
        };

        parent::add_filter('the_content', 'aya_theme_site_replace_text_wps');

        if ($action['site_seo_auto_add_tags'] == true) {
            parent::add_action('save_post', 'aya_theme_save_post_auto_add_tags');
        }
        if ($action['site_seo_auto_tag_link'] == true) {
            parent::add_filter('the_content', 'aya_theme_content_auto_tags_re_link');
        }
    }
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
            $head_title = __('你访问的资源不存在');
        }
        //首页
        else if (is_home() || is_front_page()) {
            $head_title = $site_title . $paged_title . ((!empty($sub_title)) ? $sep . $sub_title : '');
        }
        //搜索
        else if (is_search()) {
            $head_title = sprintf(__('搜索"%s"的结果'), get_search_query()) . $sep . $site_title;
        }
        //文章和页面
        else if (is_single() || is_page()) {
            $head_title = single_post_title('', false) . $sep . $site_title;
        }
        //附件
        else if (is_attachment()) {
            $head_title = __('附件：') . single_post_title('', false) . '' . $sep . $site_title;
        }
        //自定义文章类型的归档页面
        else if (is_post_type_archive()) {
            $head_title = post_type_archive_title('', false) . '' . $paged_title . $sep . $site_title;
        }
        //分类
        else if (is_category()) {
            $head_title = __('分类：') . single_term_title('', false) . '' . $paged_title . $sep . $site_title;
        }
        //标签
        else if (is_tag()) {
            $head_title = __('标签：') . single_term_title('#', false) . '' . $paged_title . $sep . $site_title;
        }
        //自定义分类法
        else if (is_tax()) {
            $head_title = __('归档：') . single_term_title('', false) . '' . $paged_title . $sep . $site_title;
        }
        //用户
        else if (is_author() && get_queried_object()) {
            $author = get_queried_object()->display_name;
            $head_title = __('用户：「') . $author . __('」') . '' . $paged_title . $sep . $site_title;
        }
        //归档（年）
        else if (is_year()) {
            $head_title = get_the_time('Y') . __('年的所有文章') . $paged_title . $sep . $site_title;
        }
        //归档（月）
        else if (is_month()) {
            $head_title = get_the_time('m') . __('月的所有文章') . $paged_title . $sep . $site_title;
        }
        //归档（日）
        else if (is_day()) {
            $head_title = get_the_time('Y-m-d') . __('的所有文章') . $paged_title . $sep . $site_title;
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

        if ($action['site_seo_action'] == false) return;

        //首页关键词
        $seo_keywords = $action['site_seo_keywords'];
        $seo_desc = $action['site_seo_description'];

        //文章页关键词
        if (is_single()) {
            $post = get_post();
            //提取Metabox中的设置
            $single_keywords = get_post_meta($post->ID, 'seo_keywords', true);
            //检查设置
            if ($single_keywords == null && empty(trim($single_keywords))) {
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
            $single_desc = get_post_meta($post->ID, 'seo_desc', true);
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
            $category_keywords = get_post_meta($category->term_id, 'seo_cat_keywords', true);
            //检查设置
            if (!empty(trim($category_keywords))) {
                $seo_keywords = $category_keywords;
            } else {
                $seo_keywords = $category->name;
            }
            //提取设置
            $category_desc = get_post_meta($category->term_id, 'seo_cat_desc', true);
            //检查设置
            if (!empty(trim($category_desc))) {
                $seo_desc = $category_desc;
            }
        }

        $head_seo = '<meta name="keywords" content="' . $seo_keywords . '" />' . "\n";
        $head_seo .= '<meta name="description" content="' . $seo_desc . '" />' . "\n";

        //输出
        echo $head_seo;
    }
    //配置canonical标签
    public function aya_theme_site_seo_canonical()
    {
        if (is_home()) {
            $url = home_url();
        } else {
            $url = get_permalink();
        }
        //输出
        echo '<link rel="canonical" href="' . $url . '" />' . "\n";
    }
    //文内关键词替换
    function aya_theme_site_replace_text_wps($content)
    {
        $action = $this->seo_action;

        if ($action['site_replace_text_wps'] !== '') {
            //重建数组
            $replace_input = $action['site_replace_text_wps'];

            //按换行符拆分
            $lines = explode("\n", $replace_input);

            $assoc_array = array();

            //遍历拆分为关联数组
            foreach ($lines as $line) {
                //清除空白字符
                $line = trim($line);

                if (!empty($line)) {
                    //按'|'拆分字符串
                    $parts = explode('|', $line);

                    //确保拆分后的数组有两个元素
                    $parts[0] = (empty($parts[0])) ? '' : $parts[0];
                    $parts[1] = (empty($parts[1])) ? '' : $parts[1];

                    //添加到结果数组中
                    $assoc_array[$parts[0]] = $parts[1];
                }
            }
            $content = str_replace(array_keys($assoc_array), $assoc_array, $content);

            return $content;
        }
    }
    //文章保存时自动触发动作添加标签
    public function aya_theme_save_post_auto_add_tags()
    {
        $tags = get_tags(array('hide_empty' => false));

        $post_id = get_the_ID();

        $post_content = get_post($post_id)->post_content;

        if ($tags) {
            foreach ($tags as $tag) {
                if (strpos($post_content, $tag->name) !== false)
                    wp_set_post_tags($post_id, $tag->name, true);
            }
        }
    }
    //文章载入时自动检索匹配的标签添加链接
    public function aya_theme_content_auto_tags_re_link($content)
    {
        //内置方法：标签按长度排序
        function tag_sort($a, $b)
        {
            if ($a->name == $b->name) return 0;
            return (strlen($a->name) > strlen($b->name)) ? -1 : 1;
        }

        //正则方法设置
        $match_num_from = 2;  //一个标签在文章中出现少于多少次不添加链接
        $match_num_to = 1; //一篇文章中同一个标签添加几次链接
        $exp_word = ''; //正则过滤参数
        $more_case = '';

        //获取全部标签
        $the_tags = get_the_tags();

        if ($the_tags) {
            //排序方法
            usort($the_tags, 'tag_sort');
            //循环
            foreach ($the_tags as $tag) {

                $link = get_tag_link($tag->term_id);

                $key_word = $tag->name;

                //生成链接
                $clean_key_word = stripslashes($key_word);

                $url = '<a href="' . $link . '" title="' . str_replace('%s', addcslashes($clean_key_word, '$'), __('更多%s相关文章')) . '" target="_blank">' . addcslashes($clean_key_word, '$') . '</a>';

                $limit = rand($match_num_from, $match_num_to);

                //过滤标签
                $content = preg_replace('|(<a[^>]+>)(.*)(' . $exp_word . ')(.*)(</a[^>]*>)|U' . $more_case, '$1$2%&&&&&%$4$5', $content);
                $content = preg_replace('|(<pre[^>]+>)(.*)(' . $exp_word . ')(.*)(</pre[^>]*>)|U' . $more_case, '$1$2%&&&&&%$4$5', $content);
                $content = preg_replace('|(<img)(.*?)(' . $exp_word . ')(.*?)(>)|U' . $more_case, '$1$2%&&&&&%$4$5', $content);

                $clean_key_word = preg_quote($clean_key_word, '\'');

                $reg_exp = '\'(?!((<.*?)|(<a.*?)|(<pre.*?)))(' . $clean_key_word . ')(?!(([^<>]*?)>)|([^>]*?</a>)|([^>]*?</pre>))\'s' . $more_case;

                $content = preg_replace($reg_exp, $url, $content, $limit);
                $content = str_replace('%&&&&&%', stripslashes($exp_word), $content);
            }
        }

        return $content;
    }
}
