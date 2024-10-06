<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 移除分类URL中Category
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.3
 **/

class AYA_Plugin_No_Category_URL
{
    public function __destruct()
    {

        add_action('created_category', array($this, 'no_category_base_refresh_rules'));
        add_action('edited_category', array($this, 'no_category_base_refresh_rules'));
        add_action('delete_category', array($this, 'no_category_base_refresh_rules'));

        add_action('init', array($this, 'no_category_base_permastruct'));

        //添加自定义规则
        add_filter('category_rewrite_rules', array($this, 'no_category_base_rewrite_rules'));
        add_filter('query_vars', array($this, 'no_category_base_query_vars'));

        //添加301方法
        add_filter('request', array($this, 'no_category_base_request'));
    }
    //清理路由重写规则
    public function no_category_base_refresh_rules()
    {
        global $wp_rewrite;

        $wp_rewrite->flush_rules();
    }
    //停用原本的分类重写规则
    public function no_category_base_deactivate()
    {
        remove_filter('category_rewrite_rules', 'no_category_base_rewrite_rules');

        self::no_category_base_refresh_rules();
    }
    //替换永久结构
    public function no_category_base_permastruct()
    {
        global $wp_rewrite;

        $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';

        register_activation_hook(__FILE__, function () {
            global $wp_rewrite;

            $wp_rewrite->flush_rules();
        });
        register_deactivation_hook(__FILE__, function () {

            remove_filter('category_rewrite_rules', 'no_category_base_rewrite_rules');

            global $wp_rewrite;

            $wp_rewrite->flush_rules();
        });
    }
    //路由规则
    public function no_category_base_rewrite_rules($category_rewrite)
    {
        //var_dump($category_rewrite);

        $category_rewrite = array();

        $categories = get_categories(array('hide_empty' => false));

        foreach ($categories as $category) {

            $category_nicename = $category->slug;

            //防止循环
            if ($category->parent == $category->cat_ID) {
                $category->parent = 0;
            } elseif ($category->parent != 0) {
                $category_nicename = get_category_parents($category->parent, false, '/', true) . $category_nicename;
            }

            $category_rewrite['(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/?$'] = 'index.php?category_name=$matches[1]';
        }

        //操作重定向
        global $wp_rewrite;

        $old_category_base = get_option('category_base') ? get_option('category_base') : 'category';
        $old_category_base = trim($old_category_base, '/');
        $category_rewrite[$old_category_base . '/(.*)$'] = 'index.php?category_redirect=$matches[1]';

        //var_dump($category_rewrite);

        return $category_rewrite;
    }
    //添加主查询参数
    public function no_category_base_query_vars($public_query_vars)
    {
        $public_query_vars[] = 'category_redirect';

        return $public_query_vars;
    }
    //发现参数存在时自动重定向
    public function no_category_base_request($query_vars)
    {
        //print_r($query_vars);

        if (isset($query_vars['category_redirect'])) {
            $catlink = trailingslashit(get_option('home')) . user_trailingslashit($query_vars['category_redirect'], 'category');
            status_header(301);
            @header("Location: $catlink");
            exit();
        }

        return $query_vars;
    }
}
