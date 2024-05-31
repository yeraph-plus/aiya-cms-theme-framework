<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

/*
 * Name: WP 功能精简/优化插件
 * Version: 1.0.11
 * Author: AIYA-CMS
 * Author URI: https://www.yeraph.com
 */

class AYA_Plugin_Optimize extends AYA_Theme_Setup
{
    public $optimize_options;

    public function __construct($args)
    {
        $this->optimize_options = $args;
    }

    public function __destruct()
    {
        $options = $this->optimize_options;

        //禁用WP自动更新
        if ($options['disable_wp_auto_update'] == true) {
            add_filter('automatic_updater_disabled', '__return_true'); //注册设置
            wp_clear_scheduled_hook('wp_version_check'); //跳过定时作业
            wp_clear_scheduled_hook('wp_update_plugins');
            wp_clear_scheduled_hook('wp_update_themes');
            remove_action('init', 'wp_schedule_update_checks'); //取消更新检测动作
            remove_action('admin_init', '_maybe_update_core');
            remove_action('admin_init', '_maybe_update_plugins');
            remove_action('admin_init', '_maybe_update_themes');
        }
        //禁用管理员邮箱确认
        if ($options['disable_admin_email_check'] == true) {
            add_filter('admin_email_check_interval', '__return_false');
        }
        //禁用 XML-RPC
        if ($options['disable_xmlrpc'] == true) {
            //操作 xmlrpc_methods
            add_filter('xmlrpc_methods', function ($methods) {
                //empty the array
                $methods = array();
                return $methods;
            });
            add_filter('xmlrpc_enabled', '__return_false');

            remove_action('xmlrpc_rsd_apis', 'rest_output_link_wp_head', 10);
            remove_action('xmlrpc_pingback.ping', 'rest_output_link_wp_head', 10);
            remove_action('xmlrpc_weblog_ping', 'rest_output_link_wp_head', 10);
        } else {
            //禁用 PingBack
            if ($options['disable_pingback'] == true) {
                //操作 xmlrpc_methods
                add_filter('xmlrpc_methods', function ($methods) {
                    $methods['pingback.ping'] = '__return_false';
                    $methods['pingback.extensions.getPingbacks'] = '__return_false';
                    return $methods;
                });
                //操作 pingbacks, enclosures, trackbacks
                remove_action('do_pings', 'do_all_pings', 10);
                //操作 _encloseme 和 do_ping 动作
                remove_action('publish_post', '_publish_post_hook', 5);
            } else {
                //不禁用 PingBack 则阻止 Ping 自己
                add_action('pre_ping', function (&$links) {
                    foreach ($links as $l => $link) {
                        if (0 === strpos($link, home_url())) unset($links[$l]);
                    }
                });
            }
        }
        //禁用 REST API
        if ($options['disable_rest_api'] == true) {
            remove_action('init', 'rest_api_init');
            remove_action('rest_api_init', 'rest_api_default_filters', 10);
            remove_action('parse_request', 'rest_api_loaded');

            add_filter('rest_enabled', '__return_false');
            add_filter('rest_jsonp_enabled', '__return_false');
            add_filter('rest_authentication_errors', '__return_false');
            add_filter('rest_authentication_errors', array($this, 'aya_theme_error_rest_api_is_off'), 1);
            //移除head中的RSET API接口
            remove_action('wp_head', 'rest_output_link_wp_head', 10);
        }
        //使 REST API 验证请求来源
        if ($options['disable_rest_api'] == false && $options['add_rest_api_referer_verify'] == true) {
            add_filter('rest_authentication_errors', array($this, 'aya_theme_captcha_rest_api_in_referer'), 1);
        }
        //禁用feed功能
        if ($options['disable_feed'] == true) {
            //执行die方法
            add_action('do_feed', array($this, 'aya_theme_error_feed_is_off'), 1);
            add_action('do_feed_atom', array($this, 'aya_theme_error_feed_is_off'), 1);
            add_action('do_feed_rdf', array($this, 'aya_theme_error_feed_is_off'), 1);
            add_action('do_feed_rss', array($this, 'aya_theme_error_feed_is_off'), 1);
            add_action('do_feed_rss2', array($this, 'aya_theme_error_feed_is_off'), 1);
            //移除head中的Feed接口
            remove_action('wp_head', 'feed_links_extra', 3);
            remove_action('wp_head', 'feed_links', 2);
        } else {
            //将自定义的文章类型加入feed
            add_filter('request', function ($query) {
                global $aya_post_type;
                if (isset($query['feed']) && !isset($query['post_type'])) {
                    $query['post_type'] = array($aya_post_type);
                }
                return $query;
            });
            //在feed中加入查看全文链接
            add_filter('the_excerpt_rss', function ($content) {
                $read_html = '<a rel="bookmark" href="' . get_permalink() . '" target="_blank">' . __('Read More') . '</a>';
                return $content . $read_html;
            });
            //移除head中的WordPress版本
            remove_action('rss2_head', 'the_generator');
            remove_action('commentsrss2_head', 'the_generator');
            remove_action('rss_head', 'the_generator');
            remove_action('rdf_header', 'the_generator');
            remove_action('atom_head', 'the_generator');
            remove_action('comments_atom_head', 'the_generator');
            remove_action('opml_head', 'the_generator');
            remove_action('app_head', 'the_generator');
        }
        //禁用字体和语言包加载
        if ($options['disable_locale_rtl'] == true) {
            //移除 front 
            add_action('init', function () {
                global $wp_rewrite;
                $wp_rewrite->front = null;
            });
            //不加载语言包
            add_filter('language_attributes', function ($language_attributes) {
                $locale = get_locale();

                if (function_exists('is_rtl') && is_rtl()) {
                    $attributes[] = 'dir="rtl"';
                }

                if ($locale) {
                    if (get_option('html_type') == 'text/html') {
                        $attributes[] = "lang=\"$locale\"";
                    }

                    if (get_option('html_type') != 'text/html') {
                        $attributes[] = "xml:lang=\"$locale\"";
                    }
                }

                return implode(' ', $attributes);
            });
        }
        //禁用 oEmbed
        if ($options['disable_head_oembed'] == true) {
            //移除json+oembed
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            //移除xml+oembed
            remove_action('wp_head', 'wp_oembed_add_host_js');

            remove_action('rest_api_init', 'wp_oembed_register_route');
            remove_filter('rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4);
            remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
            remove_filter('oembed_response_data',   'get_oembed_response_data_rich',  10, 4);
            remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result', 10);

            add_filter('embed_oembed_discover', '__return_false');
        }
        //禁用 auto-embeds（自动嵌入）
        if ($options['disable_autoembed'] == true) {
            remove_filter('the_content', [$GLOBALS['wp_embed'], 'autoembed'], 8);
            remove_filter('widget_text_content', [$GLOBALS['wp_embed'], 'autoembed'], 8);
            remove_filter('widget_block_content', [$GLOBALS['wp_embed'], 'autoembed'], 8);

            remove_action('edit_form_advanced', [$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
            remove_action('edit_page_form', [$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
        }
        //禁用自动字符转换
        if ($options['disable_texturize'] == true) {
            //禁止英文引号转义为中文引号
            add_filter('run_wptexturize', '__return_false');
            remove_filter('the_content', 'wptexturize');
            //禁止对标签自动校正
            remove_filter('the_content', 'balanceTags');
            //禁止自动为段落加 <p>
            //remove_filter('the_content', 'wpautop');
            //禁用 capital_P_dangit
            remove_filter('the_content', 'capital_P_dangit', 11);
            remove_filter('the_title', 'capital_P_dangit', 11);
            remove_filter('wp_title', 'capital_P_dangit', 11);
            remove_filter('document_title', 'capital_P_dangit', 11);
            remove_filter('comment_text', 'capital_P_dangit', 31);
            remove_filter('widget_text_content', 'capital_P_dangit', 11);
        }
        //禁用 s.w.org 标记
        if ($options['disable_sworg'] == true) {
            add_filter('wp_resource_hints', function ($hints, $relation_type) {
                if ('dns-prefetch' === $relation_type) {
                    return array_diff(wp_dependencies_unique_hosts(), $hints);
                }
                return $hints;
            }, 10, 2);
        }
        //移除去除头部冗余
        if ($options['remove_head_redundant'] == true) {
            //移除 rsd+xml 开放接口
            remove_action('wp_head', 'rsd_link');
            //移除 Windows Live Writer 的适配器接口
            remove_action('wp_head', 'wlwmanifest_link');
            //移除日志链接
            remove_action('wp_head', 'index_rel_link');
            remove_action('wp_head', 'parent_post_rel_link', 10);
            remove_action('wp_head', 'start_post_rel_link', 10);
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
            //移除默认固定链接
            remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
            remove_action('template_redirect', 'wp_shortlink_header', 11);
            //移除头部 wp-json 标签
            remove_action('wp_head', 'rest_output_link_wp_head', 10);
            remove_action('template_redirect', 'rest_output_link_header', 11);
            //当前文章的索引
            remove_action('wp_head', 'index_rel_link');
            //清除前后文信息
            remove_action('wp_head', 'parent_post_rel_link', 10, 0);
            remove_action('wp_head', 'start_post_rel_link', 10, 0);
            remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
            remove_action('wp_head', 'wp_resource_hints', 2);
            //移除WordPress版本
            remove_action('wp_head', 'wp_generator');
            //移除本页链接
            //remove_action('wp_head', 'rel_canonical');
            //移除机器人元标记
            //remove_filter('wp_robots', 'wp_robots_max_image_preview_large');
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

            add_filter('tiny_mce_plugins', function ($plugins) {
                return array_diff($plugins, array('wpemoji'));
            });
        }
        //移除静态文件版本号
        if ($options['remove_css_js_ver']) {

            add_filter('style_loader_src', function ($src) {
                if (strpos($src, 'ver='))
                    $src = remove_query_arg('ver', $src);
                return $src;
            }, 999);
            add_filter('script_loader_src', function ($src) {
                if (strpos($src, 'ver='))
                    $src = remove_query_arg('ver', $src);
                return $src;
            }, 999);
        }

        //功能调整

        //调出原生链接功能
        if ($options['add_link_manager'] == true) {
            add_filter('pre_option_link_manager_enabled', '__return_true');
        }
        //禁用修订版本功能
        if ($options['remove_revisions'] == true) {
            define('WP_POST_REVISIONS', false);
            /*
            //设置修订版本保存个数0
            add_filter('wp_revisions_to_keep', function ($num, $post) {
                return 0;
            }, 10, 2);
            */
        }
        //Sitemap中跳过输出users列表
        if ($options['remove_sitemaps_users_provider']) {
            add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
                return ($name == 'users') ? false : $provider;
            }, 10, 2);
        }
        //强制locale加载为中文
        if ($options['admin_page_locale_cn'] == true) {
            add_filter('locale', function ($locale) {
                if (is_admin()) $locale = 'zh_CN';
                return $locale;
            });
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
        //自动重命名上传
        if ($options['auto_upload_rename'] == true) {
            add_filter('wp_handle_upload_prefilter', function ($file) {
                $file['name'] = date("YmdHis") . mt_rand(1, 100) . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
                return $file;
            });
        }
        //设置原生缩略图尺寸为空
        if ($options['remove_wp_thumbnails'] == true) {
            add_filter('pre_option_thumbnail_size_w', '__return_zero');
            add_filter('pre_option_thumbnail_size_h', '__return_zero');
            add_filter('pre_option_medium_size_w', '__return_zero');
            add_filter('pre_option_medium_size_h', '__return_zero');
            add_filter('pre_option_large_size_w', '__return_zero');
            add_filter('pre_option_large_size_h', '__return_zero');
        }
        //禁用超大图片自动缩放
        if ($options['remove_image_threshold'] == true) {
            add_filter('big_image_size_threshold', '__return_false');
        }

        //地区调整

        //禁用隐私政策组件
        if ($options['remove_privacy_policy'] == true) {
            //移除注册
            remove_action('user_request_action_confirmed', '_wp_privacy_account_request_confirmed');
            remove_action('user_request_action_confirmed', '_wp_privacy_send_request_confirmation_notification', 12);
            remove_action('wp_privacy_personal_data_exporters', 'wp_register_comment_personal_data_exporter');
            remove_action('wp_privacy_personal_data_exporters', 'wp_register_media_personal_data_exporter');
            remove_action('wp_privacy_personal_data_exporters', 'wp_register_user_personal_data_exporter', 1);
            remove_action('wp_privacy_personal_data_erasers', 'wp_register_comment_personal_data_eraser');
            remove_action('init', 'wp_schedule_delete_old_privacy_export_files');
            remove_action('wp_privacy_delete_old_export_files', 'wp_privacy_delete_old_export_files');

            add_filter('option_wp_page_for_privacy_policy', '__return_zero');
        }
        //移除cn.wordpress.org下载的安装包中的无用代码
        if ($options['zh_cn_option_cleanup'] == true) {
            remove_action('admin_init', 'zh_cn_l10n_legacy_option_cleanup');
            remove_action('admin_init', 'zh_cn_l10n_settings_init');
            add_action('init', function () {
                wp_embed_unregister_handler('tudou');
                wp_embed_unregister_handler('youku');
                wp_embed_unregister_handler('56com');
            });
        }

        add_action('wp_head', array($this, 'aya_theme_site_favicon'));
        add_action('wp_enqueue_scripts', array($this, 'aya_theme_dequeue_script'));
        add_action('admin_enqueue_scripts', array($this, 'aya_theme_dequeue_admin_script'));
    }
    //操作静态文件注册
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
        if ($options['disable_head_oembed'] == true) {
            wp_deregister_script('wp-embed');
        }
    }
    //操作静态文件注册（后台）
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
    //favicon.ico
    public function aya_theme_site_favicon()
    {
        $options = $this->optimize_options;

        //检查设置
        $favicon_url = $options['site_favicon_url'];

        if ($favicon_url != '') {
            $head = '';
            //配置favicon.ico
            $head .= '<link rel="icon" type="image/png" href="' . $favicon_url . '" />' . "\n";
            $head .= '<meta name="msapplication-TileColor" content="#ffffff">' . "\n";
            $head .= '<meta name="msapplication-TileImage" content="' . $favicon_url . '">' . "\n";
            $head .= '<link rel="apple-touch-icon" href="' . $favicon_url . '" />' . "\n";
            $head .= '<meta name="apple-mobile-web-app-title" content="' . get_bloginfo('name') . '">' . "\n";
            $head .= '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
            $head .= '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";

            return $head;
        }
    }
    // Feed 返回报错
    public function aya_theme_error_feed_is_off()
    {
        $message = __('Feed has disabled. Please ask the site administrator or check this site option settings.');
        $title = __('Feed is disabled.');
        $args = array(
            'response' => 403,
            'link_url' => home_url('/'),
            'link_text' => __('Return Homepage'),
            'back_link' => false,
        );

        wp_die($message, $title, $args);

        exit;
    }
    // Rest-API 返回报错
    public function aya_theme_error_rest_api_is_off()
    {
        $message = __('Rest-API has disabled. Please ask the site administrator or check this site option settings.');
        $title = __('Rest-API is disabled.');
        $args = array(
            'response' => 403,
            'link_url' => home_url('/'),
            'link_text' => __('Return Homepage'),
            'back_link' => false,
        );

        wp_die($message, $title, $args);

        //返回请求状态
        return true;
    }
    // Rest-API 验证 HTTP_REFERER 
    public function aya_theme_captcha_rest_api_in_referer()
    {
        $referer = $_SERVER['HTTP_REFERER'];

        //验证来源是否为host
        if (empty($referer) && parse_url($referer)['host'] != parse_url(home_url())['host']) {
            //返回403错误
            return new WP_Error('rest_api_referer_verification_error', __('Rest-API requests must include the source.'), array('status' => 403));
        }
        //返回请求状态
        return true;
    }
}
