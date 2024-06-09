<?php

if (!defined('ABSPATH')) exit;

/*
 * Name: WP 添加百度统计或谷歌统计
 * Version: 1.0.0
 * Author: AIYA-CMS
 * Author URI: https://www.yeraph.com
 */

class AYA_Plugin_Head_Extra extends AYA_Theme_Setup
{
    public $extra_action;

    public function __construct($args)
    {
        $this->extra_action = $args;
    }

    public function __destruct()
    {
        parent::add_action('wp_head', 'aya_theme_site_code_extra');
    }
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
        if ($action['site_baidu_tongji'] != '') {
            $head .= self::add_baidu_tongji($action['site_baidu_tongji']) . "\n";
        }
        if ($action['site_extra_script'] != '') {
            $head .= '<script>' . $action['site_extra_script'] . '</script>' . "\n";
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
        $script .= '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $id . '"></script>';
        $script .= '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag("js",new Date());gtag("config","' . $id . '");</script>';

        return $script;
    }
    //插入百度统计代码
    public function add_baidu_tongji($id)
    {
        if ($id == '') return;

        $script = '<script type="text/javascript">var _hmt=_hmt||[];(function(){var hm=document.createElement("script");hm.src="https://hm.baidu.com/hm.js?' . $id . '";hm.setAttribute("async","true");document.getElementsByTagName("head")[0].appendChild(hm)})();</script>';

        return $script;
    }
}
