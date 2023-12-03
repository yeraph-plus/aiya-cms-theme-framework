<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Mail_Sender extends AYA_Theme_Setup
{
    var $phpmailer_options;

    public function __construct($args)
    {
        $this->phpmailer_options = $args;
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_wp_mail_init');

        if ($this->phpmailer_options['stmp_action'] == false) return;
        parent::add_action('phpmailer_init', 'aya_theme_mail_smtp_option');
    }

    public function aya_theme_wp_mail_init()
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
        $phpmailer->SMTPSecure = $options['smtp_ssl'] ? 'ssl' : '';
        $phpmailer->Username = $options['smtp_username'];
        $phpmailer->Password = $options['smtp_password'];
        $phpmailer->SMTPAuth = $options['smtp_auth'] ? true : false;
    }
}
