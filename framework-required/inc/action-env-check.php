<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_EnvCheck extends AYA_Theme_Setup
{
    var $env_check;

    public function __construct($args)
    {
        $this->env_check = $args;
    }

    public function __destruct()
    {
        parent::add_action('admin_notices', 'aya_theme_env_check');
        parent::add_action('admin_notices', 'aya_theme_wp_check');
    }

    public function aya_theme_env_check()
    {
        $content = [];
        $check = $this->env_check;

        //检查PHP版本
        $php_version = phpversion();
        $last_version = $check['php_last'];

        if (version_compare($php_version, $last_version, '<')) {
            $content[] = '<p>您正在使用过时的PHP版本<code>' . $php_version . '</code>，AIYA-CMS主题需要PHP版本大于<code>' . $last_version . '</code>才能完整使用全部功能，请升级PHP版本。</p>';
        }
        //检查PHP扩展
        $need_ext = $check['php_ext'];
        $not_ext = [];

        foreach ($need_ext as $ext) {
            if (!extension_loaded($ext)) {
                $not_ext[] = '<code>' . $ext . '</code>';
            }
        }

        if (count($not_ext) > 0) {
            $content[] = '<p>您的PHP缺少扩展' . implode(', ', $not_ext) . '，缺少这些扩展可能导致部分功能无法使用，请及时安装这些扩展。</p>';
        }

        //返回错误信息
        if (!empty($content)) {
            echo '<div id="message" class="error">' . (join('', $content)) . '</div>';
        }
    }

    public function aya_theme_wp_check()
    {
        $content = [];
        $check = $this->env_check;
        // 检查 WordPress 版本
        global $wp_version;
        $wp_last = $check['wp_last'];
        if (version_compare($wp_version, $wp_last, '<')) {
            $content[] = '<p>请升级WordPress到' . $wp_last . '以上版本。</p>';
        }

        // 检查经典编辑器是否启用
        if ($check['check_classic_editor'] == true) {
            if (!class_exists('Classic_Editor')) {
                $content[] = '<p>经典编辑器插件未启用。</p>';
            }
        }
        //返回错误信息
        if (!empty($content)) {
            echo '<div id="message" class="notice">' . (join('', $content)) . '</div>';
        }
    }
}
