<?php

if (!defined('ABSPATH')) exit;

/*
 * Name: Gravatar头像 谷歌字体 替换CDN加速
 * Version: 1.0.0
 * Author: AIYA-CMS
 * Author URI: https://www.yeraph.com
 */

class AYA_Plugin_CDN_Speed extends AYA_Theme_Setup
{
    public $speed_options;

    public function __construct($args)
    {

        $this->speed_options = $args;
    }

    public function __destruct()
    {
        $options = $this->speed_options;
        //$options['site_default_avatar']
        if ($options['site_default_avatar'] != '') {
            parent::add_filter('avatar_defaults', 'aya_theme_default_avatar');
        }
        if ($options['use_speed_gravatar'] == true && $options['use_speed_weavatar'] == false) {
            parent::add_filter('get_avatar', 'aya_theme_replace_gravatar_cdn');
            parent::add_filter('get_avatar_url', 'aya_theme_replace_gravatar_cdn');
        }
        if ($options['use_speed_google_fonts'] == true) {
            parent::add_filter('style_loader_tag', 'aya_theme_replace_google_fonts_cdn', 999, 4);
        }
        if ($options['use_speed_weavatar'] == true) {
            parent::add_filter('um_user_avatar_url_filter', 'get_weavatar_url', 1);
            parent::add_filter('bp_gravatar_url', 'get_weavatar_url', 1);
            parent::add_filter('get_avatar_url', 'get_weavatar_url', 1);
            parent::add_filter('um_user_avatar_url_filter', 'get_weavatar_url', PHP_INT_MAX);
            parent::add_filter('bp_gravatar_url', 'get_weavatar_url', PHP_INT_MAX);
            parent::add_filter('get_avatar_url', 'get_weavatar_url', PHP_INT_MAX);
            parent::add_filter('avatar_defaults', 'set_defaults_for_weavatar', 1);
            parent::add_filter('user_profile_picture_description', 'set_user_profile_picture_for_weavatar', 1);
        }
    }
    //创建一个自定义的头像标志
    public function aya_theme_default_avatar($avatar_defaults)
    {
        $options = $this->speed_options;
        //图文url路径
        $myavatar = $options['site_default_avatar'];
        //图片的描述名称
        $avatar_defaults[$myavatar] = __('默认头像');

        return $avatar_defaults;
    }
    //替换GravatarCDN源
    public function aya_theme_replace_gravatar_cdn($avatar)
    {
        $options = $this->speed_options;

        $gravatar_sources = array(
            'www.gravatar.com',
            '0.gravatar.com',
            '1.gravatar.com',
            '2.gravatar.com',
            'secure.gravatar.com',
            'cn.gravatar.com',
            'gravatar.com',
        );
        //替换CDN
        switch ($options['avatar_cdn_type']) {
            case 'qiniu':
                $url = 'dn-qiniu-avatar.qbox.me';
                break;
            case 'loli':
                $url = 'gravatar.loli.net';
                break;
            case 'v2ex':
                $url = 'cdn.v2ex.com/gravatar/';
                break;
            default:
                $url = 'cn.gravatar.com';
                break;
        }
        //自定义镜像
        if ($options['avatar_cdn_custom'] != '') {
            $url = $options['avatar_cdn_custom'];
        }
        //替换SSL
        if ($options['avatar_ssl'] == true) {
            $url = str_replace("http://", "https://", $url);
        }

        return str_replace($gravatar_sources, $url, $avatar);
    }
    //替换谷歌字体加速
    public function aya_theme_replace_google_fonts_cdn($fonts)
    {
        $options = $this->speed_options;

        $sources = array(
            'googleapis_fonts' => '//fonts.googleapis.com',
            'googleapis_ajax' => '//ajax.googleapis.com',
            'googleusercontent_themes' => '//themes.googleusercontent.com',
            'gstatic_fonts' => '//fonts.gstatic.com'
        );
        //替换CDN
        switch ($options['google_fonts_cdn_type']) {
            case 'geekzu':
                $replace = [
                    '//fonts.geekzu.org',
                    '//gapis.geekzu.org/ajax',
                    '//gapis.geekzu.org/g-themes',
                    '//gapis.geekzu.org/g-fonts'
                ];
                break;
            case 'loli':
                $replace = [
                    '//fonts.loli.net',
                    '//ajax.loli.net',
                    '//themes.loli.net',
                    '//gstatic.loli.net'
                ];
                break;
            case 'ustc':
                $replace = [
                    '//fonts.lug.ustc.edu.cn',
                    '//ajax.lug.ustc.edu.cn',
                    '//google-themes.lug.ustc.edu.cn',
                    '//fonts-gstatic.lug.ustc.edu.cn'
                ];
                break;
            case 'custom':
                $replace = [
                    $options['google_fonts_cdn_custom']['fonts_cdn'],
                    $options['google_fonts_cdn_custom']['fonts_ajax'],
                    $options['google_fonts_cdn_custom']['fonts_themes'],
                    $options['google_fonts_cdn_custom']['fonts_gstatic'],
                ];
                break;
            default:
                $replace = [
                    '//fonts.googleapis.com',
                    '//ajax.googleapis.com',
                    '//themes.googleusercontent.com',
                    '//fonts.gstatic.com'
                ];
                break;
        }
        return array_map(str_replace(['http://', 'https://'], '//', $replace), array_keys($sources), $sources);
    }
    //替换Gravatar为国内WeAvatar
    public function get_weavatar_url($url)
    {
        $sources = array(
            'www.gravatar.com',
            '0.gravatar.com',
            '1.gravatar.com',
            '2.gravatar.com',
            'secure.gravatar.com',
            'cn.gravatar.com',
            'gravatar.com',
            'sdn.geekzu.org',
            'gravatar.duoshuo.com',
            'gravatar.loli.net',
            'cravatar.cn',
        );
        return str_replace($sources, 'weavatar.com', $url);
    }
    public function set_defaults_for_weavatar($avatar_defaults)
    {
        $avatar_defaults['gravatar_default'] = 'WeAvatar 头像';
        return $avatar_defaults;
    }
    public function set_user_profile_picture_for_weavatar()
    {
        return '<a href="https://weavatar.com" target="_blank">您可以在 WeAvatar 修改您的资料图片</a>';
    }
}
