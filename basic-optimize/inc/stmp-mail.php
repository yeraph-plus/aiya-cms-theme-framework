<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-Framework 拓展 STMP送信插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.2
 **/

class AYA_Plugin_Mail_Sender extends AYA_Plugin_Setup
{
    public $phpmailer_options;

    public function __construct($args)
    {
        $this->phpmailer_options = $args;

        $options = $this->phpmailer_options;
        //关闭新用户注册通知站长的邮件
        if ($options['disable_new_user_email_admin'] !== false) {
            add_filter('wp_new_user_notification_email_admin', '__return_false');
        }
        //关闭新用户注册用户邮件通知
        if ($options['disable_new_user_email_user'] !== false) {
            add_filter('wp_new_user_notification_email', '__return_false');
        }

        parent::add_action('phpmailer_init', 'aya_theme_mail_smtp_option');
    }

    public function __destruct()
    {
    }

    public function aya_theme_mail_smtp_option($phpmailer)
    {
        $options = $this->phpmailer_options;
        $option_action = filter_var($options['stmp_action'], FILTER_VALIDATE_BOOLEAN);

        //检查启用状态
        if (!isset($options['stmp_action']) || !$option_action) {
            return;
        }

        //检查最少配置
        if (empty($options['smtp_host']) || empty($options['smtp_from'])) {
            return;
        }

        // 配置SMTP设置
        $phpmailer->isSMTP();
        $phpmailer->setFrom($options['smtp_from'], $options['smtp_from_name']);
        $phpmailer->Host = $options['smtp_host'];
        $phpmailer->Port = intval($options['smtp_port']);

        // 设置加密方式
        if ($options['smtp_secure'] === 'ssl') {
            $phpmailer->SMTPSecure = 'ssl';
        } else if ($options['smtp_secure'] === 'tls') {
            $phpmailer->SMTPSecure = 'tls';
        } else {
            $phpmailer->SMTPSecure = '';
        }

        //用户认证
        $option_auth = filter_var($options['smtp_auth'], FILTER_VALIDATE_BOOLEAN);

        if ($option_auth) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $options['smtp_username'];
            $phpmailer->Password = $options['smtp_password'];
        } else {
            $phpmailer->SMTPAuth = false;
        }

        // 调试模式（可选）
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $phpmailer->SMTPDebug = 2;
        }
    }

    public function send_callback($data)
    {
        $data = array(
            'to' => '',
            'subject' => '',
            'message' => '',
        );

        $data = wp_parse_args($data, $data);

        //确保数据不为空
        if (empty($data['to']) || empty($data['subject'])) {
            return false;
        }

        //送信
        return wp_mail($data['to'], $data['subject'], $data['message']);
    }
}
