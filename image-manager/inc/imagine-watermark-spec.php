<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 水印参数模型（纯数据）
 *
 * - 由 setup.php 负责从 WP 配置/Filter 组装数组，再转换为此模型
 * - 生成器只依赖此模型和 Imagine，不直接读取 WP 配置
 */
class AYA_Image_Watermark_Spec
{
    public $mode_type = 'off';
    public $position = 'bottom-right';
    public $font_file = '';
    public $text = '';
    public $image_file = '';
    public $font_size = 24;
    public $opacity = 80;
    public $offset_x = 15;
    public $offset_y = 15;

    /**
     * 从数组构建水印模型
     *
     * @param array $args 允许的 key:
     * - enabled(bool)
     * - mode(string, image|text)
     * - position(string)
     * - font_file(string)
     * - text(string)
     * - image_file(string)
     * - font_size(int)
     * - opacity(int, 0-100)
     * - offset_x(int)
     * - offset_y(int)
     */
    public static function from_array(array $args): self
    {
        $spec = new self();

        if (isset($args['mode_type']) && is_string($args['mode_type'])) {
            $type = strtolower(trim($args['mode_type']));
            if (in_array($type, ['off', 'image', 'text'], true)) {
                $spec->mode_type = $type;
            }
        }
        if (isset($args['position']) && is_string($args['position']) && $args['position'] !== '') {
            $spec->position = $args['position'];
        }
        if (isset($args['font_file']) && is_string($args['font_file'])) {
            $spec->font_file = $args['font_file'];
        }
        if (isset($args['text']) && is_string($args['text'])) {
            $spec->text = $args['text'];
        }
        if (isset($args['image_file']) && is_string($args['image_file'])) {
            $spec->image_file = $args['image_file'];
        }
        if (isset($args['font_size'])) {
            $spec->font_size = max(1, (int) $args['font_size']);
        }
        if (isset($args['opacity'])) {
            $spec->opacity = min(100, max(0, (int) $args['opacity']));
        }
        if (isset($args['offset_x'])) {
            $spec->offset_x = (int) $args['offset_x'];
        }
        if (isset($args['offset_y'])) {
            $spec->offset_y = (int) $args['offset_y'];
        }

        return $spec;
    }

    /**
     * 推导水印类型
     *
     * @return string off|image|text
     */
    public function type(): string
    {
        if ($this->mode_type === 'off') {
            return 'off';
        }

        if ($this->mode_type === 'image' && $this->image_file !== '' && is_file($this->image_file)) {
            return 'image';
        }

        if ($this->mode_type === 'text' && $this->text !== '' && $this->font_file !== '' && is_file($this->font_file)) {
            return 'text';
        }

        return 'off';
    }

    /**
     * 指纹（用于缓存隔离）
     *
     * 任何影响输出效果的参数变化，都应导致 fingerprint 改变。
     */
    public function fingerprint(): string
    {
        $data = [
            'mode_type' => (string) $this->mode_type,
            'position' => (string) $this->position,
            'font_file' => (string) $this->font_file,
            'text' => (string) $this->text,
            'image_file' => (string) $this->image_file,
            'font_size' => (int) $this->font_size,
            'opacity' => (int) $this->opacity,
            'offset_x' => (int) $this->offset_x,
            'offset_y' => (int) $this->offset_y,
        ];

        return sha1(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
