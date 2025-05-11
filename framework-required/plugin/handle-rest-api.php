<?php

if (!defined('ABSPATH')) {
    exit;
}


/**
 * AIYA-Framework 组件 创建自定义 REST API 端点
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/


if (!class_exists('AYA_WP_REST_API')) {
    class AYA_WP_REST_API
    {
        private static $rest_routes;

        private $base_url_prefix;
        private $base_namespace;

        //路径参数
        public function __construct($args)
        {
            if (!is_array($args))
                return;

            $this->base_url_prefix = $args['prefix'];
            $this->base_namespace = $args['namespace'];
        }
        //初始化
        public function __destruct()
        {
            if (isset($this->base_url_prefix)) {
                //使用自定义的API路径
                add_filter('rest_url_prefix', function () {
                    return $this->base_url_prefix;
                });
            }

            add_action('rest_api_init', array($this, 'register_rest_route'));
        }
        //通过类属性存储路由结构
        public function add_api($endpoint, $method, $callback, $permission_callback, $args)
        {
            $new_api = array(
                'method' => $method, //get & post
                'callback' => $callback,
                'permission_callback' => isset($args['permission_callback']) ? $args['permission_callback'] : '__return_true',
            );

            //添加类属性
            $this->rest_routes[$endpoint] = $new_api;
        }

        //注册路由 
        public function register_rest_route()
        {
            //循环
            foreach ($this->rest_routes as $route => $params) {
                //注册GET方法
                if ($params['method'] === 'get') {
                    register_rest_route(
                        $this->base_namespace,
                        '/' . $route,
                        array(
                            'methods' => WP_REST_Server::READABLE,
                            'callback' => $params['callback'],
                            'permission_callback' => $params['permission_callback'],
                        )
                    );
                }
                //注册POST方法
                else if ($params['method'] === 'post') {
                    //注册POST方法
                    register_rest_route(
                        $this->base_namespace,
                        '/' . $route,
                        array(
                            'methods' => WP_REST_Server::CREATABLE,
                            'callback' => $params['callback'],
                            'permission_callback' => $params['permission_callback'],
                            'args' => $params['args'],
                        )
                    );
                }
                //跳出
                else {
                    continue;
                }
            }
        }
        public function handle_rest_request(WP_REST_Request $request)
        {
            return call_user_func($this->callback_func, $request);
        }
        //报错处理
        public function errot_response($error_key, $additional_data = [])
        {
            $error = $this->error_messages[$error_key] ?? [
                'code' => 'unknown_error',
                'message' => '未知错误',
                'status' => 500
            ];

            return new WP_Error(
                $error['code'],
                $error['message'],
                array_merge(
                    ['status' => $error['status']],
                    $additional_data
                )
            );
        }
    }
}