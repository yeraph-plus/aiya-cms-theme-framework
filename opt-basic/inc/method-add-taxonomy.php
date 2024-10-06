<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 组件 创建自定义分类法
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Register_Tax_Type extends AYA_Theme_Setup
{
    public $register_taxonomy;

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
