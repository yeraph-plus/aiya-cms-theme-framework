<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-CMS 主题拓展 TinyMCE编辑器按钮重排+功能拓展
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.1
 * 
 **/

//插件设置
aya_add_plugin_opt(
    [
        'desc' => '编辑器拓展',
        'type' => 'title_2',
    ],
    [
        'title' => '经典编辑器增强',
        'desc' => '拓展插件，重排经典编辑器（TinyMCE）按钮、增加表格、标签清理等功能',
        'id' => 'site_plugin_editor_modify',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '经典编辑器自动上传',
        'desc' => '拓展插件，将粘贴在编辑器的本地图片自动上传到媒体库',
        'id' => 'site_plugin_editor_auto_upload',
        'type' => 'switch',
        'default' => false,
    ],
    [
        'title' => '作者选择器角色过滤',
        'desc' => '拓展插件，作者选择器只显示作者及以上角色的用户',
        'id' => 'site_plugin_editor_filter_author',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'title' => '显示所有标签',
        'desc' => '拓展插件，标签选择器显示所有标签（WP默认为显示前45个）',
        'id' => 'site_plugin_editor_show_all_tags',
        'type' => 'switch',
        'default' => true,
    ],
);

//初始化位置
add_action('admin_init', 'aya_plugin_tinymce_modify_setup');

function aya_plugin_tinymce_modify_setup()
{
    //静态文件地址
    $mce_plugin_uri = get_template_directory_uri() . '/plugins/classic-editor-modify/assets/';

    //获取设置
    $image_auto_upload = aya_plugin_opt('site_plugin_editor_auto_upload');
    $mce_rearrange_buttons = aya_plugin_opt('site_plugin_editor_modify');
    $filter_author_role = aya_plugin_opt('site_plugin_editor_filter_author');
    $show_all_tags = aya_plugin_opt('site_plugin_editor_show_all_tags');

    //组件参数
    $args = array(
        //
        'author_metabox_filter_role' => $filter_author_role,
        //编辑器显示所有标签
        'tinymce_show_all_tags' => $show_all_tags,
        //按钮重排
        'tinymce_filter_buttons' => $mce_rearrange_buttons,
        //浏览器粘贴图片自动上传
        'tinymce_upload_image' => $image_auto_upload,
        //向编辑器中注册新的插件
        'tinymce_add_plugins' => array(
            //'image' => $mce_plugin_uri . 'image.plugin.min.js',
            //'media' => $mce_plugin_uri . 'media.plugin.min.js',
            'advlist' => $mce_plugin_uri . 'advlist.plugin.min.js',
            'table' => $mce_plugin_uri . 'table.plugin.min.js',
            'toc' => $mce_plugin_uri . 'toc.plugin.min.js',
            'codesample' => $mce_plugin_uri . 'codesample.plugin.min.js',
            'textpattern' => $mce_plugin_uri . 'textpattern.plugin.min.js',
        ),
        //向编辑器中注册自定义按钮
        //wp_enqueue_script('mce-add-button', $mce_plugin_uri . 'add-quicktags.button.js', array('jquery'), '1.0.0', true);
        //'tinymce_add_buttons' => array('btnCode', 'btnPanel', 'btnPost', 'btnVideo', 'btnMusic',),
    );

    //启动
    return new AYA_Modify_TinyMCE($args);
}

class AYA_Modify_TinyMCE
{
    public $tinymce_options;

    public function __construct($args)
    {
        $this->tinymce_options = $args;

        add_filter('tiny_mce_before_init', array($this, 'filter_before_init'));

        if ($args['tinymce_filter_buttons']) {
            add_filter('mce_buttons', array($this, 'try_mce_buttons_1'));
            add_filter('mce_buttons_2', array($this, 'try_mce_buttons_2'));
            add_filter('mce_buttons_3', array($this, 'enable_more_buttons'));
        }

        add_filter('mce_external_plugins', array($this, 'add_more_buttons_plugin'));
        add_filter('gettext_with_context', array($this, 'remove_gutenberg_styles'), 10, 4);

        if ($args['tinymce_upload_image']) {
            add_filter('content_save_pre', array($this, 'filter_content_save_pre'), 1);
        }

        if ($args['author_metabox_filter_role']) {
            add_filter('wp_dropdown_users_args', array($this, 'filter_author_dropdown_args'), 10, 2);
        }
        if ($args['tinymce_show_all_tags']) {
            add_filter('get_terms_args', array($this, 'filter_editor_show_all_tags'), 10, 2);
        }
    }

    //注册编辑器插件
    public function add_more_buttons_plugin($plugin)
    {
        $plugins = (empty($this->tinymce_options['tinymce_add_plugins'])) ? array() : $this->tinymce_options['tinymce_add_plugins'];

        //增加列表中的插件
        $plugin_add = array();

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
        $insert_buttons = [
            'italic' => ['underline', 'strikethrough', 'forecolor', 'backcolor', 'styleselect'],
            'alignright' => ['alignleft', 'aligncenter', 'alignjustify'],
            'wp_more' => ['toc', 'hr', 'wp_page'],
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
            'table',
            'codesample',
        );
        $diff_buttons = array(
            'hr',
            'strikethrough',
            'forecolor',
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
            $search = $replace = $matches[1];
            $types = $matches[2];
            $images = $matches[3];

            foreach ($images as $i => $image) {
                $name = time() . '_' . wp_generate_password(8, false);
                $type = $types[$i];
                $upload = wp_upload_bits($name . '.' . $type, null, base64_decode($image));

                if (empty($upload['error'])) {
                    $replace[$i] = $upload['url'];
                    $post_id = $_POST['post_ID'] ?? 0;

                    $attachment = [
                        'post_title' => $name,
                        'post_content' => '',
                        'post_type' => 'attachment',
                        'post_parent' => $post_id,
                        'post_mime_type' => $upload['type'],
                        'guid' => $upload['url'],
                    ];

                    $id = wp_insert_attachment($attachment, $upload['file'], $post_id);
                    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
                } else {
                    $replace[$i] = '';
                }
            }

            $content = str_replace($search, $replace, $content);

            if (is_multisite()) {
                setcookie('wp-saving-post', $_POST['post_ID'] . '-saved', time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, false, is_ssl());
            }
        }

        return $content;
    }

    //编辑文章页面用户查询过滤器
    public function filter_author_dropdown_args($args, $r)
    {
        if (isset($r['name']) && $r['name'] === 'post_author_override') {
            //只包作者及以上角色
            $args['role__in'] = array('author', 'editor', 'administrator');
            //排除贡献者
            $args['role__not_in'] = array('contributor', 'subscriber');
        }

        return $args;
    }

    //编辑文字页面标签选择器完整加载
    public function filter_editor_show_all_tags($args, $taxonomies)
    {
        // 检查是否为后台编辑器请求
        if (
            is_admin() &&
            isset($taxonomies[0]) &&
            ($taxonomies[0] === 'post_tag' || $taxonomies[0] === 'tag') &&
            isset($_REQUEST['action']) &&
            $_REQUEST['action'] === 'get-tagcloud'
        ) {
            //设置为 0 取消数量限制，显示所有标签
            $args['number'] = 0;

            //按字母顺序排列标签
            $args['orderby'] = 'name';
            $args['order'] = 'ASC';
        }
        return $args;
    }
}