<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 主题通过CDN排队静态资源配置组件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_CDN_Scripts
{
    public $plugin_scripts_source = '';
    public $plugin_script_list = [];
    public $plugin_style_list = [];

    public function __construct($args)
    {
        if (!is_array($args)) return;

        if (isset($args['default_cdn_source'])) {
            $this->plugin_scripts_source = $args['default_cdn_source'];
        }

        if (isset($args['js_list'])) {
            $this->plugin_script_list = is_array($args['js_list']) ? $args['js_list'] : [];
        }
        if (isset($args['css_list'])) {
            $this->plugin_style_list = is_array($args['css_list']) ? $args['css_list'] : [];
        }

        add_action('wp_head', array($this, 'aya_theme_register_styles_assets'));
        add_action('wp_footer', array($this, 'aya_theme_register_scripts_assets'));
    }

    //定义默认的脚本位置
    public function aya_theme_assets_dir($path = '')
    {
        if (!empty($path)) {
            $path = $path . '/';
        }

        return get_template_directory_uri() . '/assets/' . $path;
    }

    //切换CDN位置
    public function aya_theme_static_scripts_cdn($source)
    {
        if (empty($source)) {
            $source = $this->plugin_scripts_source;
        }

        switch ($source) {
            case 'cdnjs':
                $url_cdn = 'https://cdnjs.cloudflare.com/ajax/libs/';
                break;
            case 'zstatic':
                $url_cdn = 'https://s4.zstatic.net/ajax/libs/';
                break;
            case 'bootcdn':
                $url_cdn = 'https://cdn.bootcdn.net/ajax/libs/';
                break;
            case 'local':
                $url_cdn = self::aya_theme_assets_dir('libs');
            default:
                $url_cdn = $source;
                break;
        }


        return $url_cdn;
    }

    //Tips:如果需要复用配置在本地CDN加载资源，需要以相同路径下载CDN的资源到本地 eg. 
    //wget -x -nH --cut-dirs=2 -P ./ https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.8/cdn.min.js

    //注册脚本资源
    function aya_theme_register_scripts_assets()
    {
        $list = $this->plugin_script_list;

        if (empty($list)) {
            return;
        }

        foreach ($list as $value) {
            $cdn_url = self::aya_theme_static_scripts_cdn($value['source']);

            wp_register_script($value['handle'], $cdn_url . '/' . $value['pack'] . '/' . $value['ver'] . '/' . $value['file'], $value['deps'], $value['ver'], true);
            wp_enqueue_script($value['handle']);
        }
    }

    //注册样式表资源
    function aya_theme_register_styles_assets()
    {
        $list = $this->plugin_style_list;

        if (empty($list)) {
            return;
        }

        foreach ($list as $value) {
            $cdn_url = self::aya_theme_static_scripts_cdn($value['source']);

            wp_register_style($value['handle'], $cdn_url . '/' . $value['pack'] . '/' . $value['ver'] . '/' . $value['file'], $value['deps'], $value['ver'], 'all');
            wp_enqueue_style($value['handle']);
        }
    }
}
