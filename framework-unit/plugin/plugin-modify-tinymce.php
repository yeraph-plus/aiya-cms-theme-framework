<?php

if (!defined('ABSPATH')) exit;

/*
 * Name: TinyMCE编辑器 按钮重排+功能拓展
 * Version: 1.0.5
 * Author: AIYA-CMS
 * Author URI: https://www.yeraph.com
 */

class AYA_Plugin_Modify_TinyMCE extends AYA_Theme_Setup
{
    public $tinymce_options;

    public function __construct($args)
    {
        $this->tinymce_options = $args;
    }

    public function __destruct()
    {
        $options = $this->tinymce_options;

        add_filter('tiny_mce_before_init', array($this, 'filter_before_init'));

        if ($options['tinymce_filter_buttons']) {
            add_filter('mce_buttons', array($this, 'try_mce_buttons_1'));
            add_filter('mce_buttons_2', array($this, 'try_mce_buttons_2'));
            add_filter('mce_buttons_3', array($this, 'enable_more_buttons'));
            add_filter('mce_external_plugins', array($this, 'add_more_buttons_plugin'));
        }

        add_filter('gettext_with_context', array($this, 'remove_gutenberg_styles'), 10, 4);

        if ($options['tinymce_upload_image']) {
            add_filter('content_save_pre', array($this, 'filter_content_save_pre'), 1);
        }
    }
    //注册编辑器插件
    public function add_more_buttons_plugin($plugin)
    {
        $plugins = (empty($this->tinymce_options['tinymce_add_plugins'])) ? array() : $this->tinymce_options['tinymce_add_plugins'];

        $plugin_add['table'] = plugins_url('..', __FILE__) . '/assects/js/mce-table-plugin.min.js';

        //增加列表中的插件
        if ($plugins != '' && is_array($plugins)) {
            foreach ($plugins as $name => $url) {
                $plugin_add[$name] = $url;
            }
        }

        return array_merge($plugin, $plugin_add);
    }
    //注册编辑器设置
    public function filter_before_init($init)
    {
        $args = array(
            'paste_data_images' => true,
            'fontsize_formats' => '12px 14px 15px 16px 17px 18px 20px 22px 24px 28px 32px 36px 40px 48px',
            'font_formats' => "微软雅黑='微软雅黑';宋体='宋体';黑体='黑体';仿宋='仿宋';楷体='楷体';隶书='隶书';幼圆='幼圆';Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats",
        );
        return array_merge($init, $args);
    }
    //注册更多按钮
    public function enable_more_buttons($buttons)
    {
        $new_buttons = (empty($this->tinymce_options['tinymce_add_buttons'])) ? array() : $this->tinymce_options['tinymce_add_buttons'];

        return array_merge($buttons, $new_buttons);
    }
    //排序编辑器第一行
    public function try_mce_buttons_1($buttons)
    {
        $insert_buttons    = [
            'italic'        => ['underline', 'strikethrough', 'forecolor', 'backcolor', 'styleselect'],
            'alignright'    => ['alignjustify'],
            'wp_more'        => ['wp_page', 'hr']
        ];
        $diff_buttons = array(
            'formatselect',
        );

        foreach ($insert_buttons as $button_before => $_buttons) {
            $pos = array_search($button_before, $buttons, true);

            if ($pos !== false) {
                $buttons = array_merge(array_slice($buttons, 0, $pos + 1), $_buttons, array_slice($buttons, $pos + 1));
            }
        }

        return array_diff($buttons, $diff_buttons);
    }
    //排序编辑器第二行
    public function try_mce_buttons_2($buttons)
    {
        $add_buttons = array(
            'formatselect',
            'fontsizeselect',
            'fontselect',
            'table'
        );
        $diff_buttons = array(
            'strikethrough',
            'forecolor',
            'hr'
        );

        return array_merge($add_buttons, array_diff($buttons, $diff_buttons));
    }
    //禁用古腾堡的Google字体
    public function remove_gutenberg_styles($translation, $text, $context, $domain)
    {
        if ($context != 'Google Font Name and Variants' || $text != 'Noto Serif:400,400i,700,700i') {
            return $translation;
        }
        return 'off';
    }
    //自动上传
    public function filter_content_save_pre($content)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $content;
        }

        if (preg_match_all('/src=\\\\"(data:image\/([^;]+);base64,([^"]+))\\\\"/i', $content, $matches)) {
            $search    = $replace    = $matches[1];
            $types    = $matches[2];
            $images    = $matches[3];

            foreach ($images as $i => $image) {
                $name    = time() . '_' . wp_generate_password(8, false);
                $type    = $types[$i];
                $upload    = wp_upload_bits($name . '.' . $type, null, base64_decode($image));

                if (empty($upload['error'])) {
                    $replace[$i]    = $upload['url'];
                    $post_id        = $_POST['post_ID'] ?? 0;

                    $attachment    = [
                        'post_title'     => $name,
                        'post_content'   => '',
                        'post_type'      => 'attachment',
                        'post_parent'    => $post_id,
                        'post_mime_type' => $upload['type'],
                        'guid'           => $upload['url'],
                    ];

                    $id    = wp_insert_attachment($attachment, $upload['file'], $post_id);
                    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
                } else {
                    $replace[$i]    = '';
                }
            }

            $content = str_replace($search, $replace, $content);

            if (is_multisite()) {
                setcookie('wp-saving-post', $_POST['post_ID'] . '-saved', time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, false, is_ssl());
            }
        }

        return $content;
    }
}
