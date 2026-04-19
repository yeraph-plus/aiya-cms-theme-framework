<?php

if (!defined('ABSPATH')) {
    exit;
}

use Imagine\Gd\Imagine as GdImagine;
use Imagine\Imagick\Imagine as ImagickImagine;
use Imagine\Image\ImagineInterface;

class AYA_Image_Imagine_Factory
{
    // 选择驱动并返回 ImagineInterface 实例
    public static function create(): ImagineInterface
    {
        if (extension_loaded('imagick')) {
            return new ImagickImagine();
        }

        return new GdImagine();
    }
}
