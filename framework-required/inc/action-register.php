<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Register_Theme extends AYA_Theme_Setup
{
    public function __construct()
    {
    }

    public function __destruct()
    {
        parent::add_action('after_setup_theme', 'aya_theme_support');
    }

    public function aya_theme_support()
    {
        //加载多语言文本文件
        load_theme_textdomain('__', '/languages');

        //将默认的帖子和评论RSS提要链接添加到<head>
        add_theme_support('automatic-feed-links');
        //支持标签
        add_theme_support('title-tag');
        //支持菜单
        add_theme_support('menus');
        //支持文章类型
        add_theme_support('post-formats', array(
            'gallery',
            'image',
            'video',
            'status',
            'audio'
        ));
        //支持缩略图
        add_theme_support('post-thumbnails', array(
            'post',
            'page'
        ));
        //搜索表单、注释表单和注释的默认核心标记
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'script',
            'style',
            'navigation-widgets',
        ));
        //支持自定义徽标
        add_theme_support('custom-logo', array(
            'height' => 240,
            'width' => 240,
            'flex-height' => true,
        ));
        //支持自定义背景图
        add_theme_support('custom-background', array(
            'default-repeat' => 'repeat',
            'default-position-x' => 'left',
            'default-position-y' => 'top',
            'default-size' => 'auto',
            'default-attachment' => 'fixed'
        ));
        //注册重写规则标签
        add_rewrite_tag('%page_type%', '([^&]+)');

        //创建设置

        //设置默认图片尺寸 
        //set_post_thumbnail_size( 1200, 10000 );//Tips：主要是用来定义图片裁剪的，但一般不需要

        //设置默认图片附件连接方式为无链接
        update_option('image_default_link_type', 'file', true); //Tips：防止图片指向附件页面，方便接入灯箱
    }
}

class AYA_Plugin_Register_Menu extends AYA_Theme_Setup
{
    var $register_menus;

    public function __construct($args)
    {
        $this->register_menus = $args;
    }

    public function __destruct()
    {
        parent::add_action('after_setup_theme', 'aya_theme_register_nav_menu');
    }

    public function aya_theme_register_nav_menu()
    {
        $menus = $this->register_menus;

        if (parent::inspect($menus)) return;

        register_nav_menus($menus);
    }
}

class AYA_Plugin_Register_Sidebar extends AYA_Theme_Setup
{
    var $register_sidebars;

    public function __construct($args)
    {
        $this->register_sidebars = $args;
    }

    public function __destruct()
    {
        parent::add_action('after_setup_theme', 'aya_theme_register_sidebar');
    }

    public function aya_theme_register_sidebar()
    {
        $sidebars = $this->register_sidebars;

        if (parent::inspect($sidebars)) return;

        //循环
        foreach ($sidebars as $id => $name) {
            register_sidebar(array(
                'name' => $name,
                'id' => $id,
                'description' => $name . __('侧边栏', 'AIYA'),
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ));
        }
    }
}

class AYA_Plugin_Register_Post_Type extends AYA_Theme_Setup
{
    var $register_post_type;
    var $rewrite_html;

    public function __construct($args)
    {
        $this->register_post_type = $args;

        if (parent::$tag_html) $this->rewrite_html = '.html';
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_register_post_type');

        parent::add_filter('post_type_link', 'aya_theme_custom_post_link', 1, 3);
    }

    public function aya_theme_register_post_type()
    {
        $types = $this->register_post_type;
        $html = $this->rewrite_html;

        if (parent::inspect($types)) return;

        global $aya_post_type;
        $aya_post_type = array();

        //循环
        foreach ($types as $type => $type_args) {

            $name = $type_args['name'];
            $slug = $type_args['slug'];
            $icon = $type_args['icon'];

            //向全局添加
            $aya_post_type[] = $slug;

            //组装文章类型参数
            $labels = array(
                'name' => $name,
                'singular_name' => $name,
                'add_new' => __('发表') . $name,
                'add_new_item' => __('发表') . $name,
                'edit_item' => __('编辑') . $name,
                'new_item' => __('新') . $name,
                'view_item' => __('查看') . $name,
                'search_items' => __('搜索') . $name,
                'not_found' => __('暂无') . $name,
                'not_found_in_trash' => __('没有已删除的') . $name,
                'parent_item_colon' => '',
                'menu_name' => $name,
            );
            $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => array(
                    'slug' => $slug,
                    'with_front' => false
                ),
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => null,
                'menu_icon' => $icon,
                'supports' => array('editor', 'author', 'title', 'custom-fields', 'comments'),
            );
            register_post_type($type, $args);
            //添加路由规则
            add_rewrite_rule('' . $slug . '/([0-9]+)?' . $html . '$', 'index.php?post_type=' . $slug . '&p=$matches[1]', 'top');
            //评论规则
            add_rewrite_rule('' . $slug . '/([0-9]+)?' . $html . '/comment-page-([0-9]{1,})$', 'index.php?post_type=' . $slug . '&p=$matches[1]&cpage=$matches[2]', 'top');
        }
    }
    //定义自定义文章的内页路径
    function aya_theme_custom_post_link($link, $post = 0)
    {
        global $aya_post_type;

        $html = $this->rewrite_html;

        $post = get_post($post);
        //比对
        if (is_object($post) && in_array($post->post_type, $aya_post_type)) {
            return home_url('' . $post->post_type . '/' . $post->ID . $html);
        } else {
            return $link;
        }
    }
}

class AYA_Plugin_Register_Tax_Type extends AYA_Theme_Setup
{
    var $register_taxonomy;

    public function __construct($args)
    {
        $this->register_taxonomy = $args;
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_register_register_taxonomy');
    }
    function aya_theme_register_register_taxonomy()
    {
        $taxonomys = $this->register_taxonomy;

        if (parent::inspect($taxonomys)) return;

        global $aya_tax_type;
        $aya_tax_type = array();

        //循环
        foreach ($taxonomys as $tax => $tax_args) {

            $tax_name = $tax_args['name'];
            $tax_slug = $tax_args['slug'];
            $hook_type = $tax_args['post_type'];

            //向全局添加
            $aya_tax_type[] = $tax_slug;

            //组装自定义分类法参数
            $labels = array(
                'name' => $tax_name,
                'singular_name' => $tax_name,
                'search_items' => __('搜索') . $tax_name,
                'all_items' => __('所有') . $tax_name,
                'parent_item' => __('父级') . $tax_name,
                'parent_item_colon' => __('父级') . $tax_name,
                'edit_item' => __('编辑') . $tax_name,
                'update_item' => __('更新') . $tax_name,
                'add_new_item' => __('添加新') . $tax_name,
                'new_item_name' => __('新') . $tax_name . __('名称'),
                'menu_name' => $tax_name,
            );
            $args_tax = array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array(
                    'slug' => $tax_slug,
                ),
            );
            register_taxonomy($tax_slug, $hook_type, $args_tax);
        }
    }
}
