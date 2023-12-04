<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Head_Label_Action
{
    var $seo_action;

    public function __construct($args)
    {
        $this->seo_action = $args;
    }

    public function __destruct()
    {
        $action = $this->seo_action;

        add_action('wp_head', array($this, 'aya_theme_head_action'));

        if ($action['seo_action'] == true) {
            //分类MetaBox
            $term_feild = array(
                array(
                    'title' => 'SEO关键词',
                    'desc' => '多个关键词之间使用<code>, </code>分隔，默认显示该分类名称。',
                    'id'   => 'seo_cat_keywords',
                    'type' => 'text',
                    'default'  => '',
                ),
                array(
                    'title' => 'SEO描述',
                    'desc' => '默认显示该分类名称。',
                    'id'   => 'seo_cat_desc',
                    'type' => 'textarea',
                    'default'  => '',
                ),
            );
            new AYA_Framework_Term_Meta($term_feild, array('category'));
            //文章Metabox
            $meta_info = array(
                'title' => '自定义SEO',
                'id' => 'seo_box',
                'context' => 'normal',
                'priority' => 'low',
                'add_box_in' => array('post'),
            );
            $post_meta = array(
                array(
                    'title' => 'SEO关键词',
                    'desc' => '多个关键词之间使用<code>, </code>分隔，留空则默认设置为文章的标签。',
                    'id'   => 'seo_keywords',
                    'type' => 'text',
                    'default'  => '',
                ),
                array(
                    'title' => 'SEO描述',
                    'desc' => '默认为文章前150个字符（推荐不超过150个字符）。',
                    'id'   => 'seo_desc',
                    'type' => 'textarea',
                    'default'  => '',
                ),
            );
            new AYA_Framework_Post_Meta($post_meta, $meta_info);

            add_action('wp_head', array($this, 'aya_theme_head_seo_action'));
            add_filter('robots_txt', array($this, 'aya_theme_filter_robots_txt'), 10, 2);
        }
    }
    //输出head标签
    public function aya_theme_head_action()
    {
        echo self::site_favicon();
        echo self::site_title();
        echo self::site_head_extra();
    }
    public function aya_theme_head_seo_action()
    {
        echo self::site_seo();
        echo self::site_seo_canonical();
    }
    //配置robots.txt
    public function aya_theme_filter_robots_txt($output, $public)
    {
        $action = $this->seo_action;

        if ($action['custom_robots_true'] && $action['custom_robots_txt'] != '') {
            $output = esc_attr(wp_strip_all_tags($action['custom_robots_txt']));
        }
        return $output;
    }
    //favicon.ico
    private function site_favicon()
    {
        $action = $this->seo_action;
        //检查设置
        if ($action['site_favicon_url'] == '') return;
        //配置favicon.ico
        $favicon = $action['site_favicon_url'];

        $head = '<link rel="icon" type="image/png" href="' . $favicon . '" />' . "\n";
        $head .= '<link rel="apple-touch-icon" href="' . $favicon . '" />' . "\n";
        $head .= '<meta name="msapplication-TileColor" content="#ffffff">' . "\n" . '<meta name="msapplication-TileImage" content="' . $favicon . '">' . "\n";

        return $head;
    }
    //额外代码
    private function site_head_extra()
    {
        $action = $this->seo_action;
        $head = '';
        //检查设置
        if ($action['site_analytics_script'] != '') {
            $head .= $action['site_analytics_script'] . "\n";
        }
        if ($action['site_extra_css'] != '') {
            $head .= '<style>' . $action['site_extra_css'] . '</style>' . "\n";
        }
        return $head;
    }
    //站点标题选择器
    private function site_title()
    {
        $action = $this->seo_action;
        //配置Title
        $site_title = ($action['site_title'] == '') ? get_bloginfo('name') : $action['site_title'];
        $sub_title = ($action['site_subtitle'] == '') ? get_bloginfo('description') : $action['site_subtitle'];
        //标题分隔符
        $sep_space = ($action['title_sep_space']) ? ' ' : '';
        $sep = $sep_space . $action['title_sep'] . $sep_space;
        //标题页码
        $paged_title = '';
        if (get_query_var('paged') && get_query_var('paged') > 1) {
            $paged_title = $sep . '第' . get_query_var('paged') . '页';
        }

        //开始创建标题
        if (is_home() || is_front_page()) {
            $head_title = $site_title . $paged_title . (($action['subtitle_true']) ? $sep . $sub_title : '');
        } else if (is_search()) {
            $head_title = '搜索"' . $_REQUEST['s'] . '"的结果' . $paged_title . $sep . $site_title;
        } else if (is_single() || is_page()) {
            $head_title = trim(wp_title('', 0)) . $sep . $site_title;
        } else if (is_author()) {
            $head_title = '用户「' . get_the_author() . '」';
        } else if (is_category()) {
            $head_title = '分类：' . single_term_title('', false) . '' . $paged_title . $sep . $site_title;
        } else if (is_tag()) {
            $head_title = '标签：' . single_term_title('#', false) . '' . $paged_title . $sep . $site_title;
        } else if (is_year()) {
            $head_title = get_the_time('Y年') . '的所有文章' . $paged_title . $sep . $site_title;
        } else if (is_month()) {
            $head_title = get_the_time('m月') . '的所有文章' . $paged_title . $sep . $site_title;
        } else if (is_day()) {
            $head_title = get_the_time('Y年m月d日') . '的所有文章' . $paged_title . $sep . $site_title;
        } else if (is_404()) {
            $head_title = '你访问的资源不存在';
        } else {
            //自定义页面的标题
            //if(get_query_var('page_type'))
            $head_title = wp_get_document_title();
        }
        //拼接标题
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
                $seo_keywords = '<meta name="keywords" content="' . $category_keywords . '"/>';
            } else {
                $seo_keywords = '<meta name="keywords" content="' . $category->name . '"/>';
            }
            //提取设置
            $category_desc = get_post_meta($category->term_id, 'seo_cat_desc', true);
            //检查设置
            if (!empty(trim($category_desc))) {
                $seo_desc = '<meta name="description" content="' . $category_desc . '"/>';
            } else {
                $seo_desc = '<meta name="description" content="' . $category->name . '"/>';
            }
        }

        $head = '<meta name="keywords" content="' . $seo_keywords . '" />' . "\n" . '<meta name="description" content="' . $seo_desc . '" />' . "\n";
        return $head;
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
