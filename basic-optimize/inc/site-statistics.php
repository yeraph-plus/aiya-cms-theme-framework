<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 添加百度统计或谷歌统计添加额外代码
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.1
 **/

class AYA_Plugin_Head_Extra
{
    public $extra_action;

    public function __construct($args)
    {
        $this->extra_action = $args;

        add_action('wp_head', array($this, 'aya_theme_site_code_extra'));
    }

    public function __destruct() {}

    //额外代码
    public function aya_theme_site_code_extra()
    {
        if (is_preview()) return;

        $action = $this->extra_action;

        $head = '';
        //检查设置
        if ($action['site_google_analytics'] != '') {
            $head .= self::add_google_analytics($action['site_google_analytics']) . "\n";
        }
        if ($action['site_extra_script'] != '') {
            $head .= '<script type="text/javascript">' . $action['site_extra_script'] . '</script>' . "\n";
        }
        if ($action['site_extra_css'] != '') {
            $head .= '<style type="text/css">' . $action['site_extra_css'] . '</style>' . "\n";
        }
        echo $head;
    }

    //插入谷歌统计代码
    public function add_google_analytics($id)
    {
        if ($id == '') return;

        $script = '<!-- Google tag (gtag.js) -->';
        $script .= '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr($id) . '"></script>';
        $script .= '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag("js",new Date());gtag("config","' . esc_attr($id) . '");</script>';

        return $script;
    }
}
