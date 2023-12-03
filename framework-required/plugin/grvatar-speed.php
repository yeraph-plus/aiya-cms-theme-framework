<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Grvatar_Speed extends AYA_Theme_Setup
{
    var $avatar_options;

    public function __construct($args)
    {

        $this->avatar_options = $args;
    }

    public function __destruct()
    {
        add_filter('get_avatar', array(&$this, 'aya_theme_replace_gravatar_cdn'));
        add_filter('get_avatar_url', array(&$this, 'aya_theme_replace_gravatar_cdn'));
    }
    //替换GravatarCDN源
    public function aya_theme_replace_gravatar_cdn($avatar)
    {
        $options = $this->avatar_options;

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
}

class AYA_Plugin_Default_Cravatar
{
    public function __destruct()
    {
        add_filter('um_user_avatar_url_filter', array(&$this, 'get_cravatar_url'), 1);
        add_filter('bp_gravatar_url', array(&$this, 'get_cravatar_url'), 1);
        add_filter('get_avatar_url', array(&$this, 'get_cravatar_url'), 1);
        add_filter('avatar_defaults', array(&$this, 'set_defaults_for_cravatar'), 1);
        add_filter('user_profile_picture_description', array(&$this, 'set_user_profile_picture_for_cravatar'), 1);
    }
    //替换Gravatar为国内Cravatar
    public function get_cravatar_url($url)
    {
        $sources = array(
            'www.gravatar.com',
            '0.gravatar.com',
            '1.gravatar.com',
            '2.gravatar.com',
            'secure.gravatar.com',
            'cn.gravatar.com',
            'gravatar.com',
        );
        return str_replace($sources, 'cravatar.cn', $url);
    }

    public function set_defaults_for_cravatar($avatar_defaults)
    {
        $avatar_defaults['gravatar_default'] = 'Cravatar 标志';

        return $avatar_defaults;
    }

    public function set_user_profile_picture_for_cravatar()
    {
        return '<a href="https://cravatar.cn" target="_blank">您可以在 Cravatar 修改您的资料图片</a>';
    }
}
