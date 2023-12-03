<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Optimize extends AYA_Theme_Setup
{
    var $optimize_options;

    public function __construct($args)
    {
        $this->optimize_options = $args;
    }

    public function __destruct()
    {
        $options = $this->optimize_options;
        //分类URL重写
        if ($options['no_category_base'] == true) {

            new AYA_Plugin_Rewrite_Category();
        }
        //启用Cravatar
        if ($options['default_cravatar'] == true) {
            new AYA_Plugin_Default_Cravatar();
        }
        //加速Gravatar
        if ($options['default_cravatar'] == false && $options['default_gravatar'] == true) {
            new AYA_Plugin_Grvatar_Speed($options['grvatar_speed']);
        }

        parent::add_action('init', 'aya_theme_wp_optimize_init');
        parent::add_action('wp_enqueue_scripts', 'aya_theme_dequeue_script');
        parent::add_action('admin_enqueue_scripts', 'aya_theme_dequeue_admin_script');
    }

    public function aya_theme_wp_optimize_init()
    {
        $options = $this->optimize_options;

        //移除去除头部冗余
        if ($options['remove_head_redundant'] == true) {
            remove_action('template_redirect', 'wp_shortlink_header', 11, 0); //移除 HTTP header 中的 link
            remove_action('wp_head', 'rsd_link'); //移除rsd+xml开放接口
            remove_action('wp_head', 'wlwmanifest_link'); //移除wlwmanifest+xml开放接口
            remove_action('wp_head', 'index_rel_link'); //当前文章的索引
            remove_action('wp_head', 'parent_post_rel_link', 10, 0); //清除前后文信息
            remove_action('wp_head', 'start_post_rel_link', 10, 0);
            remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
            remove_action('wp_head', 'wp_resource_hints', 2);
            remove_action('wp_head', 'wp_generator'); //移除WordPress版本
            remove_action('wp_head', 'rel_canonical'); //本页链接
            remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0); //移除默认固定链接
            remove_action('wp_head', 'rest_output_link_wp_head', 10); //移除头部 wp-json 标签
            remove_filter('wp_robots', 'wp_robots_max_image_preview_large');
        }
        //移除 embed
        if ($options['remove_head_oembed'] == true) {
            remove_action('wp_head', 'wp_oembed_add_discovery_links'); //移除json+oembed
            remove_action('wp_head', 'wp_oembed_add_host_js'); //移除xml+oembed
            remove_action('rest_api_init', 'wp_oembed_register_route');
            remove_filter('rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4);
            remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
            remove_filter('oembed_response_data',   'get_oembed_response_data_rich',  10, 4);
            remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result', 10);
            add_filter('embed_oembed_discover', '__return_false');
        }
        //禁用 auto-embeds
        if ($options['remove_autoembed'] == true) {
            remove_filter('the_content', array($GLOBALS['wp_embed'], 'autoembed'), 8);
        }
        //移除原生emoji's
        if ($options['remove_wp_emojicons']) {
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('embed_head', 'print_emoji_detection_script');

            remove_filter('the_content', 'capital_P_dangit');
            remove_filter('the_title', 'capital_P_dangit');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('comment_text', 'capital_P_dangit');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

            add_filter('emoji_svg_url', '__return_false');
        }
        //禁用 s.w.org 标记
        if ($options['remove_sworg'] == true) {
            add_filter('wp_resource_hints', function ($hints, $relation_type) {
                if ('dns-prefetch' === $relation_type) {
                    return array_diff(wp_dependencies_unique_hosts(), $hints);
                }
                return $hints;
            }, 10, 2);
        }
        //移除原生页面标题输出
        if ($options['remove_head_title'] == true) {
            remove_action('wp_head', '_wp_render_title_tag', 1);
        }
        //设置原生缩略图尺寸为空
        if ($options['remove_wp_thumbnails']) {
            add_filter('pre_option_thumbnail_size_w', '__return_zero');
            add_filter('pre_option_thumbnail_size_h', '__return_zero');
            add_filter('pre_option_medium_size_w', '__return_zero');
            add_filter('pre_option_medium_size_h', '__return_zero');
            add_filter('pre_option_large_size_w', '__return_zero');
            add_filter('pre_option_large_size_h', '__return_zero');
        }
        //禁用超大图片自动缩放
        if ($options['remove_image_threshold']) {
            add_filter('big_image_size_threshold', '__return_false');
        }
        //移除静态文件版本号
        if ($options['remove_css_js_ver']) {
            function remove_css_js_ver($src)
            {
                if (strpos($src, 'ver='))
                    $src = remove_query_arg('ver', $src);
                return $src;
            }
            add_filter('style_loader_src', 'remove_css_js_ver', 9999);
            add_filter('script_loader_src', 'remove_css_js_ver', 9999);
        }
        //调出原生链接功能
        if ($options['add_link_manager'] == true) {
            add_filter('pre_option_link_manager_enabled', '__return_true');
        }
        //禁用修订版本功能
        if ($options['remove_revisions'] == true) {
            add_filter('wp_revisions_to_keep', function ($num, $post) {
                //设置修订版本保存个数0
                return 0;
            }, 10, 2);
        }
        //禁用webp上传时报错
        if ($options['add_upload_webp'] == true) {
            add_filter('plupload_default_settings', function ($defaults) {
                $defaults['webp_upload_error'] = false;
                return $defaults;
            }, 10, 1);

            add_filter('plupload_init', function ($plupload_init) {
                $plupload_init['webp_upload_error'] = false;
                return $plupload_init;
            }, 10, 1);
        }
    }

    public function aya_theme_dequeue_script()
    {
        $options = $this->optimize_options;

        //移除谷歌字体
        if ($options['remove_open_sans'] == true) {
            wp_dequeue_style('open-sans', '');
            wp_deregister_style('open-sans');
        }
        //移除古腾堡编辑器样式
        if ($options['remove_gutenberg_styles'] == true) {
            wp_dequeue_style('classic-theme-styles');
            wp_dequeue_style('global-styles');
            wp_dequeue_style('wp-block-library');
            wp_dequeue_style('wp-block-library-theme');
            wp_dequeue_style('wc-blocks-style');
            wp_dequeue_style('wc-blocks-vendors-style');
            wp_dequeue_style('bp-member-block');
            wp_dequeue_style('bp-members-block');
        }
        //移除wp_footer带入embed.min.js
        wp_deregister_script('wp-embed');
    }

    public function aya_theme_dequeue_admin_script()
    {
        $options = $this->optimize_options;

        //移除谷歌字体
        if ($options['remove_open_sans'] == true) {
            wp_dequeue_style('open-sans', '');
            wp_deregister_style('open-sans');
            wp_dequeue_style('wp-editor-font');
            wp_deregister_style('wp-editor-font');
        }
        //移除自动保存
        if ($options['remove_editor_autosave'] == true) {
            wp_deregister_script('autosave');
        }
    }
}
