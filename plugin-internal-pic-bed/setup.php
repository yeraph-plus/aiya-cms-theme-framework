<?php
if (!defined('ABSPATH')) exit;

//检查插件加载位置
if (defined('AYA_RELEASE')) {
    define('PIC_PLUGIN_URI', AYA_URI . '/plugins/plugin-internal-pic-bed');
} else {
    define('PIC_PLUGIN_URI', untrailingslashit(plugin_dir_url(__FILE__)));
}
//插件功能
if (is_admin()) {
    //引入 TinyMCE 插件
    require_once (__DIR__) . '/modify-upload-picbed.php';
    //启动
    AYA_Shortcode_Pic_Bed::instance();
}
//AIYA-CMS 短代码组件：简码图床
function aya_shortcode_pic_bed_image($atts = array(), $content = '')
{
    $atts = shortcode_atts(
        array(
            'src' => '10086.cn',
        ),
        $atts,
    );

    return esc_html($content) . $atts['src'];
}
add_shortcode('pic_bed', 'aya_shortcode_pic_bed_image');
