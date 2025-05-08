<?php

if (!defined('ABSPATH')) exit;

/**
 * AIYA-Framework 拓展 STMP送信插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.1
 **/

class AYA_Plugin_Mail_Sender extends AYA_Plugin_Setup
{
    public $phpmailer_options;

    public function __construct($args)
    {
        $this->phpmailer_options = $args;
    }

    public function __destruct()
    {
        $options = $this->phpmailer_options;
        //关闭新用户注册通知站长的邮件
        if ($options['disable_new_user_email_admin'] == false) {
            add_filter('wp_new_user_notification_email_admin', '__return_false');
        }
        //关闭新用户注册用户邮件通知
        if ($options['disable_new_user_email_user'] == false) {
            add_filter('wp_new_user_notification_email', '__return_false');
        }

        parent::add_action('phpmailer_init', 'aya_theme_mail_smtp_option');
    }

    public function aya_theme_mail_smtp_option($phpmailer)
    {
        $options = $this->phpmailer_options;
        //检查启用状态
        if ($options['stmp_action'] == false) return;
        //替代邮件设置
        $phpmailer->IsSMTP();
        $phpmailer->From = $options['smtp_from'];
        $phpmailer->FromName = $options['smtp_from_name'];
        $phpmailer->Host = $options['smtp_host'];
        $phpmailer->Port = $options['smtp_port'];
        $phpmailer->SMTPSecure = ($options['smtp_ssl'] === 'yes') ? 'ssl' : '';
        $phpmailer->Username = $options['smtp_username'];
        $phpmailer->Password = $options['smtp_password'];
        $phpmailer->SMTPAuth = ($options['smtp_auth'] === 'yes') ? true : false;
    }

    public function send_callback($data)
    {
        $data = array(
            'to' => '',
            'subject' => '',
            'message' => '',
        );
        //送信
        return wp_mail($data['to'], $data['subject'], $data['message']);
    }
}
