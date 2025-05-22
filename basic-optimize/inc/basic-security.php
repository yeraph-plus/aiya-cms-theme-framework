<?php

if (!defined('ABSPATH'))
    exit;

/**
 * AIYA-Framework 拓展 WP安全性优化功能插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.5
 **/

class AYA_Plugin_Security
{
    public $security_options;

    public function __construct($args)
    {
        $this->security_options = $args;
    }

    public function __destruct()
    {
        add_action('admin_init', array($this, 'aya_theme_admin_backend_verify'));

        add_filter('allow_password_reset', array($this, 'aya_theme_disallow_password_reset'), 10, 2);

        add_filter('pre_user_name', array($this, 'aya_theme_prevent_disable_admin_user'));
        add_filter('pre_user_login', array($this, 'aya_theme_prevent_clean_admin_user'));

        add_filter('authenticate', array($this, 'aya_theme_logged_disable_admin_user'), 30, 3);

        add_filter('authenticate', array($this, 'aya_theme_allow_email_login'), 20, 3);

        //add_action('wp_login_failed', array($this, 'aya_theme_limit_login_attempts'), 10, 1);
        //add_action('wp_login', array($this, 'aya_theme_reset_login_attempts'), 10, 1);
        //add_filter('authenticate', array($this, 'aya_theme_logged_scope_limit_verify'), 10, 3);

        add_action('login_form', array($this, 'aya_theme_page_modify_login_form'));
        add_action('login_form_login', array($this, 'aya_theme_page_modify_login_action'));
        add_filter('login_form_bottom', array($this, 'aya_theme_page_modify_login_form_bottom'), 10, 2);

        add_filter('shake_error_codes', array($this, 'aya_theme_logged_shake_error_codes'));

        add_filter('wp_sitemaps_add_provider', array($this, 'aya_theme_remove_sitemap_users_provider'), 10, 2);

        add_filter('rest_endpoints', array($this, 'aya_theme_remove_restapi_users_endpoint'));

    }
    //在 WP-Sitemap 中跳过输出 users 列表
    public function aya_theme_remove_sitemap_users_provider($provider, $name)
    {
        $options = $this->security_options;

        if ($options['remove_sitemaps_users_provider']) {
            return ($name == 'users') ? false : $provider;
        }
    }
    //清理 REST-API 默认端点
    public function aya_theme_remove_restapi_users_endpoint($endpoints)
    {
        $options = $this->security_options;

        if ($options['remove_restapi_users_endpoint']) {
            if (isset($endpoints['/wp/v2/users'])) {
                unset($endpoints['/wp/v2/users']);
            }

            if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
                unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
            }
        }
        if ($options['remove_restapi_posts_endpoint']) {
            if (isset($endpoints['/wp/v2/posts'])) {
                unset($endpoints['/wp/v2/posts']);
            }
        }

        return $endpoints;
    }
    //限制后台访问
    public function aya_theme_admin_backend_verify()
    {
        if (!is_admin()) {
            return;
        }

        //DEBUG：跳过AJAX请求防止影响第三方登录方式
        if ($_SERVER['PHP_SELF'] == '/wp-admin/admin-ajax.php') {
            return;
        }

        $options = $this->security_options;

        //检查登录用户权限
        switch ($options['admin_backend_verify']) {
            case 'administrator':
                $verify = current_user_can('manage_options');
                break;
            case 'editor':
                $verify = current_user_can('publish_pages');
                break;
            case 'author':
                $verify = current_user_can('publish_posts');
                break;
            case 'contributor':
                $verify = current_user_can('edit_posts');
                break;
            case 'subscriber':
                $verify = current_user_can('read');
                break;
            default:
                $verify = true;
                break;
        }
        //重定向
        if ($verify === false) {
            wp_redirect('/');
        }
    }
    //强制使用邮箱登录
    public function aya_theme_allow_email_login($user, $username, $password)
    {
        if (is_email($username)) {
            $user = get_user_by('email', $username);

            if ($user)
                $username = $user->user_login;
        }

        return wp_authenticate_username_password(null, $username, $password);
    }
    //限制特定权限用户修改密码
    public function aya_theme_disallow_password_reset($allow, $user)
    {
        if (!$allow) {
            return false;
        }

        $options = $this->security_options;

        if ($options['admin_disallow_password_reset'] == true) {

            //验证用户角色
            $user = get_userdata($user);

            if (in_array('administrator', $user->roles)) {
                return false;
            }
        }

        return true;
    }
    //禁用 admin 用户名注册
    public function aya_theme_prevent_disable_admin_user($user)
    {
        $options = $this->security_options;

        if ($options['logged_sanitize_user_enable'] == true) {
            //排除用户名
            if ($options['logged_prevent_user_name'] != '') {
                //重建数组
                $disable_user_map = explode(',', $options['logged_prevent_user_name']);
                $disable_user_map = array_map('trim', $disable_user_map);
            }

            $disable_user_map = (empty($disable_user_map)) ? array() : $disable_user_map;

            //验证用户名是否出现在数组中
            if (in_array($user, $disable_user_map)) {
                return false;
            }
        }
        return $user;
    }
    //清理用户名中包含的 admin 字符串
    public function aya_theme_prevent_clean_admin_user($username)
    {
        $options = $this->security_options;

        if ($options['logged_sanitize_user_enable'] == true) {
            //排除用户名
            if ($options['logged_register_user_name'] != '') {
                //重建数组
                $disable_user_map = explode(',', $options['logged_register_user_name']);
                $disable_user_map = array_map('trim', $disable_user_map);
            }

            $disable_user_map = (empty($disable_user_map)) ? array() : $disable_user_map;

            //移除用户名中的指定字符
            $cleaned_username = str_ireplace($disable_user_map, '', $username);

            //返回清理后的用户名
            return $cleaned_username;
        }
        return $username;
    }
    //禁用 admin 用户名进行身份验证
    public function aya_theme_logged_disable_admin_user($user, $username, $password)
    {
        $options = $this->security_options;

        if ($options['logged_sanitize_user_enable'] == true) {
            //排除用户名
            if ($options['logged_prevent_user_name'] != '') {
                //重建数组
                $disable_user_map = explode(',', $options['logged_prevent_user_name']);
                $disable_user_map = array_map('trim', $disable_user_map);
            }

            $disable_user_map = (empty($disable_user_map)) ? array() : $disable_user_map;

            //验证用户名是否出现在数组中
            if (in_array($username, $disable_user_map)) {

                $err_id = 'login_username_not_access';
                $err_msg = __('Sorry, you cannot log in with this username.');

                $user = new WP_Error($err_id, $err_msg);
            }
        }
        return $user;
    }
    /*
    //设置允许的最大登录尝试次数
    public function aya_theme_limit_login_attempts($username)
    {
        $options = $this->security_options;

        //当前用户的登录尝试次数
        $attempts = get_transient('login_attempts_' . $username);
        $attempts = $attempts ?: 0;

        //获取限制时间
        $defend_time = $options['logged_scope_limit_times'];
        $defend_time = (is_numeric($defend_time)) ? (intval($defend_time)) : 15;

        //增加登录尝试计数
        $attempts++;
        //限制时间窗口
        set_transient('login_attempts_' . $username, $attempts, MINUTE_IN_SECONDS * $defend_time);
    }
    //登录成功后重置登录尝试次数
    public function aya_theme_reset_login_attempts($user)
    {
        //重置登录尝试次数
        delete_transient('login_attempts_' . $user->user_login);
    }
    //验证登录次数
    public function aya_theme_logged_scope_limit_verify($user, $username, $password)
    {
        $options = $this->security_options;

        if ($options['logged_scope_limit_enable'] == true) {
            //允许最大登录尝试次数
            $max_attempts = 5;

            //当前用户的登录尝试次数
            $attempts = get_transient('login_attempts_' . $username);

            //获取限制时间
            $defend_time = $options['logged_scope_limit_times'];
            $defend_time = (is_numeric($defend_time)) ? (intval($defend_time)) : 15;

            //进行攻防时间
            if ($attempts >= $max_attempts) {
                remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
                remove_filter('authenticate', 'wp_authenticate_email_password', 20, 3);

                $err_id = 'login_too_many_retries';
                $err_msg = __('You have tried multiple failed logins, please try again in ' . $defend_time . ' minutes!');

                return new WP_Error($err_id, $err_msg);
            }
        }
        return $user;
    }
    */

    //登录页功能

    //替换登陆页面表单为防守消息
    public function aya_theme_page_modify_login_form()
    {
        $options = $this->security_options;

        if ($options['login_page_param_verify'] == false) {

            //添加一个随机数隐藏段用于验证表单
            wp_nonce_field('secure-login-nonce-action', 'secure-login-nonce');
            return;
        }
        //获取参数设置
        $auth_param = $options['login_page_param_args'];
        //等待时间设置
        $wait_time = 5;
        //验证访问
        if (!isset($_GET['auth']) || $_GET['auth'] != $auth_param) {
            //发送404回执
            http_response_code(404);
            //输出消息
            $html = '';
            $html .= '<div id="secure-login-wrapper" style="position: relative;">';

            if ($options['login_page_auto_jump_times'] == true) {
                $html .= '<img src="' . esc_url(get_admin_url() . 'images/spinner.gif') . '" style="position: absolute;" />';
                $html .= '<div id="wait-for-secure-login" style="padding-left: 30px;">';
                $html .= '<p>' . __('Securing log-in to') . ' ';
                $html .= '<span id="wait-time-seconds">' . $wait_time . '</span>';
                $html .= ' ' . __('seconds left.') . '</p>';
                $html .= '<div id="redirect-to-secure-login" style="padding-left: 30px;">' . __('Redirect to secure login.') . '</div>';
                $html .= '</div>';
            } else {
                $html .= '<p>' . __('Unable to log-in for you, please ask the site administrator.') . '</p>';
            }

            $html .= '</div>';

            echo $html;

            //自动的跳转JS
            if ($options['login_page_auto_jump_times'] == true):
                ?>
                <script>
                    const waitForSeconds = 5;
                    let waited = 0;

                    const waitElement = document.getElementById("wait-for-secure-login");
                    const secondsElement = document.getElementById("wait-time-seconds");
                    const redirectElement = document.getElementById("redirect-to-secure-login");

                    redirectElement.style.display = "none";

                    document.getElementById("user_login").closest("p").remove();
                    document.getElementById("user_pass").closest(".user-pass-wrap").remove();

                    const uiInterval = setInterval(function () {
                        waited++;
                        const remaining = waitForSeconds - waited;
                        secondsElement.innerText = remaining >= 0 ? remaining + "" : "0";
                        if (remaining <= 0) clearInterval(uiInterval);
                    }, 1000);
                    setTimeout(function () {
                        waitElement.style.display = "none";
                        redirectElement.style.display = "inherit";
                        const href = window.location.href;
                        const hashParts = href.split("#");
                        const connector = hashParts[0].indexOf("?") > 0 ? "&" : "?";
                        window.location.href = hashParts[0] + connector + "auth=<?= $auth_param; ?>" + (hashParts.length > 1 ? "#" + hashParts[1] : "");
                    }, waitForSeconds * 1000);
                </script>
                </form>
                <?php
            else:
                ?>
                <script>
                    document.getElementById("user_login").closest("p").remove();
                    document.getElementById("user_pass").closest(".user-pass-wrap").remove();
                </script>
                </form>
                <?php
            endif;
            //加载原结构
            login_footer();
            //退出
            exit;
        }
        //验证正常
        else {
            //添加一个随机数隐藏段用于验证表单
            wp_nonce_field('secure-login-nonce-action', 'secure-login-nonce');
        }
    }
    //验证登录页面的表单
    public function aya_theme_page_modify_login_action()
    {
        //不是POST且未验证表单隐藏段
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['secure-login-nonce']) || !wp_verify_nonce($_POST['secure-login-nonce'], 'secure-login-nonce-action'))) {
            return self::aya_theme_error_login_action();
        }
    }
    //DEBUG：其他表单兼容性
    public function aya_theme_page_modify_login_form_bottom($content, $args)
    {
        //添加缓冲区忽略不是当前插件添加的表单
        ob_start();

        wp_nonce_field('secure-login-nonce-action', 'secure-login-nonce');

        $field = ob_get_contents();

        ob_end_clean();

        return $content . $field;
    }
    //创建登录框动态
    public function aya_theme_logged_shake_error_codes($error_codes)
    {
        $error_codes[] = 'login_too_many_retries';
        $error_codes[] = 'login_username_not_access';

        return $error_codes;
    }

    //返回登录报错
    public function aya_theme_error_login_action()
    {
        $message = __('Sorry, this feels not very secure.');
        $title = __('Access was denied.');
        $args = array(
            'response' => 403,
            "link_text" => __("Login form"),
            "link_url" => wp_login_url(),
        );

        wp_die($message, $title, $args);

        exit;
    }
}
