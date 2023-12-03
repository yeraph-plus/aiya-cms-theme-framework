<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Security extends AYA_Theme_Setup
{
    var $security_options;

    public function __construct($args)
    {
        $this->security_options = $args;
    }

    public function __destruct()
    {
        parent::add_action('init', 'aya_theme_wp_security_init');
        parent::add_action('init', 'aya_theme_rewind_reject');
        parent::add_action('admin_init', 'aya_theme_admin_login_protection');
    }

    public function aya_theme_wp_security_init()
    {
        $options = $this->security_options;
        //禁用 xmlrpc
        if ($options['disable_xmlrpc'] == true) {
            //操作xmlrpc_methods
            add_filter('xmlrpc_methods', function ($methods) {
                $methods = array(); //empty the array
                return $methods;
            });
            add_filter('xmlrpc_enabled', '__return_false');

            remove_action('xmlrpc_rsd_apis', 'rest_output_link_wp_head', 10);
            remove_action('xmlrpc_pingback.ping', 'rest_output_link_wp_head', 10);
            remove_action('xmlrpc_weblog_ping', 'rest_output_link_wp_head', 10);
        }
        //禁用 PingBack
        if ($options['disable_pingback'] == true) {
            //操作xmlrpc_methods
            add_filter('xmlrpc_methods', function ($methods) {
                $methods['pingback.ping'] = '__return_false';
                $methods['pingback.extensions.getPingbacks'] = '__return_false';
                return $methods;
            });
            //禁用 pingbacks, enclosures, trackbacks
            remove_action('do_pings', 'do_all_pings', 10);
            //禁用 _encloseme 和 do_ping 动作
            remove_action('publish_post', '_publish_post_hook', 5);
        } else {
            //不禁用 PingBack 则阻止 Ping 自己
            add_action('pre_ping', function (&$links) {
                $home = home_url();

                foreach ($links as $l => $link)
                    if (0 === strpos($link, $home)) {
                        unset($links[$l]);
                    }
            });
        }
        //禁用feed功能
        if ($options['disable_feed'] == true) {
            //直接退出
            function rest_disable_feed()
            {
                exit(__('Feed已经被关闭。请询问管理员或检查设置。'));
            }
            add_action('do_feed', 'rest_disable_feed', 1);
            add_action('do_feed_rdf', 'rest_disable_feed', 1);
            add_action('do_feed_rss', 'rest_disable_feed', 1);
            add_action('do_feed_rss2', 'rest_disable_feed', 1);
            add_action('do_feed_atom', 'rest_disable_feed', 1);

            remove_action('wp_head', 'feed_links_extra', 3); //head中的移除Feed接口
            remove_action('wp_head', 'feed_links', 2);
        } else {
            //将自定义的文章类型加入feed
            function feed_tweet_request($query)
            {
                global $aya_post_type;
                if (isset($query['feed']) && !isset($query['post_type'])) {
                    $query['post_type'] = array($aya_post_type);
                }
                return $query;
            }
            add_filter('request', 'feed_tweet_request');
            //在feed中加入查看全文链接
            function feed_read_more($content)
            {
                return $content . '<p><a rel="bookmark" href="' . get_permalink() . '" target="_blank">' . __('查看全文') . '</a></p>';
            }
            add_filter('the_excerpt_rss', 'feed_read_more');
        }
        //禁用 REST API
        if ($options['disable_rest_api'] == true) {
            add_filter('rest_enabled', '__return_false');
            add_filter('rest_jsonp_enabled', '__return_false');
            add_filter('rest_authentication_errors', function ($access) {
                return new WP_Error('rest_cannot_access', __('REST API已经被禁用。请询问管理员或检查设置。'), array('status' => 403));
            });
        }
        //使 REST API 验证用户登录
        if ($options['disable_rest_api'] == false && $options['add_rest_api_logged_verify'] == true) {
            add_filter('rest_authentication_errors', function ($result) {
                //DEBUG
                if (true === $result || is_wp_error($result)) {
                    return $result;
                }
                if (!is_user_logged_in()) {
                    return new WP_Error('rest_not_logged_in', __('REST API用户验证失败。请询问管理员或检查设置。'), array('status' => 401));
                }
                return $result;
            });
        }
    }

    public function aya_theme_rewind_reject()
    {
        $options = $this->security_options;
        //获取设置
        if ($options['add_access_reject'] == true) {
            $key_list = (empty($options['add_access_reject_list'])) ? array() : explode(',', $options['add_access_reject_list']);
            $key_list = array_map('trim', $key_list);

            if (count($key_list) > 0) {
                //循环
                foreach ($key_list as $key) {
                    //获取请求参数
                    if (isset($_GET[$key])) {
                        header('HTTP/1.1 403 Forbidden');
                        exit;
                    }
                }
            }
        }
    }

    public function aya_theme_admin_login_protection()
    {
        $options = $this->security_options;
        //登录页面添加参数
        if ($options['add_admin_login_protection'] == true) {
            //如果未登录
            if (!is_user_logged_in()) {
                $args_args = $options['admin_login_args'];
                $args_pass = $options['admin_login_args_val'];
                //获取请求参数
                if ($_GET[$args_args] != $args_pass) {
                    //重定向
                    wp_redirect('/');
                }
            }
        }
        //检查登录用户权限
        if ($options['add_admin_logged_verify'] == true) {
            if (is_admin()) {
                //DEBUG：过滤AJAX请求防止影响第三方登录方式
                if (!current_user_can('edit_posts') && $_SERVER['PHP_SELF'] != '/wp-admin/admin-ajax.php') {
                    //重定向
                    wp_redirect('/');
                }
            }
        }
        //隐藏登录面板错误提示
        if ($options['disable_login_errors'] == true) {
            add_filter('login_errors', '__return_false');
        }
        //禁用找回密码
        if ($options['disable_allow_password_reset'] == true) {
            add_filter('allow_password_reset', '__return_false');
        }
        //禁用管理员邮箱确认
        if ($options['disable_admin_email_check'] == true) {
            add_filter('admin_email_check_interval', '__return_false');
        }
    }
}
