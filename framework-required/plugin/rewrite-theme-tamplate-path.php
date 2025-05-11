<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-Framework 组件 自定义主题模板和路由插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.2
 **/

class AYA_Plugin_Theme_Redefine_Template extends AYA_Framework_Setup
{
    public $template_path;

    public function __construct()
    {
        //定义模板位置
        $this->template_path = 'templates';
    }

    public function __destruct()
    {
        //添加自定义的路径过滤器
        parent::add_filter('404_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('archive_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('attachment_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('author_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('category_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('date_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('embed_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('frontpage_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('home_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('index_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('page_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('paged_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('privacypolicy_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('search_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('single_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('singular_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('tag_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        parent::add_filter('taxonomy_template_hierarchy', 'aya_theme_rc_template_hierarchy');
        //添加重写的过滤器
        //parent::add_action('template_include', 'aya_template_reload');
    }
    //重写查询位置
    public function aya_theme_rc_template_hierarchy($template)
    {
        $new_template_path = $this->template_path . '/';

        if (!is_array($template)) {
            $new_template = array($new_template_path . $template . '.php');
        } else {
            $new_template = array_map(function ($array) use ($new_template_path) {
                return $new_template_path . $array;
            }, $template);
        }
        //返回原加载
        return $new_template;
    }

    //To be continued...

    //自定义查询模板
    public function aya_query_template($type, $templates = array())
    {
        if (empty($type) || is_array($type))
            return '';

        $new_template_path = $this->template_path . '/';

        if (empty($type)) {
            $templates = array($new_template_path . $type . '.php');
        } else {
            $templates = array_map(function ($array) use ($new_template_path) {
                return $new_template_path . $array;
            }, $templates);
        }
        //加载模板
        return locate_template($templates);
    }
    //直接重建WP默认加载
    public function aya_template_reload($template)
    {
        //From wp-includes/template-loader.php
        $tag_templates = array(
            'is_embed' => 'aya_embed_template',
            'is_404' => 'aya_404_template',
            'is_search' => 'aya_search_template',
            'is_front_page' => 'aya_front_page_template',
            'is_home' => 'aya_home_template',
            'is_privacy_policy' => 'aya_privacy_policy_template',
            'is_post_type_archive' => 'aya_post_type_archive_template',
            'is_tax' => 'aya_taxonomy_template',
            'is_attachment' => 'aya_attachment_template',
            'is_single' => 'aya_single_template',
            'is_page' => 'aya_page_template',
            'is_singular' => 'aya_singular_template',
            'is_category' => 'aya_category_template',
            'is_tag' => 'aya_tag_template',
            'is_author' => 'aya_author_template',
            'is_date' => 'aya_date_template',
            'is_archive' => 'aya_archive_template',
        );
        $template = false;

        //循环验证
        foreach ($tag_templates as $tag => $type_template) {
            if (call_user_func($tag)) {
                $template = call_user_func(array(&$this, $type_template));
            }
        }
        //如果没找到，则返回首页
        if (!$template) {
            $template = $this->aya_index_template();
        }

        return $template;
    }
    //首页
    public function aya_index_template()
    {
        //TODO: if(get_option('index_page') == 'xxx') -> index-xxx.php
        return $this->aya_query_template('index');
    }
    //404
    public function aya_404_template()
    {
        return $this->aya_query_template('404');
    }
    //归档
    public function aya_archive_template()
    {
        $templates = array();

        $post_types = array_filter((array) get_query_var('post_type'));

        if (count($post_types) === 1) {
            $post_type = reset($post_types);

            $templates[] = 'archive-' . $post_type . '.php';
        }
        //默认
        $templates[] = 'archive.php';

        return $this->aya_query_template('archive', $templates);
    }
    //自定义归档
    public function aya_post_type_archive_template()
    {
        $post_type = get_query_var('post_type');

        if (is_array($post_type)) {
            $post_type = reset($post_type);
        }

        //WP_Post_Type
        $obj = get_post_type_object($post_type);

        if (!($obj instanceof WP_Post_Type) || !$obj->has_archive) {
            return false;
        }

        return $this->aya_archive_template();
    }
    //作者
    public function aya_author_template()
    {
        $templates = array();

        //WP_User
        $author = get_queried_object();
        //添加用户角色模板
        $author_role = count($author->roles) ? $author->roles[0] : 'subscriber';

        if ($author instanceof WP_User) {
            $templates[] = 'author-' . $author->user_nicename . '.php';
            $templates[] = 'author-' . $author->ID . '.php';
            $templates[] = 'author-' . $author_role . '.php';
        }

        //默认
        $templates[] = 'author.php';

        return $this->aya_query_template('author', $templates);
    }
    //分类
    public function aya_category_template()
    {
        $templates = array();

        $category = get_queried_object();

        if (!empty($category->slug)) {

            $slug_decoded = urldecode($category->slug);

            if ($slug_decoded !== $category->slug) {
                $templates[] = 'category-' . $slug_decoded . '.php';
            }
            $templates[] = 'category-' . $category->slug . '.php';
        }

        //默认
        $templates[] = 'category.php';

        return $this->aya_query_template('category', $templates);
    }
    //标签
    public function aya_tag_template()
    {
        $templates = array();

        $tag = get_queried_object();

        if (!empty($tag->slug)) {

            $slug_decoded = urldecode($tag->slug);

            if ($slug_decoded !== $tag->slug) {
                $templates[] = 'tag-' . $slug_decoded . '.php';
            }
            $templates[] = 'tag-' . $tag->slug . '.php';
        }

        //默认
        $templates[] = 'tag.php';

        return $this->aya_query_template('tag', $templates);
    }
    //自定义分类
    public function aya_taxonomy_template()
    {
        $templates = array();

        $term = get_queried_object();

        if (!empty($term->slug)) {

            $taxonomy = $term->taxonomy;

            $slug_decoded = urldecode($term->slug);

            if ($slug_decoded !== $term->slug) {
                $templates[] = 'tax-' . $taxonomy . '-' . $slug_decoded . '.php';
            }
            $templates[] = 'tax-' . $taxonomy . '-' . $term->slug . '.php';
            $templates[] = 'tax' . $taxonomy . '.php';
        }

        //默认
        $templates[] = 'tax.php';

        return $this->aya_query_template('taxonomy', $templates);
    }
    //时间归档
    public function aya_date_template()
    {
        return $this->aya_query_template('date', array('date.php'));
    }
    //HOME
    public function aya_home_template()
    {
        $templates = array(
            'home.php',
            'index.php'
        );

        return $this->aya_query_template('home', $templates);
    }
    //自定义主页
    public function aya_front_page_template()
    {
        $templates = array(
            'front-page.php',
            'home.php',
            'index.php'
        );

        return $this->aya_query_template('frontpage', $templates);
    }
    //隐私政策
    public function aya_privacy_policy_template()
    {
        return $this->aya_query_template('privacy-policy');
    }
    //页面
    public function aya_page_template()
    {
        $templates = array();

        $page = get_query_var('pagename');

        if ($page) {
            $page_decoded = urldecode($page);
            if ($page !== $page_decoded) {
                $templates[] = 'page-' . $page_decoded . '.php';
            }
            $templates[] = 'page-' . $page . '.php';
        }

        $templates[] = 'page.php';

        return $this->aya_query_template('page', $templates);
    }
    //搜索
    public function aya_search_template()
    {
        return $this->aya_query_template('search');
    }
    //文章
    public function aya_single_template()
    {
        $templates = array();

        $object = get_queried_object();

        if (!empty($object->post_type)) {

            $single = $object->post_name;
            $single_decoded = urldecode($object->post_name);

            if ($single !== $single_decoded) {
                $templates[] = 'single-' . $object->post_name . '-' . urldecode($object->post_name) . '.php';
            }

            $templates[] = 'single-' . $object->post_type . '-' . $object->post_name . '.php';
            $templates[] = 'single-' . $object->post_type . '.php';
        }

        $templates[] = 'single.php';

        return $this->aya_query_template('single', $templates);
    }
    //嵌入组件
    public function aya_embed_template()
    {
        $object = get_queried_object();

        $templates = array();

        if (!empty($object->post_type)) {

            $post_format = get_post_format($object);

            if ($post_format) {
                $templates[] = 'embed-' . $object->post_type . '-' . $post_format . '.php';
            }
            $templates[] = 'embed-' . $object->post_type . '.php';
        }

        $templates[] = 'embed.php';

        return $this->aya_query_template('embed', $templates);
    }
    //单页
    public function aya_singular_template()
    {
        return $this->aya_query_template('singular');
    }
    //附件
    public function aya_attachment_template()
    {
        $attachment = get_queried_object();

        $templates = array();

        if ($attachment) {
            if (str_contains($attachment->post_mime_type, '/')) {
                list($type, $subtype) = explode('/', $attachment->post_mime_type);
            } else {
                list($type, $subtype) = array($attachment->post_mime_type, '');
            }

            if (!empty($subtype)) {
                $templates[] = "{$type}-{$subtype}.php";
                $templates[] = "{$subtype}.php";
            }
            $templates[] = "{$type}.php";
        }
        $templates[] = 'attachment.php';

        return $this->aya_query_template('attachment', $templates);
    }
}
