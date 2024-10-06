<?php
if (!defined('ABSPATH')) exit;

//检查插件加载位置
if (defined('AYA_RELEASE')) {
    define('MCE_PLUGIN_URI', AYA_URI . '/plugins/framework-required');
} else {
    define('MCE_PLUGIN_URI', untrailingslashit(plugin_dir_url(__FILE__)));
}
//插件功能
function aya_tinymce_modify_setup()
{
    //检查经典编辑器是否启用
    if (!is_plugin_active('classic-editor/classic-editor.php')) return;
    //引入 TinyMCE 插件
    require_once (__DIR__) . '/modify-classic-editor.php';
    //组件参数
    $args = array(
        //按钮重排
        'tinymce_filter_buttons' => true,
        //本地粘贴图片自动上传
        'tinymce_upload_image' => false,
        //向编辑器中注册新的插件
        'tinymce_add_plugins' => array(
            //'image' => MCE_PLUGIN_URI . '/assects/mce-plugin/image.plugin.min.js',
            //'media' => MCE_PLUGIN_URI . '/assects/mce-plugin/media.plugin.min.js',
            'advlist' => MCE_PLUGIN_URI . '/assects/mce-plugin/advlist.plugin.min.js',
            'table' => MCE_PLUGIN_URI . '/assects/mce-plugin/table.plugin.min.js',
            'toc' => MCE_PLUGIN_URI . '/assects/mce-plugin/toc.plugin.min.js',
            'code' => MCE_PLUGIN_URI . '/assects/mce-plugin/code.plugin.min.js',
            'codesample' => MCE_PLUGIN_URI . '/assects/mce-plugin/codesample.plugin.min.js',
            'textpattern' => MCE_PLUGIN_URI . '/assects/mce-plugin/textpattern.plugin.min.js',
        ),
        //向编辑器中注册插件按钮
        //'tinymce_add_buttons' => array('btnCode', 'btnPanel', 'btnPost', 'btnVideo', 'btnMusic',),
    );
    //启动
    new AYA_Modify_TinyMCE($args);
}
add_action('admin_init', 'aya_tinymce_modify_setup');
