<?php

if (!defined('ABSPATH')) {
    exit;
}

class AYA_Image_Cover_Spec
{
    public $model = 'photo'; // photo | pattern
    public $width = 800;
    public $height = 600;

    public $background_image = '';
    public $background_color = '';

    public $font_file = '';
    public $font_size = 70;
    public $title = '';
    public $title_color = '';

    public $overlay_opacity = 30;
    public $max_chars = 15;
    public $line_spacing = 12;
    public $pattern_material_path = '';
    public $label_alpha = 45;
    public $label_padding_x = 32;
    public $label_padding_y = 16;

    /**
     * 从数组构建封面配置
     *
     * @param array $args 允许的 key:
     * - model(photo|pattern)
     * - width/height
     * - background_image(本地绝对路径)
     * - background_color
     * - font_file(本地绝对路径)
     * - font_size(字体大小)
     * - title(标题文本)
     * - title_color(例如 "#ffffff")
     * - overlay_opacity(0-100)
     * - max_chars(默认 15)
     * - line_spacing(行距像素)
     * - pattern_material_path(花纹素材目录或单文件路径)
     * - label_alpha(衬色层透明度参数)
     * - label_padding_x(衬色左右扩展像素)
     * - label_padding_y(衬色上下扩展像素)
     */
    public static function from_array(array $args): self
    {
        $spec = new self();

        if (isset($args['model']) && is_string($args['model']) && $args['model'] !== '') {
            $spec->model = $args['model'];
        }
        if (isset($args['width'])) {
            $spec->width = max(1, (int) $args['width']);
        }
        if (isset($args['height'])) {
            $spec->height = max(1, (int) $args['height']);
        }

        if (isset($args['background_image']) && is_string($args['background_image'])) {
            $spec->background_image = $args['background_image'];
        }
        if (isset($args['background_color']) && is_string($args['background_color']) && $args['background_color'] !== '') {
            $spec->background_color = $args['background_color'];
        }

        if (isset($args['font_file']) && is_string($args['font_file'])) {
            $spec->font_file = $args['font_file'];
        }
        if (isset($args['font_size'])) {
            $spec->font_size = max(1, (int) $args['font_size']);
        }
        if (isset($args['title']) && is_string($args['title'])) {
            $spec->title = $args['title'];
        }
        if (isset($args['title_color']) && is_string($args['title_color']) && $args['title_color'] !== '') {
            $spec->title_color = $args['title_color'];
        }

        if (isset($args['overlay_opacity'])) {
            $spec->overlay_opacity = min(100, max(0, (int) $args['overlay_opacity']));
        }
        if (isset($args['max_chars'])) {
            $spec->max_chars = max(1, (int) $args['max_chars']);
        }
        if (isset($args['line_spacing'])) {
            $spec->line_spacing = max(0, (int) $args['line_spacing']);
        }
        if (isset($args['pattern_material_path']) && is_string($args['pattern_material_path'])) {
            $spec->pattern_material_path = $args['pattern_material_path'];
        }
        if (isset($args['label_alpha'])) {
            $spec->label_alpha = min(100, max(0, (int) $args['label_alpha']));
        }
        if (isset($args['label_padding_x'])) {
            $spec->label_padding_x = max(0, (int) $args['label_padding_x']);
        }
        if (isset($args['label_padding_y'])) {
            $spec->label_padding_y = max(0, (int) $args['label_padding_y']);
        }

        if ($spec->background_color === '') {
            $spec->background_color = self::random_pretty_color();
        }

        return $spec;
    }

    // 相同 fingerprint 输入参数，复用同一个输出文件
    public function fingerprint(): string
    {
        $data = [
            'model' => (string) $this->model,
            'width' => (int) $this->width,
            'height' => (int) $this->height,
            'background_image' => (string) $this->background_image,
            'background_color' => (string) $this->background_color,
            'font_file' => (string) $this->font_file,
            'font_size' => (int) $this->font_size,
            'title' => (string) $this->title,
            'title_color' => (string) $this->title_color,
            'overlay_opacity' => (int) $this->overlay_opacity,
            'max_chars' => (int) $this->max_chars,
            'line_spacing' => (int) $this->line_spacing,
            'pattern_material_path' => (string) $this->pattern_material_path,
            'label_alpha' => (int) $this->label_alpha,
            'label_padding_x' => (int) $this->label_padding_x,
            'label_padding_y' => (int) $this->label_padding_y,
        ];

        return sha1(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    // 未显式设置背景色时，从柔和配色中随机选一个
    private static function random_pretty_color(): string
    {
        $palette = [
            '#1f2937',
            '#334155',
            '#3b4a6b',
            '#4b5563',
            '#1d4e89',
            '#2f3e46',
            '#3d405b',
            '#264653',
            '#2b2d42',
            '#374151',
        ];

        return $palette[array_rand($palette)];
    }
}
