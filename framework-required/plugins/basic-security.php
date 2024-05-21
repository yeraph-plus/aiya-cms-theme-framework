<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Theme_Setup')) exit;

class AYA_Plugin_Security extends AYA_Theme_Setup
{
    public $security_options;

    public function __construct($args)
    {
        $this->security_options = $args;
    }

    public function __destruct()
    {
        parent::add_action('admin_init', 'aya_theme_admin_backend_verify');
        parent::add_filter('allow_password_reset', 'aya_theme_disallow_password_reset', 10, 2);

        parent::add_action('init', 'aya_theme_rewind_reject');

        parent::add_action('wp_login_failed', 'aya_theme_logged_scope_limit');
        parent::add_filter('authenticate', 'aya_theme_logged_scope_limit_verify', 1, 3);
        parent::add_filter('shake_error_codes', 'aya_theme_logged_shake_error_codes');
    }
    //限制后台访问
    public function aya_theme_admin_backend_verify()
    {

        if (!is_admin()) return;

        //DEBUG：跳过AJAX请求防止影响第三方登录方式
        if ($_SERVER['PHP_SELF'] == '/wp-admin/admin-ajax.php') return;

        $options = $this->security_options;

        if (empty($options['admin_backend_verify'])) $verify = true;

        //检查登录用户权限
        switch ($options['admin_backend_verify']) {
                //case 'administrator':
                //$verify = current_user_can('manage_options');
                //break;
            case 'editor':
                $verify = (current_user_can('publish_pages')  && !current_user_can('manage_options'));
                break;
            case 'author':
                $verify = (current_user_can('publish_posts')  &&  !current_user_can('publish_pages'));
                break;
            case 'contributor':
                $verify = (current_user_can('edit_posts')  &&  !current_user_can('publish_posts'));
                break;
            case 'subscriber':
                $verify = (current_user_can('read')  && !current_user_can('edit_posts'));
                break;
            default:
                $verify = true;
                break;
        }
        //重定向
        if ($verify == false) {
            wp_redirect('/');
        }
    }
    //限制特定权限用户修改密码
    public function aya_theme_disallow_password_reset($allow, $user)
    {
        if (!$allow) return false;

        $options = $this->security_options;

        if ($options['disable_admin_allow_password_reset'] == true) {

            //验证用户角色
            $user = get_userdata($user);

            if (in_array('administrator', $user->roles)) {
                return false;
            }
        }

        return true;
    }
    //根据IP计数登录次数
    public function aya_theme_logged_scope_limit($username)
    {
        $options = $this->security_options;

        if ($options['logged_scope_limit_enable'] == true) {
            //验证参数
            $scope = $options['logged_scope_limit_times'];
            $scope = (is_numeric($scope)) ? (intval($scope)) : 15;

            //获取IP
            $key = $_SERVER['REMOTE_ADDR'] ?? '';

            $times = wp_cache_get($key, 'aya_login_limit');

            $times = $times ?: 0;

            wp_cache_set($key, $times + 1, 'aya_login_limit', MINUTE_IN_SECONDS * $scope);
        }
    }
    //验证登录次数
    public function aya_theme_logged_scope_limit_verify($user, $username, $password)
    {
        $options = $this->security_options;

        if ($options['logged_scope_limit_enable'] == true) {
            //验证参数
            $scope = $options['logged_scope_limit_times'];
            $scope = (is_numeric($scope)) ? (intval($scope)) : 15;

            //获取IP
            $key = $_SERVER['REMOTE_ADDR'] ?? '';

            $times = wp_cache_get($key, 'aya_login_limit');
            $times = $times ?: 0;

            //进行攻防时间
            if ($times > 5) {
                remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
                remove_filter('authenticate', 'wp_authenticate_email_password', 20, 3);

                $err_id = 'login_too_many_retries';
                $err_msg = '你已尝试多次失败登录，请分钟' . $scope . '后重试！';

                return new WP_Error($err_id, $err_msg);
            }

            return $user;
        }
    }
    //创建登录框动态
    public function aya_theme_logged_shake_error_codes($error_codes)
    {
        $error_codes[] = 'login_too_many_retries';
        return $error_codes;
    }
    //验证当前URL参数
    public function aya_theme_rewind_reject()
    {
        $options = $this->security_options;
        //获取设置
        if ($options['add_access_reject_switch'] == true) {
            $key_list = $options['add_access_reject_list'];
            $key_list = (empty($key_list)) ? array() : explode(',', $key_list);
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
}
