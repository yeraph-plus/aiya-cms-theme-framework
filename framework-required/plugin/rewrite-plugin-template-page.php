<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-Framework 组件 在插件中注册页面模板的方法插件
 * 
 * 这个方法不添加页面路由，只是方便从插件中向主题添加页面模板
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_Add_Template_Page_for_Theme extends AYA_Framework_Setup
{
    public $templates;
    public $template_page_path;

    //添加需要的主题模板过滤器
    private function __construct($args)
    {
        //入参格式
        /*
        $templates = array(
            'plugin_templates' => array(
                'example-template' => '插件提供-示例模板',
            ),
            'plugin_path' => plugin_dir_path(__DIR__),
        );
        */
        $this->templates = $args['plugin_templates'];
        //定义模板位置
        $this->template_page_path = $args['plugin_path'];

        //主题的模板列表索引
        parent::add_filter('theme_page_templates', 'aya_plugin_add_new_template');
        //操作过滤WP的模板页面缓存
        parent::add_filter('wp_insert_post_data', 'aya_plugin_register_project_templates');
        //验证模板可用然后返回模板的真实路径
        parent::add_filter('template_include', 'aya_plugin_view_project_template');
    }
    //在页面模板的下拉列表注册
    public function aya_plugin_add_new_template($posts_templates)
    {
        //附加数组
        $posts_templates = array_merge($posts_templates, $this->templates);

        return $posts_templates;
    }
    //注册模板文件的路由方法，使WP误认为文件存在于主题中
    public function aya_plugin_register_project_templates($atts)
    {
        //创建一个主题缓存
        $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());

        //重建缓存列表
        $templates = wp_get_theme()->get_page_templates();

        if (empty($templates)) {
            $templates = array();
        }

        wp_cache_delete($cache_key, 'themes');

        //附加新添加的模板
        $templates = array_merge($templates, $this->templates);

        wp_cache_add($cache_key, $templates, 'themes', 1800);

        return $atts;
    }
    //验证模板是否已分配给页面
    public function aya_plugin_view_project_template($template)
    {
        global $post;

        if (!$post) {
            return $template;
        }

        $this_template = get_post_meta($post->ID, '_wp_page_template', true);
        //兼容WP默认模板设置
        if (!isset($this->templates[$this_template])) {
            return $template;
        }

        //转换模板文件路径
        $template_file = $this->template_page_path . $this_template . '.php';

        //如果文件存在则返回
        if (file_exists($template_file)) {
            return $template_file;
        }
        //没找到文件则不处理防止报错
        return $template;
    }
}
