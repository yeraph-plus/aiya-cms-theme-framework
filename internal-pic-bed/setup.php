<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-CMS 主题拓展 简码图床控件
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
        'desc' => '简码图床',
        'type' => 'title_2',
    ],
    [
        'title' => '图片上传控件',
        'desc' => '拓展插件，允许不通过 WP 媒体库直接上传图片',
        'id' => 'site_plugin_sc_picbed',
        'type' => 'switch',
        'default' => false,
    ]
);

if (!aya_plugin_opt('site_plugin_sc_picbed')) {
    return;
}

//插件后台页面
add_action('admin_init', 'aya_internal_pic_bed_setup');
add_action('admin_menu', 'aya_internal_pic_bed_admin_menu');

function aya_internal_pic_bed_setup()
{
    //样式表
    wp_enqueue_style('shortcode-pic-bed', get_template_directory_uri() . '/plugins/internal-pic-bed/assets/bed-style.css');
    //AJAX
    add_action('wp_ajax_handle_image_upload', array('AYA_SimplePicBed', 'handle_image_upload'));
    add_action('wp_ajax_nopriv_handle_image_upload', array('AYA_SimplePicBed', 'handle_image_upload'));
}

//菜单链接
function aya_internal_pic_bed_admin_menu()
{
    add_menu_page('上传图片', '简码图床', 'manage_options', 'shortcode-pic-bed', 'aya_internal_pic_bed_upload_page', 'dashicons-format-image', 99);
    add_submenu_page('shortcode-pic-bed', '查看上传', '图片列表', 'manage_options', 'shortcode-pic-view', 'aya_internal_pic_bed_list_view', 99);
}

function aya_internal_pic_bed_upload_page()
{
    include (__DIR__) . '/upload/pic-upload.php';
}

function aya_internal_pic_bed_list_view()
{
    include (__DIR__) . '/upload/pic-viewer.php';
}

//AIYA-CMS 短代码组件：简码图床
add_shortcode('pic_bed', 'aya_shortcode_pic_bed_image');

//短代码参数
function aya_shortcode_pic_bed_image($atts = array())
{
    $atts = shortcode_atts(
        array(
            'src' => '',
            'class' => '', //size-full alignnone aligncenter alignright alignleft
            'nsfw' => 'false', //true false
            'width' => 0,
            'height' => 0,
            'title' => '',
            'alt' => 'image/jpeg',
        ),
        $atts,
    );

    //[pic_bed src="upload-pics/2025/04/upload.png" width="2381" height="1544" title="upload.png" alt="image/png" /]

    //拼接URL
    if (strpos($atts['src'], 'http://') === 0 || strpos($atts['src'], 'https://') === 0) {
        $image_url = esc_url($atts['src']);
    } else {
        $image_url = content_url() . '/' . ltrim($atts['src'], '/');
    }

    $class = esc_attr($atts['class']);
    $width = esc_attr($atts['width']);
    $height = esc_attr($atts['height']);
    $title = esc_attr($atts['title']);
    $alt = esc_attr($atts['alt']);

    return '<img src="' . $image_url . '" class="' . $class . '" width="' . $width . '" height="' . $height . '" title="' . $title . '" alt="' . $alt . '" />';
}

//简单图床
class AYA_SimplePicBed
{
    public static $upload_path = 'upload-pics';
    public static $upload_max_size = '10';
    public static $upload_mime = [
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        'image/gif' => '.gif',
        'image/webp' => '.webp',
    ];
    //创建本地文件夹
    private static function local_mkdir()
    {
        $dir_sub_name = self::$upload_path . '/' . date('Y') . '/' . date('m') . '/';

        //在 wp-content 下创建
        $local_dir = trailingslashit(WP_CONTENT_DIR) . $dir_sub_name;
        //判断文件夹是否存在
        if (!is_dir($local_dir)) {
            //创建文件夹
            wp_mkdir_p($local_dir);
        }
        //返回拼接的路径
        return $local_dir;
    }
    //文件处理
    private static function handle_image_manager($image_file)
    {
        if (function_exists('aya_image_manager_process_uploaded_image')) {
            $image_file = aya_image_manager_process_uploaded_image($image_file, false);

            if ($image_file === false) {
                return self::upload_error(__('图片上传失败', 'AIYA'));
            }
        }

        return $image_file;
    }
    //检查上传权限
    public static function current_nonce()
    {
        if (!is_user_logged_in()) {
            return false;
        } else {
            wp_nonce_field('handle_image_upload_action', 'handle_image_upload_nonce');
        }
    }
    //上传控件
    public static function handle_image_upload()
    {
        //防止意外上传
        if (!isset($_POST['handle_image_upload_nonce']) && !isset($_FILES['image_upload'])) {
            return self::upload_error(__('选择图片文件，支持jpg/png/gif/webp。', 'AIYA'));
        }

        //设置允许上传的类型
        $default_mime = self::$upload_mime;
        //获取文件信息
        $file = $_FILES['image_upload'];
        //异常处理
        try {
            //验证nonce
            if (!wp_verify_nonce($_POST['handle_image_upload_nonce'], 'handle_image_upload_action')) {
                throw new Exception(__('非法操作', 'AIYA'));
            }

            //验证文件格式
            $file_tmp_name = $file['tmp_name'];

            //finfo检查真实的文件类型
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            $real_mime = finfo_file($finfo, $file_tmp_name);

            finfo_close($finfo);

            if (!isset($default_mime[$real_mime])) {
                throw new Exception(__('不支持的文件类型', 'AIYA'));
            }

            if ($file['size'] > self::$upload_max_size * 1024 * 1024) {
                throw new Exception(__('文件过大', 'AIYA'));
            }
            if ($file['error'] > 0) {
                throw new Exception(__('上传错误 ', 'AIYA') . $file['error'] . '');
            }

            //保存文件
            $local_dir = self::local_mkdir();
            $file_title = sanitize_file_name($file['name']);

            //生成文件名
            $random_string = wp_generate_password(8, false);
            $file_name = date('d') . '-' . time() . '-' . $random_string . $default_mime[$real_mime];

            //尝试保存文件到目录
            $file_move = move_uploaded_file($file_tmp_name, $local_dir . $file_name);

            if ($file_move === false) {
                throw new Exception(__('无文件权限', 'AIYA'));
            }

            //进行图片处理
            $trans_file = self::handle_image_manager($local_dir . $file_name);
            if ($trans_file === false) {
                throw new Exception(__('图片处理器错误 ', 'AIYA') . print_r($trans_file));
            }

            //输出插件的图片信息格式
            return self::upload_done($trans_file, $file_title);
        }
        //抛出报错
        catch (Exception $e) {
            return self::upload_error($e->getMessage());
        }
    }
    //读取控件
    public static function handle_image_viewer()
    {
        //接收删除参数
        if (isset($_GET['delete']) && !empty($_GET['delete'])) {
            //TODO
        }

        $root_dir = trailingslashit(WP_CONTENT_DIR) . self::$upload_path;

        //内部方法
        function trans_url($path)
        {
            return esc_url(WP_CONTENT_URL . str_replace(WP_CONTENT_DIR, '', $path));
        }
        function entry_dir($dir, $title = '')
        {
            if (!is_dir($dir))
                return;

            $files = scandir($dir);

            foreach ($files as $file) {
                if ($file != "." && $file != "..") {

                    $file_path = $dir . '/' . $file;

                    if (is_file($file_path)) {
                        echo '<img src="' . trans_url($file_path) . '" title="' . esc_attr($file) . '" alt="' . esc_attr($file) . '" />';
                    }
                    if (is_dir($file_path)) {
                        echo '<hr />';
                        echo '<p>' . esc_html($title) . ' "' . esc_url(trans_url($file_path)) . '/"</p>';
                        //递归
                        entry_dir($file_path, '位置');
                    }
                }
            }
        }
        entry_dir($root_dir, '根目录');
        //print_r($handle);
        return;
    }
    //生成上传错误
    public static function upload_error($error_msg)
    {
        header('Content-Type: application/json');

        $callback_array = array(
            'status' => 'error',
            'data' => $error_msg,
        );

        wp_send_json($callback_array);
    }
    //生成图片信息
    public static function upload_done($this_image, $image_title)
    {
        header('Content-Type: application/json');

        if (!file_exists($this_image)) {
            return self::upload_error(__('图片文件不存在', 'AIYA') . '"' . $image_title . '"');
        }
        //提取图片宽高
        $image_size = getimagesize($this_image);
        $width = $image_size[0];
        $height = $image_size[1];
        $mime = $image_size['mime'];

        //根据WP上传目录截取本地路径
        $path_file = str_replace(trailingslashit(WP_CONTENT_DIR), '', $this_image);
        //拼接为URL
        $image_url = set_url_scheme(WP_CONTENT_URL) . '/' . $path_file;

        //批量输出为JSON格式
        $done_array = array(
            'status' => 'success',
            'data' => array(
                'image' => array(
                    'width' => $width,
                    'height' => $height,
                    'mime' => $mime,
                    'title' => $image_title
                ),
                'url' => $image_url,
                'relative_path' => './wp-content' . $path_file,
                'shortcode' => '[pic_bed src="' . $path_file . '" width="' . $width . '" height="' . $height . '" title="' . $mime . '" alt="' . $image_title . '" /]',
                'html' => '<img src="' . $image_url . '" width="' . $width . '" height="' . $height . '" title="' . $mime . '" alt="' . $image_title . '" />',
            )
        );

        wp_send_json($done_array);
    }
}
