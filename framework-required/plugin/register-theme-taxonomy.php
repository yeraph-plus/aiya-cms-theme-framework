<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-Framework 组件 创建自定义分类法插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Register_Tax_Type extends AYA_Framework_Setup
{
    public $register_taxonomy;

    public function __construct($args)
    {
        if (!is_array($args))
            return;

        $this->register_taxonomy = $args;
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_register_register_taxonomy');
    }
    function aya_theme_register_register_taxonomy()
    {
        if (parent::inspect($this->register_taxonomy))
            return;

        //循环
        foreach ($this->register_taxonomy as $tax => $tax_args) {

            $tax_name = $tax_args['name'];
            $tax_slug = $tax_args['slug'];
            $hook_type = $tax_args['post_type'];
            $tag_mode = (is_bool($tax_args['tag_mode'] ?? null)) ? !$tax_args['tag_mode'] : true;

            //组装自定义分类法参数
            $labels = array(
                'name' => $tax_name,
                'singular_name' => $tax_name,
                'search_items' => __('搜索', 'AIYA_FRAMEWORK') . $tax_name,
                'all_items' => __('所有', 'AIYA_FRAMEWORK') . $tax_name,
                'parent_item' => __('父级', 'AIYA_FRAMEWORK') . $tax_name,
                'parent_item_colon' => __('父级', 'AIYA_FRAMEWORK') . $tax_name,
                'edit_item' => __('编辑', 'AIYA_FRAMEWORK') . $tax_name,
                'update_item' => __('更新', 'AIYA_FRAMEWORK') . $tax_name,
                'add_new_item' => __('添加新', 'AIYA_FRAMEWORK') . $tax_name,
                'new_item_name' => __('新', 'AIYA_FRAMEWORK') . $tax_name . __('名称', 'AIYA_FRAMEWORK'),
                'menu_name' => $tax_name,
            );
            $args_tax = array(
                'hierarchical' => $tag_mode,
                'labels' => $labels,
                'show_ui' => true,
                'show_in_rest' => true,
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
