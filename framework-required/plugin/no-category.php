<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Rewrite_Category
{
    public function __destruct()
    {
        //去除分类中的category
        add_action('created_category', array($this, 'no_category_base_refresh_rules'));
        add_action('edited_category', array($this, 'no_category_base_refresh_rules'));
        add_action('delete_category', array($this, 'no_category_base_refresh_rules'));
        add_action('init', array($this, 'no_category_base_permastruct'));
        add_filter('category_rewrite_rules', array($this, 'no_category_base_rewrite_rules'));
        add_filter('query_vars', array($this, 'no_category_base_query_vars'));
        add_filter('request', array($this, 'no_category_base_request'));
    }
    //DEBUG：注册一个rewrite标记覆盖WP内置的方法
    public function no_category_base_permastruct()
    {
        global $wp_rewrite;

        $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
    }
    //重写方法
    public function no_category_base_rewrite_rules($category_rewrite)
    {
        $category_rewrite = array();
        $categories = get_categories(array('hide_empty' => false));
        foreach ($categories as $category) {
            $category_nicename = $category->slug;
            if ($category->parent == $category->cat_ID)
                $category->parent = 0;
            elseif ($category->parent != 0)
                $category_nicename = get_category_parents($category->parent, false, '/', true) . $category_nicename;
            $category_rewrite['(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/?$'] = 'index.php?category_name=$matches[1]';
        }
        $old_category_base = get_option('category_base') ? get_option('category_base') : 'category';
        $old_category_base = trim($old_category_base, '/');
        $category_rewrite[$old_category_base . '/(.*)$'] = 'index.php?category_redirect=$matches[1]';
        return $category_rewrite;
    }
    //返回方法
    public function no_category_base_query_vars($public_query_vars)
    {
        $public_query_vars[] = 'category_redirect';
        return $public_query_vars;
    }

    public function no_category_base_request($query_vars)
    {
        if (isset($query_vars['category_redirect'])) {
            $catlink = trailingslashit(get_option('home')) . user_trailingslashit($query_vars['category_redirect'], 'category');
            status_header(301);
            header("Location: $catlink");
            exit();
        }
        return $query_vars;
    }
}
