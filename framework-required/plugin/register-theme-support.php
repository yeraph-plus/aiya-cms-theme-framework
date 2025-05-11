<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-Framework 组件 注册主题功能插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_After_Setup_Theme extends AYA_Framework_Setup
{
    public $register_theme_support;

    public function __construct($args)
    {
        if (!is_array($args))
            return;

        $this->register_theme_support = $args;
    }

    public function __destruct()
    {
        parent::add_action('after_setup_theme', 'aya_theme_support');
    }

    public function aya_theme_support()
    {
        $supports = $this->register_theme_support;

        //加载多语言文本文件
        load_theme_textdomain('__', '/languages');

        //循环
        foreach ($supports as $feature => $args) {
            //注册支持
            add_theme_support($feature, $args);
        }

        //注册重写规则标签
        add_rewrite_tag('%page_type%', '([^&]+)');

        //创建设置

        //设置默认图片尺寸
        //Tips：主要是用来定义图片裁剪的，但一般不需要
        //set_post_thumbnail_size(1200, 10000);

        //设置默认图片附件连接方式为无链接
        //Tips：防止图片指向附件页面，方便接入灯箱
        update_option('image_default_link_type', 'none', true);
    }
}
