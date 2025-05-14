<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIYA-Framework 拓展 WP搜索和主查询SQL查询优化插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.5
 **/

class AYA_Plugin_UA_Firewall
{
    public $firewall_options;

    public function __construct($args)
    {
        $this->firewall_options = $args;
    }

    public function __destruct()
    {
        add_action('init', array($this, 'aya_block_reject_url_rewind'));
        add_action('init', array($this, 'aya_block_reject_user_agent'));
        add_action('init', array($this, 'aya_block_reject_ips'));
    }

    //验证访问URL参数
    public function aya_block_reject_url_rewind()
    {
        $options = $this->firewall_options;
        //获取设置
        if ($options['waf_reject_argument_switch'] == true) {
            //获取屏蔽参数列表
            if ($options['waf_reject_argument_list'] != '') {
                //重建数组
                $key_list = explode(',', $options['waf_reject_argument_list']);
                $key_list = array_map('trim', $key_list);
            }

            $key_list = (empty($key_list)) ? array() : $key_list;

            if (count($key_list) > 0) {
                //循环
                foreach ($key_list as $key) {
                    //获取请求参数
                    if (isset($_GET[$key])) {
                        //返回报错
                        return self::aya_theme_error_rewind_url_reject();
                    }
                }
            }
        }
    }

    //验证访问UA
    public function aya_block_reject_user_agent()
    {
        $options = $this->firewall_options;

        //获取设置
        if ($options['waf_reject_useragent_switch'] == true) {
            //获取UA信息
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            //禁止空UA
            if ($options['waf_reject_useragent_empty'] == true) {
                //不存在则返回报错
                if (!$user_agent)
                    return self::aya_theme_error_rewind_ua_reject();
            }
            //UA信息转为小写
            $user_agent = strtolower($user_agent);
            //获取UA黑名单
            if ($options['waf_reject_useragent_list'] != '') {
                //重建数组
                $ua_black_list = explode(',', $options['waf_reject_useragent_list']);
                $ua_black_list = array_map('trim', $ua_black_list);
            }

            $ua_black_list = (empty($ua_black_list)) ? array() : $ua_black_list;

            if (count($ua_black_list) > 0) {
                //循环
                foreach ($ua_black_list as $black_ua) {
                    //判断是否是数组中存在的UA
                    if (strpos($user_agent, strtolower($black_ua)) !== false) {
                        //返回报错
                        return self::aya_theme_error_rewind_ua_reject();
                    }
                }
            }
        }
    }

    //验证访问IP
    public function aya_block_reject_ips()
    {
        $options = $this->firewall_options;

        //获取设置
        if ($options['waf_reject_ips_switch'] == true) {
            //获取IP
            $ip = $_SERVER['REMOTE_ADDR'];
            $ip = trim($ip);
            //获取UA黑名单
            if ($options['waf_reject_ip_list'] != '') {
                $ip_black_list = explode("\n", $options['waf_reject_ip_list']);
                $ip_black_list = array_map('trim', $ip_black_list);
            }

            $ip_black_list = (empty($ip_black_list)) ? array() : $ip_black_list;

            if (count($ip_black_list) > 0) {

                //声明用于计算IP段的匿名方法

                //匹配CIDR
                $ip_in_cidr = function ($ip, $cidr) {
                    list($subnet, $mask) = explode('/', $cidr);
                    return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet);
                };

                //匹配通配符
                $ip_in_wildcard = function ($ip, $wildcard) {
                    $pattern = str_replace(['.', '*'], ['\.', '\d+'], $wildcard);
                    return preg_match('/^' . $pattern . '$/', $ip);
                };

                //匹配范围
                $ip_in_range = function ($ip, $range) {
                    list($start, $end) = explode('-', $range);
                    $ip_long = ip2long($ip);
                    return $ip_long >= ip2long($start) && $ip_long <= ip2long($end);
                };

                //循环
                foreach ($ip_black_list as $black_ip) {

                    $is_black = false;
                    $black_ip = trim($black_ip);

                    if (!$black_ip) {
                        continue;
                    }

                    //判断是否是数组中存在的IP
                    if (strpos($black_ip, '/') !== false) {
                        if ($ip_in_cidr($ip, $black_ip)) {
                            $is_black = true;
                        }
                    } elseif (strpos($black_ip, '*') !== false) {
                        if ($ip_in_wildcard($ip, $black_ip)) {
                            $is_black = true;
                        }
                    } elseif (strpos($black_ip, '-') !== false) {
                        if ($ip_in_range($ip, $black_ip)) {
                            $is_black = true;
                        }
                    } else {
                        if ($ip === $black_ip) {
                            $is_black = true;
                        }
                    }

                    //返回报错
                    if ($is_black) {
                        return self::aya_theme_error_rewind_ua_reject();
                    }
                }
            }
        }
    }

    //


    //返回参数非法报错
    public function aya_theme_error_rewind_url_reject()
    {
        $message = __('The URL carries unlawful args.');
        $title = __('Access was denied.');
        $args = array(
            'response' => 403,
            'link_url' => home_url('/'),
            'link_text' => __('Return Homepage'),
            'back_link' => false,
        );

        wp_die($message, $title, $args);

        exit;
    }
    //返回UA非法报错
    public function aya_theme_error_rewind_ua_reject()
    {
        $message = __('The current userAgent or IP is disabled by the site administrator.');
        $title = __('Access was denied.');
        $args = array(
            'response' => 403,
            'back_link' => false,
        );

        wp_die($message, $title, $args);

        exit;
    }
}