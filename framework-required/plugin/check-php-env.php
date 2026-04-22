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

class AYA_Plugin_PHP_Env_Check
{
    public function __construct($args)
    {
        self::aya_theme_check_php_version($args['php_last']);
        self::aya_theme_check_php_loaded($args['php_ext']);
        self::aya_theme_check_wp_version($args['wp_last']);
    }

    //检查PHP版本
    public function aya_theme_check_php_version($php_last_version)
    {
        $php_version = phpversion();

        if (version_compare($php_version, $php_last_version, '<')) {
            //提示信息
            $message = sprintf(__('您正在使用过时的PHP版本<code>%s</code>， AIYA-Framework 拓展 主题需要PHP版本大于<code>%s</code>才能完整使用全部功能，请升级PHP版本。', 'aiya-framework'), $php_version, $php_last_version);

            add_action('admin_notices', function () use ($message) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
            });

            return false;
        }

        return true;
    }

    //检查PHP扩展
    public function aya_theme_check_php_loaded($php_need_ext)
    {
        $not_ext = [];

        foreach ($php_need_ext as $ext) {
            if (!extension_loaded($ext)) {
                $not_ext[] = '<code>' . $ext . '</code>';
            }
        }

        if (count($not_ext) > 0) {
            $message = sprintf(__('您的PHP缺少扩展%s，缺少这些扩展可能导致部分功能无法使用，请及时安装这些扩展。', 'aiya-framework'), implode(', ', $not_ext));

            add_action('admin_notices', function () use ($message) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
            });

            return false;
        }

        return true;
    }

    //检查WordPress版本
    public function aya_theme_check_wp_version($wp_last_version)
    {
        //检查WordPress版本
        global $wp_version;

        if (version_compare($wp_version, $wp_last_version, '<')) {
            $message = sprintf(__('请升级 WordPress 到%s以上版本。', 'aiya-framework'), $wp_last_version);

            add_action('admin_notices', function () use ($message) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
            });

            return false;
        }

        return true;
    }
}
