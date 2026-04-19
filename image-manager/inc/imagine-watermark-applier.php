<?php

if (!defined('ABSPATH')) {
    exit;
}

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Box;
use Imagine\Image\Point;

class AYA_Image_Upload_Applier
{
    private $imagine;

    public function __construct(?ImagineInterface $imagine = null)
    {
        $this->imagine = $imagine ?: AYA_Image_Imagine_Factory::create();
    }

    // 处理上传图片
    public function uploaded_process(
        string $source_path,
        AYA_Image_Watermark_Spec $wm_spec,
        string $target_format,
        int $max_width = 0,
        array $save_options = []
    ) {
        if ($source_path === '' || !is_file($source_path) || (@exif_imagetype($source_path) === false)) {
            return false;
        }

        $source_ext = strtolower(pathinfo($source_path, PATHINFO_EXTENSION));
        $target_format = strtolower(trim($target_format));

        if ($target_format === '') {
            $target_format = $source_ext;
        }

        // 仅当目标扩展名变化时，格式转换允许删除源文件
        $need_convert = strcasecmp($target_format, $source_ext) !== 0;

        // 处理路径中的反斜杠
        $dir = str_replace('\\', '/', dirname($source_path));
        $name = pathinfo($source_path, PATHINFO_FILENAME);
        $dest_path = $dir . '/' . $name . '.' . $target_format;

        try {
            $image = $this->imagine->open($source_path);
            if ($max_width > 0) {
                $image = $this->resize_by_max_width($image, $max_width);
            }
            $image = $this->apply_watermark($image, $wm_spec);

            $ext = strtolower(pathinfo($dest_path, PATHINFO_EXTENSION));
            $opts = $this->merge_save_options($ext, $save_options);

            $image->save($dest_path, $opts);

            if ($need_convert && is_file($dest_path)) {
                @unlink($source_path);
            }

            return is_file($dest_path) ? $dest_path : false;
        } catch (Throwable $e) {
            return false;
        }
    }

    // 应用水印
    public function apply_watermark(ImageInterface $image, AYA_Image_Watermark_Spec $spec): ImageInterface
    {
        $type = $spec->type();

        if ($type === 'off') {
            return $image;
        }

        $mark = null;

        if ($type === 'image') {
            try {
                $mark = $this->imagine->open($spec->image_file);
            } catch (Throwable $e) {
                return $image;
            }
        } elseif ($type === 'text') {
            $mark = $this->create_text_watermark($spec);

            if (!$mark) {
                return $image;
            }
        }

        if (!$mark) {
            return $image;
        }

        $base_size = $image->getSize();
        $mark_size = $mark->getSize();

        $point = $this->compute_position(
            $spec->position,
            $base_size->getWidth(),
            $base_size->getHeight(),
            $mark_size->getWidth(),
            $mark_size->getHeight(),
            (int) $spec->offset_x,
            (int) $spec->offset_y
        );

        if (!$point) {
            return $image;
        }

        $image->paste($mark, $point);

        return $image;
    }

    // 创建文字水印
    private function create_text_watermark(AYA_Image_Watermark_Spec $spec)
    {
        try {
            $palette = new RGB();

            $alpha = min(100, max(0, (int) $spec->opacity));
            $shadow_alpha = min(100, $alpha + 20);

            $font_color = $palette->color('#ffffff', $alpha);
            $shadow_color = $palette->color('#333333', $shadow_alpha);
            // 透明画布（alpha 通道），避免粘贴时出现黑色底块。
            $background = $palette->color([0, 0, 0], 0);

            $font = $this->imagine->font($spec->font_file, (int) $spec->font_size, $font_color);
            $box = $font->box($spec->text);

            $shift_x = -2;
            $shift_y = -2;
            $padding = 2;
            $canvas_w = $box->getWidth() + ($padding * 2) + abs($shift_x);
            $canvas_h = $box->getHeight() + ($padding * 2) + abs($shift_y);
            $canvas = $this->imagine->create(new Box($canvas_w, $canvas_h), $background);

            $base_x = $padding + max(0, -$shift_x);
            $base_y = $padding + max(0, -$shift_y);

            // 第一层：阴影字
            $shadow_font = $this->imagine->font($spec->font_file, (int) $spec->font_size, $shadow_color);
            $canvas->draw()->text($spec->text, $shadow_font, new Point($base_x, $base_y));
            // 第二层：主字，左移 2px 下移 2px
            $canvas->draw()->text($spec->text, $font, new Point($base_x + $shift_x, $base_y + $shift_y));

            return $canvas;
        } catch (Throwable $e) {
            return false;
        }
    }

    // 计算水印位置
    private function compute_position(
        string $position,
        int $base_w,
        int $base_h,
        int $mark_w,
        int $mark_h,
        int $offset_x,
        int $offset_y
    ) {
        $size_x = (int) floor(($base_w - $mark_w) / 2);
        $size_y = (int) floor(($base_h - $mark_h) / 2);

        switch ($position) {
            case 'center-center':
                return new Point($size_x, $size_y);
            case 'center-left':
                return new Point((int) floor($size_x / 2), $size_y);
            case 'center-right':
                return new Point($size_x + (int) floor($size_x / 2), $size_y);
            case 'center-top':
                return new Point($size_x, (int) floor($size_y / 2));
            case 'center-bottom':
                return new Point($size_x, $size_y + (int) floor($size_y / 2));
            case 'top-left':
                return new Point($offset_x, $offset_y);
            case 'top-center':
                return new Point($size_x, $offset_y);
            case 'top-right':
                return new Point($size_x * 2 - $offset_x, $offset_y);
            case 'bottom-left':
                return new Point($offset_x, $size_y * 2 - $offset_y);
            case 'bottom-center':
                return new Point($size_x, $size_y * 2 - $offset_y);
            case 'bottom-right':
                return new Point($size_x * 2 - $offset_x, $size_y * 2 - $offset_y);
            default:
                return false;
        }
    }

    // 合并默认保存参数
    private function merge_save_options(string $ext, array $save_options): array
    {
        $defaults = [];

        if ($ext === 'jpg' || $ext === 'jpeg') {
            if (!isset($save_options['jpeg_quality'])) {
                $defaults['jpeg_quality'] = 96;
            }
        } elseif ($ext === 'bmp') {
            if (!isset($save_options['bmp_quality'])) {
                $defaults['bmp_quality'] = 96;
            }
        } elseif ($ext === 'png') {
            if (!isset($save_options['png_compression_level'])) {
                $defaults['png_compression_level'] = 9;
            }
        } elseif ($ext === 'webp') {
            if (!isset($save_options['webp_quality'])) {
                $defaults['webp_quality'] = 96;
            }
        } elseif ($ext === 'avif') {
            if (!isset($save_options['avif_quality'])) {
                $defaults['avif_quality'] = 96;
            }
        } elseif ($ext === 'gif') {
            if (!isset($save_options['flatten'])) {
                $defaults['flatten'] = false;
            }
        }

        return array_merge($defaults, $save_options);
    }

    // 超过最大宽度时按比例缩放，不放大
    private function resize_by_max_width(ImageInterface $image, int $max_width): ImageInterface
    {
        $size = $image->getSize();
        $origin_w = $size->getWidth();
        $origin_h = $size->getHeight();

        if ($max_width <= 0 || $origin_w <= 0 || $origin_h <= 0 || $origin_w <= $max_width) {
            return $image;
        }

        $target_h = (int) floor(($origin_h * $max_width) / $origin_w);
        $target_h = max(1, $target_h);

        return $image->resize(new Box($max_width, $target_h));
    }
}
