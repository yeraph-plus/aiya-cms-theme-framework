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
        private $routes = array();

        private $base_namespace;

        //初始化
        public function __construct($namespace)
        {
            //命名空间参数
            $this->base_namespace = $namespace;
        }

        //注册路由
        public function register_route($endpoint, $params = array())
        {
            //处理入参
            $default_param = [
                'methods' => 'GET',
                'callback' => null,
                'permission_callback' => '__return_true',
                'args' => []
            ];
            $params = wp_parse_args($params, $default_param);

            if (!empty($params['args'])) {
                $params['args'] = $this->process_arguments($params['args']);
            }
            //匿名方法传递
            add_action('rest_api_init', function () use ($endpoint, $params) {
                //注册
                register_rest_route($this->base_namespace, '/' . $endpoint, $params);
            });
        }
        //报错处理
        public function error_response($error_key, $additional_data = array())
        {
            //错误信息模板
            $default_errors_group = array(
                'invalid_param' => [
                    'code' => 'invalid_parameter',
                    'message' => __('参数验证失败', 'AIYA_FRAMEWORK'),
                    'status' => 400
                ],
                'permission_denied' => [
                    'code' => 'forbidden',
                    'message' => __('没有访问权限', 'AIYA_FRAMEWORK'),
                    'status' => 403
                ],
                'not_found' => [
                    'code' => 'not_found',
                    'message' => __('资源不存在', 'AIYA_FRAMEWORK'),
                    'status' => 404
                ]
            );

            $error = $default_errors_group[$error_key] ?? array(
                'code' => 'unknown_error',
                'message' => '未知错误',
                'status' => 500
            );

            return new WP_Error(
                $error['code'],
                $error['message'],
                array_merge(
                    ['status' => $error['status']],
                    $additional_data
                )
            );
        }
        //响应成功
        public function response($data, $status = 200)
        {
            return new WP_REST_Response([
                'success' => true,
                'data' => $data
            ], $status);
        }
        //处理参数定义
        private function process_arguments($args)
        {
            $processed = [];

            foreach ($args as $param => $settings) {
                $processed[$param] = wp_parse_args($settings, [
                    'required' => false,
                    'type' => 'string',
                    'validate_callback' => null,
                    'sanitize_callback' => null,
                ]);
                //类型验证
                if (!$processed[$param]['validate_callback']) {
                    $processed[$param]['validate_callback'] = function ($value) use ($processed, $param) {
                        return $this->validate_type(
                            $value,
                            $processed[$param]['type']
                        );
                    };
                }
                //数据清理
                if (!$processed[$param]['sanitize_callback']) {
                    //如果没有设置清理函数，则使用默认的
                    if ($processed[$param]['type'] === 'max_length') {
                        $processed[$param]['sanitize_callback'] = function ($value) use ($processed, $param) {
                            return $this->sanitize_type(
                                $value,
                                $processed[$param]['type'],
                                $processed[$param]['max_length']
                            );
                        };
                    }
                }
            }

            return $processed;
        }
        //自动类型验证
        private function validate_type($value, $type)
        {
            switch ($type) {
                case 'int':
                    return is_int($value);
                case 'numeric':
                    return is_numeric($value);
                case 'bool':
                    return is_bool($value);
                case 'array':
                    return is_array($value);
                case 'not_null':
                    return !is_null(trim($value));
                case 'string':
                default:
                    return is_string($value);
            }
        }
        //自动数据清理
        private function sanitize_type($value, $type, $need_length = 400)
        {
            switch ($type) {
                case 'max_length':
                    $value = wp_strip_all_tags($value);
                    return substr($value, 0, $need_length);
                case 'int':
                    return intval($value);
                case 'numeric':
                    return floatval($value);
                case 'bool':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                case 'array':
                    return (array) $value;
                case 'html':
                    return wp_kses_post($value);
                case 'string':
                    return sanitize_text_field($value);
                default:
                    return trim($value);
            }
        }
    }
}