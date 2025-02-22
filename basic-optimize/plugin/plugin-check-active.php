<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 主题PHP版本检测/拓展检测小功能
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

class AYA_Plugin_PluginCheck extends AYA_Theme_Setup
{
    public function __construct($args)
    {
        //Fix 确保函数存在
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach ($args as $plugin => $plugin_path) {
            return self::aya_theme_wp_plugin_activation_check($plugin, $plugin_path);
        }
    }

    //检查插件是否启用
    public function aya_theme_wp_plugin_activation_check($plugin_name, $plugin_entry_path)
    {
        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_entry_path;
        //检查文件存在
        if (!file_exists($plugin_path)) {
            return false;
        }
        //检测插件状态
        if (!is_plugin_active($plugin_path)) {
            //获取插件信息
            $plugin_data = get_plugin_data($plugin_path);
            $plugin_name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin_name;

            $message = sprintf(__('请启用 %s 插件以确保功能正常。', 'AIYA'), $plugin_name);

            add_action('admin_notices', function () use ($message) {
                echo '<div class="notice notice-error is-dismissible"><p>'
                    . esc_html($message)
                    . '</p></div>';
            });

            return false;
        }

        return true;
    }
}
