<?php

if (!defined('ABSPATH')) {
    exit;
}

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;

/**
 * 文章封面生成器
 *
 * 支持两种模型：
 * - photo：传入图片做背景 + 黑色半透明蒙版 + 居中标题
 * - pattern：纯色背景 + 随机“花纹片段”叠加 + 居中标题
 *
 * 关键点：
 * - 标题默认限制 15 字，并会按字数自动拆成 1~2 行居中绘制
 * - 标题颜色使用“背景平均色取反”得到，保证与背景对比度
 *
 * 边界：
 * - 不调用 WordPress API（路径解析、缓存目录创建、URL↔path 转换由 setup.php 负责）
 */

class AYA_Image_Cover_Generator
{
    private $imagine;

    public function __construct(?ImagineInterface $imagine = null)
    {
        $this->imagine = $imagine ?: AYA_Image_Imagine_Factory::create();
    }

    /**
     * 生成封面图片
     *
     * @param AYA_Image_Cover_Spec $spec        封面配置模型
     * @param string                  $dest_path   目标文件绝对路径
     * @param array                   $save_options Imagine 保存参数（jpeg_quality/webp_quality 等）
     *
     * @return string|false 成功返回目标路径
     */
    public function generate(AYA_Image_Cover_Spec $spec, string $dest_path, array $save_options = [])
    {
        if ($dest_path === '') {
            return false;
        }

        $dest_dir = dirname($dest_path);
        if (!is_dir($dest_dir)) {
            if (!mkdir($dest_dir, 0755, true) && !is_dir($dest_dir)) {
                return false;
            }
        }

        $w = (int) $spec->width;
        $h = (int) $spec->height;

        if ($w <= 0 || $h <= 0) {
            return false;
        }

        try {
            if ($spec->model === 'pattern') {
                $image = $this->build_pattern_background($spec, $w, $h);
            } else {
                $image = $this->build_photo_background($spec, $w, $h);
            }

            $image = $this->draw_center_title($image, $spec, $w, $h);
        } catch (Throwable $e) {
            return false;
        }

        $ext = strtolower(pathinfo($dest_path, PATHINFO_EXTENSION));
        $opts = $this->merge_save_options($ext, $save_options);

        try {
            $image->save($dest_path, $opts);
        } catch (Throwable $e) {
            return false;
        }

        return is_file($dest_path) ? $dest_path : false;
    }

    // 使用传入背景图+黑色半透明蒙版（photo 模式）
    private function build_photo_background(AYA_Image_Cover_Spec $spec, int $w, int $h): ImageInterface
    {
        $base = null;

        if ($spec->background_image !== '' && is_file($spec->background_image)) {
            $base = $this->imagine->open($spec->background_image);
            $base = $this->resize_cover_crop_center($base, $w, $h);
        }

        if (!$base) {
            $palette = new RGB();
            $base = $this->imagine->create(new Box($w, $h), $palette->color('#000000', 100));
        }

        $palette = new RGB();
        $overlay = $this->imagine->create(new Box($w, $h), $palette->color([0, 0, 0], (int) $spec->overlay_opacity));
        $base->paste($overlay, new Point(0, 0));

        return $base;
    }

    // 使用纯色背景+随机背景叠加（pattern 模式）
    private function build_pattern_background(AYA_Image_Cover_Spec $spec, int $w, int $h): ImageInterface
    {
        $palette = new RGB();
        $bg_hex = $this->normalize_hex_color((string) $spec->background_color);
        // alpha: 0 = 不透明，100 = 全透明。pattern 底色应为不透明层。
        $base = $this->imagine->create(new Box($w, $h), $palette->color($bg_hex, 100));

        $pattern = $this->load_pattern_source_from_material($spec, $w, $h);

        if ($pattern) {
            $piece = $this->resize_contain_center($pattern, $w, $h);
            $piece_size = $piece->getSize();
            $place_x = (int) floor(($w - $piece_size->getWidth()) / 2);
            $place_y = (int) floor(($h - $piece_size->getHeight()) / 2);
            $base->paste($piece, new Point($place_x, $place_y));
        }

        return $base;
    }

    // 读取外部花纹素材（目录随机或单文件），转换为可裁剪画布
    private function load_pattern_source_from_material(AYA_Image_Cover_Spec $spec, int $w, int $h)
    {
        $material = trim((string) $spec->pattern_material_path);
        if ($material === '') {
            return false;
        }

        $file = $this->pick_random_pattern_file($material);
        if ($file === false) {
            return false;
        }

        try {
            $image = $this->imagine->open($file);
            return $this->resize_keep_full_height($image, $w, $h);
        } catch (Throwable $e) {
            return false;
        }
    }

    // 目录随机取图；若传入的是文件路径则直接返回
    private function pick_random_pattern_file(string $material)
    {
        if (is_file($material)) {
            return $material;
        }
        if (!is_dir($material)) {
            return false;
        }

        $patterns = [
            '*.jpg',
            '*.jpeg',
            '*.png',
            '*.webp',
            '*.avif',
            '*.bmp',
        ];

        $files = [];
        foreach ($patterns as $pattern) {
            $found = glob(rtrim($material, '/\\') . DIRECTORY_SEPARATOR . $pattern);
            if (is_array($found) && !empty($found)) {
                $files = array_merge($files, $found);
            }
        }

        if (empty($files)) {
            return false;
        }

        return $files[array_rand($files)];
    }

    // 缩略图缩放并居中裁剪图片
    private function resize_cover_crop_center(ImageInterface $image, int $width, int $height): ImageInterface
    {
        $origin_size = $image->getSize();
        $origin_w = $origin_size->getWidth();
        $origin_h = $origin_size->getHeight();

        $ratio = max($width / $origin_w, $height / $origin_h);
        $scaled = $origin_size->scale($ratio);

        $image->resize($scaled);

        $crop_x = (int) floor(($scaled->getWidth() - $width) / 2);
        $crop_y = (int) floor(($scaled->getHeight() - $height) / 2);

        return $image->crop(new Point($crop_x, $crop_y), new Box($width, $height));
    }

    // 素材按高度适配，完整保留高度
    private function resize_keep_full_height(ImageInterface $image, int $width, int $height): ImageInterface
    {
        $origin_size = $image->getSize();
        $origin_w = $origin_size->getWidth();
        $origin_h = $origin_size->getHeight();

        if ($origin_w <= 0 || $origin_h <= 0 || $height <= 0) {
            return $image;
        }

        $ratio = $height / $origin_h;
        $target_w = max(1, (int) floor($origin_w * $ratio));
        $target_h = max(1, (int) floor($origin_h * $ratio));

        $scaled = new Box($target_w, $target_h);
        return $image->resize($scaled);
    }

    // 等比缩放到目标范围内并保持完整内容（用于 pattern 素材居中贴合）
    private function resize_contain_center(ImageInterface $image, int $width, int $height): ImageInterface
    {
        $origin_size = $image->getSize();
        $origin_w = $origin_size->getWidth();
        $origin_h = $origin_size->getHeight();

        if ($origin_w <= 0 || $origin_h <= 0) {
            return $image;
        }

        $ratio = min($width / $origin_w, $height / $origin_h);
        $target_w = max(1, (int) floor($origin_w * $ratio));
        $target_h = max(1, (int) floor($origin_h * $ratio));

        return $image->resize(new Box($target_w, $target_h));
    }

    // 遮罩色取整图平均色；若平均色偏亮则取反，避免浅色背景下遮罩不明显
    private function compute_label_mask_color(ImageInterface $image, int $w, int $h): string
    {
        $avg = $this->sample_average_rgb($image, $w, $h);
        $brightness = (int) floor(0.299 * $avg['r'] + 0.587 * $avg['g'] + 0.114 * $avg['b']);

        if ($brightness >= 128) {
            $avg = [
                'r' => 255 - $avg['r'],
                'g' => 255 - $avg['g'],
                'b' => 255 - $avg['b'],
            ];
        }

        return sprintf('#%02x%02x%02x', $avg['r'], $avg['g'], $avg['b']);
    }

    // 近似算法估算整体亮度/主色趋势
    private function sample_average_rgb(ImageInterface $image, int $w, int $h): array
    {
        $steps = 20;
        $sum_r = 0;
        $sum_g = 0;
        $sum_b = 0;
        $count = 0;

        for ($i = 0; $i < $steps; $i++) {
            for ($j = 0; $j < $steps; $j++) {
                $x = (int) floor(($w - 1) * $i / max(1, $steps - 1));
                $y = (int) floor(($h - 1) * $j / max(1, $steps - 1));

                $c = $image->getColorAt(new Point($x, $y));
                $sum_r += (int) $c->getRed();
                $sum_g += (int) $c->getGreen();
                $sum_b += (int) $c->getBlue();
                $count++;
            }
        }

        if ($count <= 0) {
            return ['r' => 0, 'g' => 0, 'b' => 0];
        }

        return [
            'r' => (int) floor($sum_r / $count),
            'g' => (int) floor($sum_g / $count),
            'b' => (int) floor($sum_b / $count),
        ];
    }

    // 居中绘制标题
    /**
     * 标题先截断到 max_chars
     * 再按长度拆分为 1~2 行
     * 每行分别测量 box，整体块垂直居中，行内水平居中
     * 通过偏移层+主文字双次绘制，形成轻微立体感
     */
    private function draw_center_title(
        ImageInterface $image,
        AYA_Image_Cover_Spec $spec,
        int $w,
        int $h
    ): ImageInterface {
        $title = $this->normalize_title($spec->title, (int) $spec->max_chars);
        if ($title === '' || $spec->font_file === '' || !is_file($spec->font_file)) {
            return $image;
        }
        $lines = $this->split_title_lines($title);

        $text_color = (string) $spec->title_color;
        $label_base_color = $this->compute_label_mask_color($image, $w, $h);


        $palette = new RGB();
        $font_hex = trim($text_color) !== '' ? $text_color : '#ffffff';
        $font_color = $palette->color($font_hex, 100);
        $font = $this->imagine->font($spec->font_file, (int) $spec->font_size, $font_color);
        $shadow_hex = $this->pick_shadow_hex($font_hex);
        $shadow_color = $palette->color($shadow_hex, 45);
        $shadow_font = $this->imagine->font($spec->font_file, (int) $spec->font_size, $shadow_color);
        $shadow_offset = 2;

        $label_alpha = min(100, max(0, (int) $spec->label_alpha));
        $label_padding_x = max(0, (int) $spec->label_padding_x);
        $label_padding_y = max(0, (int) $spec->label_padding_y);
        $label_hex = trim($label_base_color) !== '' ? $label_base_color : '#000000';
        $label_color = $palette->color($label_hex, $label_alpha);

        $line_boxes = [];
        $max_width = 0;
        $total_height = 0;
        foreach ($lines as $line) {
            $box = $font->box($line);
            $line_boxes[] = $box;
            $max_width = max($max_width, $box->getWidth());
            $total_height += $box->getHeight();
        }
        $total_height += max(0, count($lines) - 1) * (int) $spec->line_spacing;

        $start_y = (int) floor(($h - $total_height) / 2);
        $current_y = $start_y;
        $line_positions = [];

        foreach ($lines as $idx => $line) {
            $box = $line_boxes[$idx];
            $x = (int) floor(($w - $box->getWidth()) / 2);
            $y = $current_y;
            $line_positions[] = ['line' => $line, 'x' => $x, 'y' => $y, 'box' => $box];

            $label_x1 = max(0, $x - $label_padding_x);
            $label_y1 = max(0, $y - $label_padding_y);
            $label_x2 = min($w - 1, $x + $box->getWidth() + $label_padding_x);
            $label_y2 = min($h - 1, $y + $box->getHeight() + $label_padding_y);
            $image->draw()->rectangle(
                new Point($label_x1, $label_y1),
                new Point($label_x2, $label_y2),
                $label_color,
                true
            );

            $current_y += $box->getHeight() + (int) $spec->line_spacing;
        }

        // 先绘制所有遮罩，再双次绘制文字（偏移层 -> 主文字）。
        foreach ($line_positions as $item) {
            $line = $item['line'];
            $x = $item['x'];
            $y = $item['y'];
            $image->draw()->text($line, $shadow_font, new Point($x + $shadow_offset, $y + $shadow_offset));
            $image->draw()->text($line, $font, new Point($x, $y));
        }

        return $image;
    }

    // DEBUG：掉多余空白、去掉换行、截断
    private function normalize_title(string $title, int $max_chars): string
    {
        $title = trim(preg_replace('/\s+/u', ' ', str_replace(["\r", "\n"], ' ', $title)));
        if ($title === '') {
            return '';
        }

        return mb_substr($title, 0, max(1, $max_chars), 'UTF-8');
    }

    // 标题拆行计算，> 7 字：按长度对半拆分两行
    private function split_title_lines(string $title): array
    {
        $len = mb_strlen($title, 'UTF-8');
        if ($len <= 7) {
            return [$title];
        }

        $cut = (int) ceil($len / 2);
        $a = mb_substr($title, 0, $cut, 'UTF-8');
        $b = mb_substr($title, $cut, $len - $cut, 'UTF-8');

        if ($b === '') {
            return [$a];
        }

        return [$a, $b];
    }

    // 根据字体色选择阴影颜色
    private function pick_shadow_hex(string $text_hex): string
    {
        $rgb = $this->hex_to_rgb($text_hex);
        $bright = (int) floor(0.299 * $rgb['r'] + 0.587 * $rgb['g'] + 0.114 * $rgb['b']);

        return $bright >= 128 ? '#000000' : '#ffffff';
    }

    // 标准化十六进制颜色
    private function normalize_hex_color(string $hex): string
    {
        $rgb = $this->hex_to_rgb($hex);
        return sprintf('#%02x%02x%02x', $rgb['r'], $rgb['g'], $rgb['b']);
    }

    // 十六进制色值转 RGB（#RGB/#RRGGBB）
    private function hex_to_rgb(string $hex): array
    {
        $hex = trim($hex);
        if ($hex === '') {
            return ['r' => 0, 'g' => 0, 'b' => 0];
        }
        if ($hex[0] === '#') {
            $hex = substr($hex, 1);
        }
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return ['r' => 0, 'g' => 0, 'b' => 0];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    // 合并默认保存参数
    private function merge_save_options(string $ext, array $save_options): array
    {
        $defaults = [];

        if ($ext === 'jpg' || $ext === 'jpeg') {
            if (!isset($save_options['jpeg_quality'])) {
                $defaults['jpeg_quality'] = 96;
            }
        } elseif ($ext === 'webp') {
            if (!isset($save_options['webp_quality'])) {
                $defaults['webp_quality'] = 96;
            }
        } elseif ($ext === 'avif') {
            if (!isset($save_options['avif_quality'])) {
                $defaults['avif_quality'] = 96;
            }
        }

        return array_merge($defaults, $save_options);
    }
}
