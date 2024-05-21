<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

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

        parent::add_action('wp_head', 'aya_theme_head_action');

        if ($action['seo_action'] == true) {
            parent::add_action('wp_head', 'aya_theme_head_seo_action');
            parent::add_filter('robots_txt', 'custom_robots_txt_filter', 10, 2);
            parent::add_filter('wp_sitemaps_add_provider', 'remove_sitemaps_add_provider', 10, 2);
        }
    }
    //输出head标签
    public function aya_theme_head_action()
    {
        echo self::site_favicon();
        echo self::site_title();
    }
    public function aya_theme_head_seo_action()
    {
        echo self::site_seo();
        echo self::site_seo_canonical();
    }
    //配置robots.txt
    public function custom_robots_txt_filter($output, $public)
    {
        $action = $this->seo_action;

        if ($action['custom_robots_true'] && $action['custom_robots_txt'] != '') {
            //替换为自定义输出
            $output = esc_attr(wp_strip_all_tags($action['custom_robots_txt']));
        }
        return $output;
    }
    //Sitemap中跳过users列表
    public function remove_sitemaps_add_provider($provider, $name)
    {
        $action = $this->seo_action;

        if ($action['remove_sitemaps_provider']) {
            return ($name == 'users') ? false : $provider;
        }
        return ($name == 'users') ? true : $provider;
    }
    //favicon.ico
    private function site_favicon()
    {
        $action = $this->seo_action;

        //检查设置
        $favicon_url = $action['site_favicon_url'];

        if ($favicon_url != '') {
            $head = '';
            //配置favicon.ico
            $head .= '<link rel="icon" type="image/png" href="' . $favicon_url . '" />' . "\n";
            $head .= '<meta name="msapplication-TileColor" content="#ffffff">' . "\n";
            $head .= '<meta name="msapplication-TileImage" content="' . $favicon_url . '">' . "\n";
            $head .= '<link rel="apple-touch-icon" href="' . $favicon_url . '" />' . "\n";
            $head .= '<meta name="apple-mobile-web-app-title" content="' . get_bloginfo('name') . '">' . "\n";
            $head .= '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
            $head .= '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";

            return $head;
        }
    }
    //站点标题选择器
    private function site_title()
    {
        $action = $this->seo_action;
        //配置Title
        $site_title = ($action['site_title'] == '') ? get_bloginfo('name') : $action['site_title'];
        //配置副标题
        $sub_title = ($action['site_subtitle'] == '') ? get_bloginfo('description') : $action['site_subtitle'];
        //标题分隔符
        $sep_space = ($action['title_sep_space']) ? ' ' : '';
        $sep = $sep_space . $action['title_sep'] . $sep_space;
        //标题页码
        $paged_title = '';
        if (get_query_var('paged') && get_query_var('paged') >= 2) {
            $paged_title = $sep . '第' . get_query_var('paged') . '页';
        }

        //开始创建标题
        if (is_404()) {
            $head_title = '你访问的资源不存在';
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
            $head_title = '附件：' . single_post_title('', false) . '' . $sep . $site_title;
        }
        //自定义文章类型的归档页面
        else if (is_post_type_archive()) {
            $head_title = post_type_archive_title('', false) . '' . $paged_title . $sep . $site_title;
        }
        //分类
        else if (is_category()) {
            $head_title = '分类：' . single_term_title('', false) . '' . $paged_title . $sep . $site_title;
        }
        //标签
        else if (is_tag()) {
            $head_title = '标签：' . single_term_title('#', false) . '' . $paged_title . $sep . $site_title;
        }
        //自定义分类法
        else if (is_tax()) {
            $head_title = '归档：' . single_term_title('', false) . '' . $paged_title . $sep . $site_title;
        }
        //用户
        else if (is_author() && get_queried_object()) {
            $author = get_queried_object()->display_name;
            $head_title = '用户：「' . $author . '」' . '' . $paged_title . $sep . $site_title;
        }
        //归档（年）
        else if (is_year()) {
            $head_title = get_the_time('Y') . '年的所有文章' . $paged_title . $sep . $site_title;
        }
        //归档（月）
        else if (is_month()) {
            $head_title = get_the_time('m') . '月的所有文章' . $paged_title . $sep . $site_title;
        }
        //归档（日）
        else if (is_day()) {
            $head_title = get_the_time('Y-m-d') . '的所有文章' . $paged_title . $sep . $site_title;
        }
        //默认首页标题
        else {
            $head_title = $site_title . ((!empty($sub_title)) ? $sep . $sub_title : '');
        }

        //拼接HTML
        $head = '<title>' . $head_title . '</title>' . "\n";

        return $head;
    }
    //SEO功能
    private function site_seo()
    {
        $action = $this->seo_action;
        //首页关键词
        $seo_keywords = $action['home_seo_keywords'];
        $seo_desc = $action['home_seo_description'];

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

        return $head_seo;
    }
    //配置canonical标签
    private function site_seo_canonical()
    {
        if (is_home()) {
            $url = home_url();
        } else {
            $url = get_permalink();
        }
        $head = '<link rel="canonical" href="' . $url . '" />' . "\n";
        return $head;
    }
}
