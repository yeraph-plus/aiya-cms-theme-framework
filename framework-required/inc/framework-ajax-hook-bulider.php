<?php
if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework AJAX方法构造器
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

/*
//Ajax Demo
class AYA_Ajax_Demo extends AYA_Ajax
{
    function ajax_action()
    {
        $action = array(
            'name' => 'ajax_demo',
            'callback_func' => array($this, 'ajax_callback'),
            'public' => true,
            'create_nonce' => 'ajax_demo_nonce',
        );

        return $action;
    }
    public function ajax_callback()
    {
        parent::check_ajax_referer();

        echo 'Hello World!';

        die();
    }
}
*/

if (!class_exists('AYA_Ajax')) {
    abstract class AYA_Ajax
    {
        public $action_name;
        public $callback_func;

        public $public_access;
        public $create_nonce;
        public $nonce_name;

        abstract public function ajax_action();

        public function __construct()
        {
            $action = $this->ajax_action();

            $this->action_name = $action['name'];
            $this->callback_func = $action['callback_func'];
            $this->public_access = $action['public'];
            $this->create_nonce = $action['create_nonce'];

            $this->nonce_name = 'security';
        }
        public function __destruct()
        {
            //注册异步方法
            add_action('wp_ajax_' . $this->action_name, $this->callback_func);

            if ($this->public_access) {
                add_action('wp_ajax_nopriv_' . $this->action_name, $this->callback_func);
            }
        }

        //验证AJAX请求字段
        protected function check_ajax_referer()
        {
            //Tips: POST请求时读取变量为 aya_ajax.url
            if (!empty($this->create_nonce)) {
                check_ajax_referer($this->create_nonce, $this->nonce_name);
            }
        }

        //获取验证字段
        protected function get_ajax_referer($echo = true)
        {
            //返回隐藏的表单
            return wp_nonce_field($this->create_nonce, $this->nonce_name, true, $echo);
        }

        //结构化AJAX请求URL
        protected function get_action_url($action, $args = array())
        {
            $url = admin_url('admin-ajax.php?action=') . $action;

            if (!empty($this->create_nonce)) {
                $url .= '&' . $this->nonce_name . '=' . wp_create_nonce($this->create_nonce);
            }

            if (!empty($args)) {
                $url .= '&' . http_build_query($args);
            }

            return $url;
        }

        //注册URL变量
        public static function localize()
        {
            //Tips: POST请求时读取变量为 aya_local.ajax_url
            add_action('wp_enqueue_scripts', wp_localize_script(
                'aya-local-url',
                'aya_local',
                array(
                    'home_url' => home_url(),
                    'ajax_url' => admin_url('admin-ajax.php'),
                )
            ));
        }

        //访问原始 POST 数据
        public static function get_req_body()
        {
            //使用PHP伪协议方法
            $body = @file_get_contents('php://input');

            return json_decode($body, true);
        }
    }
}
