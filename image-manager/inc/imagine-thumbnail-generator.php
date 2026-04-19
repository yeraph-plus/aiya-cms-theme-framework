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
 * 缩略图生成器（纯 Imagine）
 *
 * 职责：
 * - 基于源图生成指定尺寸的 cover+center crop 缩略图
 * - 可选叠加文字/图片水印（由 WatermarkSpec 描述）
 *
 * 边界：
 * - 不调用 WordPress API
 * - 源图/目标路径都必须是“本地绝对路径”（由 setup.php 负责解析）
 */
class AYA_Image_Thumbnail_Generator
{
    private $imagine;

    public function __construct(?ImagineInterface $imagine = null)
    {
        $this->imagine = $imagine ?: AYA_Image_Imagine_Factory::create();
    }

    /**
     * 生成缩略图
     *
     * @param string                     $source_path   原图绝对路径
     * @param string                     $dest_path     目标文件绝对路径
     * @param int                        $width         目标宽度
     * @param int                        $height        目标高度
     * @param array                      $save_options  Imagine 保存参数（jpeg_quality/webp_quality 等）
     *
     * @return string|false 成功返回目标路径
     */
    public function generate(
        string $source_path,
        string $dest_path,
        int $width,
        int $height,
        array $save_options = []
    ) {
        if ($source_path === '' || $dest_path === '') {
            return false;
        }
        if ($width <= 0 || $height <= 0) {
            return false;
        }
        if (!is_file($source_path)) {
            return false;
        }

        $dest_dir = dirname($dest_path);
        if (!is_dir($dest_dir)) {
            if (!mkdir($dest_dir, 0755, true) && !is_dir($dest_dir)) {
                return false;
            }
        }

        if (is_file($dest_path)) {
            return $dest_path;
        }

        try {
            $image = $this->imagine->open($source_path);
        } catch (Throwable $e) {
            return false;
        }

        try {
            $image = $this->render_thumbnail_frame($image, $width, $height);
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

    // 缩放并居中裁剪
    private function render_thumbnail_frame(ImageInterface $image, int $width, int $height): ImageInterface
    {
        if (!$this->should_use_blur_composite($image, $width, $height)) {
            return $this->resize_cover_crop_center($image, $width, $height);
        }

        return $this->resize_with_blur_composite($image, $width, $height);
    }

    // 比例差异过大时启用双层渲染
    private function should_use_blur_composite(ImageInterface $image, int $width, int $height): bool
    {
        $origin_size = $image->getSize();
        $origin_w = $origin_size->getWidth();
        $origin_h = $origin_size->getHeight();
        if ($origin_w <= 0 || $origin_h <= 0 || $width <= 0 || $height <= 0) {
            return false;
        }

        $origin_ratio = $origin_w / $origin_h;
        $target_ratio = $width / $height;
        $ratio_gap = abs(log($origin_ratio / $target_ratio));

        return $ratio_gap >= 0.35;
    }

    // 使用「背景裁剪+模糊遮罩+前景等比缩放」生成缩略图
    private function resize_with_blur_composite(ImageInterface $image, int $width, int $height): ImageInterface
    {
        $background = $this->resize_cover_crop_center($image->copy(), $width, $height);
        $background->effects()->blur(16);

        // 叠加一层遮罩
        $palette = new RGB();
        $overlay = $this->imagine->create(new Box($width, $height), $palette->color([255, 255, 255], 55));
        $background->paste($overlay, new Point(0, 0));

        $foreground = $this->resize_contain_center($image->copy(), $width, $height);
        $fg_size = $foreground->getSize();
        $offset_x = (int) floor(($width - $fg_size->getWidth()) / 2);
        $offset_y = (int) floor(($height - $fg_size->getHeight()) / 2);
        $background->paste($foreground, new Point($offset_x, $offset_y));

        return $background;
    }

    // 等比缩放到目标区域内（不裁剪）
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

    // 缩放并居中裁剪
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
}
