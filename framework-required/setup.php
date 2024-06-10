<?php

/**
 * Plugin Name: AIYA-Framework
 * Plugin URI: https://www.yeraph.com/
 * Description: A framework of options provided for AIYA-CMS theme.
 * Version: 1.1
 * Author: Yeraph Studio
 * Author URI: https://www.yeraph.com/
 * License: GPLv3 or later
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

//引入设置框架
require_once plugin_dir_path(__FILE__) . '/framework-setup.php';

/**
 * 封装好的Framework创建方法
 * 
 * 方法 new_opt($conf) 合并$inst和$field参数创建设置页面
 * 方法 new_tex($conf) 合并$inst和$field参数创建分类Metabox键
 * 方法 new_opt($conf) 合并$inst和$field参数创建文章Metabox键
 * 方法 get_opt($name, $inst) 获取设置页面下的设置内容
 * 方法 get_checked($name, $inst) 先判断get_opt方法，然后返回的值是否为选中状态
 * 方法 out_opt($name, $inst) 直接输出get_opt方法返回的内容
 * 方法 out_checked($name, $inst, $output) 先判断get_opt方法，然后输出$output（用于直接输出内容）
 */

if (!class_exists('AYF')) {
    class AYF extends AYA_Framework_Setup
    {
        private static $def_inst = 'aya_option';

        public function __construct()
        {
            parent::instance();

            parent::__construct();
        }
        //简化调用
        public static function new_opt($conf = array())
        {
            if ($conf == array()) return;

            //创建设置页面参数
            $inst = array(
                'title' => (empty($conf['title']) ? __('Settings') : $conf['title']),
                'slug' => (empty($conf['slug'])) ? self::$def_inst : self::$def_inst . '_' . $conf['slug'],
                'icon' => (empty($conf['icon'])) ? '' : $conf['icon'],
                'parent' => (empty($conf['parent'])) ? '' : self::$def_inst . '_' . $conf['parent'],
                'desc' => (empty($conf['desc'])) ? '' : $conf['desc'],
            );
            $field_conf = $conf['fields'];

            new AYA_Framework_Options_Page($field_conf, $inst);
        }
        public static function new_tex($conf = array(), $inst = array())
        {
            if ($conf == array()) return;

            new AYA_Framework_Term_Meta($conf, $inst);
        }
        public static function new_box($conf = array(), $inst = array())
        {
            if ($conf == array()) return;

            new AYA_Framework_Post_Meta($conf, $inst);
        }
        //检查是否选择
        public static function get_checked($name, $opt_inst = '', $default = false)
        {
            $value = self::get_opt($name, $opt_inst);

            if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
                return true;
            }
            if ($value === null) {
                return $default;
            }
            return false;
        }
        //提取设置
        public static function get_opt($name, $opt_inst = '', $default = null)
        {
            if ($opt_inst != '') {
                $config = get_option('aya_opt_' . self::$def_inst . '_' . $opt_inst);
            } else {
                $config = get_option('aya_opt_' . self::$def_inst);
            }

            if ($config && isset($config[$name])) {
                return $config[$name];
            }
            return $default;
        }
        //判断输出
        public static function out_checked($name, $opt_inst = '', $output = '', $default = null)
        {
            if (self::get_checked($name, $opt_inst, $default)) {
                echo $output;
            }
        }
        //直接输出
        public static function out_opt($name, $opt_inst = '', $default = null)
        {
            $output = self::get_opt($name, $opt_inst, $default);
            if ($output != null) {
                echo $output;
            }
        }
    }
}
