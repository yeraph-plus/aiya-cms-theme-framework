<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-CMS 主题图片处理依赖
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 3.0
 * 
 **/

//插件设置
aya_add_plugin_opt(
    [
        'desc' => '图片处理器配置',
        'type' => 'title_2',
    ],
    [
        'title' => '接管媒体库',
        'desc' => '使用主题提供的图片处理器接管 WordPress 默认的图片处理功能',
        'id' => 'site_plugin_image_handle_wp_bool',
        'type' => 'switch',
        'default' => true,
    ],
    [
        'desc' => '注意：如果你的 PHP 环境没有安装 libwebp 库或 libavif 库，无法使用对应格式保存图片',
        'type' => 'warning',
    ],
    [
        'title' => '使用格式转换',
        'desc' => '使用 WebP 或 AVIF 格式保存图片，提高加载速度',
        'id' => 'site_plugin_image_save_format',
        'type' => 'radio',
        'sub' => [
            'off' => '保持原格式',
            'webp' => 'WebP 格式',
            'avif' => 'AVIF 格式',
        ],
        'default' => 'off',
    ],
    [
        'title' => '图片最大宽度限制',
        'desc' => '图片上传的最大宽度，超过宽度会被缩放，设置为 0 则不限制',
        'id' => 'site_plugin_image_max_width',
        'type' => 'number',
        'default' => '0',
    ],
    [
        'title' => '图片保存质量',
        'desc' => '图片保存时的质量，范围 0-100',
        'id' => 'site_plugin_image_quality',
        'type' => 'number',
        'default' => '96',
    ],
    [
        'title' => '水印开关',
        'desc' => '使用图片或文本为图片叠加水印',
        'id' => 'site_plugin_image_watermark_mode',
        'type' => 'radio',
        'sub' => [
            'off' => '禁用',
            'image' => '图片',
            'text' => '文本',
        ],
        'default' => 'off',
    ],
    [
        'title' => '水印位置',
        'desc' => '水印在图片上的位置',
        'id' => 'site_plugin_image_watermark_position',
        'type' => 'select',
        'sub' => [
            'center-center' => '居中',
            'center-left' => '居中左侧',
            'center-right' => '居中右侧',
            'center-top' => '居中顶部',
            'center-bottom' => '居中底部',
            'top-right' => '顶部右侧',
            'top-left' => '顶部左侧',
            'top-center' => '顶部居中',
            'bottom-right' => '底部右侧',
            'bottom-left' => '底部左侧',
            'bottom-center' => '底部居中',
        ],
        'default' => 'bottom-right',
    ],
    [
        'title' => '水印图片文件',
        'desc' => '如果使用图片创建水印，需要上传图片文件（使用绝对路径）',
        'id' => 'site_plugin_image_watermark_image_file',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => '水印字体文件',
        'desc' => '如果使用文本创建水印，需要上传字体文件（使用绝对路径）',
        'id' => 'site_plugin_image_watermark_font_file',
        'type' => 'select',
        'sub' => [
            'light' => 'Light',
            'regular' => 'Regular',
            'medium' => 'Medium',
            'bold' => 'Bold',
        ],
        'default' => 'regular',
    ],
    [
        'title' => '水印文本',
        'desc' => '水印显示的文本',
        'id' => 'site_plugin_image_watermark_text',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => '水印字体大小',
        'desc' => '水印字体的大小',
        'id' => 'site_plugin_image_watermark_font_size',
        'type' => 'number',
        'default' => '24',
    ],
    [
        'title' => '水印透明度',
        'desc' => '水印的透明度，范围 0-100，100 表示完全透明，0 表示完全不透明',
        'id' => 'site_plugin_image_watermark_opacity',
        'type' => 'number',
        'default' => '20',
    ],
);

require_once (__DIR__) . '/inc/imagine-factory.php';
require_once (__DIR__) . '/inc/imagine-watermark-spec.php';
require_once (__DIR__) . '/inc/imagine-watermark-applier.php';
require_once (__DIR__) . '/inc/imagine-thumbnail-generator.php';
require_once (__DIR__) . '/inc/imagine-cover-spec.php';
require_once (__DIR__) . '/inc/imagine-cover-generator.php';
require_once (__DIR__) . '/post-cover-generator.php';

// 计算 Imagine 库使用的保存参数
function aya_image_manager_save_options(string $format = 'jpg'): array
{
    $format = strtolower(trim($format));

    $opt_quality = aya_plugin_opt('site_plugin_image_quality');
    $quality = min(100, max(0, intval($opt_quality)));

    switch ($format) {
        case 'jpg':
        case 'jpeg':
            return ['jpeg_quality' => $quality];
        case 'bmp':
            return ['bmp_quality' => $quality];
        case 'png':
            $png_compression_level = 9 - (int) round(($quality / 100) * 9);
            $png_compression_level = min(9, max(0, $png_compression_level));
            return ['png_compression_level' => $png_compression_level];
        case 'webp':
            return ['webp_quality' => $quality];
        case 'avif':
            return ['avif_quality' => $quality];
    }

    return [];
}

// 图片保存格式参数
function aya_image_manager_save_format(string $source_path = ''): string
{
    $format = aya_plugin_opt('site_plugin_image_save_format');

    if (empty($format) || !in_array($format, ['webp', 'avif']) || $format === 'off') {
        $format =  strtolower(pathinfo($source_path, PATHINFO_EXTENSION));
    }

    $format = strtolower(trim($format));

    return $format;
}

// 转换水印字体文件的路径参数
function aya_image_manager_font_file_path(string $font_file = ''): string
{
    if (empty($font_file)) {
        $font_file = aya_plugin_opt('site_plugin_image_watermark_font_file');
    }

    $font_file = str_replace(
        [
            'light',
            'regular',
            'medium',
            'bold'
        ],
        [
            'AlibabaPuHuiTi-3-45-Light.otf',
            'AlibabaPuHuiTi-3-55-Regular.otf',
            'AlibabaPuHuiTi-3-65-Medium.otf',
            'AlibabaPuHuiTi-3-85-Bold.otf'
        ],
        $font_file
    );

    return get_template_directory() . '/assets/font/' . $font_file;
}

/*
 * ------------------------------------------------------------------------------
 * 缩略图调度器
 * ------------------------------------------------------------------------------
 */

// 计算对应图片的缩略图目标路径
function aya_image_manager_thumb_dest_path(string $source_path, int $width, int $height): string
{
    $format = aya_image_manager_save_format($source_path);

    $hash = sha1($source_path . '|' . $width . '|' . $height . '|' . $format);

    $id = substr($hash, 0, 16);

    $dir = trailingslashit(WP_CONTENT_DIR) . 'thumbnail/' . $width . 'x' . $height;

    // 创建目录
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    return trailingslashit($dir) . $id . '.' . $format;
}

// 生成缩略图
function aya_image_manager_thumb_generate(string $source_path, int $thumb_w, int $thumb_h)
{
    if (empty($source_path)) {
        return false;
    }

    $thumb_w = (int) $thumb_w;
    $thumb_h = (int) $thumb_h;

    if ($thumb_w <= 0 || $thumb_h <= 0) {
        return false;
    }

    $thumb_format = aya_plugin_opt('site_plugin_image_webp_bool') ? 'webp' : 'jpg';

    $dest_path = aya_image_manager_thumb_dest_path($source_path, $thumb_w, $thumb_h, $thumb_format);

    // 缩略图已存在
    if (is_file($dest_path)) {
        return $dest_path;
    }

    // 生成缩略图
    $generator = new AYA_Image_Thumbnail_Generator();
    // 生成保存参数
    $save_options = aya_image_manager_save_options($thumb_format);

    $thumb_local = $generator->generate($source_path, $dest_path, $thumb_w, $thumb_h, $save_options);

    if (!$thumb_local) {
        return false;
    }

    return $thumb_local;
}

// 计算缩略图的相对路径
function aya_image_relpath_thumb_from_local($thumb_local)
{
    if (empty($thumb_local) || !is_string($thumb_local)) {
        return false;
    }

    $wp_content_dir = untrailingslashit(WP_CONTENT_DIR);

    $normalized_file = str_replace(DIRECTORY_SEPARATOR, '/', $thumb_local);
    $normalized_content = str_replace(DIRECTORY_SEPARATOR, '/', $wp_content_dir);

    if (strpos($normalized_file, $normalized_content) !== 0) {
        return false;
    }

    $rel = ltrim(substr($normalized_file, strlen($normalized_content)), '/');

    return $rel !== '' ? $rel : false;
}

//文章缩略图处理
function aya_get_post_thumb($image_url = false, $post_id = 0, $size_w = 400, $size_h = 300)
{
    if ($post_id != 0) {
        // 优先读取 MetaBox 的缩略图缓存
        $thumb_post_meta = get_post_meta($post_id, '_aya_thumb', true);

        // 检查缓存数据
        if (is_string($thumb_post_meta) && $thumb_post_meta !== '') {
            // 拼接完整 URL
            return trailingslashit(WP_CONTENT_URL) . ltrim($thumb_post_meta, '/');
        }
        // 直接删除记录方便刷新缓存
        //delete_post_meta($post_id, '_aya_thumb');
    }

    //没有传入图片URL时开始搜寻缩略图
    if ($image_url == false && $post_id != 0) {
        // 无传入图片则从正文提取首图
        $post_content = get_the_content($post_id);
        $image_url = aya_match_post_first_image($post_content, false);

        //是否为本站图片
        if (cur_is_external_url($image_url)) {
            return $image_url;
        }
    }

    // 仍无图则退回到主题默认
    if ($image_url === false) {
        // 仍无图则退回到主题默认图
        $default_thumb = aya_opt('site_default_thumb_upload', 'basic');
        $image_file = aya_local_path_with_url($default_thumb, true);

        // 直接返回默认图防止被写入记录
        return aya_local_path_with_url(aya_image_manager_thumb_generate($image_file, $size_w, $size_h), false);
    }

    // 生成缩略图流程
    $image_file = aya_local_path_with_url($image_url, true);
    // 生成缩略图
    $thumb_local = aya_image_manager_thumb_generate($image_file, $size_w, $size_h);
    $thumb_url = aya_local_path_with_url($thumb_local, false);

    if ($post_id != 0) {
        // 保存相对路径
        $rel = aya_image_relpath_thumb_from_local($thumb_local);

        if ($rel) {
            update_post_meta($post_id, '_aya_thumb', $rel);
        }
    }

    return $thumb_url;
}

/*
 * ------------------------------------------------------------------------------
 * 媒体库上传控制
 * ------------------------------------------------------------------------------
 */

add_filter('wp_handle_upload', 'aya_image_manager_handle_wp_upload', 20, 2);

// 读取设置水印生成参数
function aya_image_manager_build_watermark_spec(): AYA_Image_Watermark_Spec
{
    $args = [
        'mode_type' =>  aya_plugin_opt('site_plugin_image_watermark_mode'),
        'position' => aya_plugin_opt('site_plugin_image_watermark_position'),
        'font_file' => aya_image_manager_font_file_path(),
        'text' => aya_plugin_opt('site_plugin_image_watermark_text'),
        'image_file' => aya_plugin_opt('site_plugin_image_watermark_image_file'),
        'font_size' => aya_plugin_opt('site_plugin_image_watermark_font_size'),
        'opacity' => aya_plugin_opt('site_plugin_image_watermark_opacity'),
        //'offset_x' => 15,
        //'offset_y' => 15,
    ];

    return AYA_Image_Watermark_Spec::from_array($args);
}

// 处理媒体库上传
function aya_image_manager_handle_wp_upload(array $upload, string $context): array
{
    if (!aya_plugin_opt('site_plugin_image_handle_wp_bool')) {
        return $upload;
    }

    if (empty($upload['file']) || !is_string($upload['file']) || !is_file($upload['file'])) {
        return $upload;
    }

    $mime = isset($upload['type']) && is_string($upload['type']) ? $upload['type'] : '';
    if (strpos($mime, 'image/') !== 0) {
        return $upload;
    }

    $old_file = $upload['file'];
    $new_file = aya_image_manager_process_uploaded_image($old_file);
    if (!is_string($new_file) || $new_file === '' || !is_file($new_file)) {
        return ['error' => '图片处理失败，已取消上传'];
    }

    $new_file = wp_normalize_path($new_file);
    $upload['file'] = $new_file;

    if (!empty($upload['url']) && is_string($upload['url']) && $new_file !== $old_file) {
        $new_basename = wp_basename($new_file);
        $upload['url'] = preg_replace('/[^\/\?#]+(?=[\?#]|$)/', $new_basename, $upload['url']);
    }

    $filetype = wp_check_filetype(wp_basename($new_file));
    if (!empty($filetype['type'])) {
        $upload['type'] = $filetype['type'];
    }

    return $upload;
}

// 检查 WP 当前图像编辑器是否支持目标格式；不支持则回退原格式，避免媒体库丢缩略图。
function aya_image_manager_resolve_supported_format(string $target_format, string $fallback_ext): string
{
    $target_format = strtolower(trim($target_format));
    $fallback_ext = strtolower(trim($fallback_ext));

    if ($target_format === '') {
        return $fallback_ext;
    }

    $mime_map = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'bmp' => 'image/bmp',
    ];

    $target_mime = isset($mime_map[$target_format]) ? $mime_map[$target_format] : '';
    if ($target_mime === '') {
        return $fallback_ext;
    }

    if (!function_exists('wp_image_editor_supports')) {
        return $fallback_ext;
    }

    if (!wp_image_editor_supports(['mime_type' => $target_mime])) {
        return $fallback_ext;
    }

    return $target_format;
}

// 基于本地绝对路径处理上传图片
function aya_image_manager_process_uploaded_image(string $source_path, bool $try_format = true)
{
    if ($source_path === '' || !is_file($source_path)) {
        return false;
    }

    $max_width = intval(aya_plugin_opt('site_plugin_image_max_width'));
    $wm_spec = aya_image_manager_build_watermark_spec();
    $target_format = aya_image_manager_save_format($source_path);

    // 如果在 WP 媒体库上传，测试格式支持
    if (!$try_format) {
        $source_ext = strtolower(pathinfo($source_path, PATHINFO_EXTENSION));
        $target_format = aya_image_manager_resolve_supported_format($target_format, $source_ext);
    }

    $save_options = aya_image_manager_save_options($target_format);

    $applier = new AYA_Image_Upload_Applier();

    return $applier->uploaded_process($source_path, $wm_spec, $target_format, $max_width, $save_options);
}
