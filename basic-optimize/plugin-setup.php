<?php

if (!defined('ABSPATH'))
    exit;

/**
 * AIYA-CMS Theme Options Framework 组件功能
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/

 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.4
 **/


if (!class_exists('AYA_Plugin_Setup')) {
    class AYA_Plugin_Setup extends AYA_Framework_Setup
    {
        private static $include_once = null;
        private static $plugin_array = array();

        //实例化方法
        public static function action($plugin_name, $args = false, $slug = null)
        {
            // 如果没有传入别名，按照原本的模块方法启动
            if (empty($slug)) {
                AYF::module($plugin_name, $args);

                return;
            }

            $fetch_args = self::_action_plugin_opt($args, $slug);

            AYF::module($plugin_name, $fetch_args);
        }

        //插件功能转换参数
        private static function _action_plugin_opt($field_array, $opt_sulg)
        {
            if (!is_array($field_array))
                return;

            $action_array = array();

            //遍历
            foreach ($field_array as $field) {
                //跳过
                if (empty($field['id'])) {
                    continue;
                }
                //验证选项布尔型
                if ($field['type'] === 'switch') {
                    $action_array[$field['id']] = AYF::get_checked($field['id'], $opt_sulg);
                } else {
                    $action_array[$field['id']] = AYF::get_opt($field['id'], $opt_sulg);
                }
            }

            //数组打印工具
            //print_r($action_array);
            //self::_print_plugin_action($field_array);
            //返回
            return $action_array;
        }

        //打印当前设置表单
        private static function _print_plugin_action($field_array)
        {
            if (!is_array($field_array))
                return;

            $setting_array = array();
            $i = 0;
            foreach ($field_array as $field) {
                $i++;
                if (empty($field['id'])) {
                    continue;
                }
                $setting_array[$i] = $field['id'] . '/' . $field['title'] . '/' . $field['desc'];
            }

            print_r($setting_array);
        }

        //Include方法
        public static function include_plugins()
        {
            if (is_null(self::$include_once)) {
                //根目录
                $plugin_dir = (__DIR__) . '/inc';
                //遍历
                foreach (glob($plugin_dir . '/*.php') as $plugin_file) {
                    //验证文件是否存在
                    if (!is_file($plugin_file)) {
                        //print('Error File: ' . $plugin_file);
                        continue;
                    }

                    include $plugin_file;
                }
                //标记已加载
                self::$include_once = true;
            }
        }
    }
}
