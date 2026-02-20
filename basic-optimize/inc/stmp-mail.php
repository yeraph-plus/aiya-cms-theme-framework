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
            parent::add_filter('wp_new_user_notification_email_admin', '__return_false');
        }
        //关闭新用户注册用户邮件通知
        if ($options['disable_new_user_email_user'] !== false) {
            parent::add_filter('wp_new_user_notification_email', '__return_false');
        }


        parent::add_action('phpmailer_init', 'aya_theme_mail_smtp_option');
    }

    public function __destruct() {}

    public function aya_theme_mail_smtp_option($phpmailer)
    {
        $options = $this->phpmailer_options;

        //检查最少配置
        if (empty($options['smtp_host']) || empty($options['smtp_from'])) {
            return;
        }

        global $phpmailer;

        if (!($phpmailer instanceof PHPMailer\PHPMailer\PHPMailer)) {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
            require_once ABSPATH . WPINC . '/class-wp-phpmailer.php';

            $phpmailer = new WP_PHPMailer(true);

            $phpmailer::$validator = static function ($email) {
                return (bool) is_email($email);
            };
        }

        //发件人
        $from_email =  (empty($options['smtp_from'])) ? $options['smtp_from'] : '';
        $from_name  = (empty($options['smtp_from_name'])) ? $options['smtp_from_name'] : '';
        $content_type = (empty($options['smtp_content_type'])) ? $options['smtp_content_type'] : '';

        //配置SMTP设置
        $phpmailer->isSMTP();

        try {
            $phpmailer->setFrom($from_email, $from_name, false);
        } catch (PHPMailer\PHPMailer\Exception $e) {
            return;
        }

        $phpmailer->Host = $options['smtp_host'];
        $phpmailer->Port = intval($options['smtp_port']);

        //设置加密方式
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

        //关闭自动TLS
        $phpmailer->SMTPAutoTLS = false;

        //强制设置邮件内容类型
        if (! isset($content_type)) {
            $content_type = 'text/plain';
        }

        $content_type = apply_filters('wp_mail_content_type', $content_type);

        $phpmailer->ContentType = $content_type;

        if ('text/html' === $content_type) {
            $phpmailer->isHTML(true);
        }

        //强制设置邮件字符集
        if (! isset($charset)) {
            $charset = get_bloginfo('charset');
        }

        $phpmailer->CharSet = apply_filters('wp_mail_charset', $charset);

        //关闭SSL证书验证
        if (!empty($options['smtp_disable_ssl_verification'])) {
            $phpmailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ),
            );
        }

        //通过过滤器添加 Reply-To
        $reply_to = apply_filters('smtpmailer_reply_to', '');
        if (!empty($reply_to)) {
            $addresses = array_map('trim', explode(',', $reply_to));
            foreach ($addresses as $addr) {
                if (!empty($addr)) {
                    try {
                        $phpmailer->addReplyTo($addr);
                    } catch (PHPMailer\PHPMailer\Exception $e) {
                    }
                }
            }
        }

        // 调试模式
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $phpmailer->SMTPDebug = 2;
            $phpmailer->Debugoutput = 'html';
        }
    }

    //使用 wp_mail() 函数发件方法
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
